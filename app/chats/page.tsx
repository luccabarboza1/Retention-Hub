import Link from 'next/link'
import { prisma } from '@/lib/db'
import { format } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import { ChatFilters } from '@/components/ChatFilters'
import { MessageSquare, FolderOpen, Flame, Clock, Eye } from 'lucide-react'
import { cn } from '@/lib/utils'

export default async function ChatsPage({
  searchParams,
}: {
  searchParams: Promise<{ q?: string; status?: string; start?: string; end?: string; page?: string }>
}) {
  const { q, status, start, end, page } = await searchParams
  const pageNum = Math.max(1, Number(page ?? 1))
  const take = 30
  const skip = (pageNum - 1) * take

  const where: Record<string, any> = { deletedAt: null }

  if (q && q.length >= 2) {
    where.OR = [
      { id: { contains: q, mode: 'insensitive' as const } },
      { card: { customer: { companyName: { contains: q, mode: 'insensitive' as const } } } },
    ]
  }

  if (status === 'open') {
    where.closedAt = null
  } else if (status === 'closed') {
    where.closedAt = { not: null }
  }

  if (start || end) {
    where.createdAt = {}
    if (start) where.createdAt.gte = new Date(start)
    if (end) where.createdAt.lte = new Date(end)
  }

  const [chats, total, openCount, closedCount, avgFirstResponseRaw, closedChatsTimes] = await Promise.all([
    prisma.chat.findMany({
      where,
      orderBy: { createdAt: 'desc' },
      take,
      skip,
      include: {
        card: {
          include: {
            customer: { select: { id: true, companyName: true } },
          },
        },
        interactions: { select: { agent: true }, distinct: ['agent'] },
      },
    }),
    prisma.chat.count({ where }),
    prisma.chat.count({ where: { deletedAt: null, closedAt: null } }),
    prisma.chat.count({ where: { deletedAt: null, closedAt: { not: null } } }),
    prisma.chat.aggregate({
      where: { deletedAt: null, firstResponseHours: { not: null } },
      _avg: { firstResponseHours: true },
    }),
    prisma.chat.findMany({
      where: { deletedAt: null, closedAt: { not: null }, startedAt: { not: null } },
      select: { startedAt: true, closedAt: true },
    }),
  ])

  // Calculate Average Duration
  const totalDurationMs = closedChatsTimes.reduce(
    (acc, c) => acc + (c.closedAt!.getTime() - c.startedAt!.getTime()),
    0
  )
  const avgDurationHours =
    closedChatsTimes.length > 0 ? totalDurationMs / (1000 * 60 * 60) / closedChatsTimes.length : 0

  const totalPages = Math.ceil(total / take)

  const kpis = [
    {
      label: 'Total de Chats',
      value: openCount + closedCount,
      icon: MessageSquare,
      color: 'text-violet-600 bg-violet-50 dark:bg-violet-950/20 dark:text-violet-400',
    },
    {
      label: 'Chats Abertos',
      value: openCount,
      icon: FolderOpen,
      color: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 dark:text-emerald-400',
    },
    {
      label: '1ª Resposta Média',
      value: avgFirstResponseRaw._avg.firstResponseHours
        ? `${Number(avgFirstResponseRaw._avg.firstResponseHours).toFixed(1)}h`
        : '—',
      icon: Flame,
      color: 'text-amber-600 bg-amber-50 dark:bg-amber-950/20 dark:text-amber-400',
    },
    {
      label: 'Duração Média',
      value: avgDurationHours > 0 ? `${avgDurationHours.toFixed(1)}h` : '—',
      icon: Clock,
      color: 'text-sky-600 bg-sky-50 dark:bg-sky-950/20 dark:text-sky-400',
    },
  ]

  return (
    <div className="flex flex-col h-full">
      <header className="flex items-center justify-between px-6 py-4 border-b border-border shrink-0">
        <div>
          <h1 className="text-lg font-semibold text-slate-900 dark:text-white leading-none">Chats</h1>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Histórico e métricas de conversação com clientes de ouvidoria.
          </p>
        </div>
      </header>

      <div className="p-6 space-y-6 flex-grow overflow-y-auto">
        {/* KPIs Grid */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          {kpis.map((k) => {
            const Icon = k.icon
            return (
              <div
                key={k.label}
                className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-4 shadow-premium transition-all duration-150 hover:-translate-y-0.5 hover:shadow-[0_4px_20px_rgba(124,58,237,0.06)]"
              >
                <div className="flex items-center justify-between mb-2">
                  <span className="field-label !mb-0">{k.label}</span>
                  <span className={`p-1.5 rounded-lg ${k.color} shrink-0`}>
                    <Icon className="w-4 h-4" />
                  </span>
                </div>
                <p className="text-xl font-bold text-slate-800 dark:text-slate-100">{k.value}</p>
              </div>
            )
          })}
        </div>

        {/* Filters */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 p-4 rounded-xl shadow-premium">
          <ChatFilters
            currentSearch={q}
            currentStatus={status}
            currentStart={start}
            currentEnd={end}
          />
        </div>

        {/* Table */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl overflow-hidden shadow-premium">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                <tr>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">ID Chat</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Card</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Cliente</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Status</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Agentes</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Primeira Resposta</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Duração</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Data de Início</th>
                  <th className="text-right px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Ações</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                {chats.map((chat) => {
                  const durationHours =
                    chat.closedAt && chat.startedAt
                      ? (chat.closedAt.getTime() - chat.startedAt.getTime()) / (1000 * 60 * 60)
                      : null

                  return (
                    <tr key={chat.id} className="hover:bg-slate-50/50 dark:hover:bg-slate-850/30 transition-colors">
                      <td className="px-4 py-3 font-mono text-xs font-semibold text-slate-800 dark:text-slate-250">
                        {chat.id.slice(0, 12)}…
                      </td>
                      <td className="px-4 py-3">
                        <Link
                          href={`/cards/${chat.card.id}`}
                          className="font-medium hover:text-violet-650 hover:underline transition-colors"
                        >
                          #{chat.card.id}
                        </Link>
                      </td>
                      <td className="px-4 py-3">
                        <Link
                          href={`/customers/${chat.card.customer.id}`}
                          className="font-semibold text-slate-900 dark:text-slate-200 hover:text-violet-600 dark:hover:text-violet-400 transition-colors"
                        >
                          {chat.card.customer.companyName}
                        </Link>
                      </td>
                      <td className="px-4 py-3">
                        <span
                          className={cn(
                            'text-[10px] uppercase tracking-wide font-bold px-2 py-0.5 rounded-full border',
                            chat.closedAt
                              ? 'bg-slate-50 text-slate-650 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'
                              : 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/40'
                          )}
                        >
                          {chat.closedAt ? 'Concluído' : 'Aberto'}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-xs text-slate-500 dark:text-slate-450">
                        {chat.interactions.map((i) => i.agent).join(', ') || '—'}
                      </td>
                      <td className="px-4 py-3 text-xs text-slate-500 dark:text-slate-450">
                        {chat.firstResponseHours ? `${Number(chat.firstResponseHours).toFixed(1)}h` : '—'}
                      </td>
                      <td className="px-4 py-3 text-xs text-slate-500 dark:text-slate-455 font-medium">
                        {durationHours !== null ? `${durationHours.toFixed(1)}h` : '—'}
                      </td>
                      <td className="px-4 py-3 text-xs text-slate-400">
                        {chat.startedAt ? format(new Date(chat.startedAt), 'dd/MM/yyyy HH:mm', { locale: ptBR }) : '—'}
                      </td>
                      <td className="px-4 py-3 text-right">
                        <Link
                          href={`/chats/${chat.id}`}
                          className="inline-flex p-1.5 text-slate-400 hover:text-violet-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-all"
                        >
                          <Eye className="w-4 h-4" />
                        </Link>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>

          {chats.length === 0 && (
            <div className="p-8 text-center text-slate-400">Nenhum chat encontrado.</div>
          )}
        </div>

        {/* Pagination */}
        {totalPages > 1 && (
          <div className="flex items-center justify-between text-xs text-slate-500">
            <span>Total de {total} chats</span>
            <div className="flex gap-1.5">
              {pageNum > 1 && (
                <Link
                  href={`?${new URLSearchParams({
                    ...(q ? { q } : {}),
                    ...(status ? { status } : {}),
                    ...(start ? { start } : {}),
                    ...(end ? { end } : {}),
                    page: String(pageNum - 1),
                  })}`}
                  className="btn-outline py-1.5 px-3"
                >
                  Anterior
                </Link>
              )}
              <span className="py-1.5 px-3 bg-slate-50 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 font-medium">
                {pageNum} / {totalPages}
              </span>
              {pageNum < totalPages && (
                <Link
                  href={`?${new URLSearchParams({
                    ...(q ? { q } : {}),
                    ...(status ? { status } : {}),
                    ...(start ? { start } : {}),
                    ...(end ? { end } : {}),
                    page: String(pageNum + 1),
                  })}`}
                  className="btn-outline py-1.5 px-3"
                >
                  Próxima
                </Link>
              )}
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
