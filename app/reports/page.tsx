import { prisma } from '@/lib/db'
import { getSettingJson, SETTING_KEYS } from '@/lib/app-settings'
import { ReportsClient } from './ReportsClient'

export default async function ReportsPage({
  searchParams,
}: {
  searchParams: Promise<{ from?: string; to?: string; agent?: string; team?: string; status?: string; origin?: string }>
}) {
  const { from, to, agent, team, status, origin } = await searchParams

  const fromDate = from ? new Date(from) : new Date(Date.now() - 30 * 24 * 3600 * 1000)
  const toDate = to ? new Date(to + 'T23:59:59') : new Date()

  const [agents, teams, origins, columns] = await Promise.all([
    getSettingJson<string[]>(SETTING_KEYS.cardOmbudsmanAgents, []),
    getSettingJson<string[]>(SETTING_KEYS.cardResponsibleTeams, []),
    getSettingJson<string[]>(SETTING_KEYS.cardTicketOrigins, []),
    prisma.kanbanColumn.findMany({ orderBy: { order: 'asc' }, select: { name: true } }),
  ])

  const where: Record<string, any> = {
    deletedAt: null as null,
    createdAt: { gte: fromDate, lte: toDate },
    ...(agent ? { ombudsmanAgent: agent } : {}),
    ...(team ? { responsibleTeam: team } : {}),
    ...(status ? { status } : {}),
    ...(origin ? { ticketOrigin: origin } : {}),
  }

  const cards = await prisma.card.findMany({
    where,
    select: {
      id: true,
      status: true,
      priority: true,
      ombudsmanAgent: true,
      responsibleTeam: true,
      contactReason: true,
      finishedAt: true,
      deadlineAt: true,
      rating: true,
      createdAt: true,
      ticketOrigin: true,
      firstResponseHours: true,
      usageTimePostOmbudsmanHours: true,
      customer: { select: { id: true, companyName: true, tier: true } },
      product: { select: { id: true, productType: true } },
    },
    orderBy: { createdAt: 'desc' },
  })

  const serializedCards = cards.map((c) => ({
    ...c,
    firstResponseHours: c.firstResponseHours ? Number(c.firstResponseHours) : null,
    usageTimePostOmbudsmanHours: c.usageTimePostOmbudsmanHours ? Number(c.usageTimePostOmbudsmanHours) : null,
    createdAt: c.createdAt.toISOString(),
    finishedAt: c.finishedAt?.toISOString() ?? null,
    deadlineAt: c.deadlineAt?.toISOString() ?? null,
  }))

  return (
    <ReportsClient
      cards={serializedCards}
      filters={{
        from: fromDate.toISOString().slice(0, 10),
        to: toDate.toISOString().slice(0, 10),
        agent,
        team,
        status,
        origin,
      }}
      options={{
        agents,
        teams,
        origins,
        columns: columns.map((c) => c.name),
      }}
    />
  )
}
