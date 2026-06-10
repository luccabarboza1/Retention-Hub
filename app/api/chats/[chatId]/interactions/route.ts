import { prisma } from '@/lib/db'
import { z } from 'zod/v4'

const schema = z.object({
  agent: z.string().min(1).max(100),
  interactedOn: z.string(),
})

export async function POST(
  request: Request,
  { params }: { params: Promise<{ chatId: string }> }
) {
  const { chatId } = await params
  const chat = await prisma.chat.findFirst({ where: { id: chatId, deletedAt: null } })
  if (!chat) return Response.json({ message: 'Not found.' }, { status: 404 })

  const body = await request.json()
  const parsed = schema.safeParse(body)
  if (!parsed.success) return Response.json({ errors: parsed.error.issues }, { status: 422 })

  const interaction = await prisma.chatAgentInteraction.upsert({
    where: {
      chatId_agent_interactedOn: {
        chatId,
        agent: parsed.data.agent,
        interactedOn: new Date(parsed.data.interactedOn),
      },
    },
    create: {
      chatId,
      agent: parsed.data.agent,
      interactedOn: new Date(parsed.data.interactedOn),
    },
    update: {},
  })

  return Response.json(interaction, { status: 201 })
}
