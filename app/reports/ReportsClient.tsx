'use client'

import { useRouter, useSearchParams } from 'next/navigation'
import { Download, Calendar, Users, Star, Flame, Clock, Layers, Filter } from 'lucide-react'
import { cn } from '@/lib/utils'
import { DatePicker } from '@/components/DatePicker'
import { SimpleSelect } from '@/components/SimpleSelect'


type CardRow = {
  id: number
  status: string
  priority: string
  ombudsmanAgent: string | null
  responsibleTeam: string | null
  contactReason: string | null
  finishedAt: string | null
  deadlineAt: string | null
  rating: number | null
  createdAt: string
  ticketOrigin: string | null
  firstResponseHours: number | null
  usageTimePostOmbudsmanHours: number | null
  customer: { id: number; companyName: string; tier: string | null }
  product: { id: number; productType: string } | null
}

type Props = {
  cards: CardRow[]
  filters: { from: string; to: string; agent?: string; team?: string; status?: string; origin?: string }
  options: { agents: string[]; teams: string[]; origins: string[]; columns: string[] }
}

export function ReportsClient({ cards, filters, options }: Props) {
  const router = useRouter()
  const sp = useSearchParams()

  function applyFilter(key: string, value: string) {
    const params = new URLSearchParams(sp.toString())
    if (value) params.set(key, value)
    else params.delete(key)
    router.push(`/reports?${params.toString()}`)
  }

  function exportCsv() {
    const headers = [
      'ID',
      'Cliente',
      'Tier',
      'Status',
      'Prioridade',
      'Agente',
      'Time',
      'Origem',
      'Motivo',
      '1ª Resposta (h)',
      'Pós-Ouvidoria (h)',
      'Avaliação',
      'Criado',
      'Finalizado',
    ]
    const rows = cards.map((c) => [
      c.id,
      `"${c.customer.companyName}"`,
      c.customer.tier ?? '',
      c.status,
      c.priority,
      c.ombudsmanAgent ?? '',
      c.responsibleTeam ?? '',
      c.ticketOrigin ?? '',
      `"${(c.contactReason ?? '').replace(/"/g, '""')}"`,
      c.firstResponseHours ?? '',
      c.usageTimePostOmbudsmanHours ?? '',
      c.rating ?? '',
      c.createdAt.slice(0, 10),
      c.finishedAt?.slice(0, 10) ?? '',
    ])
    const csv = [headers.join(','), ...rows.map((r) => r.join(','))].join('\n')
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `relatorio-${filters.from}-${filters.to}.csv`
    a.click()
    URL.revokeObjectURL(url)
  }

  // --- KPI Metrics ---
  const totalCards = cards.length
  const openCards = cards.filter((c) => !c.finishedAt).length

  const ratedCards = cards.filter((c) => c.rating)
  const avgRating =
    ratedCards.length > 0
      ? ratedCards.reduce((acc, c) => acc + c.rating!, 0) / ratedCards.length
      : 0

  const responseCards = cards.filter((c) => c.firstResponseHours !== null)
  const avgResponse =
    responseCards.length > 0
      ? responseCards.reduce((acc, c) => acc + c.firstResponseHours!, 0) / responseCards.length
      : 0

  const postTimeCards = cards.filter((c) => c.usageTimePostOmbudsmanHours !== null)
  const avgPostTime =
    postTimeCards.length > 0
      ? postTimeCards.reduce((acc, c) => acc + c.usageTimePostOmbudsmanHours!, 0) / postTimeCards.length
      : 0

  // --- Groupings for Charts ---

  // 1. Group by Team
  const teamCounts: Record<string, number> = {}
  cards.forEach((c) => {
    const key = c.responsibleTeam || 'Sem Time'
    teamCounts[key] = (teamCounts[key] || 0) + 1
  })
  const byTeam = Object.entries(teamCounts).map(([team, count]) => ({ team, count }))

  // 2. Group by Reason (Top 10)
  const reasonCounts: Record<string, number> = {}
  cards.forEach((c) => {
    const key = c.contactReason || 'Sem Motivo'
    reasonCounts[key] = (reasonCounts[key] || 0) + 1
  })
  const byReason = Object.entries(reasonCounts)
    .map(([reason, count]) => ({ reason, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 10)

  // 3. Group by Origin
  const originCounts: Record<string, number> = {}
  cards.forEach((c) => {
    const key = c.ticketOrigin || 'Sem Origem'
    originCounts[key] = (originCounts[key] || 0) + 1
  })
  const byOrigin = Object.entries(originCounts).map(([origin, count]) => ({ origin, count }))

  // 4. Group by Product Type
  const productCounts: Record<string, number> = {}
  cards.forEach((c) => {
    const key = c.product?.productType || 'Sem Produto'
    productCounts[key] = (productCounts[key] || 0) + 1
  })
  const byProduct = Object.entries(productCounts).map(([product, count]) => ({ product, count }))

  // 5. Group by Tier
  const tierCounts: Record<string, number> = {}
  cards.forEach((c) => {
    const key = c.customer.tier || 'Sem Tier'
    tierCounts[key] = (tierCounts[key] || 0) + 1
  })
  const byTier = Object.entries(tierCounts).map(([tier, count]) => ({ tier, count }))

  // 6. Ratings distribution (1–5 + sem nota)
  const ratingDist = [1, 2, 3, 4, 5].map((star) => ({
    label: `${star} ★`,
    count: cards.filter((c) => c.rating !== null && Math.floor(c.rating) === star).length,
  }))
  ratingDist.push({ label: 'Sem Nota', count: cards.filter((c) => c.rating === null || c.rating === 0).length })

  // 7. Avg first response by agent
  const agentResp: Record<string, { total: number; count: number }> = {}
  cards.forEach((c) => {
    if (c.ombudsmanAgent && c.firstResponseHours !== null) {
      agentResp[c.ombudsmanAgent] = agentResp[c.ombudsmanAgent] || { total: 0, count: 0 }
      agentResp[c.ombudsmanAgent].total += c.firstResponseHours
      agentResp[c.ombudsmanAgent].count += 1
    }
  })
  const responseByAgent = Object.entries(agentResp)
    .map(([agent, d]) => ({ agent, avg: d.total / d.count, count: d.count }))
    .sort((a, b) => b.avg - a.avg)

  // 8. Avg post-ombudsman time by team
  const teamPost: Record<string, { total: number; count: number }> = {}
  cards.forEach((c) => {
    if (c.responsibleTeam && c.usageTimePostOmbudsmanHours !== null) {
      teamPost[c.responsibleTeam] = teamPost[c.responsibleTeam] || { total: 0, count: 0 }
      teamPost[c.responsibleTeam].total += c.usageTimePostOmbudsmanHours
      teamPost[c.responsibleTeam].count += 1
    }
  })
  const postTimeByTeam = Object.entries(teamPost)
    .map(([team, d]) => ({ team, avg: d.total / d.count, count: d.count }))
    .sort((a, b) => b.avg - a.avg)

  // Helper for rendering star rating
  function renderStars(rating: number) {
    if (rating === 0) return '—'
    const full = Math.floor(rating)
    return '★'.repeat(full) + '☆'.repeat(5 - full) + ` (${rating.toFixed(1)})`
  }

  const kpis = [
    {
      label: 'Cards no Período',
      value: totalCards,
      icon: Layers,
      color: 'text-violet-600 bg-violet-50 dark:bg-violet-950/20 dark:text-violet-400',
    },
    {
      label: 'Abertos no Fluxo',
      value: openCards,
      icon: Flame,
      color: 'text-rose-600 bg-rose-50 dark:bg-rose-950/20 dark:text-rose-400',
    },
    {
      label: 'Avaliação Média',
      value: avgRating > 0 ? avgRating.toFixed(1) : '—',
      sub: avgRating > 0 ? '★'.repeat(Math.round(avgRating)) : '',
      icon: Star,
      color: 'text-amber-600 bg-amber-50 dark:bg-amber-950/20 dark:text-amber-400',
    },
    {
      label: '1ª Resposta Média',
      value: avgResponse > 0 ? `${avgResponse.toFixed(1)}h` : '—',
      icon: Clock,
      color: 'text-sky-600 bg-sky-50 dark:bg-sky-950/20 dark:text-sky-400',
    },
    {
      label: 'Tempo Pós-Ouvidoria',
      value: avgPostTime > 0 ? `${avgPostTime.toFixed(1)}h` : '—',
      icon: Clock,
      color: 'text-indigo-600 bg-indigo-50 dark:bg-indigo-950/20 dark:text-indigo-400',
    },
  ]

  return (
    <div className="flex flex-col h-full">
      {/* Header */}
      <header className="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800 shrink-0 bg-white dark:bg-slate-900">
        <div>
          <h1 className="text-lg font-semibold text-slate-900 dark:text-white leading-none">Relatórios</h1>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Métricas consolidadas de Customer Success e Ouvidoria.
          </p>
        </div>
        <button onClick={exportCsv} className="btn-primary">
          <Download className="w-4 h-4" />
          Exportar CSV
        </button>
      </header>

      <div className="p-6 space-y-6 overflow-y-auto flex-grow">
        {/* Filters Panel */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 p-5 rounded-2xl shadow-premium">
          <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-1.5">
            <Filter className="w-3.5 h-3.5 text-violet-500" />
            Filtros do Relatório
          </h3>
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <div>
              <label className="field-label text-[10px]">De</label>
              <DatePicker
                size="sm"
                value={filters.from}
                onChange={(val) => applyFilter('from', val)}
              />
            </div>
            <div>
              <label className="field-label text-[10px]">Até</label>
              <DatePicker
                size="sm"
                value={filters.to}
                onChange={(val) => applyFilter('to', val)}
              />
            </div>
            <div>
              <label className="field-label text-[10px]">Agente</label>
              <SimpleSelect
                size="sm"
                value={filters.agent ?? ''}
                onChange={(val) => applyFilter('agent', val)}
                options={[
                  { value: '', label: 'Todos os Agentes' },
                  ...options.agents.map((a) => ({ value: a, label: a })),
                ]}
              />
            </div>
            <div>
              <label className="field-label text-[10px]">Time</label>
              <SimpleSelect
                size="sm"
                value={filters.team ?? ''}
                onChange={(val) => applyFilter('team', val)}
                options={[
                  { value: '', label: 'Todos os Times' },
                  ...options.teams.map((t) => ({ value: t, label: t })),
                ]}
              />
            </div>
            <div>
              <label className="field-label text-[10px]">Origem</label>
              <SimpleSelect
                size="sm"
                value={filters.origin ?? ''}
                onChange={(val) => applyFilter('origin', val)}
                options={[
                  { value: '', label: 'Todas Origens' },
                  ...options.origins.map((o) => ({ value: o, label: o })),
                ]}
              />
            </div>
            <div>
              <label className="field-label text-[10px]">Status</label>
              <SimpleSelect
                size="sm"
                value={filters.status ?? ''}
                onChange={(val) => applyFilter('status', val)}
                options={[
                  { value: '', label: 'Todos Status' },
                  ...options.columns.map((c) => ({ value: c, label: c })),
                ]}
              />
            </div>
          </div>
        </div>

        {/* KPIs Grid */}
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
          {kpis.map((k) => {
            const Icon = k.icon
            return (
              <div
                key={k.label}
                className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-4 shadow-premium hover:-translate-y-0.5 hover:shadow-[0_4px_20px_rgba(124,58,237,0.06)] transition-all duration-150"
              >
                <div className="flex items-center justify-between mb-1.5">
                  <span className="field-label !mb-0">{k.label}</span>
                  <span className={`p-1 rounded-lg ${k.color} shrink-0`}>
                    <Icon className="w-3.5 h-3.5" />
                  </span>
                </div>
                <div className="flex items-baseline gap-1.5">
                  <p className="text-xl font-bold text-slate-850 dark:text-slate-100">{k.value}</p>
                  {k.sub && <span className="text-xs text-amber-500 font-bold font-mono">{k.sub}</span>}
                </div>
              </div>
            )
          })}
        </div>

        {/* Horizontal Bar Charts */}
        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          {/* Por Time */}
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Por Time</h3>
            <div className="space-y-3">
              {byTeam
                .sort((a, b) => b.count - a.count)
                .map((t) => {
                  const max = Math.max(...byTeam.map((x) => x.count), 1)
                  const pct = Math.round((t.count / max) * 100)
                  return (
                    <div key={t.team} className="space-y-1">
                      <div className="flex justify-between items-center text-xs">
                        <span className="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[200px]">{t.team}</span>
                        <span className="font-bold text-slate-500">{t.count}</span>
                      </div>
                      <div className="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div className="h-full bg-violet-600 rounded-full" style={{ width: `${pct}%` }} />
                      </div>
                    </div>
                  )
                })}
              {byTeam.length === 0 && <p className="text-xs text-slate-400 text-center py-4">Sem dados</p>}
            </div>
          </div>

          {/* Por Origem */}
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Por Origem</h3>
            <div className="space-y-3">
              {byOrigin
                .sort((a, b) => b.count - a.count)
                .map((o) => {
                  const max = Math.max(...byOrigin.map((x) => x.count), 1)
                  const pct = Math.round((o.count / max) * 100)
                  return (
                    <div key={o.origin} className="space-y-1">
                      <div className="flex justify-between items-center text-xs">
                        <span className="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[200px]">{o.origin}</span>
                        <span className="font-bold text-slate-500">{o.count}</span>
                      </div>
                      <div className="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div className="h-full bg-violet-600 rounded-full" style={{ width: `${pct}%` }} />
                      </div>
                    </div>
                  )
                })}
              {byOrigin.length === 0 && <p className="text-xs text-slate-400 text-center py-4">Sem dados</p>}
            </div>
          </div>

          {/* Por Produto */}
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Por Produto</h3>
            <div className="space-y-3">
              {byProduct
                .sort((a, b) => b.count - a.count)
                .map((p) => {
                  const max = Math.max(...byProduct.map((x) => x.count), 1)
                  const pct = Math.round((p.count / max) * 100)
                  return (
                    <div key={p.product} className="space-y-1">
                      <div className="flex justify-between items-center text-xs">
                        <span className="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[200px]">{p.product}</span>
                        <span className="font-bold text-slate-500">{p.count}</span>
                      </div>
                      <div className="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div className="h-full bg-violet-600 rounded-full" style={{ width: `${pct}%` }} />
                      </div>
                    </div>
                  )
                })}
              {byProduct.length === 0 && <p className="text-xs text-slate-400 text-center py-4">Sem dados</p>}
            </div>
          </div>

          {/* Por Tier */}
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Por Tier</h3>
            <div className="space-y-3">
              {byTier
                .sort((a, b) => b.count - a.count)
                .map((t) => {
                  const max = Math.max(...byTier.map((x) => x.count), 1)
                  const pct = Math.round((t.count / max) * 100)
                  return (
                    <div key={t.tier} className="space-y-1">
                      <div className="flex justify-between items-center text-xs">
                        <span className="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[200px] text-capitalize">{t.tier}</span>
                        <span className="font-bold text-slate-500">{t.count}</span>
                      </div>
                      <div className="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div className="h-full bg-violet-600 rounded-full" style={{ width: `${pct}%` }} />
                      </div>
                    </div>
                  )
                })}
              {byTier.length === 0 && <p className="text-xs text-slate-400 text-center py-4">Sem dados</p>}
            </div>
          </div>

          {/* Por Motivo (Top 10) */}
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium md:col-span-2">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4">Top 10 Motivos de Contato</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {byReason
                .sort((a, b) => b.count - a.count)
                .map((r) => {
                  const max = Math.max(...byReason.map((x) => x.count), 1)
                  const pct = Math.round((r.count / max) * 100)
                  return (
                    <div key={r.reason} className="space-y-1">
                      <div className="flex justify-between items-center text-xs">
                        <span className="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[180px]" title={r.reason}>
                          {r.reason}
                        </span>
                        <span className="font-bold text-slate-500">{r.count}</span>
                      </div>
                      <div className="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div className="h-full bg-violet-600 rounded-full" style={{ width: `${pct}%` }} />
                      </div>
                    </div>
                  )
                })}
              {byReason.length === 0 && <p className="text-xs text-slate-400 text-center py-4 col-span-2">Sem dados</p>}
            </div>
          </div>

          {/* Distribuição de Avaliações */}
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-1.5">
              <Star className="w-4 h-4 text-violet-600" /> Distribuição de Avaliações
            </h3>
            <div className="space-y-3">
              {ratingDist.map((r) => {
                const max = Math.max(...ratingDist.map((x) => x.count), 1)
                const pct = Math.round((r.count / max) * 100)
                return (
                  <div key={r.label} className="flex items-center gap-3">
                    <span className="text-xs font-semibold text-slate-600 dark:text-slate-400 w-16 shrink-0">{r.label}</span>
                    <div className="flex-1 h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                      <div
                        className={cn(
                          'h-full rounded-full',
                          r.label === 'Sem Nota' ? 'bg-slate-300 dark:bg-slate-600' : 'bg-gradient-to-r from-amber-400 to-yellow-500'
                        )}
                        style={{ width: `${pct}%` }}
                      />
                    </div>
                    <span className="text-xs font-bold text-slate-500 w-6 text-right">{r.count}</span>
                  </div>
                )
              })}
            </div>
          </div>

          {/* 1ª Resposta por Agente */}
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-1.5">
              <Clock className="w-4 h-4 text-violet-600" /> 1ª Resposta por Agente (média)
            </h3>
            <div className="space-y-3 max-h-[260px] overflow-y-auto pr-1">
              {responseByAgent.map((a) => {
                const max = Math.max(...responseByAgent.map((x) => x.avg), 1)
                const pct = Math.round((a.avg / max) * 100)
                return (
                  <div key={a.agent} className="space-y-1">
                    <div className="flex justify-between items-center text-xs">
                      <span className="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[160px]">{a.agent}</span>
                      <span className="font-bold text-violet-600 dark:text-violet-400">
                        {a.avg.toFixed(1)}h <span className="text-[10px] text-slate-400 font-normal">({a.count})</span>
                      </span>
                    </div>
                    <div className="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                      <div className="h-full bg-violet-600 rounded-full" style={{ width: `${pct}%` }} />
                    </div>
                  </div>
                )
              })}
              {responseByAgent.length === 0 && <p className="text-xs text-slate-400 text-center py-4">Sem dados</p>}
            </div>
          </div>

          {/* Pós-Ouvidoria por Time */}
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-1.5">
              <Clock className="w-4 h-4 text-violet-600" /> Permanência Pós-Ouvidoria por Time (média)
            </h3>
            <div className="space-y-3 max-h-[260px] overflow-y-auto pr-1">
              {postTimeByTeam.map((t) => {
                const max = Math.max(...postTimeByTeam.map((x) => x.avg), 1)
                const pct = Math.round((t.avg / max) * 100)
                return (
                  <div key={t.team} className="space-y-1">
                    <div className="flex justify-between items-center text-xs">
                      <span className="font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[160px]">{t.team}</span>
                      <span className="font-bold text-violet-600 dark:text-violet-400">
                        {t.avg.toFixed(1)}h <span className="text-[10px] text-slate-400 font-normal">({t.count})</span>
                      </span>
                    </div>
                    <div className="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                      <div className="h-full bg-violet-600 rounded-full" style={{ width: `${pct}%` }} />
                    </div>
                  </div>
                )
              })}
              {postTimeByTeam.length === 0 && <p className="text-xs text-slate-400 text-center py-4">Sem dados</p>}
            </div>
          </div>
        </div>

        {/* Card Table */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl overflow-hidden shadow-premium">
          <div className="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/10">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100">Cards no Período ({cards.length})</h3>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                <tr>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider"># ID</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Cliente</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Status</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Prioridade</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Agente</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Origem</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Avaliação</th>
                  <th className="text-left px-4 py-3 text-[10px] uppercase font-bold text-slate-400 tracking-wider">Criado em</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                {cards.slice(0, 100).map((c) => (
                  <tr key={c.id} className="hover:bg-slate-50/50 dark:hover:bg-slate-850/30 transition-colors">
                    <td className="px-4 py-2.5 font-mono text-xs text-slate-400">#{c.id}</td>
                    <td className="px-4 py-2.5 font-semibold text-slate-800 dark:text-slate-250">
                      <a href={`/customers/${c.customer.id}`} className="hover:text-violet-600">
                        {c.customer.companyName}
                      </a>
                    </td>
                    <td className="px-4 py-2.5">
                      <span className="text-[10px] font-bold border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-850 px-2 py-0.5 rounded text-slate-650 dark:text-slate-350">
                        {c.status}
                      </span>
                    </td>
                    <td className="px-4 py-2.5 capitalize text-xs text-slate-500 dark:text-slate-400">{c.priority}</td>
                    <td className="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-450">{c.ombudsmanAgent ?? '—'}</td>
                    <td className="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-450">{c.ticketOrigin ?? '—'}</td>
                    <td className="px-4 py-2.5 font-mono text-xs font-bold text-amber-500">{c.rating ? '★'.repeat(c.rating) : '—'}</td>
                    <td className="px-4 py-2.5 text-xs text-slate-400">{c.createdAt.slice(0, 10)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {cards.length > 100 && (
            <p className="px-5 py-3 text-xs text-slate-400 border-t border-slate-150 dark:border-slate-800 bg-slate-50/20">
              Mostrando 100 de {cards.length} cards. Exporte o arquivo CSV para ver a listagem completa.
            </p>
          )}
        </div>
      </div>
    </div>
  )
}
