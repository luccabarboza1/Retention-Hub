import { prisma } from '@/lib/db'
import { getActor, withAudit } from '@/lib/audit'
import { syncCardTags } from '@/lib/tags'
import { fireWebhookEvent } from '@/lib/webhooks'
import { z } from 'zod/v4'

const createSchema = z.object({
  customerId: z.number().int(),
  productId: z.number().int().optional().nullable(),
  startedAt: z.string(),
  status: z.string().max(50).optional(),
  priority: z.enum(['baixa', 'normal', 'alta', 'urgente']).optional(),
  deadlineAt: z.string().optional().nullable(),
  ticketOrigin: z.string().max(100).optional().nullable(),
  ombudsmanAgent: z.string().max(100).optional().nullable(),
  raClaimLink: z.string().max(500).optional().nullable(),
  contactReason: z.string().max(255).optional().nullable(),
  reasonDetails: z.string().optional().nullable(),
  responsibleTeam: z.string().max(100).optional().nullable(),
  appliedSolution: z.string().optional().nullable(),
  tags: z.array(z.string()).optional(),
})

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url)
  const customerId = searchParams.get('customer_id')
  const status = searchParams.get('status')
  const ombudsmanAgent = searchParams.get('ombudsman_agent')

  const where: Record<string, unknown> = { deletedAt: null }
  if (customerId) where.customerId = Number(customerId)
  if (status) where.status = status
  if (ombudsmanAgent) where.ombudsmanAgent = ombudsmanAgent

  const cards = await prisma.card.findMany({
    where,
    include: {
      customer: { select: { id: true, companyName: true, clientName: true } },
      product: { select: { id: true, productType: true, planName: true } },
      tags: { include: { tag: true } },
    },
    orderBy: [{ startedAt: 'desc' }],
  })

  return Response.json(
    cards.map((c) => ({ ...c, tags: c.tags.map((t) => t.tag.name) }))
  )
}

export async function POST(request: Request) {
  const body = await request.json()
  const parsed = createSchema.safeParse(body)
  if (!parsed.success) return Response.json({ errors: parsed.error.issues }, { status: 422 })

  const actor = getActor(request)
  const { tags = [], ...data } = parsed.data

  let status = data.status
  if (!status) {
    const firstColumn = await prisma.kanbanColumn.findFirst({ orderBy: { order: 'asc' } })
    status = firstColumn?.name ?? 'Aberto'
  }

  const card = await prisma.card.create({
    data: withAudit(
      {
        ...data,
        status,
        startedAt: new Date(data.startedAt),
        deadlineAt: data.deadlineAt ? new Date(data.deadlineAt) : null,
      },
      actor
    ),
    include: {
      customer: true,
      product: true,
      tags: { include: { tag: true } },
    },
  })

  await prisma.cardActivityLog.create({
    data: { cardId: card.id, actor, action: 'created' },
  })

  if (tags.length > 0) await syncCardTags(card.id, tags)

  const fresh = await prisma.card.findUniqueOrThrow({
    where: { id: card.id },
    include: {
      customer: { include: { tags: { include: { tag: true } } } },
      product: true,
      tags: { include: { tag: true } },
    },
  })

  await fireWebhookEvent('card.created', card.id, {
    event: 'card.created',
    timestamp: new Date().toISOString(),
    data: {
      ...fresh,
      tags: fresh.tags.map((t) => t.tag.name),
      customer: { ...fresh.customer, tags: fresh.customer.tags.map((t) => t.tag.name) },
    },
  })

  return Response.json({ ...fresh, tags: fresh.tags.map((t) => t.tag.name) }, { status: 201 })
}
