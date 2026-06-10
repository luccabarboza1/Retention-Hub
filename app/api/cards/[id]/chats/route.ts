import { prisma } from '@/lib/db'
import { getActor, withAudit } from '@/lib/audit'
import { z } from 'zod/v4'

const createSchema = z.object({
  id: z.string().min(1),
  startedAt: z.string().optional().nullable(),
  closedAt: z.string().optional().nullable(),
  firstResponseHours: z.number().min(0).optional().nullable(),
})

export async function GET(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const chats = await prisma.chat.findMany({
    where: { ombudsmanCardId: Number(id), deletedAt: null },
    include: { interactions: true },
    orderBy: { createdAt: 'desc' },
  })
  return Response.json(chats)
}

export async function POST(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const card = await prisma.card.findFirst({ where: { id: Number(id), deletedAt: null } })
  if (!card) return Response.json({ message: 'Not found.' }, { status: 404 })

  const body = await request.json()
  const parsed = createSchema.safeParse(body)
  if (!parsed.success) return Response.json({ errors: parsed.error.issues }, { status: 422 })

  const actor = getActor(request)
  const chat = await prisma.chat.create({
    data: withAudit(
      {
        id: parsed.data.id,
        ombudsmanCardId: Number(id),
        startedAt: parsed.data.startedAt ? new Date(parsed.data.startedAt) : null,
        closedAt: parsed.data.closedAt ? new Date(parsed.data.closedAt) : null,
        firstResponseHours: parsed.data.firstResponseHours,
      },
      actor
    ),
  })

  return Response.json(chat, { status: 201 })
}
