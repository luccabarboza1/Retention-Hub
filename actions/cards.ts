'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { prisma } from '@/lib/db'
import { withAudit } from '@/lib/audit'
import { syncCardTags } from '@/lib/tags'
import { fireWebhookEvent } from '@/lib/webhooks'

export async function getCardCount(customerId: number) {
  const [total, open] = await Promise.all([
    prisma.card.count({ where: { customerId, deletedAt: null } }),
    prisma.card.count({ where: { customerId, deletedAt: null, finishedAt: null } }),
  ])
  return { total, open }
}

export async function getProductsForCustomer(customerId: number) {
  return prisma.product.findMany({
    where: { customerId, deletedAt: null },
    select: { id: true, productType: true, planName: true, externalId: true },
  })
}

export type NewCardChat = {
  id: string
  startedAt?: string | null
  closedAt?: string | null
  firstResponseHours?: number | null
  agents?: string[]
}

export async function createCard(data: {
  customerId: number
  productId?: number | null
  startedAt: string
  priority?: string
  ombudsmanAgent?: string | null
  ticketOrigin?: string | null
  responsibleTeam?: string | null
  contactReason?: string | null
  reasonDetails?: string | null
  deadlineAt?: string | null
  chats?: NewCardChat[]
}) {
  const { chats, ...cardData } = data
  const firstCol = await prisma.kanbanColumn.findFirst({ orderBy: { order: 'asc' } })
  const card = await prisma.card.create({
    data: withAudit(
      {
        ...cardData,
        status: firstCol?.name ?? 'Aberto',
        startedAt: new Date(cardData.startedAt),
        deadlineAt: cardData.deadlineAt ? new Date(cardData.deadlineAt) : null,
      },
      'system'
    ),
    include: {
      customer: { include: { tags: { include: { tag: true } } } },
      product: true,
      tags: { include: { tag: true } },
    },
  })

  await prisma.cardActivityLog.create({
    data: { cardId: card.id, actor: 'system', action: 'created' },
  })

  for (const chat of chats ?? []) {
    const id = chat.id.trim()
    if (!id) continue
    if (await prisma.chat.findUnique({ where: { id } })) continue
    const startedAt = chat.startedAt ? new Date(chat.startedAt) : null
    await prisma.chat.create({
      data: withAudit(
        {
          id,
          ombudsmanCardId: card.id,
          startedAt,
          closedAt: chat.closedAt ? new Date(chat.closedAt) : null,
          firstResponseHours: chat.firstResponseHours ?? null,
        },
        'system'
      ),
    })
    const agents = [...new Set((chat.agents ?? []).map((a) => a.trim()).filter(Boolean))]
    if (agents.length > 0) {
      await prisma.chatAgentInteraction.createMany({
        data: agents.map((agent) => ({ chatId: id, agent, interactedOn: startedAt ?? new Date() })),
        skipDuplicates: true,
      })
    }
  }

  await fireWebhookEvent('card.created', card.id, {
    event: 'card.created',
    timestamp: new Date().toISOString(),
    data: {
      ...card,
      tags: card.tags.map((t) => t.tag.name),
      customer: { ...card.customer, tags: card.customer.tags.map((t) => t.tag.name) },
    },
  })

  redirect(`/cards/${card.id}`)
}

export async function moveCard(cardId: number, status: string, actor = 'system') {
  const card = await prisma.card.findFirst({ where: { id: cardId, deletedAt: null } })
  if (!card) return

  const col = await prisma.kanbanColumn.findFirst({ where: { name: status } })
  const finishedAt =
    col?.type === 'concluido' && !card.finishedAt ? new Date() : card.finishedAt

  await prisma.card.update({
    where: { id: cardId },
    data: { status, finishedAt, updatedBy: actor },
  })

  await prisma.cardActivityLog.create({
    data: { cardId, actor, action: 'status', fromValue: card.status, toValue: status },
  })

  const isFinished = col?.type === 'concluido' && !card.finishedAt
  const fresh = await prisma.card.findUniqueOrThrow({
    where: { id: cardId },
    include: {
      customer: { include: { tags: { include: { tag: true } } } },
      product: true,
      tags: { include: { tag: true } },
    },
  })

  await fireWebhookEvent(isFinished ? 'card.finished' : 'card.updated', cardId, {
    event: isFinished ? 'card.finished' : 'card.updated',
    timestamp: new Date().toISOString(),
    data: {
      ...fresh,
      tags: fresh.tags.map((t) => t.tag.name),
      customer: { ...fresh.customer, tags: fresh.customer.tags.map((t) => t.tag.name) },
    },
  })

  revalidatePath('/')
}

