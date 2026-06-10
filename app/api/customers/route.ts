import { prisma } from '@/lib/db'
import { Prisma } from '@prisma/client'
import { getActor, withAudit } from '@/lib/audit'
import { syncCustomerTags } from '@/lib/tags'
import { fireWebhookEvent } from '@/lib/webhooks'
import { z } from 'zod/v4'

const createSchema = z.object({
  clientName: z.string().min(1).max(255),
  companyName: z.string().min(1).max(255),
  email: z.email().optional().nullable(),
  relatedEmails: z.array(z.string()).optional().nullable(),
  monthlyFee: z.number().min(0).optional().nullable(),
  contractedAt: z.string().optional().nullable(),
  canceledAt: z.string().optional().nullable(),
  instagramFollowersCount: z.number().int().min(0).optional(),
  segment: z.string().optional().nullable(),
  companySize: z.string().optional().nullable(),
  tier: z.string().optional().nullable(),
  planName: z.string().optional().nullable(),
  tags: z.array(z.string()).optional(),
})

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url)
  const q = searchParams.get('q')
  const tier = searchParams.get('tier')
  const segment = searchParams.get('segment')

  const where: Record<string, unknown> = { deletedAt: null }

  if (tier) where.tier = tier
  if (segment) where.segment = segment

  if (q && q.length >= 2) {
    where.OR = [
      { companyName: { contains: q, mode: 'insensitive' } },
      { clientName: { contains: q, mode: 'insensitive' } },
      { email: { contains: q, mode: 'insensitive' } },
      { planName: { contains: q, mode: 'insensitive' } },
      { tier: { contains: q, mode: 'insensitive' } },
    ]
  }

  const customers = await prisma.customer.findMany({
    where,
    include: {
      tags: { include: { tag: true } },
    },
    orderBy: { companyName: 'asc' },
  })

  return Response.json(
    customers.map((c) => ({
      ...c,
      tags: c.tags.map((t) => t.tag.name),
    }))
  )
}

export async function POST(request: Request) {
  const body = await request.json()
  const parsed = createSchema.safeParse(body)
  if (!parsed.success) {
    return Response.json({ errors: parsed.error.issues }, { status: 422 })
  }

  const { tags = [], ...data } = parsed.data
  const actor = getActor(request)

  const customer = await prisma.customer.create({
    data: withAudit(
      {
        ...data,
        relatedEmails: data.relatedEmails === null ? Prisma.DbNull : data.relatedEmails,
        contractedAt: data.contractedAt ? new Date(data.contractedAt) : null,
        canceledAt: data.canceledAt ? new Date(data.canceledAt) : null,
      },
      actor
    ),
    include: { tags: { include: { tag: true } }, products: true },
  })

  if (tags.length > 0) {
    await syncCustomerTags(customer.id, tags)
  }

  const fresh = await prisma.customer.findUniqueOrThrow({
    where: { id: customer.id },
    include: { tags: { include: { tag: true } }, products: true },
  })

  await fireWebhookEvent('customer.created', customer.id, {
    event: 'customer.created',
    timestamp: new Date().toISOString(),
    data: { ...fresh, tags: fresh.tags.map((t) => t.tag.name) },
  })

  return Response.json({ ...fresh, tags: fresh.tags.map((t) => t.tag.name) }, { status: 201 })
}
