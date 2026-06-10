import { notFound } from 'next/navigation'
import Link from 'next/link'
import { prisma } from '@/lib/db'
import { getSettingJson, SETTING_KEYS } from '@/lib/app-settings'
import { CardDetailView } from '@/components/CardDetailView'

export default async function CardPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params

  const [card, agents, origins, teams, templates, columns] = await Promise.all([
    prisma.card.findFirst({
      where: { id: Number(id), deletedAt: null },
      include: {
        customer: { include: { tags: { include: { tag: true } } } },
        product: true,
        tags: { include: { tag: true } },
        comments: { orderBy: { createdAt: 'asc' } },
        activityLogs: { orderBy: { createdAt: 'desc' }, take: 50 },
        chats: { where: { deletedAt: null }, orderBy: { startedAt: 'desc' } },
        relatedFrom: {
          include: {
            related: {
              select: { id: true, contactReason: true, status: true, customer: { select: { companyName: true } } },
            },
          },
        },
      },
    }),
    getSettingJson<string[]>(SETTING_KEYS.cardOmbudsmanAgents, []),
    getSettingJson<string[]>(SETTING_KEYS.cardTicketOrigins, []),
    getSettingJson<string[]>(SETTING_KEYS.cardResponsibleTeams, []),
    prisma.solutionTemplate.findMany({ orderBy: [{ productType: 'asc' }, { title: 'asc' }] }),
    prisma.kanbanColumn.findMany({ orderBy: { order: 'asc' } }),
  ])

  if (!card) notFound()

  const allTags = await prisma.tag.findMany({ where: { type: 'card' }, orderBy: { name: 'asc' } })

  const serialized = {
    ...card,
    // Decimal fields
    firstResponseHours: card.firstResponseHours?.toNumber() ?? null,
    raPublicResponseHours: card.raPublicResponseHours?.toNumber() ?? null,
    usageTimePostOmbudsmanHours: card.usageTimePostOmbudsmanHours?.toNumber() ?? null,
    // Date fields
    startedAt: card.startedAt.toISOString(),
    deadlineAt: card.deadlineAt?.toISOString() ?? null,
    finishedAt: card.finishedAt?.toISOString() ?? null,
    createdAt: card.createdAt.toISOString(),
    updatedAt: card.updatedAt.toISOString(),
    // Relations
    tags: card.tags.map((t) => t.tag.name),
    customer: {
      ...card.customer,
      monthlyFee: card.customer.monthlyFee?.toNumber() ?? null,
      contractedAt: card.customer.contractedAt?.toISOString() ?? null,
      canceledAt: card.customer.canceledAt?.toISOString() ?? null,
      createdAt: card.customer.createdAt.toISOString(),
      updatedAt: card.customer.updatedAt.toISOString(),
      tags: card.customer.tags.map((t) => t.tag.name),
    },
    product: card.product ? {
      ...card.product,
      consumption: card.product.consumption.toNumber(),
      externalCreatedAt: card.product.externalCreatedAt?.toISOString() ?? null,
      createdAt: card.product.createdAt.toISOString(),
      updatedAt: card.product.updatedAt.toISOString(),
    } : null,
    comments: card.comments.map((c) => ({
      ...c,
      createdAt: c.createdAt.toISOString(),
      updatedAt: c.updatedAt.toISOString(),
    })),
    activityLogs: card.activityLogs.map((l) => ({
      ...l,
      createdAt: l.createdAt.toISOString(),
    })),
    chats: card.chats.map((c) => ({
      ...c,
      firstResponseHours: c.firstResponseHours?.toNumber() ?? null,
      startedAt: c.startedAt?.toISOString() ?? null,
      closedAt: c.closedAt?.toISOString() ?? null,
      createdAt: c.createdAt.toISOString(),
      updatedAt: c.updatedAt.toISOString(),
    })),
    relatedFrom: card.relatedFrom.map((r) => ({ ...r, related: r.related })),
  }

  return (
    <div className="p-6 max-w-6xl mx-auto">
      <CardDetailView
        card={serialized}
        options={{ agents, origins, teams }}
        allTags={allTags.map((t) => t.name)}
        templates={templates}
        columns={columns}
      />
    </div>
  )
}
