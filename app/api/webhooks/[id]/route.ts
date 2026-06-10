import { prisma } from '@/lib/db'
import { z } from 'zod/v4'

const VALID_TRIGGERS = [
  'card.created', 'card.updated', 'card.finished', 'card.deleted',
  'customer.created', 'customer.updated', 'customer.deleted', '*',
]

const updateSchema = z.object({
  name: z.string().min(1).max(100).optional(),
  url: z.url().max(2048).optional(),
  triggerTypes: z.array(z.string()).min(1).optional(),
  description: z.string().optional().nullable(),
  isActive: z.boolean().optional(),
})

const safeSelect = {
  id: true, name: true, url: true, triggerTypes: true,
  isActive: true, description: true, createdBy: true, createdAt: true, updatedAt: true,
}

export async function GET(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const sub = await prisma.webhookSubscription.findFirst({
    where: { id: Number(id), deletedAt: null },
    select: safeSelect,
  })
  if (!sub) return Response.json({ message: 'Not found.' }, { status: 404 })
  return Response.json(sub)
}

export async function PUT(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const sub = await prisma.webhookSubscription.findFirst({
    where: { id: Number(id), deletedAt: null },
  })
  if (!sub) return Response.json({ message: 'Not found.' }, { status: 404 })

  const body = await request.json()
  const parsed = updateSchema.safeParse(body)
  if (!parsed.success) return Response.json({ errors: parsed.error.issues }, { status: 422 })

  if (parsed.data.triggerTypes) {
    const invalid = parsed.data.triggerTypes.filter((t) => !VALID_TRIGGERS.includes(t))
    if (invalid.length > 0) {
      return Response.json({ message: `Invalid trigger types: ${invalid.join(', ')}` }, { status: 422 })
    }
  }

  const updated = await prisma.webhookSubscription.update({
    where: { id: Number(id) },
    data: {
      ...parsed.data,
      triggerTypes: parsed.data.triggerTypes
        ? [...new Set(parsed.data.triggerTypes)]
        : undefined,
    },
    select: safeSelect,
  })

  return Response.json(updated)
}

export async function DELETE(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const sub = await prisma.webhookSubscription.findFirst({
    where: { id: Number(id), deletedAt: null },
  })
  if (!sub) return Response.json({ message: 'Not found.' }, { status: 404 })

  await prisma.webhookSubscription.update({
    where: { id: Number(id) },
    data: { deletedAt: new Date() },
  })

  return new Response(null, { status: 204 })
}
