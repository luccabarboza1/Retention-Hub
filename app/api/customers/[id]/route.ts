import { prisma } from '@/lib/db'
import { Prisma } from '@prisma/client'
import { getActor, withAuditUpdate } from '@/lib/audit'
import { syncCustomerTags } from '@/lib/tags'
import { fireWebhookEvent } from '@/lib/webhooks'
import { z } from 'zod/v4'

const updateSchema = z.object({
  clientName: z.string().min(1).max(255).optional(),
  companyName: z.string().min(1).max(255).optional(),
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

async function getCustomer(id: number) {
  return prisma.customer.findFirst({
    where: { id, deletedAt: null },
    include: {
      tags: { include: { tag: true } },
      products: { where: { deletedAt: null } },
    },
  })
}

export async function GET(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const customer = await getCustomer(Number(id))
  if (!customer) return Response.json({ message: 'Not found.' }, { status: 404 })
  return Response.json({ ...customer, tags: customer.tags.map((t) => t.tag.name) })
}

export async function PUT(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const customer = await getCustomer(Number(id))
  if (!customer) return Response.json({ message: 'Not found.' }, { status: 404 })

  const body = await request.json()
  const parsed = updateSchema.safeParse(body)
  if (!parsed.success) {
    return Response.json({ errors: parsed.error.issues }, { status: 422 })
  }

  const { tags, ...data } = parsed.data
  const actor = getActor(request)

  await prisma.customer.update({
    where: { id: Number(id) },
    data: withAuditUpdate(
      {
        ...data,
        relatedEmails: data.relatedEmails === null ? Prisma.DbNull : data.relatedEmails,
        contractedAt: data.contractedAt !== undefined
          ? data.contractedAt ? new Date(data.contractedAt) : null
          : undefined,
        canceledAt: data.canceledAt !== undefined
          ? data.canceledAt ? new Date(data.canceledAt) : null
          : undefined,
      },
      actor
    ),
  })

  if (tags !== undefined) {
    await syncCustomerTags(Number(id), tags)
  }

  const updated = await getCustomer(Number(id))
  await fireWebhookEvent('customer.updated', Number(id), {
    event: 'customer.updated',
    timestamp: new Date().toISOString(),
    data: { ...updated, tags: updated!.tags.map((t) => t.tag.name) },
  })

  return Response.json({ ...updated, tags: updated!.tags.map((t) => t.tag.name) })
}

export async function DELETE(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const customer = await getCustomer(Number(id))
  if (!customer) return Response.json({ message: 'Not found.' }, { status: 404 })

  const actor = getActor(request)
  const now = new Date()

  await prisma.customer.update({
    where: { id: Number(id) },
    data: { deletedAt: now, deletedBy: actor },
  })

  await fireWebhookEvent('customer.deleted', Number(id), {
    event: 'customer.deleted',
    timestamp: now.toISOString(),
    deleted_id: Number(id),
    data: {
      id: customer.id,
      company_name: customer.companyName,
      client_name: customer.clientName,
      customer_id: customer.id,
      deleted_at: now.toISOString(),
    },
  })

  return new Response(null, { status: 204 })
}
