'use server'

import { revalidatePath } from 'next/cache'
import { prisma } from '@/lib/db'
import { withAudit } from '@/lib/audit'
import { getSetting, SETTING_KEYS } from '@/lib/app-settings'
import { lookupCustomerByEmail, type CustomerLookupResult } from './customers'

export type ChatLookupData = {
  id?: string
  startedAt?: string | null
  closedAt?: string | null
  firstResponseHours?: number | null
  agents?: string[]
  interactions?: { agent: string; interactedOn?: string | null }[]
}

export type ChatLookupResult =
  | { status: 'ok'; chats: ChatLookupData[] }
  | { status: 'not_configured' }
  | { status: 'error'; message: string }

function normalizeChats(raw: unknown): ChatLookupData[] {
  if (Array.isArray(raw)) return raw as ChatLookupData[]
  if (raw && typeof raw === 'object') {
    const obj = raw as Record<string, unknown>
    if (Array.isArray(obj.chats)) return obj.chats as ChatLookupData[]
    return [obj as ChatLookupData]
  }
  return []
}

export async function lookupChat(query: string): Promise<ChatLookupResult> {
  const url = await getSetting(SETTING_KEYS.chatLookupUrl)
  if (!url) return { status: 'not_configured' }

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ query }),
    })
    if (!res.ok) {
      return { status: 'error', message: `O endpoint do Umbler Talk retornou um erro (${res.status}).` }
    }
    const chats = normalizeChats(await res.json())
    return { status: 'ok', chats }
  } catch {
    return {
      status: 'error',
      message: 'Não foi possível conectar ao Umbler Talk. Verifique se o endpoint está ativo.',
    }
  }
}

export type ContactLookupResult = {
  customer: CustomerLookupResult
  phone: string | null
  chats: ChatLookupData[]
  chatStatus: 'ok' | 'not_configured' | 'error' | 'skipped'
}

function extractPhone(data: Record<string, unknown>): string | null {
  const v = data.phone ?? data.telefone ?? data.phoneNumber ?? data.phone_number
  return v != null ? String(v) : null
}

// Lookup unificado: chama o endpoint de cliente e, com o telefone retornado,
// o endpoint de chat — os dois em sequência, expostos como um só na UI.
export async function lookupContact(
  email: string,
  knownPhone?: string | null
): Promise<ContactLookupResult> {
  const customer = await lookupCustomerByEmail(email, knownPhone)

  let phone: string | null = knownPhone ?? null
  let chats: ChatLookupData[] = []
  let chatStatus: ContactLookupResult['chatStatus'] = 'skipped'

  if (customer.status === 'ok') {
    phone = extractPhone(customer.data) ?? phone
  }

  if (phone) {
    const res = await lookupChat(phone)
    chatStatus = res.status === 'ok' ? 'ok' : res.status
    if (res.status === 'ok') chats = res.chats
  }

  return { customer, phone, chats, chatStatus }
}

export async function createChatForCard(
  cardId: number,
  data: {
    id: string
    startedAt?: string | null
    closedAt?: string | null
    firstResponseHours?: number | null
    agents?: string[]
  }
) {
  const card = await prisma.card.findFirst({ where: { id: cardId, deletedAt: null } })
  if (!card) throw new Error('Card não encontrado.')

  const id = data.id.trim()
  if (!id) throw new Error('Informe o ID do chat.')

  const existing = await prisma.chat.findUnique({ where: { id } })
  if (existing) throw new Error('Já existe um chat com esse ID.')

  const startedAt = data.startedAt ? new Date(data.startedAt) : null

  await prisma.chat.create({
    data: withAudit(
      {
        id,
        ombudsmanCardId: cardId,
        startedAt,
        closedAt: data.closedAt ? new Date(data.closedAt) : null,
        firstResponseHours: data.firstResponseHours ?? null,
      },
      'system'
    ),
  })

  const agents = [...new Set((data.agents ?? []).map((a) => a.trim()).filter(Boolean))]
  if (agents.length > 0) {
    const interactedOn = startedAt ?? new Date()
    await prisma.chatAgentInteraction.createMany({
      data: agents.map((agent) => ({ chatId: id, agent, interactedOn })),
      skipDuplicates: true,
    })
  }

  revalidatePath(`/cards/${cardId}`)
}