export async function addComment(cardId: number, content: string, author: string) {
  await prisma.cardComment.create({ data: { cardId, content, author } })
  await prisma.cardActivityLog.create({
    data: { cardId, actor: author, action: 'note', toValue: content.slice(0, 100) },
  })
  revalidatePath(`/cards/${cardId}`)
}

export async function deleteComment(commentId: number, cardId: number) {
  await prisma.cardComment.delete({ where: { id: commentId } })
  revalidatePath(`/cards/${cardId}`)
}

export async function addRelated(cardId: number, relatedCardId: number) {
  await prisma.$transaction([
    prisma.relatedCard.upsert({
      where: { cardId_relatedCardId: { cardId, relatedCardId } },
      create: { cardId, relatedCardId },
      update: {},
    }),
    prisma.relatedCard.upsert({
      where: { cardId_relatedCardId: { cardId: relatedCardId, relatedCardId: cardId } },
      create: { cardId: relatedCardId, relatedCardId: cardId },
      update: {},
    }),
  ])
  await prisma.cardActivityLog.create({
    data: { cardId, actor: 'system', action: 'related_added', toValue: String(relatedCardId) },
  })
  revalidatePath(`/cards/${cardId}`)
}

export async function removeRelated(cardId: number, relatedCardId: number) {
  await prisma.$transaction([
    prisma.relatedCard.deleteMany({ where: { cardId, relatedCardId } }),
    prisma.relatedCard.deleteMany({ where: { cardId: relatedCardId, relatedCardId: cardId } }),
  ])
  revalidatePath(`/cards/${cardId}`)
}

export async function updateCard(cardId: number, data: Record<string, unknown>, actor = 'system') {
  const { tags, ...fields } = data

  if ('deadlineAt' in fields) {
    fields.deadlineAt = fields.deadlineAt ? new Date(fields.deadlineAt as string) : null
  }

  await prisma.card.update({
    where: { id: cardId },
    data: { ...fields, updatedBy: actor } as Parameters<typeof prisma.card.update>[0]['data'],
  })

  if (Array.isArray(tags)) {
    await syncCardTags(cardId, tags as string[])
  }

  const fresh = await prisma.card.findUniqueOrThrow({
    where: { id: cardId },
    include: {
      customer: { include: { tags: { include: { tag: true } } } },
      product: true,
      tags: { include: { tag: true } },
    },
  })

  await fireWebhookEvent('card.updated', cardId, {
    event: 'card.updated',
    timestamp: new Date().toISOString(),
    data: {
      ...fresh,
      tags: fresh.tags.map((t) => t.tag.name),
      customer: { ...fresh.customer, tags: fresh.customer.tags.map((t) => t.tag.name) },
    },
  })

  revalidatePath(`/cards/${cardId}`)
}

export async function deleteCard(cardId: number, actor = 'system') {
  const card = await prisma.card.findFirst({ where: { id: cardId, deletedAt: null } })
  if (!card) return

  const now = new Date()
  await prisma.card.update({
    where: { id: cardId },
    data: { deletedAt: now, deletedBy: actor },
  })

  await prisma.cardActivityLog.create({
    data: { cardId, actor, action: 'deleted' },
  })

  await fireWebhookEvent('card.deleted', cardId, {
    event: 'card.deleted',
    timestamp: now.toISOString(),
    deleted_id: cardId,
  })

  redirect('/')
}
