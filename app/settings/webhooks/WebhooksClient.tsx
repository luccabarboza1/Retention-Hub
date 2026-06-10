'use client'

import { useState, useTransition } from 'react'
import { Plus, Trash2, Copy, Check, ShieldCheck, HelpCircle } from 'lucide-react'
import { toast } from 'sonner'
import { createWebhook, updateWebhook, deleteWebhook } from '@/actions/settings'
import { ConfirmDialog } from '@/components/ConfirmDialog'
import { format } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import { cn } from '@/lib/utils'

type Sub = { id: number; name: string; url: string; triggerTypes: string[]; isActive: boolean; createdAt: string }

const ALL_TRIGGERS = [
  'customer.created', 'customer.updated', 'customer.deleted',
  'card.created', 'card.updated', 'card.deleted', 'card.finished',
]

export function WebhooksClient({ initialSubscriptions }: { initialSubscriptions: Sub[] }) {
  const [subs, setSubs] = useState(initialSubscriptions)
  const [creating, setCreating] = useState(false)
  const [form, setForm] = useState({ name: '', url: '', triggerTypes: ALL_TRIGGERS })
  const [newSecret, setNewSecret] = useState<{ id: number; secret: string } | null>(null)
  const [copied, setCopied] = useState(false)
  const [, startTransition] = useTransition()

  function toggleTrigger(t: string) {
    setForm((f) => ({
      ...f,
      triggerTypes: f.triggerTypes.includes(t) ? f.triggerTypes.filter((x) => x !== t) : [...f.triggerTypes, t],
    }))
  }

  function create() {
    if (!form.url.trim() || !form.name.trim() || form.triggerTypes.length === 0) return
    startTransition(async () => {
      try {
        const sub = await createWebhook(form)
        setSubs((prev) => [{
          id: sub.id,
          name: sub.name,
          url: sub.url,
          triggerTypes: sub.triggerTypes,
          isActive: sub.isActive,
          createdAt: sub.createdAt.toISOString(),
        }, ...prev])
        setNewSecret({ id: sub.id, secret: sub.secret })
        setCreating(false)
        setForm({ name: '', url: '', triggerTypes: ALL_TRIGGERS })
        toast.success('Webhook criado com sucesso!')
      } catch {
        toast.error('Erro ao criar webhook.')
      }
    })
  }

  function remove(id: number) {
    startTransition(async () => {
      try {
        await deleteWebhook(id)
        setSubs((prev) => prev.filter((s) => s.id !== id))
        toast.success('Webhook removido.')
      } catch {
        toast.error('Erro ao remover webhook.')
      }
    })
  }

  function toggleActive(id: number, isActive: boolean) {
    startTransition(async () => {
      try {
        await updateWebhook(id, { isActive })
        setSubs((prev) => prev.map((s) => (s.id === id ? { ...s, isActive } : s)))
        toast.success(isActive ? 'Webhook ativado' : 'Webhook desativado')
      } catch {
        toast.error('Erro ao alternar status.')
      }
    })
  }

  function copySecret() {
    if (!newSecret) return
    navigator.clipboard.writeText(newSecret.secret)
    setCopied(true)
    setTimeout(() => setCopied(false), 2000)
  }

  return (
    <div className="p-6 space-y-6 max-w-4xl">
      <div className="flex items-center justify-between bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 p-4 rounded-2xl shadow-premium">
        <div>
          <h2 className="text-base font-bold text-slate-850 dark:text-slate-100 leading-none">Webhooks / Subscriptions</h2>
          <p className="text-xs text-slate-450 dark:text-slate-400 mt-1">Dispare eventos HTTP POST para urls externas quando dados mudarem.</p>
        </div>
        <button type="button" onClick={() => setCreating(true)} className="btn-primary">
          <Plus className="w-4 h-4" /> Novo Webhook
        </button>
      </div>

      {newSecret && (
        <div className="bg-amber-50 dark:bg-amber-950/20 border border-amber-250 dark:border-amber-900/60 shadow-[0_0_12px_rgba(245,158,11,0.06)] rounded-2xl p-5 space-y-3 animate-fadeIn">
          <div className="flex items-center gap-2 text-amber-800 dark:text-amber-300">
            <ShieldCheck className="w-5 h-5 text-amber-500 shrink-0" />
            <p className="text-sm font-semibold">
              Guarde o secret agora — ele não será exibido novamente.
            </p>
          </div>
          <div className="flex items-center gap-2">
            <code className="flex-1 font-mono text-xs bg-white dark:bg-black/30 rounded-xl px-3.5 py-2.5 border border-amber-200 dark:border-amber-800/80 break-all select-all text-slate-800 dark:text-slate-200">
              {newSecret.secret}
            </code>
            <button type="button" onClick={copySecret} className="btn-outline shrink-0 p-2.5 rounded-xl">
              {copied ? <Check className="w-4 h-4 text-green-650" /> : <Copy className="w-4 h-4" />}
            </button>
          </div>
          <div className="flex justify-between items-center">
            <button type="button" onClick={() => setNewSecret(null)} className="text-xs text-amber-600 dark:text-amber-400 font-bold hover:underline">
              Confirmo que copiei e guardei o secret
            </button>
          </div>
        </div>
      )}

      {creating && (
        <div className="bg-white dark:bg-slate-900 border border-violet-650 dark:border-violet-800 rounded-2xl p-5 space-y-4 animate-fadeIn shadow-[0_4px_24px_rgba(124,58,237,0.08)]">
          <h3 className="text-sm font-bold text-slate-800 dark:text-slate-100">Configurar Novo Webhook</h3>
          <div>
            <label className="field-label">Nome de Identificação *</label>
            <input
              className="field-input"
              placeholder="Ex: Integração CRM Slack"
              value={form.name}
              onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
            />
          </div>
          <div>
            <label className="field-label">URL de Destino (Endpoint HTTP POST) *</label>
            <input
              type="url"
              className="field-input"
              placeholder="https://suaapi.com/webhooks/CS"
              value={form.url}
              onChange={(e) => setForm((f) => ({ ...f, url: e.target.value }))}
            />
          </div>
          <div>
            <label className="field-label">Eventos para Assinar</label>
            <div className="grid grid-cols-2 md:grid-cols-3 gap-2 mt-1">
              {ALL_TRIGGERS.map((t) => (
                <label key={t} className="flex items-center gap-2 text-xs font-semibold text-slate-650 dark:text-slate-350 cursor-pointer p-2 rounded-xl bg-slate-50 dark:bg-slate-850 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                  <input
                    type="checkbox"
                    checked={form.triggerTypes.includes(t)}
                    onChange={() => toggleTrigger(t)}
                    className="rounded text-violet-600 focus:ring-violet-400"
                  />
                  {t}
                </label>
              ))}
            </div>
          </div>
          <div className="flex gap-2 pt-2">
            <button type="button" onClick={create} className="btn-primary">Criar webhook</button>
            <button type="button" onClick={() => setCreating(false)} className="btn-outline">Cancelar</button>
          </div>
        </div>
      )}

      <div className="space-y-4">
        {subs.length === 0 && !creating && (
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-8 text-center text-slate-400 text-xs">
            Nenhuma assinatura de webhook configurada.
          </div>
        )}
        {subs.map((sub) => (
          <div key={sub.id} className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-4 space-y-3 hover:shadow-premium transition-all duration-150">
            <div className="flex items-start justify-between gap-3">
              <div className="min-w-0">
                <p className="text-sm font-bold text-slate-800 dark:text-slate-200">{sub.name}</p>
                <p className="text-xs font-mono text-slate-450 dark:text-slate-400 truncate mt-0.5">{sub.url}</p>
                <p className="text-[10px] text-slate-400 mt-1">ID #{sub.id} · Criado em {format(new Date(sub.createdAt), 'dd/MM/yyyy', { locale: ptBR })}</p>
              </div>
              <div className="flex items-center gap-3 shrink-0">
                {/* Active Switch Toggle style */}
                <button
                  type="button"
                  onClick={() => toggleActive(sub.id, !sub.isActive)}
                  className={cn(
                    'relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out outline-none',
                    sub.isActive ? 'bg-violet-600' : 'bg-slate-250 dark:bg-slate-700'
                  )}
                >
                  <span
                    className={cn(
                      'pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out',
                      sub.isActive ? 'translate-x-4' : 'translate-x-0'
                    )}
                  />
                </button>

                <ConfirmDialog
                  title="Excluir Webhook?"
                  description="Esta assinatura será permanentemente excluída e parará de enviar payloads."
                  onConfirm={() => remove(sub.id)}
                  trigger={
                    <button type="button" className="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                      <Trash2 className="w-4 h-4" />
                    </button>
                  }
                />
              </div>
            </div>
            <div className="flex flex-wrap gap-1.5 pt-1 border-t border-slate-100/50 dark:border-slate-800/50">
              {sub.triggerTypes.map((t) => (
                <span key={t} className="text-[9px] font-bold uppercase tracking-wide px-2 py-0.5 rounded bg-slate-50 dark:bg-slate-800 text-slate-500 border border-slate-200/40 dark:border-slate-700/40">{t}</span>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
