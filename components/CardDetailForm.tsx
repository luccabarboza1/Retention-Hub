'use client'

import { useState, useTransition, useCallback, useRef } from 'react'
import { updateCard, addComment, deleteComment } from '@/actions/cards'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { Trash2, Send } from 'lucide-react'
import { cn } from '@/lib/utils'
import { toast } from 'sonner'
import { DatePicker } from '@/components/DatePicker'


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
  tags: string[]
  comments: { id: number; author: string | null; content: string; createdAt: string }[]
}

type Options = { agents: string[]; origins: string[]; teams: string[] }

export function CardDetailForm({
  card,
  options,
  allTags,
  templates,
}: {
  card: Card
  options: Options
  allTags: string[]
  templates: { id: number; title: string; body: string; productType: string | null }[]
}) {
  const [, startTransition] = useTransition()
  const [saving, setSaving] = useState(false)
  const [comment, setComment] = useState('')
  const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null)

  const [fields, setFields] = useState({
    priority: card.priority,
    ombudsmanAgent: card.ombudsmanAgent ?? '',
    ticketOrigin: card.ticketOrigin ?? '',
    responsibleTeam: card.responsibleTeam ?? '',
    contactReason: card.contactReason ?? '',
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
        setSaving(false)
        toast.success('Salvo')
      })
    },
    [card.id, startTransition]
  )

  function setField(key: keyof typeof fields, value: string | string[]) {
    setFields((f) => ({ ...f, [key]: value }))
    if (debounceRef.current) clearTimeout(debounceRef.current)
    debounceRef.current = setTimeout(() => save({ [key]: value }), 1000)
  }

  function onBlurSave(key: keyof typeof fields, value: string | string[]) {
    if (debounceRef.current) clearTimeout(debounceRef.current)
    save({ [key]: value })
  }

  function handleSubmitComment(e: React.FormEvent) {
    e.preventDefault()
    if (!comment.trim()) return
    const text = comment
    setComment('')
    startTransition(() => addComment(card.id, text, 'system'))
  }

  return (
    <div className="space-y-6">
      {/* Fields */}
      <div className="bg-card border border-border rounded-xl p-5 space-y-4">
        <div className="flex items-center justify-between">
          <h3 className="text-sm font-semibold">Detalhes do Card</h3>
          {saving && <span className="text-xs text-muted-foreground">Salvando...</span>}
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="field-label">Prioridade</label>
            <select className="field-input" value={fields.priority}
              onChange={(e) => setField('priority', e.target.value)}
              onBlur={(e) => onBlurSave('priority', e.target.value)}
            >
              {['baixa', 'normal', 'alta', 'urgente'].map((p) => (
                <option key={p} value={p}>{p}</option>
              ))}
            </select>
          </div>
          <div>
            <label className="field-label">Prazo</label>
            <DatePicker
              value={fields.deadlineAt}
              onChange={(val) => {
                setField('deadlineAt', val)
                onBlurSave('deadlineAt', val)
              }}
            />
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          {[
            { key: 'ombudsmanAgent' as const, label: 'Agente', opts: options.agents },
            { key: 'ticketOrigin' as const, label: 'Origem', opts: options.origins },
            { key: 'responsibleTeam' as const, label: 'Time', opts: options.teams },
          ].map(({ key, label, opts }) => (
            <div key={key}>
              <label className="field-label">{label}</label>
              <select className="field-input" value={fields[key]}
                onChange={(e) => setField(key, e.target.value)}
                onBlur={(e) => onBlurSave(key, e.target.value)}
              >
                <option value="">—</option>
                {opts.map((o) => <option key={o} value={o}>{o}</option>)}
              </select>
            </div>
          ))}
          <div>
            <label className="field-label">Avaliação (1–5)</label>
            <input type="number" min={1} max={5} className="field-input" value={fields.rating}
              onChange={(e) => setField('rating', e.target.value)}
              onBlur={(e) => onBlurSave('rating', e.target.value)}
            />
          </div>
        </div>

        <div>
          <label className="field-label">Motivo do Contato</label>
          <input type="text" className="field-input" value={fields.contactReason}
            onChange={(e) => setField('contactReason', e.target.value)}
            onBlur={(e) => onBlurSave('contactReason', e.target.value)}
          />
        </div>

        <div>
          <label className="field-label">Detalhes</label>
          <textarea className="field-input min-h-20 resize-y" value={fields.reasonDetails}
            onChange={(e) => setField('reasonDetails', e.target.value)}
            onBlur={(e) => onBlurSave('reasonDetails', e.target.value)}
          />
        </div>

        <div>
          <label className="field-label">Solução Aplicada</label>
          <textarea className="field-input min-h-20 resize-y" value={fields.appliedSolution}
            onChange={(e) => setField('appliedSolution', e.target.value)}
            onBlur={(e) => onBlurSave('appliedSolution', e.target.value)}
          />
          {templates.length > 0 && (
            <div className="mt-1 flex flex-wrap gap-1">
              {templates.map((t) => (
                <button
                  key={t.id}
                  type="button"
                  className="text-xs text-[var(--brand-600)] hover:underline"
                  onClick={() => {
                    setFields((f) => ({ ...f, appliedSolution: t.body }))
                    save({ appliedSolution: t.body })
                  }}
                >
                  {t.title}
                </button>
              ))}
            </div>
          )}
        </div>

        <div>
          <label className="field-label">Link RA</label>
          <input type="url" className="field-input" value={fields.raClaimLink}
            onChange={(e) => setField('raClaimLink', e.target.value)}
            onBlur={(e) => onBlurSave('raClaimLink', e.target.value)}
            placeholder="https://..."
          />
        </div>

        {/* Tags */}
        <div>
          <label className="field-label">Tags</label>
          <div className="flex flex-wrap gap-1.5 mb-2">
            {fields.tags.map((t) => (
              <Badge key={t} variant="secondary" className="cursor-pointer"
                onClick={() => {
                  const next = fields.tags.filter((x) => x !== t)
                  setFields((f) => ({ ...f, tags: next }))
                  save({ tags: next })
                }}
              >
                {t} ×
              </Badge>
            ))}
          </div>
          <select className="field-input" value=""
            onChange={(e) => {
              const v = e.target.value
              if (!v || fields.tags.includes(v)) return
              const next = [...fields.tags, v]
              setFields((f) => ({ ...f, tags: next }))
              save({ tags: next })
              e.target.value = ''
            }}
          >
            <option value="">Adicionar tag...</option>
            {allTags.filter((t) => !fields.tags.includes(t)).map((t) => (
              <option key={t} value={t}>{t}</option>
            ))}
          </select>
        </div>
      </div>

      <Separator />

      {/* Comments */}
      <div className="bg-card border border-border rounded-xl p-5 space-y-4">
        <h3 className="text-sm font-semibold">Comentários ({card.comments.length})</h3>

        <div className="space-y-3">
          {card.comments.map((c) => (
            <div key={c.id} className="flex items-start gap-3 group">
              <div className="flex-1 bg-muted rounded-lg p-3">
                {c.author && <p className="text-xs font-medium text-[var(--brand-600)] mb-1">{c.author}</p>}
                <p className="text-sm text-foreground whitespace-pre-wrap">{c.content}</p>
              </div>
              <button
                className={cn('opacity-0 group-hover:opacity-100 transition-opacity text-muted-foreground hover:text-destructive mt-3')}
                onClick={() => startTransition(() => deleteComment(c.id, card.id))}
              >
                <Trash2 className="w-3.5 h-3.5" />
              </button>
            </div>
          ))}
        </div>

        <form onSubmit={handleSubmitComment} className="flex gap-2">
          <input
            type="text"
            className="field-input flex-1"
            placeholder="Adicionar comentário..."
            value={comment}
            onChange={(e) => setComment(e.target.value)}
          />
          <button type="submit" className="btn-primary px-3" disabled={!comment.trim()}>
            <Send className="w-4 h-4" />
          </button>
        </form>
      </div>
    </div>
  )
}
