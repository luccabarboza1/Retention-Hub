'use client'

import { useState, useTransition, useCallback, useRef, useEffect } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'
import { format } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import {
  Trash2,
  Send,
  Link2,
  Plus,
  MessageSquare,
  Building,
  User,
  DollarSign,
  TrendingUp,
  Tag,
  AlertCircle,
  FileText,
  UserCheck,
  Calendar,
  Layers,
  Sparkles,
} from 'lucide-react'
import { cn } from '@/lib/utils'
import { toast } from 'sonner'
import {
  updateCard,
  addComment,
  deleteComment,
  addRelated,
  removeRelated,
  deleteCard,
} from '@/actions/cards'
import { saveCardOptions } from '@/actions/settings'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { ManagedCombobox } from '@/components/ManagedCombobox'
import { DatePicker } from '@/components/DatePicker'
import { TagInput } from '@/components/TagInput'
import { ConfirmDialog } from '@/components/ConfirmDialog'
import { ActivityTimeline } from '@/components/ActivityTimeline'
import { SimpleSelect } from '@/components/SimpleSelect'

type Card = {
  id: number
  status: string
  priority: string
  ombudsmanAgent: string | null
  ticketOrigin: string | null
  responsibleTeam: string | null
  contactReason: string | null
  reasonDetails: string | null
  appliedSolution: string | null
  raClaimLink: string | null
  rating: number | null
  deadlineAt: string | null
  startedAt: string
  createdAt: string
  tags: string[]
  customer: {
    id: number
    companyName: string
    clientName: string
    email: string | null
    tier: string | null
    monthlyFee: any
  }
  product: { id: number; productType: string; planName: string | null } | null
  comments: { id: number; author: string | null; content: string; createdAt: string }[]
  activityLogs: { id: number; actor: string | null; action: string; fromValue: string | null; toValue: string | null; createdAt: string }[]
  chats: { id: string; startedAt: string | null; closedAt: string | null }[]
  relatedFrom: {
    relatedCardId: number
    related: { id: number; contactReason: string | null; status: string; customer: { companyName: string } }
  }[]
}

type Column = { id: number; name: string; color: string }
type Options = { agents: string[]; origins: string[]; teams: string[] }

interface CardDetailViewProps {
  card: Card
  options: Options
  allTags: string[]
  templates: { id: number; title: string; body: string; productType: string | null }[]
  columns: Column[]
}

// Color map for status badge
const colorMap: Record<string, string> = {
  blue: 'bg-sky-50 text-sky-700 dark:bg-sky-950/30 dark:text-sky-400 border-sky-200/60 dark:border-sky-800/60',
  yellow: 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border-amber-200/60 dark:border-amber-800/60',
  green: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-800/60',
  red: 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border-rose-200/60 dark:border-rose-800/60',
  purple: 'bg-purple-50 text-purple-700 dark:bg-purple-950/30 dark:text-purple-400 border-purple-200/60 dark:border-purple-800/60',
  pink: 'bg-pink-50 text-pink-700 dark:bg-pink-950/30 dark:text-pink-400 border-pink-200/60 dark:border-pink-800/60',
  indigo: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400 border-indigo-200/60 dark:border-indigo-800/60',
  gray: 'bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200/60 dark:border-slate-700/60',
}

