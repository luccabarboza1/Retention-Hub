import Link from 'next/link'
import { prisma } from '@/lib/db'
import { getSettingJson, SETTING_KEYS } from '@/lib/app-settings'
import { formatDistanceToNow } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import { revalidatePath } from 'next/cache'
import {
  Users,
  UserCheck,
  UserPlus,
  KanbanSquare,
  FolderOpen,
  Percent,
  DollarSign,
  AlertCircle,
  Calendar,
  UserMinus,
  CheckCircle,
  Star,
  Clock,
  MessageSquare,
  AlertTriangle,
} from 'lucide-react'

// Color map from prompt
const colorMap: Record<string, string> = {
  blue: 'sky',
  yellow: 'amber',
  green: 'emerald',
  red: 'rose',
  purple: 'purple',
  pink: 'pink',
  indigo: 'indigo',
  gray: 'slate',
}

const colorStyles: Record<string, { bg: string; border: string; text: string; dot: string; glow: string }> = {
  sky: {
    bg: 'bg-sky-50/80 dark:bg-sky-950/20',
    border: 'border-sky-200 dark:border-sky-800/60',
    text: 'text-sky-800 dark:text-sky-300',
    dot: 'bg-sky-500',
    glow: 'shadow-[0_0_8px_rgba(56,189,248,0.6)]',
  },
  amber: {
    bg: 'bg-amber-50/80 dark:bg-amber-950/20',
    border: 'border-amber-200 dark:border-amber-800/60',
    text: 'text-amber-800 dark:text-amber-300',
    dot: 'bg-amber-500',
    glow: 'shadow-[0_0_8px_rgba(245,158,11,0.6)]',
  },
  emerald: {
    bg: 'bg-emerald-50/80 dark:bg-emerald-950/20',
    border: 'border-emerald-200 dark:border-emerald-800/60',
    text: 'text-emerald-800 dark:text-emerald-300',
    dot: 'bg-emerald-500',
    glow: 'shadow-[0_0_8px_rgba(16,185,129,0.6)]',
  },
  rose: {
    bg: 'bg-rose-50/80 dark:bg-rose-950/20',
    border: 'border-rose-200 dark:border-rose-800/60',
    text: 'text-rose-800 dark:text-rose-300',
    dot: 'bg-rose-500',
    glow: 'shadow-[0_0_8px_rgba(244,63,94,0.6)]',
  },
  purple: {
    bg: 'bg-purple-50/80 dark:bg-purple-950/20',
    border: 'border-purple-200 dark:border-purple-800/60',
    text: 'text-purple-800 dark:text-purple-300',
    dot: 'bg-purple-500',
    glow: 'shadow-[0_0_8px_rgba(168,85,247,0.6)]',
  },
  pink: {
    bg: 'bg-pink-50/80 dark:bg-pink-950/20',
    border: 'border-pink-200 dark:border-pink-800/60',
    text: 'text-pink-800 dark:text-pink-300',
    dot: 'bg-pink-500',
    glow: 'shadow-[0_0_8px_rgba(236,72,153,0.6)]',
  },
  indigo: {
    bg: 'bg-indigo-50/80 dark:bg-indigo-950/20',
    border: 'border-indigo-200 dark:border-indigo-800/60',
    text: 'text-indigo-800 dark:text-indigo-300',
    dot: 'bg-indigo-500',
    glow: 'shadow-[0_0_8px_rgba(99,102,241,0.6)]',
  },
  slate: {
    bg: 'bg-slate-50/80 dark:bg-slate-800/50',
    border: 'border-slate-200 dark:border-slate-700/60',
    text: 'text-slate-800 dark:text-slate-300',
    dot: 'bg-slate-500',
    glow: 'shadow-[0_0_8px_rgba(100,116,139,0.6)]',
  },
}

function getColColor(color: string) {
  const mapped = colorMap[color] || color
  return colorStyles[mapped] || colorStyles.slate
}

