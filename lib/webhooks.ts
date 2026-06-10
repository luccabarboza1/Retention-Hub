import { after } from 'next/server'
import { prisma } from './db'
import type { WebhookSubscription, WebhookDispatchLog } from '@prisma/client'

const RETRY_DELAYS = [30, 60, 120, 240, 300]

type LogWithSubscription = WebhookDispatchLog & { subscription: WebhookSubscription }

async function hmacSha256(data: string, secret: string): Promise<string> {
  const enc = new TextEncoder()
  const key = await crypto.subtle.importKey(
    'raw', enc.encode(secret), { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']
  )
  const sig = await crypto.subtle.sign('HMAC', key, enc.encode(data))
  return Array.from(new Uint8Array(sig)).map((b) => b.toString(16).padStart(2, '0')).join('')
}

export async function dispatchWebhook(log: LogWithSubscription): Promise<void> {
  const payloadJson = JSON.stringify(log.payload)
  const signature = await hmacSha256(payloadJson, log.subscription.secret)
  const dispatchedAt = new Date()
  let httpStatus: number | null = null
  let responseBody: string | null = null
  let errorMessage: string | null = null

  try {
    const res = await fetch(log.targetUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Umbler-Signature': `hmac-sha256=${signature}`,
        'X-Umbler-Event': log.eventType,
      },
      body: payloadJson,
      signal: AbortSignal.timeout(25_000),
    })

    httpStatus = res.status
    responseBody = (await res.text()).slice(0, 4000)

    if (res.ok) {
      await prisma.webhookDispatchLog.update({
        where: { id: log.id },
        data: { status: 'success', httpStatus, responseBody, dispatchedAt, respondedAt: new Date() },
      })
      return
    }
  } catch (e) {
    errorMessage = String(e).slice(0, 1000)
  }

  const nextAttempt = log.attemptNumber + 1
  const isPermanentlyFailed = log.attemptNumber >= log.maxAttempts
  const delayIndex = Math.min(log.attemptNumber - 1, RETRY_DELAYS.length - 1)

  await prisma.webhookDispatchLog.update({
    where: { id: log.id },
    data: {
      status: isPermanentlyFailed ? 'permanently_failed' : 'failed',
      httpStatus,
      responseBody,
      errorMessage,
      attemptNumber: nextAttempt,
      dispatchedAt,
      respondedAt: new Date(),
      nextRetryAt: isPermanentlyFailed
        ? null
        : new Date(Date.now() + RETRY_DELAYS[delayIndex] * 1000),
    },
  })
}

export async function fireWebhookEvent(
  eventType: string,
  entityId: number,
  payload: object
): Promise<void> {
  const subscriptions = await prisma.webhookSubscription.findMany({
    where: { isActive: true, deletedAt: null },
  })

  const matching = subscriptions.filter((s) => {
    const types = s.triggerTypes as string[]
    return types.includes(eventType) || types.includes('*')
  })

  if (matching.length === 0) return

  const logs = await prisma.webhookDispatchLog.createManyAndReturn({
    data: matching.map((sub) => ({
      subscriptionId: sub.id,
      eventType,
      eventEntityId: entityId,
      payload,
      targetUrl: sub.url,
      status: 'pending',
      maxAttempts: 5,
    })),
  })

  const subById = new Map(matching.map((s) => [s.id, s]))
  const withSubscription: LogWithSubscription[] = logs.map((log) => ({
    ...log,
    subscription: subById.get(log.subscriptionId)!,
  }))

  // Envio imediato: roda depois da resposta ser enviada (Fluid Compute mantém a
  // função viva). A linha no outbox já foi gravada, então o cron de backstop
  // reprocessa qualquer disparo que não concluir aqui.
  after(() => Promise.allSettled(withSubscription.map(dispatchWebhook)))
}
