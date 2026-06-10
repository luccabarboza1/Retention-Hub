import { prisma } from '@/lib/db'
import { dispatchWebhook } from '@/lib/webhooks'

const IMMEDIATE_DISPATCH_GRACE_MS = 2 * 60 * 1000

export async function GET(request: Request) {
  const authHeader = request.headers.get('authorization')
  if (authHeader !== `Bearer ${process.env.CRON_SECRET}`) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 })
  }

  const now = new Date()
  const graceCutoff = new Date(now.getTime() - IMMEDIATE_DISPATCH_GRACE_MS)

  const pending = await prisma.webhookDispatchLog.findMany({
    where: {
      OR: [
        // Backstop: pendentes que o envio imediato não concluiu (função caiu, etc.).
        // Espera a janela de carência pra não disparar em duplicado com o after().
        { status: 'pending', createdAt: { lte: graceCutoff } },
        // Retries agendados por uma tentativa anterior que falhou.
        { status: 'failed', nextRetryAt: { lte: now } },
        { status: 'failed', nextRetryAt: null },
      ],
    },
    include: { subscription: true },
    take: 50,
  })

  await Promise.allSettled(pending.map(dispatchWebhook))

  return Response.json({ processed: pending.length })
}