export default async function DashboardPage() {
  const [
    totalCustomers,
    activeCustomers,
    totalCards,
    openCards,
    closedCards,
    workloadRaw,
    oldestOpen,
    unassigned,
    recentCards,
    kanbanCols,
    agents,
  ] = await Promise.all([
    prisma.customer.count({ where: { deletedAt: null } }),
    prisma.customer.count({ where: { deletedAt: null, canceledAt: null } }),
    prisma.card.count({ where: { deletedAt: null } }),
    prisma.card.count({ where: { deletedAt: null, finishedAt: null } }),
    prisma.card.count({ where: { deletedAt: null, finishedAt: { not: null } } }),
    prisma.card.groupBy({
      by: ['ombudsmanAgent'],
      where: { deletedAt: null, finishedAt: null, ombudsmanAgent: { not: null } },
      _count: { id: true },
      orderBy: { _count: { id: 'desc' } },
      take: 8,
    }),
    prisma.card.findMany({
      where: { deletedAt: null, finishedAt: null },
      orderBy: { startedAt: 'asc' },
      take: 6,
      include: { customer: { select: { companyName: true } } },
    }),
    prisma.card.findMany({
      where: { deletedAt: null, finishedAt: null, ombudsmanAgent: null },
      orderBy: { startedAt: 'asc' },
      take: 8,
      include: { customer: { select: { companyName: true } } },
    }),
    prisma.card.findMany({
      where: { deletedAt: null },
      orderBy: { createdAt: 'desc' },
      take: 6,
      include: { customer: { select: { companyName: true } } },
    }),
    prisma.kanbanColumn.findMany({ orderBy: { order: 'asc' } }),
    getSettingJson<string[]>(SETTING_KEYS.cardOmbudsmanAgents, []),
  ])

  const closureRate = totalCards > 0 ? Math.round((closedCards / totalCards) * 100) : 0

  const startOfMonth = new Date()
  startOfMonth.setDate(1)
  startOfMonth.setHours(0, 0, 0, 0)
  const newThisMonth = await prisma.customer.count({
    where: { deletedAt: null, createdAt: { gte: startOfMonth } },
  })

  const mrr = await prisma.customer.aggregate({
    where: { deletedAt: null },
    _sum: { monthlyFee: true },
  })

  // Quality KPIs + daily chats volume (últimos 30 dias)
  const thirtyDaysAgo = new Date()
  thirtyDaysAgo.setHours(0, 0, 0, 0)
  thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 29)

  const [chatsRaw, ratingAgg, firstRespAgg, reincidentGroups] = await Promise.all([
    prisma.chat.findMany({
      where: { deletedAt: null, startedAt: { gte: thirtyDaysAgo } },
      select: { startedAt: true },
    }),
    prisma.card.aggregate({ where: { deletedAt: null, rating: { gt: 0 } }, _avg: { rating: true } }),
    prisma.card.aggregate({
      where: { deletedAt: null, firstResponseHours: { not: null } },
      _avg: { firstResponseHours: true },
    }),
    prisma.card.groupBy({
      by: ['customerId'],
      where: { deletedAt: null },
      _count: { id: true },
      having: { id: { _count: { gt: 1 } } },
    }),
  ])

  const avgRating = ratingAgg._avg.rating ? Number(ratingAgg._avg.rating) : 0
  const avgFirstResponse = firstRespAgg._avg.firstResponseHours ? Number(firstRespAgg._avg.firstResponseHours) : 0
  const reincidentCases = reincidentGroups.reduce((acc, g) => acc + g._count.id, 0)

  // Série diária de chats
  const chatCounts: Record<string, number> = {}
  for (const ch of chatsRaw) {
    if (ch.startedAt) {
      const key = ch.startedAt.toISOString().slice(0, 10)
      chatCounts[key] = (chatCounts[key] || 0) + 1
    }
  }
  const dailyChats: { date: string; label: string; count: number }[] = []
  const cursor = new Date(thirtyDaysAgo)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  while (cursor <= today) {
    const key = cursor.toISOString().slice(0, 10)
    dailyChats.push({ date: key, label: `${key.slice(8, 10)}/${key.slice(5, 7)}`, count: chatCounts[key] || 0 })
    cursor.setDate(cursor.getDate() + 1)
  }

  // Coordenadas do gráfico de linha
  const chartW = 720
  const chartH = 180
  const padL = 32
  const padR = 16
  const padT = 16
  const padB = 26
  const innerW = chartW - padL - padR
  const innerH = chartH - padT - padB
  const maxChats = Math.max(...dailyChats.map((d) => d.count), 4)
  const points = dailyChats.map((d, i) => ({
    x: padL + (i / Math.max(dailyChats.length - 1, 1)) * innerW,
    y: chartH - padB - (d.count / maxChats) * innerH,
    ...d,
  }))
  const linePath = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(' ')
  const areaPath =
    points.length > 1
      ? `${linePath} L ${points[points.length - 1].x.toFixed(1)} ${chartH - padB} L ${points[0].x.toFixed(1)} ${chartH - padB} Z`
      : ''
  const totalChats30 = dailyChats.reduce((acc, d) => acc + d.count, 0)

  // Kanban counts
  const kanbanCounts = await Promise.all(
    kanbanCols.map(async (col) => {
      const count = await prisma.card.count({
        where: { deletedAt: null, status: col.name },
      })
      return { col, count }
    })
  )

  const kpis = [
    {
      label: 'Total de Clientes',
      value: totalCustomers,
      icon: Users,
      color: 'text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-950/20',
      sub: 'Cadastrados no sistema',
    },
    {
      label: 'Clientes Ativos',
      value: activeCustomers,
      icon: UserCheck,
      color: 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20',
      sub: 'Sem cancelamento ativo',
    },
    {
      label: 'Novos este mês',
      value: newThisMonth,
      icon: UserPlus,
      color: 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/20',
      sub: `Desde ${startOfMonth.toLocaleDateString('pt-BR')}`,
    },
    {
      label: 'Total de Cards',
      value: totalCards,
      icon: KanbanSquare,
      color: 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/20',
      sub: 'Histórico acumulado',
    },
    {
      label: 'Cards Abertos',
      value: openCards,
      icon: FolderOpen,
      color: 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/20',
      sub: 'Aguardando atendimento',
    },
    {
      label: 'Taxa de Encerramento',
      value: `${closureRate}%`,
      icon: Percent,
      color: 'text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/20',
      sub: `${closedCards} de ${totalCards} concluídos`,
    },
    {
      label: 'MRR Total',
      value: mrr._sum.monthlyFee ? `R$ ${Number(mrr._sum.monthlyFee).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}` : '—',
      icon: DollarSign,
      color: 'text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/20',
      sub: 'Recorrência mensal ativa',
    },
  ]

  const maxWorkload = Math.max(...workloadRaw.map((w) => w._count.id), 1)

  return (
    <div className="p-6 space-y-6">
      <div>
        <h1 className="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Dashboard</h1>
        <p className="text-xs text-slate-500 dark:text-slate-400">Visão geral do fluxo de retenção e atendimento.</p>
      </div>

      {/* KPIs Grid */}
      <div className="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-4">
        {kpis.map((k) => {
          const Icon = k.icon
          return (
            <div key={k.label} className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-4 shadow-premium transition-all duration-150 hover:-translate-y-0.5 hover:shadow-[0_4px_20px_rgba(124,58,237,0.06)]">
              <div className="flex items-center justify-between mb-2">
                <span className="field-label !mb-0">{k.label}</span>
                <span className={`p-1.5 rounded-lg ${k.color} shrink-0`}>
                  <Icon className="w-4 h-4" />
                </span>
              </div>
              <p className="text-xl font-bold text-slate-800 dark:text-slate-100">{k.value}</p>
              <p className="text-[10px] text-slate-400 dark:text-slate-500 mt-1 truncate">{k.sub}</p>
            </div>
          )
        })}
      </div>

      {/* Volume de Chats + KPIs de Qualidade */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Volume diário de chats */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium lg:col-span-8 flex flex-col">
          <div className="flex items-center justify-between mb-1">
            <h2 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
              <MessageSquare className="w-4 h-4 text-violet-600" />
              Volume Diário de Chats
            </h2>
            <span className="text-[10px] text-slate-400">{totalChats30} nos últimos 30 dias</span>
          </div>
          <p className="text-[10px] text-slate-400 dark:text-slate-500 mb-3">Chats iniciados por dia no período.</p>

          <div className="w-full flex-1 min-h-[180px]">
            {totalChats30 === 0 ? (
              <div className="h-full flex items-center justify-center border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                <p className="text-sm text-slate-400">Sem chats no período.</p>
              </div>
            ) : (
              <svg viewBox={`0 0 ${chartW} ${chartH}`} className="w-full h-full overflow-visible">
                <defs>
                  <linearGradient id="dash-area" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stopColor="#7c3aed" stopOpacity="0.25" />
                    <stop offset="100%" stopColor="#7c3aed" stopOpacity="0" />
                  </linearGradient>
                </defs>
                {[0, 0.25, 0.5, 0.75, 1].map((r) => {
                  const y = padT + r * innerH
                  return (
                    <g key={r}>
                      <line
                        x1={padL}
                        y1={y}
                        x2={chartW - padR}
                        y2={y}
                        stroke="currentColor"
                        strokeWidth="0.5"
                        strokeDasharray="4"
                        className="text-slate-200 dark:text-slate-800"
                      />
                      <text x={padL - 6} y={y + 3} textAnchor="end" className="text-[9px] fill-slate-400 font-mono">
                        {Math.round(maxChats * (1 - r))}
                      </text>
                    </g>
                  )
                })}
                {areaPath && <path d={areaPath} fill="url(#dash-area)" />}
                {points.length > 1 && (
                  <path d={linePath} fill="none" stroke="#7c3aed" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
                )}
                {points.map((p, i) => (
                  <g key={p.date}>
                    {p.count > 0 && (
                      <circle cx={p.x} cy={p.y} r={3} fill="#7c3aed" className="stroke-white dark:stroke-slate-900 stroke-2" />
                    )}
                    {i % Math.ceil(dailyChats.length / 8) === 0 && (
                      <text x={p.x} y={chartH - 6} textAnchor="middle" className="text-[8px] fill-slate-400 font-mono">
                        {p.label}
                      </text>
                    )}
                  </g>
                ))}
              </svg>
            )}
          </div>
        </div>

        {/* KPIs de Qualidade */}
        <div className="lg:col-span-4 grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-4">
          {[
            {
              label: 'Avaliação Média',
              value: avgRating > 0 ? avgRating.toFixed(1) : '—',
              icon: Star,
              color: 'text-yellow-600 bg-yellow-50 dark:bg-yellow-950/20 dark:text-yellow-400',
              sub: 'Nota dada pelos clientes',
            },
            {
              label: '1ª Resposta Média',
              value: avgFirstResponse > 0 ? `${avgFirstResponse.toFixed(1)}h` : '—',
              icon: Clock,
              color: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 dark:text-emerald-400',
              sub: 'Tempo até a 1ª resposta',
            },
            {
              label: 'Casos Reincidentes',
              value: reincidentCases,
              icon: AlertTriangle,
              color: 'text-amber-600 bg-amber-50 dark:bg-amber-950/20 dark:text-amber-400',
              sub: 'Cards de clientes recorrentes',
            },
          ].map((k) => {
            const Icon = k.icon
            return (
              <div
                key={k.label}
                className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-4 shadow-premium flex flex-col justify-center"
              >
                <div className="flex items-center justify-between mb-2">
                  <span className="field-label !mb-0">{k.label}</span>
                  <span className={`p-1.5 rounded-lg ${k.color} shrink-0`}>
                    <Icon className="w-4 h-4" />
                  </span>
                </div>
                <p className="text-xl font-bold text-slate-800 dark:text-slate-100">{k.value}</p>
                <p className="text-[10px] text-slate-400 dark:text-slate-500 mt-1 truncate">{k.sub}</p>
              </div>
            )
          })}
        </div>
      </div>

      {/* Seção Central - 2 Colunas */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Carga por agente */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium lg:col-span-7 flex flex-col">
          <h2 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
            <CheckCircle className="w-4 h-4 text-violet-600" />
            Carga por Agente
          </h2>
          {workloadRaw.length === 0 ? (
            <div className="flex-1 flex items-center justify-center p-8 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
              <p className="text-sm text-slate-400">Nenhum card atribuído a agentes ativos.</p>
            </div>
          ) : (
            <div className="space-y-4 flex-1">
              {workloadRaw.map((w) => {
                const percentage = Math.round((w._count.id / maxWorkload) * 100)
                return (
                  <div key={w.ombudsmanAgent} className="space-y-1.5">
                    <div className="flex justify-between items-center text-xs">
                      <span className="font-medium text-slate-700 dark:text-slate-300">{w.ombudsmanAgent}</span>
                      <span className="font-semibold text-violet-600 dark:text-violet-400">{w._count.id} card{w._count.id !== 1 ? 's' : ''}</span>
                    </div>
                    <div className="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                      <div
                        className="h-full bg-gradient-to-r from-violet-600 to-indigo-500 rounded-full transition-all duration-300"
                        style={{ width: `${percentage}%` }}
                      />
                    </div>
                  </div>
                )
              })}
            </div>
          )}
        </div>

        {/* Cards sem agente */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium lg:col-span-5 flex flex-col">
          <h2 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
            <UserMinus className="w-4 h-4 text-amber-500" />
            Cards sem Agente ({unassigned.length})
          </h2>
          {unassigned.length === 0 ? (
            <div className="flex-1 flex items-center justify-center p-8 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
              <p className="text-sm text-slate-400">Todos os cards têm agentes responsáveis.</p>
            </div>
          ) : (
            <div className="divide-y divide-slate-100 dark:divide-slate-800 flex-1 overflow-y-auto max-h-[300px] pr-1">
              {unassigned.map((c) => (
                <div key={c.id} className="py-2.5 flex items-center justify-between gap-3 first:pt-0 last:pb-0">
                  <div className="min-w-0">
                    <Link
                      href={`/cards/${c.id}`}
                      className="text-sm font-medium text-slate-800 dark:text-slate-200 hover:text-violet-600 dark:hover:text-violet-400 transition-colors block truncate"
                    >
                      {c.customer.companyName}
                    </Link>
                    <p className="text-[10px] text-slate-400 mt-0.5 truncate">{c.contactReason || 'Sem motivo informado'}</p>
                  </div>
                  <form
                    action={async (fd) => {
                      'use server'
                      const cardId = Number(fd.get('cardId'))
                      const agent = fd.get('agent') as string
                      if (!agent) return
                      const { updateCard } = await import('@/actions/cards')
                      await updateCard(cardId, { ombudsmanAgent: agent })
                      revalidatePath('/dashboard')
                    }}
                    className="flex gap-1 items-center shrink-0"
                  >
                    <input type="hidden" name="cardId" value={c.id} />
                    <div className="select-wrap">
                      <select
                        name="agent"
                        required
                        className="field-input-sm pr-7 max-w-[130px]"
                      >
                        <option value="">Agente...</option>
                        {agents.map((a) => (
                          <option key={a} value={a}>
                            {a}
                          </option>
                        ))}
                      </select>
                    </div>
                    <button type="submit" className="text-[10px] uppercase font-bold tracking-wider bg-violet-600 hover:bg-violet-700 text-white px-2 py-1.5 rounded-lg transition-colors shadow-sm">
                      Atribuir
                    </button>
                  </form>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Seção Inferior - 3 Colunas */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {/* Mais antigos em aberto */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium flex flex-col">
          <h2 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-3 flex items-center gap-2">
            <AlertCircle className="w-4 h-4 text-rose-500" />
            Mais Antigos em Aberto
          </h2>
          <div className="divide-y divide-slate-100 dark:divide-slate-800 flex-1">
            {oldestOpen.map((c) => (
              <div key={c.id} className="py-2.5 flex items-start justify-between gap-3 first:pt-0 last:pb-0">
                <div className="min-w-0">
                  <Link
                    href={`/cards/${c.id}`}
                    className="text-sm font-medium text-slate-800 dark:text-slate-200 hover:text-violet-600 dark:hover:text-violet-400 transition-colors block truncate"
                  >
                    {c.customer.companyName}
                  </Link>
                  <p className="text-[10px] text-slate-400 mt-0.5 truncate">{c.contactReason || 'Sem motivo'}</p>
                </div>
                <span className="text-[10px] bg-slate-50 dark:bg-slate-800 px-2 py-0.5 rounded text-slate-500 shrink-0 mt-0.5 font-medium">
                  {formatDistanceToNow(new Date(c.startedAt), { locale: ptBR, addSuffix: true })}
                </span>
              </div>
            ))}
            {oldestOpen.length === 0 && (
              <div className="p-8 text-center text-slate-400 text-sm">Não há cards em aberto.</div>
            )}
          </div>
        </div>

        {/* Cards recentes */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium flex flex-col">
          <h2 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-3 flex items-center gap-2">
            <Calendar className="w-4 h-4 text-sky-500" />
            Cards Recentes
          </h2>
          <div className="divide-y divide-slate-100 dark:divide-slate-800 flex-1">
            {recentCards.map((c) => (
              <div key={c.id} className="py-2.5 flex items-start justify-between gap-3 first:pt-0 last:pb-0">
                <div className="min-w-0">
                  <Link
                    href={`/cards/${c.id}`}
                    className="text-sm font-medium text-slate-800 dark:text-slate-200 hover:text-violet-600 dark:hover:text-violet-400 transition-colors block truncate"
                  >
                    {c.customer.companyName}
                  </Link>
                  <span className="text-[9px] uppercase tracking-wider font-semibold text-slate-400 mt-0.5 block">{c.status}</span>
                </div>
                <span className="text-[10px] text-slate-400 shrink-0 mt-0.5">
                  {formatDistanceToNow(new Date(c.createdAt), { locale: ptBR, addSuffix: true })}
                </span>
              </div>
            ))}
            {recentCards.length === 0 && (
              <div className="p-8 text-center text-slate-400 text-sm">Nenhum card recente.</div>
            )}
          </div>
        </div>

        {/* Mini Kanban */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium flex flex-col">
          <h2 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
            <KanbanSquare className="w-4 h-4 text-indigo-500" />
            Mini Kanban
          </h2>
          <div className="grid grid-cols-1 gap-2 flex-1">
            {kanbanCounts.map(({ col, count }) => {
              const styles = getColColor(col.color)
              return (
                <div
                  key={col.id}
                  className={`flex items-center justify-between px-4 py-2.5 rounded-xl border ${styles.bg} ${styles.border} ${styles.text} transition-all duration-150 hover:-translate-y-0.5`}
                >
                  <div className="flex items-center gap-2.5 min-w-0">
                    <span className={`w-2 h-2 rounded-full shrink-0 ${styles.dot} ${styles.glow}`} />
                    <span className="text-sm font-medium truncate">{col.name}</span>
                  </div>
                  <span className="text-sm font-bold">{count}</span>
                </div>
              )
            })}
          </div>
        </div>
      </div>
    </div>
  )
}
