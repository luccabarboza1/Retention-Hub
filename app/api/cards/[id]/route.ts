import { prisma } from '@/lib/db'
import { getActor, withAuditUpdate } from '@/lib/audit'
import { syncCardTags } from '@/lib/tags'
import { fireWebhookEvent } from '@/lib/webhooks'
import { z } from 'zod/v4'

const updateSchema = z.object({
  status: z.string().max(50).optional(),
  priority: z.enum(['baixa', 'normal', 'alta', 'urgente']).optional(),
  productId: z.number().int().optional().nullable(),
  deadlineAt: z.string().optional().nullable(),
  ticketOrigin: z.string().max(100).optional().nullable(),
  ombudsmanAgent: z.string().max(100).optional().nullable(),
  raClaimLink: z.string().max(500).optional().nullable(),
  rating: z.number().int().min(1).max(5).optional().nullable(),
  firstResponseHours: z.number().min(0).optional().nullable(),
  raPublicResponseHours: z.number().min(0).optional().nullable(),
  usageTimePostOmbudsmanHours: z.number().min(0).optional().nullable(),
  contactReason: z.string().max(255).optional().nullable(),
  reasonDetails: z.string().optional().nullable(),
  responsibleTeam: z.string().max(100).optional().nullable(),
  appliedSolution: z.string().optional().nullable(),
  tags: z.array(z.string()).optional(),
})

const TRACKED_FIELDS = [
  { key: 'status', action: 'status' },
  { key: 'ombudsmanAgent', action: 'agent' },
  { key: 'priority', action: 'priority' },
  { key: 'contactReason', action: 'contact_reason' },
  { key: 'responsibleTeam', action: 'responsible_team' },
  { key: 'ticketOrigin', action: 'ticket_origin' },
  { key: 'rating', action: 'rating' },
  { key: 'deadlineAt', action: 'deadline_at' },
  { key: 'raClaimLink', action: 'ra_claim_link' },
  { key: 'reasonDetails', action: 'reason_details' },
  { key: 'appliedSolution', action: 'applied_solution' },
] as const

async function getCardFull(id: number) {
  return prisma.card.findFirst({
    where: { id, deletedAt: null },
    include: {
      customer: { include: { tags: { include: { tag: true } } } },
      product: true,
      tags: { include: { tag: true } },
      comments: { orderBy: { createdAt: 'asc' } },
      activityLogs: { orderBy: { createdAt: 'desc' }, take: 50 },
      chats: { where: { deletedAt: null }, orderBy: { createdAt: 'desc' } },
      relatedFrom: {
        include: { related: { include: { customer: { select: { companyName: true } } } } },
      },
    },
  })
}

export async function GET(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const card = await getCardFull(Number(id))
  if (!card) return Response.json({ message: 'Not found.' }, { status: 404 })
  return Response.json({ ...card, tags: card.tags.map((t) => t.tag.name) })
}

export async function PUT(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const card = await prisma.card.findFirst({ where: { id: Number(id), deletedAt: null } })
  if (!card) return Response.json({ message: 'Not found.' }, { status: 404 })

  const body = await request.json()
  const parsed = updateSchema.safeParse(body)
  if (!parsed.success) return Response.json({ errors: parsed.error.issues }, { status: 422 })

  const actor = getActor(request)
  const { tags, ...data } = parsed.data

  // Determine if status is transitioning to a finished column
  let finishedAt = card.finishedAt
  if (data.status && data.status !== card.status) {
    const col = await prisma.kanbanColumn.findFirst({ where: { name: data.status } })
    if (col?.type === 'concluido' && !card.finishedAt) finishedAt = new Date()
  }

  const updated = await prisma.card.update({
    where: { id: Number(id) },
    data: withAuditUpdate(
      {
        ...data,
        finishedAt,
        deadlineAt: data.deadlineAt !== undefined
          ? data.deadlineAt ? new Date(data.deadlineAt) : null
          : undefined,
      },
      actor
    ),
  })

  // Activity log for each changed field
  const logs = TRACKED_FIELDS.filter(({ key }) => {
    if (!(key in data)) return false
    const prev = String(card[key as keyof typeof card] ?? '')
    const next = String(data[key as keyof typeof data] ?? '')
    return prev !== next
  }).map(({ key, action }) => ({
    cardId: Number(id),
    actor,
    action,
    fromValue: String(card[key as keyof typeof card] ?? ''),
    toValue: String(data[key as keyof typeof data] ?? ''),
  }))

  if (logs.length > 0) {
    await prisma.cardActivityLog.createMany({ data: logs })
  }

  if (tags !== undefined) {
    await syncCardTags(Number(id), tags)
    await prisma.cardActivityLog.create({
      data: { cardId: Number(id), actor, action: 'tags', toValue: tags.join(', ') },
    })
  }

  const fresh = await getCardFull(Number(id))
  const isFinished = finishedAt && !card.finishedAt

  await fireWebhookEvent(isFinished ? 'card.finished' : 'card.updated', Number(id), {
    event: isFinished ? 'card.finished' : 'card.updated',
    timestamp: new Date().toISOString(),
    data: {
      ...fresh,
      tags: fresh!.tags.map((t) => t.tag.name),
      customer: { ...fresh!.customer, tags: fresh!.customer.tags.map((t) => t.tag.name) },
    },
  })

  return Response.json({ ...fresh, tags: fresh!.tags.map((t) => t.tag.name) })
}

export async function DELETE(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const card = await prisma.card.findFirst({ where: { id: Number(id), deletedAt: null } })
  if (!card) return Response.json({ message: 'Not found.' }, { status: 404 })

  const actor = getActor(request)
  const now = new Date()

  await prisma.card.update({
    where: { id: Number(id) },
    data: { deletedAt: now, deletedBy: actor },
  })

  await fireWebhookEvent('card.deleted', Number(id), {
    event: 'card.deleted',
    timestamp: now.toISOString(),
    deleted_id: Number(id),
    data: {
      id: card.id,
      contact_reason: card.contactReason,
      status: card.status,
      customer_id: card.customerId,
      deleted_at: now.toISOString(),
    },
  })

  return new Response(null, { status: 204 })
}
