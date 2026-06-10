import { prisma } from '@/lib/db'
import { CardWizard } from '@/components/CardWizard'
import { getSettingJson, SETTING_KEYS } from '@/lib/app-settings'

export default async function CardCreatePage() {
  const [customers, columns, agents, origins, teams, allTags] = await Promise.all([
    prisma.customer.findMany({
      where: { deletedAt: null },
      select: { id: true, companyName: true, clientName: true },
      orderBy: { companyName: 'asc' },
    }),
    prisma.kanbanColumn.findMany({ orderBy: { order: 'asc' } }),
    getSettingJson<string[]>(SETTING_KEYS.cardOmbudsmanAgents, []),
    getSettingJson<string[]>(SETTING_KEYS.cardTicketOrigins, []),
    getSettingJson<string[]>(SETTING_KEYS.cardResponsibleTeams, []),
    prisma.tag.findMany({ where: { type: 'card' }, orderBy: { name: 'asc' } }),
  ])

  return (
    <div className="p-6 max-w-4xl mx-auto space-y-6">
      <h1 className="text-xl font-semibold">Novo Card</h1>
      <CardWizard
        columns={columns}
        customers={customers}
        options={{ agents, origins, teams }}
        allTags={allTags.map((t) => t.name)}
      />
    </div>
  )
}
