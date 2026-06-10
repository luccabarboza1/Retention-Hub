import { prisma } from '@/lib/db'
import { getActor, withAuditUpdate } from '@/lib/audit'
import { z } from 'zod/v4'

const updateSchema = z.object({
  startedAt: z.string().optional().nullable(),
  closedAt: z.string().optional().nullable(),
  firstResponseHours: z.number().min(0).optional().nullable(),
})

export async function GET(_req: Request, { params }: { params: Promise<{ chatId: string }> }) {
  const { chatId } = await params
  const chat = await prisma.chat.findFirst({
    where: { id: chatId, deletedAt: null },
    include: { interactions: true },
  })
  if (!chat) return Response.json({ message: 'Not found.' }, { status: 404 })
  return Response.json(chat)
}

export async function PUT(
  request: Request,
  { params }: { params: Promise<{ chatId: string }> }
) {
  const { chatId } = await params
  const chat = await prisma.chat.findFirst({ where: { id: chatId, deletedAt: null } })
  if (!chat) return Response.json({ message: 'Not found.' }, { status: 404 })

  const body = await request.json()
  const parsed = updateSchema.safeParse(body)
  if (!parsed.success) return Response.json({ errors: parsed.error.issues }, { status: 422 })

  const actor = getActor(request)
  const updated = await prisma.chat.update({
    where: { id: chatId },
    data: withAuditUpdate(
      {
        startedAt: parsed.data.startedAt !== undefined
          ? parsed.data.startedAt ? new Date(parsed.data.startedAt) : null
          : undefined,
        closedAt: parsed.data.closedAt !== undefined
          ? parsed.data.closedAt ? new Date(parsed.data.closedAt) : null
          : undefined,
        firstResponseHours: parsed.data.firstResponseHours,
      },
      actor
    ),
  })

  return Response.json(updated)
}
