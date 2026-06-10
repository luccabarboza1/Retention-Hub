import { prisma } from '@/lib/db'
import { WebhooksClient } from './WebhooksClient'

export default async function WebhooksSettingsPage() {
  const subscriptions = await prisma.webhookSubscription.findMany({
    where: { deletedAt: null },
    orderBy: { createdAt: 'desc' },
  })

  return (
    <WebhooksClient
      initialSubscriptions={subscriptions.map((s) => ({
        id: s.id,
        name: s.name,
        url: s.url,
        triggerTypes: s.triggerTypes as string[],
        isActive: s.isActive,
        createdAt: s.createdAt.toISOString(),
      }))}
    />
  )
}