export function CardDetailView({
  card,
  options: initialOptions,
  allTags,
  templates,
  columns,
}: CardDetailViewProps) {
  const router = useRouter()
  const [isPending, startTransition] = useTransition()
  const [saving, setSaving] = useState(false)
  const [commentText, setCommentText] = useState('')
  const [relatedInput, setRelatedInput] = useState('')
  const [activeTab, setActiveTab] = useState<'geral' | 'resolucao' | 'relacoes' | 'atividades'>('geral')
  const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null)

  useEffect(() => {
    setTimeout(() => {
      const select = document.querySelector('select.field-input')
      const button = document.querySelector('button.field-input')
      const selectRect = select ? select.getBoundingClientRect() : null
      const buttonRect = button ? button.getBoundingClientRect() : null
      const selectStyle = select ? window.getComputedStyle(select) : null
      const buttonStyle = button ? window.getComputedStyle(button) : null
      
      const data = {
        selectHeight: selectRect ? selectRect.height : 'null',
        buttonHeight: buttonRect ? buttonRect.height : 'null',
        selectPadding: selectStyle ? selectStyle.padding : 'null',
        buttonPadding: buttonStyle ? buttonStyle.padding : 'null',
        selectBorder: selectStyle ? selectStyle.borderWidth : 'null',
        buttonBorder: buttonStyle ? buttonStyle.borderWidth : 'null',
        selectFont: selectStyle ? selectStyle.fontSize : 'null',
        buttonFont: buttonStyle ? buttonStyle.fontSize : 'null',
        selectLineHeight: selectStyle ? selectStyle.lineHeight : 'null',
        buttonLineHeight: buttonStyle ? buttonStyle.lineHeight : 'null'
      }
      
      const params = new URLSearchParams(data as any).toString()
      fetch('/api/health?' + params)
    }, 1500)
  }, [])

  // Options state
  const [options, setOptions] = useState(initialOptions)
  const [fields, setFields] = useState({
    contactReason: card.contactReason ?? '',
    priority: card.priority,
    status: card.status,
    ombudsmanAgent: card.ombudsmanAgent ?? '',
    ticketOrigin: card.ticketOrigin ?? '',
    responsibleTeam: card.responsibleTeam ?? '',
    reasonDetails: card.reasonDetails ?? '',
    appliedSolution: card.appliedSolution ?? '',
    raClaimLink: card.raClaimLink ?? '',
    rating: card.rating?.toString() ?? '',
    deadlineAt: card.deadlineAt ? card.deadlineAt.slice(0, 10) : '',
    tags: card.tags,
  })

  const save = useCallback(
    (updates: Partial<typeof fields>) => {
      setSaving(true)
      startTransition(async () => {
        try {
          await updateCard(card.id, {
            ...updates,
            ombudsmanAgent: updates.ombudsmanAgent || null,
            ticketOrigin: updates.ticketOrigin || null,
            responsibleTeam: updates.responsibleTeam || null,
            contactReason: updates.contactReason || null,
            reasonDetails: updates.reasonDetails || null,
            appliedSolution: updates.appliedSolution || null,
            raClaimLink: updates.raClaimLink || null,
            rating: updates.rating ? Number(updates.rating) : null,
            deadlineAt: updates.deadlineAt || null,
          })
        } catch {
          toast.error('Erro ao salvar campos.')
        } finally {
          setSaving(false)
        }
      })
    },
    [card.id]
  )

  function setField(key: keyof typeof fields, value: any) {
    setFields((f) => ({ ...f, [key]: value }))
    if (debounceRef.current) clearTimeout(debounceRef.current)
    debounceRef.current = setTimeout(() => save({ [key]: value }), 1000)
  }

  function handleBlurSave(key: keyof typeof fields, value: any) {
    if (debounceRef.current) clearTimeout(debounceRef.current)
    save({ [key]: value })
  }

  function handleAddComment(e: React.FormEvent) {
    e.preventDefault()
    if (!commentText.trim()) return
    const txt = commentText
    setCommentText('')
    startTransition(async () => {
      await addComment(card.id, txt, 'system')
      toast.success('Comentário adicionado!')
    })
  }

  function handleDeleteComment(commentId: number) {
    startTransition(async () => {
      await deleteComment(commentId, card.id)
      toast.success('Comentário excluído!')
    })
  }

  function handleAddRelated(e: React.FormEvent) {
    e.preventDefault()
    const rId = Number(relatedInput.trim())
    if (isNaN(rId) || rId <= 0) {
      toast.error('Insira um ID válido.')
      return
    }
    setRelatedInput('')
    startTransition(async () => {
      try {
        await addRelated(card.id, rId)
        toast.success('Card relacionado vinculado!')
      } catch {
        toast.error('Erro ao vincular card.')
      }
    })
  }

  function handleRemoveRelated(relatedCardId: number) {
    startTransition(async () => {
      try {
        await removeRelated(card.id, relatedCardId)
        toast.success('Vínculo removido.')
      } catch {
        toast.error('Erro ao remover vínculo.')
      }
    })
  }

  function handleDeleteCard() {
    startTransition(async () => {
      try {
        await deleteCard(card.id, 'system')
        toast.success('Card excluído com sucesso!')
      } catch {
        toast.error('Erro ao excluir card.')
      }
    })
  }

  // Get status color config
  const colObj = columns.find((c) => c.name === fields.status)
  const statusBadgeClass = colObj ? (colorMap[colObj.color] || colorMap.gray) : colorMap.gray

  return (
    <div className="space-y-6">
      {/* Top Header */}
      <div className="flex flex-wrap items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 p-4 rounded-2xl shadow-premium">
        <div className="flex items-center gap-2 text-xs text-slate-400">
          <Link href="/" className="hover:text-violet-600 transition-colors">
            Board
          </Link>
          <span>/</span>
          <span className="font-semibold text-slate-600 dark:text-slate-300">Card #{card.id}</span>
        </div>

        <div className="flex items-center gap-3">
          {saving && <span className="text-xs text-slate-400 animate-pulse">Salvando alterações...</span>}

          <SimpleSelect
            value={fields.status}
            onChange={(val) => setField('status', val)}
            options={columns.map((c) => ({ value: c.name, label: c.name }))}
            size="sm"
            className="min-w-[130px]"
          />

          <span className={cn('text-xs px-2.5 py-1 rounded-lg border font-semibold', statusBadgeClass)}>
            {fields.status}
          </span>

          <ConfirmDialog
            title="Excluir Card?"
            description="Esta ação arquivará o card e não poderá ser desfeita na interface principal."
            onConfirm={handleDeleteCard}
            trigger={
              <button className="btn-outline border-rose-200 text-rose-600 hover:bg-rose-50 dark:border-rose-900/40 dark:hover:bg-rose-950/20 px-3 py-1.5 rounded-xl transition-all">
                <Trash2 className="w-4 h-4 shrink-0" />
                Excluir
              </button>
            }
          />
        </div>
      </div>

      {/* Main Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Left Column (Fields, Tags, Relations, Chats) */}
        <div className="lg:col-span-7 space-y-6">
          {/* Header Selector Tabs */}
          <div className="flex border-b border-slate-200/60 dark:border-slate-800 gap-1 mb-2">
            {[
              { id: 'geral', label: 'Geral' },
              { id: 'resolucao', label: 'Resolução & Detalhes' },
              { id: 'relacoes', label: `Vínculos (${card.relatedFrom.length + card.chats.length})` },
              { id: 'atividades', label: `Atividades (${card.activityLogs.length})` },
            ].map((t) => (
              <button
                key={t.id}
                type="button"
                onClick={() => setActiveTab(t.id as any)}
                className={cn(
                  'px-4 py-2.5 text-xs font-bold uppercase tracking-wider border-b-2 transition-all cursor-pointer -mb-[2px]',
                  activeTab === t.id
                    ? 'border-violet-600 text-violet-750 dark:text-violet-400'
                    : 'border-transparent text-slate-400 hover:text-slate-655 dark:hover:text-slate-350'
                )}
              >
                {t.label}
              </button>
            ))}
          </div>

          {activeTab === 'geral' && (
            <div className="space-y-6 animate-fadeIn">
              {/* Main Info */}
              <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-4">
                <div>
                  <label className="field-label">Título / Motivo do Contato</label>
                  <input
                    type="text"
                    value={fields.contactReason}
                    onChange={(e) => setField('contactReason', e.target.value)}
                    onBlur={(e) => handleBlurSave('contactReason', e.target.value)}
                    className="w-full text-xl font-bold bg-transparent border-none outline-none focus:ring-0 text-slate-800 dark:text-slate-100 p-0 placeholder:text-slate-400"
                    placeholder="Motivo do contato..."
                  />
                </div>

                <Separator />

                {/* Grid fields */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="field-label">Prioridade</label>
                    <SimpleSelect
                      value={fields.priority}
                      onChange={(val) => setField('priority', val)}
                      options={[
                        { value: 'baixa', label: 'Baixa' },
                        { value: 'normal', label: 'Normal' },
                        { value: 'alta', label: 'Alta' },
                        { value: 'urgente', label: 'Urgente' },
                      ]}
                    />
                  </div>

                  <div>
                    <label className="field-label">Prazo</label>
                    <DatePicker
                      value={fields.deadlineAt}
                      onChange={(val) => setField('deadlineAt', val)}
                    />
                  </div>

                  <ManagedCombobox
                    label="Agente"
                    value={fields.ombudsmanAgent}
                    onChange={(val) => setField('ombudsmanAgent', val)}
                    options={options.agents}
                    onOptionsChange={(list) => {
                      setOptions((prev) => ({ ...prev, agents: list }))
                      startTransition(async () => {
                        await saveCardOptions('agents', list)
                      })
                    }}
                    placeholder="Sem agente..."
                  />

                  <ManagedCombobox
                    label="Origem"
                    value={fields.ticketOrigin}
                    onChange={(val) => setField('ticketOrigin', val)}
                    options={options.origins}
                    onOptionsChange={(list) => {
                      setOptions((prev) => ({ ...prev, origins: list }))
                      startTransition(async () => {
                        await saveCardOptions('origins', list)
                      })
                    }}
                    placeholder="Sem origem..."
                  />

                  <ManagedCombobox
                    label="Time Responsável"
                    value={fields.responsibleTeam}
                    onChange={(val) => setField('responsibleTeam', val)}
                    options={options.teams}
                    onOptionsChange={(list) => {
                      setOptions((prev) => ({ ...prev, teams: list }))
                      startTransition(async () => {
                        await saveCardOptions('teams', list)
                      })
                    }}
                    placeholder="Sem time..."
                    className="sm:col-span-2"
                  />

                  <div>
                    <label className="field-label">Avaliação (1 a 5)</label>
                    <input
                      type="number"
                      min={1}
                      max={5}
                      value={fields.rating}
                      onChange={(e) => setField('rating', e.target.value)}
                      onBlur={(e) => handleBlurSave('rating', e.target.value)}
                      className="field-input"
                      placeholder="Nota final do cliente..."
                    />
                  </div>

                  {card.product && (
                    <div>
                      <label className="field-label">Produto Vinculado</label>
                      <input
                        type="text"
                        disabled
                        value={`${card.product.productType} — ${card.product.planName || '#' + card.product.id}`}
                        className="field-input opacity-70 bg-slate-50 dark:bg-slate-800"
                      />
                    </div>
                  )}
                </div>

                <div>
                  <label className="field-label">Link RA (Reclame Aqui)</label>
                  <input
                    type="url"
                    value={fields.raClaimLink}
                    onChange={(e) => setField('raClaimLink', e.target.value)}
                    onBlur={(e) => handleBlurSave('raClaimLink', e.target.value)}
                    className="field-input"
                    placeholder="https://..."
                  />
                </div>
              </div>

              {/* Tags Section */}
              <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-3">
                <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                  <Tag className="w-4 h-4 text-violet-650" />
                  Marcadores do Card
                </h3>
                <TagInput
                  value={fields.tags}
                  onChange={(val) => {
                    setFields((f) => ({ ...f, tags: val }))
                    save({ tags: val })
                  }}
                  suggestions={allTags}
                  placeholder="Adicionar tags..."
                />
              </div>
            </div>
          )}

          {activeTab === 'resolucao' && (
            <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-4 animate-fadeIn">
              <div>
                <label className="field-label">Detalhes da Ouvidoria</label>
                <textarea
                  value={fields.reasonDetails}
                  onChange={(e) => setField('reasonDetails', e.target.value)}
                  onBlur={(e) => handleBlurSave('reasonDetails', e.target.value)}
                  className="field-input min-h-[140px] resize-y"
                  placeholder="Descreva o problema ou histórico..."
                />
              </div>

              <div>
                <label className="field-label">Solução Aplicada</label>
                <textarea
                  value={fields.appliedSolution}
                  onChange={(e) => setField('appliedSolution', e.target.value)}
                  onBlur={(e) => handleBlurSave('appliedSolution', e.target.value)}
                  className="field-input min-h-[140px] resize-y"
                  placeholder="Que medidas foram tomadas..."
                />
                {templates.length > 0 && (
                  <div className="mt-2 flex flex-wrap gap-1.5 items-center">
                    <span className="text-[10px] uppercase font-bold text-slate-400 mr-1 flex items-center gap-1">
                      <Sparkles className="w-3 h-3 text-violet-500" /> Templates:
                    </span>
                    {templates.map((t) => (
                      <button
                        key={t.id}
                        type="button"
                        onClick={() => {
                          setFields((f) => ({ ...f, appliedSolution: t.body }))
                          save({ appliedSolution: t.body })
                        }}
                        className="text-xs px-2 py-0.5 rounded bg-violet-50 text-violet-650 border border-violet-100 hover:bg-violet-100 transition-colors cursor-pointer"
                      >
                        {t.title}
                      </button>
                    ))}
                  </div>
                )}
              </div>
            </div>
          )}

          {activeTab === 'relacoes' && (
            <div className="space-y-6 animate-fadeIn">
              {/* Related Cards */}
              <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-3">
                <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                  <Link2 className="w-4 h-4 text-violet-650" />
                  Cards Relacionados ({card.relatedFrom.length})
                </h3>

                <form onSubmit={handleAddRelated} className="flex gap-2 max-w-sm">
                  <input
                    type="number"
                    value={relatedInput}
                    onChange={(e) => setRelatedInput(e.target.value)}
                    placeholder="ID do card (Ex: 12)"
                    className="field-input-sm"
                  />
                  <button type="submit" className="btn-primary btn-sm">
                    <Plus className="w-3.5 h-3.5" /> Vincular
                  </button>
                </form>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                  {card.relatedFrom.map((r) => (
                    <div
                      key={r.relatedCardId}
                      className="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200/50 dark:border-slate-800"
                    >
                      <div className="min-w-0">
                        <Link
                          href={`/cards/${r.relatedCardId}`}
                          className="text-xs font-semibold text-slate-800 dark:text-slate-200 hover:text-violet-655 truncate block"
                        >
                          #{r.relatedCardId} — {r.related.customer.companyName}
                        </Link>
                        <p className="text-[10px] text-slate-400 truncate">{r.related.contactReason || 'Sem motivo'}</p>
                      </div>
                      <button
                        type="button"
                        onClick={() => handleRemoveRelated(r.relatedCardId)}
                        className="text-slate-400 hover:text-rose-500 p-1 transition-colors shrink-0 cursor-pointer"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  ))}
                  {card.relatedFrom.length === 0 && (
                    <p className="text-xs text-slate-400 col-span-2">Nenhum card relacionado vinculado.</p>
                  )}
                </div>
              </div>

              {/* Linked Chats */}
              <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-3">
                <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                  <MessageSquare className="w-4 h-4 text-violet-650" />
                  Chats Vinculados ({card.chats.length})
                </h3>
                <div className="divide-y divide-slate-100 dark:divide-slate-850">
                  {card.chats.map((ch) => (
                    <div key={ch.id} className="py-2 flex items-center justify-between gap-3 first:pt-0 last:pb-0">
                      <div>
                        <Link
                          href={`/chats/${ch.id}`}
                          className="text-xs font-semibold text-slate-800 dark:text-slate-200 hover:text-violet-655"
                        >
                          Chat #{ch.id}
                        </Link>
                        <p className="text-[10px] text-slate-400">
                          Iniciado em:{' '}
                          {ch.startedAt
                            ? format(new Date(ch.startedAt), 'dd/MM/yyyy HH:mm', { locale: ptBR })
                            : 'Sem data'}
                        </p>
                      </div>
                      <Badge variant={ch.closedAt ? 'secondary' : 'default'} className="text-[10px]">
                        {ch.closedAt ? 'Concluído' : 'Aberto'}
                      </Badge>
                    </div>
                  ))}
                  {card.chats.length === 0 && (
                    <p className="text-xs text-slate-400 py-2">Nenhum chat vinculado a este card.</p>
                  )}
                </div>
              </div>
            </div>
          )}

          {activeTab === 'atividades' && (
            <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-3 animate-fadeIn">
              <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                Atividades Recentes
              </h3>
              <div className="max-h-[500px] overflow-y-auto pr-1">
                <ActivityTimeline logs={card.activityLogs} />
              </div>
            </div>
          )}
        </div>

        {/* Right Column (Client Data, Comments, Activity Timeline) */}
        <div className="lg:col-span-5 space-y-6">
          {/* Customer Summary Card */}
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-3">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
              <Building className="w-4 h-4 text-violet-600" />
              Dados do Cliente
            </h3>

            <div className="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl space-y-2 border border-slate-100 dark:border-slate-700/40">
              <div>
                <Link
                  href={`/customers/${card.customer.id}`}
                  className="font-bold text-sm text-slate-800 dark:text-slate-100 hover:text-violet-600 block transition-colors"
                >
                  {card.customer.companyName}
                </Link>
                <p className="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                  <User className="w-3.5 h-3.5 shrink-0" /> {card.customer.clientName}
                </p>
              </div>

              {card.customer.email && (
                <p className="text-xs text-slate-400 truncate">{card.customer.email}</p>
              )}

              <div className="flex gap-2 items-center pt-1.5 border-t border-slate-200/50 dark:border-slate-700/50 text-[11px] text-slate-500">
                {card.customer.tier && (
                  <Badge variant="outline" className="text-[9px] uppercase tracking-wider">
                    {card.customer.tier}
                  </Badge>
                )}
                {card.customer.monthlyFee && (
                  <span className="flex items-center text-violet-600 dark:text-violet-400 font-semibold">
                    <DollarSign className="w-3.5 h-3.5" />
                    {Number(card.customer.monthlyFee).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}/mês
                  </span>
                )}
              </div>
            </div>
          </div>

          {/* Comments Section */}
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-4">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100">
              Comentários ({card.comments.length})
            </h3>

            <div className="space-y-3 max-h-[300px] overflow-y-auto pr-1">
              {card.comments.map((c) => (
                <div key={c.id} className="flex items-start gap-2.5 group">
                  <div className="flex-1 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/30 dark:border-slate-750/30 rounded-xl p-3">
                    <div className="flex justify-between items-center mb-1">
                      <span className="text-[10px] font-bold text-violet-600 dark:text-violet-400">
                        {c.author || 'Sistema'}
                      </span>
                      <span className="text-[9px] text-slate-400">
                        {format(new Date(c.createdAt), 'dd/MM/yyyy HH:mm', { locale: ptBR })}
                      </span>
                    </div>
                    <p className="text-xs text-slate-750 dark:text-slate-350 whitespace-pre-wrap leading-relaxed">
                      {c.content}
                    </p>
                  </div>
                  <button
                    type="button"
                    onClick={() => handleDeleteComment(c.id)}
                    className="opacity-0 group-hover:opacity-100 transition-opacity text-slate-400 hover:text-rose-500 p-1 mt-2.5 rounded-full shrink-0"
                  >
                    <Trash2 className="w-3 h-3" />
                  </button>
                </div>
              ))}
              {card.comments.length === 0 && (
                <p className="text-xs text-slate-400 text-center py-4">Nenhum comentário ainda.</p>
              )}
            </div>

            <form onSubmit={handleAddComment} className="flex gap-2">
              <input
                type="text"
                className="field-input-sm flex-1"
                placeholder="Responder ao card..."
                value={commentText}
                onChange={(e) => setCommentText(e.target.value)}
              />
              <button
                type="submit"
                className="btn-primary btn-sm shrink-0"
                disabled={!commentText.trim()}
              >
                <Send className="w-3.5 h-3.5" />
              </button>
            </form>
          </div>

        </div>
      </div>
    </div>
  )
}
