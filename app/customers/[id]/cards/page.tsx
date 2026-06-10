import { notFound } from 'next/navigation'
import Link from 'next/link'
import { prisma } from '@/lib/db'
import { ChevronLeft, SlidersHorizontal, Calendar } from 'lucide-react'
import { format } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import { cn } from '@/lib/utils'

const PRIORITY_CLASSES: Record<string, string> = {
  urgente: 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-200/65 dark:border-rose-900/50',
  alta: 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/65 dark:border-amber-900/50',
  normal: 'bg-sky-50 text-sky-700 dark:bg-sky-950/30 dark:text-sky-400 border border-sky-200/65 dark:border-sky-900/50',
  baixa: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/65 dark:border-emerald-900/50',
}

export default async function CustomerCardsPage({
  params,
  searchParams,
}: {
  params: Promise<{ id: string }>
  searchParams: Promise<{ status?: string; page?: string }>
}) {
  const { id } = await params
  const { status, page } = await searchParams
  const pageNum = Math.max(1, Number(page ?? 1))
  const take = 30
  const skip = (pageNum - 1) * take

  const customer = await prisma.customer.findFirst({
    where: { id: Number(id), deletedAt: null },
    select: { id: true, companyName: true, clientName: true },
  })

  if (!customer) notFound()

  const where = {
    customerId: Number(id),
    deletedAt: null as null,
    ...(status ? { status } : {}),
  }

  const [cards, total, columns] = await Promise.all([
    prisma.card.findMany({
      where,
      orderBy: { createdAt: 'desc' },
      take,
      skip,
      select: {
        id: true,
        status: true,
        priority: true,
        contactReason: true,
        ombudsmanAgent: true,
        createdAt: true,
        finishedAt: true,
        deadlineAt: true,
      },
    }),
    prisma.card.count({ where }),
    prisma.kanbanColumn.findMany({ orderBy: { order: 'asc' }, select: { name: true } }),
  ])

  const totalPages = Math.ceil(total / take)

  return (
    <div className="p-6 max-w-5xl mx-auto space-y-6">
      {/* Header */}
      <div className="flex items-center gap-3 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 p-4 rounded-2xl shadow-premium">
        <Link href={`/customers/${id}`} className="text-slate-400 hover:text-slate-650 transition-colors">
          <ChevronLeft className="w-5 h-5" />
        </Link>
        <div>
          <h1 className="text-lg font-bold text-slate-850 dark:text-slate-100 leading-none">
            Histórico de Cards — {customer.companyName}
          </h1>
          <p className="text-xs text-slate-450 dark:text-slate-400 mt-1">
            Lista completa de atendimentos e status na ouvidoria ({total} no total).
          </p>
        </div>
      </div>

      {/* Filter bar */}
      <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 p-4 rounded-xl shadow-premium flex flex-wrap items-center justify-between gap-3">
        <form className="flex gap-2 items-center">
          <div className="select-wrap">
            <select
              name="status"
              defaultValue={status ?? ''}
              className="field-input-sm min-w-[150px]"
            >
              <option value="">Todos os status</option>
              {columns.map((c) => (
                <option key={c.name} value={c.name}>
                  {c.name}
                </option>
              ))}
            </select>
          </div>
          <button type="submit" className="btn-outline btn-sm font-semibold">
            Filtrar
          </button>
        </form>
        {status && (
          <Link
            href={`/customers/${id}/cards`}
            className="text-xs text-rose-500 hover:underline font-medium"
          >
            Limpar Filtro
          </Link>
        )}
      </div>

      {/* Table */}
      <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl overflow-hidden shadow-premium">
        <table className="w-full text-sm">
          <thead className="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
            <tr>
              <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider"># ID</th>
              <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Motivo do Contato</th>
              <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Status</th>
              <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Prioridade</th>
              <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Agente</th>
              <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Data de Criação</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
            {cards.map((c) => (
              <tr key={c.id} className="hover:bg-slate-50/30 transition-colors">
                <td className="px-4 py-3 text-slate-400 font-mono text-xs">#{c.id}</td>
                <td className="px-4 py-3">
                  <Link
                    href={`/cards/${c.id}`}
                    className="font-semibold text-slate-800 dark:text-slate-200 hover:text-violet-600 transition-colors"
                  >
                    {c.contactReason ?? `Card #${c.id}`}
                  </Link>
                </td>
                <td className="px-4 py-3">
                  <span className="text-[10px] font-bold border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-2 py-0.5 rounded text-slate-600 dark:text-slate-350">
                    {c.status}
                  </span>
                </td>
                <td className="px-4 py-3">
                  <span className={cn('text-[10px] uppercase font-bold tracking-wide px-2 py-0.5 rounded', PRIORITY_CLASSES[c.priority] || PRIORITY_CLASSES.normal)}>
                    {c.priority}
                  </span>
                </td>
                <td className="px-4 py-3 text-slate-500 dark:text-slate-400 font-medium">{c.ombudsmanAgent ?? '—'}</td>
                <td className="px-4 py-3 text-slate-400 text-xs">
                  {format(new Date(c.createdAt), 'dd/MM/yyyy HH:mm', { locale: ptBR })}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        {cards.length === 0 && (
          <div className="p-8 text-center text-slate-400">Nenhum card registrado neste status.</div>
        )}
      </div>

      {/* Pagination */}
      {totalPages > 1 && (
        <div className="flex items-center justify-between text-xs text-slate-500">
          <span>{total} cards</span>
          <div className="flex gap-2">
            {pageNum > 1 && (
              <Link
                href={`?${new URLSearchParams({ ...(status ? { status } : {}), page: String(pageNum - 1) })}`}
                className="btn-outline py-1.5 px-3 text-xs"
              >
                Anterior
              </Link>
            )}
            <span className="py-1.5 px-3 bg-slate-50 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 font-medium">
              {pageNum} / {totalPages}
            </span>
            {pageNum < totalPages && (
              <Link
                href={`?${new URLSearchParams({ ...(status ? { status } : {}), page: String(pageNum + 1) })}`}
                className="btn-outline py-1.5 px-3 text-xs"
              >
                Próxima
              </Link>
            )}
          </div>
        </div>
      )}
    </div>
  )
}
