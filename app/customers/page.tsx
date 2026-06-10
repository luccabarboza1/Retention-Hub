import Link from 'next/link'
import { Plus, Eye, PlusCircle } from 'lucide-react'
import { prisma } from '@/lib/db'
import { format } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import { CustomerFilters } from '@/components/CustomerFilters'
import { cn } from '@/lib/utils'

export default async function CustomersPage({
  searchParams,
}: {
  searchParams: Promise<{ q?: string; tag?: string; page?: string }>
}) {
  const { q, tag, page } = await searchParams
  const pageNum = Math.max(1, Number(page ?? 1))
  const take = 30
  const skip = (pageNum - 1) * take

  const where: Record<string, unknown> = { deletedAt: null }

  if (q && q.length >= 2) {
    where.OR = [
      { companyName: { contains: q, mode: 'insensitive' } },
      { clientName: { contains: q, mode: 'insensitive' } },
      { email: { contains: q, mode: 'insensitive' } },
    ]
  }

  if (tag) where.tags = { some: { tag: { name: tag } } }

  const [customers, total, allCustomerTags] = await Promise.all([
    prisma.customer.findMany({
      where,
      include: {
        tags: { include: { tag: true } },
        cards: {
          where: { deletedAt: null },
          select: { finishedAt: true },
        },
      },
      orderBy: { companyName: 'asc' },
      take,
      skip,
    }),
    prisma.customer.count({ where }),
    prisma.tag.findMany({ where: { type: 'customer' }, orderBy: { name: 'asc' } }),
  ])

  const totalPages = Math.ceil(total / take)

  return (
    <div className="flex flex-col h-full">
      <header className="flex items-center justify-between px-6 py-4 border-b border-border shrink-0">
        <div>
          <h1 className="text-lg font-semibold text-slate-900 dark:text-white leading-none">Clientes</h1>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">Gerenciamento de contas e tiers de atendimento.</p>
        </div>
        <Link href="/customers/create" className="btn-primary">
          <Plus className="w-4 h-4" />
          Novo Cliente
        </Link>
      </header>

      <div className="p-6 space-y-4 flex-grow overflow-y-auto">
        {/* Filters */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 p-4 rounded-xl shadow-premium">
          <CustomerFilters
            tags={allCustomerTags.map((t) => t.name)}
            currentTag={tag}
            currentSearch={q}
          />
        </div>

        {/* Table */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl overflow-hidden shadow-premium">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                <tr>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Empresa</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Contato</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Plano</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tier</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Cards (Abertos/Total)</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Contratação</th>
                  <th className="text-right px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Ações</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                {customers.map((c) => {
                  const totalCards = c.cards.length
                  const openCards = c.cards.filter((card) => !card.finishedAt).length
                  const isEnterprise = c.tier?.toLowerCase() === 'enterprise'

                  return (
                    <tr key={c.id} className="hover:bg-slate-50/50 dark:hover:bg-slate-850/30 transition-colors">
                      <td className="px-4 py-3">
                        <Link href={`/customers/${c.id}`} className="font-semibold text-slate-900 dark:text-slate-200 hover:text-violet-600 dark:hover:text-violet-400 transition-colors">
                          {c.companyName}
                        </Link>
                        {c.email && <p className="text-xs text-slate-400">{c.email}</p>}
                        <div className="flex flex-wrap gap-1 mt-1">
                          {c.tags.slice(0, 3).map((t) => (
                            <span key={t.tagId} className="text-[9px] px-1.5 py-0.2 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded">
                              {t.tag.name}
                            </span>
                          ))}
                        </div>
                      </td>
                      <td className="px-4 py-3 text-slate-700 dark:text-slate-300 font-medium">{c.clientName}</td>
                      <td className="px-4 py-3 text-slate-500 dark:text-slate-400 text-xs">{c.planName || '—'}</td>
                      <td className="px-4 py-3">
                        {c.tier && (
                          <span
                            className={cn(
                              'text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full border font-bold',
                              isEnterprise
                                ? 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-950/30 dark:text-violet-400 dark:border-violet-900/60'
                                : 'bg-slate-50 text-slate-650 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'
                            )}
                          >
                            {c.tier}
                          </span>
                        )}
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex items-center gap-2">
                          <span className="text-slate-700 dark:text-slate-300 font-medium">
                            {openCards} / {totalCards}
                          </span>
                          {openCards > 0 && (
                            <span className="inline-flex items-center gap-0.5 text-[9px] px-1.5 py-0.5 bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/40 rounded font-bold uppercase tracking-wide shrink-0">
                              Alerta
                            </span>
                          )}
                        </div>
                      </td>
                      <td className="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                        {c.contractedAt ? format(new Date(c.contractedAt), 'dd/MM/yyyy', { locale: ptBR }) : '—'}
                      </td>
                      <td className="px-4 py-3 text-right">
                        <div className="flex justify-end gap-1.5">
                          <Link
                            href={`/customers/${c.id}`}
                            className="p-1.5 text-slate-400 hover:text-violet-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-all"
                            title="Ver Detalhes"
                          >
                            <Eye className="w-4 h-4" />
                          </Link>
                          <Link
                            href={`/cards/create?customerId=${c.id}`}
                            className="p-1.5 text-slate-400 hover:text-violet-600 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-all"
                            title="Novo Card"
                          >
                            <PlusCircle className="w-4 h-4" />
                          </Link>
                        </div>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>

          {customers.length === 0 && (
            <div className="p-8 text-center text-slate-400">
              Nenhum cliente cadastrado ou encontrado.
            </div>
          )}
        </div>

        {/* Pagination */}
        {totalPages > 1 && (
          <div className="flex items-center justify-between text-xs text-slate-550 dark:text-slate-455">
            <span>Total de {total} clientes</span>
            <div className="flex gap-1.5">
              {pageNum > 1 && (
                <Link href={`?${new URLSearchParams({ ...(q ? { q } : {}), ...(tag ? { tag } : {}), page: String(pageNum - 1) })}`} className="btn-outline py-1.5 px-3">
                  Anterior
                </Link>
              )}
              <span className="py-1.5 px-3 bg-slate-50 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 font-medium">
                {pageNum} / {totalPages}
              </span>
              {pageNum < totalPages && (
                <Link href={`?${new URLSearchParams({ ...(q ? { q } : {}), ...(tag ? { tag } : {}), page: String(pageNum + 1) })}`} className="btn-outline py-1.5 px-3">
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
