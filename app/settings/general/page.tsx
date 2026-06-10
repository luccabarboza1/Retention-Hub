import { getSetting, getSettingJson, SETTING_KEYS } from '@/lib/app-settings'
import { prisma } from '@/lib/db'
import { GeneralSettingsClient } from './GeneralSettingsClient'

export default async function GeneralSettingsPage() {
  const [agents, origins, teams, tiers, segments, columns, lookupUrl, chatLookupUrl] = await Promise.all([
    getSettingJson<string[]>(SETTING_KEYS.cardOmbudsmanAgents, []),
    getSettingJson<string[]>(SETTING_KEYS.cardTicketOrigins, []),
    getSettingJson<string[]>(SETTING_KEYS.cardResponsibleTeams, []),
    getSettingJson<string[]>(SETTING_KEYS.customerTiers, []),
    getSettingJson<string[]>(SETTING_KEYS.customerSegments, []),
    prisma.kanbanColumn.findMany({ orderBy: { order: 'asc' } }),
    getSetting(SETTING_KEYS.customerLookupUrl, ''),
    getSetting(SETTING_KEYS.chatLookupUrl, ''),
  ])

  return (
    <GeneralSettingsClient
      initialAgents={agents}
      initialOrigins={origins}
      initialTeams={teams}
      initialTiers={tiers}
      initialSegments={segments}
      initialColumns={columns.map((c) => ({ id: c.id, name: c.name, color: c.color, type: c.type }))}
      initialLookupUrl={lookupUrl ?? ''}
      initialChatLookupUrl={chatLookupUrl ?? ''}
    />
  )
}
