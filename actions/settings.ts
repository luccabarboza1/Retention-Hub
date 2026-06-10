'use server'

import { revalidatePath } from 'next/cache'
import { prisma } from '@/lib/db'
import { getSetting, setSetting, clearSettingCache, SETTING_KEYS } from '@/lib/app-settings'

export async function saveCardOptions(type: string, options: string[]) {
  const keyMap: Record<string, string> = {
    agents: SETTING_KEYS.cardOmbudsmanAgents,
    origins: SETTING_KEYS.cardTicketOrigins,
    teams: SETTING_KEYS.cardResponsibleTeams,
  }
  const key = keyMap[type]
  if (!key) throw new Error('Invalid type')
  await setSetting(key, JSON.stringify(options))
  revalidatePath('/settings/general')
}

export async function saveCustomerOptions(type: string, options: string[]) {
  const keyMap: Record<string, string> = {
    tiers: SETTING_KEYS.customerTiers,
    segments: SETTING_KEYS.customerSegments,
  }
  const key = keyMap[type]
  if (!key) throw new Error('Invalid type')
  await setSetting(key, JSON.stringify(options))
  revalidatePath('/settings/general')
}

export async function createTag(name: string, type: 'customer' | 'card') {
  const existing = await prisma.tag.findFirst({
    where: { name: { equals: name, mode: 'insensitive' }, type },
  })
  if (existing) return existing
  const tag = await prisma.tag.create({ data: { name, type } })
  revalidatePath('/settings/tags')
  return tag
}

export async function deleteTag(id: number) {
  await prisma.customerTag.deleteMany({ where: { tagId: id } })
  await prisma.cardTag.deleteMany({ where: { tagId: id } })
  await prisma.tag.delete({ where: { id } })
  revalidatePath('/settings/tags')
}

export async function createTemplate(data: { title: string; body: string; productType?: string | null }) {
  const template = await prisma.solutionTemplate.create({ data })
  revalidatePath('/settings/templates')
  return template
}

export async function updateTemplate(id: number, data: { title: string; body: string; productType?: string | null }) {
  await prisma.solutionTemplate.update({ where: { id }, data })
  revalidatePath('/settings/templates')
}

export async function deleteTemplate(id: number) {
  await prisma.solutionTemplate.delete({ where: { id } })
  revalidatePath('/settings/templates')
}

export async function createProductPlanConfig(data: {
  productType: string; planName: string; pricePerUnit: number; unitLabel: string
}) {
  const config = await prisma.productPlanConfig.create({ data })
  revalidatePath('/settings/products')
  return config
}

export async function updateProductPlanConfig(id: number, data: {
  productType?: string; planName?: string; pricePerUnit?: number; unitLabel?: string
}) {
  await prisma.productPlanConfig.update({ where: { id }, data })
  revalidatePath('/settings/products')
}

export async function deleteProductPlanConfig(id: number) {
  await prisma.productPlanConfig.delete({ where: { id } })
  revalidatePath('/settings/products')
}

export async function createKanbanColumn(data: { name: string; color: string; type: string }) {
  const max = await prisma.kanbanColumn.aggregate({ _max: { order: true } })
  const order = (max._max.order ?? 0) + 1
  const col = await prisma.kanbanColumn.create({ data: { ...data, order } })
  revalidatePath('/')
  return col
}

export async function updateKanbanColumn(id: number, data: { name?: string; color?: string; type?: string }) {
  const current = await prisma.kanbanColumn.findUniqueOrThrow({ where: { id } })
  if (data.name && data.name !== current.name) {
    await prisma.card.updateMany({ where: { status: current.name }, data: { status: data.name } })
  }
  await prisma.kanbanColumn.update({ where: { id }, data })
  revalidatePath('/')
}

export async function deleteKanbanColumn(id: number) {
  const col = await prisma.kanbanColumn.findUniqueOrThrow({ where: { id } })
  const count = await prisma.card.count({ where: { status: col.name, deletedAt: null } })
  if (count > 0) throw new Error('Coluna possui cards. Mova-os antes de excluir.')
  await prisma.kanbanColumn.delete({ where: { id } })
  revalidatePath('/')
}

export async function saveCustomerLookupUrl(url: string) {
  await setSetting(SETTING_KEYS.customerLookupUrl, url)
  clearSettingCache(SETTING_KEYS.customerLookupUrl)
  revalidatePath('/settings/general')
}

export async function saveChatLookupUrl(url: string) {
  await setSetting(SETTING_KEYS.chatLookupUrl, url)
  clearSettingCache(SETTING_KEYS.chatLookupUrl)
  revalidatePath('/settings/general')
}

export async function createWebhook(data: { name: string; url: string; triggerTypes: string[] }) {
  const bytes = new Uint8Array(32)
  crypto.getRandomValues(bytes)
  const secret = Array.from(bytes).map((b) => b.toString(16).padStart(2, '0')).join('')
  const sub = await prisma.webhookSubscription.create({
    data: { name: data.name, url: data.url, triggerTypes: data.triggerTypes, secret, isActive: true },
  })
  revalidatePath('/settings/webhooks')
  return { ...sub, secret, triggerTypes: sub.triggerTypes as string[] }
}

export async function updateWebhook(id: number, data: { isActive?: boolean; url?: string }) {
  await prisma.webhookSubscription.update({ where: { id }, data })
  revalidatePath('/settings/webhooks')
}

export async function deleteWebhook(id: number) {
  await prisma.webhookSubscription.update({ where: { id }, data: { deletedAt: new Date() } })
  revalidatePath('/settings/webhooks')
}

export async function deleteAndReplaceOption(
  entityType: 'card' | 'customer',
  field: string,
  oldValue: string,
  newValue: string | null
) {
  if (entityType === 'card') {
    await prisma.card.updateMany({
      where: { [field]: oldValue },
      data: { [field]: newValue },
    })
  } else {
    await prisma.customer.updateMany({
      where: { [field]: oldValue },
      data: { [field]: newValue },
    })
  }

  const keyMap: Record<string, string> = {
    ombudsman_agent: SETTING_KEYS.cardOmbudsmanAgents,
    ticket_origin: SETTING_KEYS.cardTicketOrigins,
    responsible_team: SETTING_KEYS.cardResponsibleTeams,
    tier: SETTING_KEYS.customerTiers,
    segment: SETTING_KEYS.customerSegments,
  }
  const key = keyMap[field]
  if (key) {
    const raw = await getSetting(key)
    if (raw) {
      const opts: string[] = JSON.parse(raw)
      const updated = opts.filter((o) => o !== oldValue)
      if (newValue && !updated.includes(newValue)) updated.push(newValue)
      await setSetting(key, JSON.stringify(updated))
    }
  }

  revalidatePath('/settings/general')
}
