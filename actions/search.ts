'use server'

import { prisma } from '@/lib/db'

export type SearchCustomer = {
  id: number
  companyName: string
  clientName: string
  email: string | null
  tier: string | null
}

export type SearchCard = {
  id: number
  contactReason: string | null
  status: string
  ombudsmanAgent: string | null
  companyName: string
}

export type SearchChat = {
  id: string
  companyName: string
  cardId: number
}

export type SearchResults = {
  customers: SearchCustomer[]
  cards: SearchCard[]
  chats: SearchChat[]
}

const EMPTY: SearchResults = { customers: [], cards: [], chats: [] }

export async function globalSearch(query: string): Promise<SearchResults> {
  const q = query.trim()
  if (q.length < 2) return EMPTY

  const pattern = `%${q}%`
  const numeric = /^\d+$/.test(q) ? Number(q) : null

  const [customers, cards, chats] = await Promise.all([
    prisma.$queryRaw<SearchCustomer[]>`
      SELECT id, company_name AS "companyName", client_name AS "clientName", email, tier
      FROM customers
      WHERE deleted_at IS NULL AND (
        company_name ILIKE ${pattern} OR
        client_name ILIKE ${pattern} OR
        email ILIKE ${pattern} OR
        plan_name ILIKE ${pattern} OR
        tier ILIKE ${pattern} OR
        related_emails::text ILIKE ${pattern}
      )
      ORDER BY company_name ASC
      LIMIT 8
    `,
    prisma.card.findMany({
      where: {
        deletedAt: null,
        OR: [
          { contactReason: { contains: q, mode: 'insensitive' } },
          { ombudsmanAgent: { contains: q, mode: 'insensitive' } },
          { reasonDetails: { contains: q, mode: 'insensitive' } },
          { appliedSolution: { contains: q, mode: 'insensitive' } },
          { responsibleTeam: { contains: q, mode: 'insensitive' } },
          { ticketOrigin: { contains: q, mode: 'insensitive' } },
          ...(numeric !== null ? [{ id: numeric }] : []),
        ],
      },
      select: {
        id: true,
        contactReason: true,
        status: true,
        ombudsmanAgent: true,
        customer: { select: { companyName: true } },
      },
      orderBy: { createdAt: 'desc' },
      take: 8,
    }),
    prisma.chat.findMany({
      where: { deletedAt: null, id: { contains: q, mode: 'insensitive' } },
      select: {
        id: true,
        ombudsmanCardId: true,
        card: { select: { customer: { select: { companyName: true } } } },
      },
      orderBy: { startedAt: 'desc' },
      take: 5,
    }),
  ])

  return {
    customers,
    cards: cards.map((c) => ({
      id: c.id,
      contactReason: c.contactReason,
      status: c.status,
      ombudsmanAgent: c.ombudsmanAgent,
      companyName: c.customer.companyName,
    })),
    chats: chats.map((c) => ({
      id: c.id,
      cardId: c.ombudsmanCardId,
      companyName: c.card.customer.companyName,
    })),
  }
}
