import { prisma } from './db'

const cache = new Map<string, string | null>()

export async function getSetting(key: string, fallback?: string): Promise<string | null> {
  if (cache.has(key)) return cache.get(key) ?? fallback ?? null
  const row = await prisma.appSetting.findUnique({ where: { key } })
  const value = row?.value ?? null
  cache.set(key, value)
  return value ?? fallback ?? null
}

export async function getSettingJson<T = unknown>(key: string, fallback: T): Promise<T> {
  const raw = await getSetting(key)
  if (!raw) return fallback
  try {
    return JSON.parse(raw) as T
  } catch {
    return fallback
  }
}

export async function setSetting(key: string, value: string): Promise<void> {
  await prisma.appSetting.upsert({
    where: { key },
    create: { key, value },
    update: { value },
  })
  cache.set(key, value)
}

export function clearSettingCache(key?: string): void {
  if (key) cache.delete(key)
  else cache.clear()
}

export const SETTING_KEYS = {
  cardOmbudsmanAgents: 'card_ombudsman_agents',
  cardTicketOrigins: 'card_ticket_origins',
  cardResponsibleTeams: 'card_responsible_teams',
  customerTiers: 'customer_tiers',
  customerSegments: 'customer_segments',
  customerLookupUrl: 'customer_lookup_url',
  chatLookupUrl: 'chat_lookup_url',
} as const
