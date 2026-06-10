import { prisma } from '@/lib/db'
import { getActor } from '@/lib/audit'
import { z } from 'zod/v4'

const VALID_TRIGGERS = [
  'card.created', 'card.updated', 'card.finished', 'card.deleted',
  'customer.created', 'customer.updated', 'customer.deleted', '*',
]

const createSchema = z.object({
  name: z.string().min(1).max(100),
  url: z.url().max(2048),
  triggerTypes: z.array(z.string()).min(1),
  description: z.string().optional().nullable(),
  isActive: z.boolean().optional(),
})

function generateSecret(): string {
  const bytes = crypto.getRandomValues(new Uint8Array(32))
  return Array.from(bytes).map((b) => b.toString(16).padStart(2, '0')).join('')
}

export async function GET() {
  const subscriptions = await prisma.webhookSubscription.findMany({
    where: { deletedAt: null },
    select: {
      id: true, name: true, url: true, triggerTypes: true,
      isActive: true, description: true, createdBy: true, createdAt: true, updatedAt: true,
    },
    orderBy: { createdAt: 'desc' },
  })
  return Response.json(subscriptions)
}

export async function POST(request: Request) {
  const body = await request.json()
  const parsed = createSchema.safeParse(body)
  if (!parsed.success) return Response.json({ errors: parsed.error.issues }, { status: 422 })

  const invalid = (parsed.data.triggerTypes as string[]).filter(
    (t) => !VALID_TRIGGERS.includes(t)
  )
  if (invalid.length > 0) {
    return Response.json({ message: `Invalid trigger types: ${invalid.join(', ')}` }, { status: 422 })
  }

  const actor = getActor(request)
  const secret = generateSecret()
  const triggerTypes = [...new Set(parsed.data.triggerTypes)] as string[]

  const subscription = await prisma.webhookSubscription.create({
    data: {
      name: parsed.data.name,
      url: parsed.data.url,
      triggerTypes,
      secret,
      isActive: parsed.data.isActive ?? true,
      description: parsed.data.description ?? null,
      createdBy: actor,
    },
    select: {
      id: true, name: true, url: true, triggerTypes: true,
      isActive: true, description: true, createdBy: true, createdAt: true,
    },
  })

  return Response.json({ ...subscription, secret }, { status: 201 })
}
