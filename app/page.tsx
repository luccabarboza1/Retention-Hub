import Link from 'next/link'
import { Plus } from 'lucide-react'
import { prisma } from '@/lib/db'
import { KanbanBoard } from '@/components/KanbanBoard'
import { BoardFilters } from '@/components/BoardFilters'

export default async function BoardPage({
  searchParams,
}: {
  searchParams: Promise<{ tag?: string; priority?: string }>
}) {
  const { tag, priority } = await searchParams

  const [columns, allCardTags] = await Promise.all([
    prisma.kanbanColumn.findMany({ orderBy: { order: 'asc' } }),
    prisma.tag.findMany({ where: { type: 'card' }, orderBy: { name: 'asc' } }),
  ])

  const cardWhere: Record<string, unknown> = { deletedAt: null }
  if (priority) cardWhere.priority = priority
  if (tag) cardWhere.tags = { some: { tag: { name: tag } } }

  const cards = await prisma.card.findMany({
    where: cardWhere,
    include: {
      customer: { select: { companyName: true } },
      tags: { include: { tag: true } },
    },
  })

  const PRIORITY_ORDER = ['urgente', 'alta', 'normal', 'baixa']
  const sortedCards = [...cards].sort((a, b) => {
    const pa = PRIORITY_ORDER.indexOf(a.priority)
    const pb = PRIORITY_ORDER.indexOf(b.priority)
    if (pa !== pb) return pa - pb
    return new Date(b.startedAt).getTime() - new Date(a.startedAt).getTime()
  })

  const cardsByColumn: Record<string, typeof sortedCards> = {}
  for (const col of columns) cardsByColumn[col.name] = []
  for (const card of sortedCards) {
    if (cardsByColumn[card.status]) cardsByColumn[card.status].push(card)
  }

  const serialized = Object.fromEntries(
    Object.entries(cardsByColumn).map(([k, v]) => [
      k,
      v.map((c) => ({
        ...c,
        tags: c.tags.map((t) => t.tag.name),
        deadlineAt: c.deadlineAt?.toISOString() ?? null,
        startedAt: c.startedAt.toISOString(),
        finishedAt: c.finishedAt?.toISOString() ?? null,
        createdAt: c.createdAt.toISOString(),
        updatedAt: c.updatedAt.toISOString(),
      })),
    ])
  )

  return (
    <div className="flex flex-col h-full">
      <header className="flex items-center justify-between px-6 py-4 border-b border-border shrink-0">
        <div>
          <h1 className="text-lg font-semibold text-slate-900 dark:text-white leading-none">Board</h1>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">Acompanhamento e triagem de ouvidoria.</p>
        </div>
        <div className="flex items-center gap-3">
          <BoardFilters
            tags={allCardTags.map((t) => t.name)}
            currentTag={tag}
            currentPriority={priority}
          />
          <Link href="/cards/create" className="btn-primary">
            <Plus className="w-4 h-4" />
            Novo Card
          </Link>
        </div>
      </header>
      <div className="flex-1 overflow-x-auto p-6">
        <KanbanBoard columns={columns} cardsByColumn={serialized} />
      </div>
    </div>
  )
}
