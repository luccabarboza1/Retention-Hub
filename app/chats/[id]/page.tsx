import { notFound } from 'next/navigation'
import Link from 'next/link'
import { prisma } from '@/lib/db'
import { ChevronLeft, Calendar, ShieldAlert, Sparkles, MessageSquare, Clock, User } from 'lucide-react'
import { format } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import { cn } from '@/lib/utils'

export default async function ChatDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params

  const chat = await prisma.chat.findFirst({
    where: { id, deletedAt: null },
    include: {
      card: {
        include: {
          customer: { select: { id: true, companyName: true, clientName: true } },
        },
      },
      interactions: { orderBy: [{ interactedOn: 'asc' }, { agent: 'asc' }] },
    },
  })

  if (!chat) notFound()

  const durationHours = chat.startedAt && chat.closedAt
    ? ((chat.closedAt.getTime() - chat.startedAt.getTime()) / 3600000).toFixed(1)
    : null

  const kpis = [
    {
      label: 'Iniciado em',
      value: chat.startedAt ? format(new Date(chat.startedAt), "dd/MM/yy HH:mm", { locale: ptBR }) : '—',
      icon: Calendar,
      color: 'text-violet-600 bg-violet-50 dark:bg-violet-950/20 dark:text-violet-400',
    },
    {
      label: 'Fechado em',
      value: chat.closedAt ? format(new Date(chat.closedAt), "dd/MM/yy HH:mm", { locale: ptBR }) : '—',
      icon: Clock,
      color: chat.closedAt
        ? 'text-slate-600 bg-slate-50 dark:bg-slate-800 dark:text-slate-300'
        : 'text-emerald-650 bg-emerald-50 dark:bg-emerald-950/20 dark:text-emerald-400',
    },
    {
      label: '1ª Resposta',
      value: chat.firstResponseHours ? `${Number(chat.firstResponseHours).toFixed(1)}h` : '—',
      icon: Sparkles,
      color: 'text-amber-600 bg-amber-50 dark:bg-amber-950/20 dark:text-amber-400',
    },
    {
      label: 'Duração Total',
      value: durationHours ? `${durationHours}h` : '—',
      icon: Clock,
      color: 'text-sky-600 bg-sky-50 dark:bg-sky-950/20 dark:text-sky-400',
    },
  ]

  return (
    <div className="p-6 max-w-4xl mx-auto space-y-6">
      {/* Header */}
      <div className="flex items-center gap-3 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 p-4 rounded-2xl shadow-premium">
        <Link href="/chats" className="text-slate-400 hover:text-slate-650 transition-colors">
          <ChevronLeft className="w-5 h-5" />
        </Link>
        <div className="flex-1 min-w-0">
          <h1 className="text-sm font-mono font-bold text-slate-800 dark:text-slate-100 truncate">
            Chat {chat.id}
          </h1>
          <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Visualizar histórico e métricas de atendimento.
          </p>
        </div>
        <div>
          {chat.closedAt ? (
            <span className="text-[10px] uppercase tracking-wide font-bold px-2.5 py-1 rounded-full border bg-slate-50 text-slate-600 border-slate-250 dark:bg-slate-800 dark:text-slate-350 dark:border-slate-700">
              Fechado
            </span>
          ) : (
            <span className="text-[10px] uppercase tracking-wide font-bold px-2.5 py-1 rounded-full border bg-emerald-50 text-emerald-700 border-emerald-250 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/40 animate-pulse">
              Aberto
            </span>
          )}
        </div>
      </div>

      {/* KPI Cards Grid */}
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
                  <Icon className="w-3.5 h-3.5" />
                </span>
              </div>
              <p className="text-sm font-semibold text-slate-850 dark:text-slate-100">{k.value}</p>
            </div>
          )
        })}
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Agent list */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium flex flex-col">
          <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-3 flex items-center gap-2">
            <User className="w-4 h-4 text-violet-650" />
            Agentes que Interagiram
          </h3>
          {chat.interactions.length === 0 ? (
            <p className="text-xs text-slate-400 p-4 border border-dashed border-slate-100 dark:border-slate-800 rounded-xl text-center flex-1 flex items-center justify-center">
              Nenhuma interação registrada.
            </p>
          ) : (
            <div className="divide-y divide-slate-100 dark:divide-slate-800 flex-1">
              {chat.interactions.map((i) => (
                <div key={i.id} className="flex items-center justify-between py-2.5 first:pt-0 last:pb-0">
                  <span className="text-xs font-semibold text-slate-700 dark:text-slate-300">{i.agent}</span>
                  <span className="text-xs text-slate-400">
                    {format(new Date(i.interactedOn), 'dd/MM/yyyy', { locale: ptBR })}
                  </span>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Card info */}
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium flex flex-col justify-between">
          <div>
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 mb-3 flex items-center gap-2">
              <ShieldAlert className="w-4 h-4 text-violet-650" />
              Informações do Card Vinculado
            </h3>
            <div className="space-y-3.5">
              <div>
                <p className="field-label text-[10px]">Cliente / Ouvidoria</p>
                <Link
                  href={`/customers/${chat.card.customer.id}`}
                  className="text-xs font-bold text-slate-800 dark:text-slate-100 hover:text-violet-650"
                >
                  {chat.card.customer.companyName}
                </Link>
                <p className="text-[10px] text-slate-450 dark:text-slate-400 mt-0.5">
                  Contato: {chat.card.customer.clientName}
                </p>
              </div>

              <div>
                <p className="field-label text-[10px]">Card da Ouvidoria</p>
                <Link
                  href={`/cards/${chat.card.id}`}
                  className="text-xs font-bold text-violet-600 dark:text-violet-400 hover:underline"
                >
                  #{chat.card.id} — {chat.card.contactReason || 'Sem motivo'}
                </Link>
              </div>
            </div>
          </div>

          <div className="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span className="text-[10px] text-slate-400">Status do Card:</span>
            <span className="text-xs font-semibold px-2 py-0.5 rounded border bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-350">
              {chat.card.status}
            </span>
          </div>
        </div>
      </div>
    </div>
  )
}
