'use client'

import { useState, useTransition } from 'react'
import { Plus, Pencil, Trash2, X, Check, FileText } from 'lucide-react'
import { toast } from 'sonner'
import { createTemplate, updateTemplate, deleteTemplate } from '@/actions/settings'
import { ConfirmDialog } from '@/components/ConfirmDialog'

type Template = { id: number; title: string; body: string; productType: string | null }

function TemplateRow({ tpl, onUpdate, onDelete }: {
  tpl: Template
  onUpdate: (id: number, data: { title: string; body: string; productType: string | null }) => Promise<void>
  onDelete: (id: number) => Promise<void>
}) {
  const [editing, setEditing] = useState(false)
  const [form, setForm] = useState({ title: tpl.title, body: tpl.body, productType: tpl.productType ?? '' })
  const [isPending, startTransition] = useTransition()

  function save() {
    if (!form.title.trim() || !form.body.trim()) {
      toast.error('Título e conteúdo são obrigatórios')
      return
    }
    startTransition(async () => {
      await onUpdate(tpl.id, { ...form, productType: form.productType || null })
      setEditing(false)
      toast.success('Template atualizado')
    })
  }

  if (editing) {
    return (
      <div className="border border-violet-500/50 dark:border-violet-500/30 rounded-xl p-5 bg-violet-50/10 dark:bg-violet-950/5 space-y-4 animate-fadeIn">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div className="space-y-1">
            <label className="field-label text-[10px]">Título do Template *</label>
            <input
              className="field-input text-xs"
              value={form.title}
              onChange={(e) => setForm((f) => ({ ...f, title: e.target.value }))}
              placeholder="Ex: Reembolso Aprovado"
              disabled={isPending}
            />
          </div>
          <div className="space-y-1">
            <label className="field-label text-[10px]">Vincular ao Produto (Opcional)</label>
            <input
              className="field-input text-xs"
              value={form.productType}
              onChange={(e) => setForm((f) => ({ ...f, productType: e.target.value }))}
              placeholder="Ex: Talk2, Host..."
              disabled={isPending}
            />
          </div>
        </div>

        <div className="space-y-1">
          <label className="field-label text-[10px]">Conteúdo do Template *</label>
          <textarea
            className="field-input min-h-28 text-xs font-sans resize-y"
            value={form.body}
            onChange={(e) => setForm((f) => ({ ...f, body: e.target.value }))}
            placeholder="Escreva a resposta padrão aqui..."
            disabled={isPending}
          />
        </div>

        <div className="flex gap-2">
          <button type="button" onClick={save} className="btn-primary text-xs py-2 px-4" disabled={isPending}>
            <Check className="w-4 h-4" /> Salvar Alterações
          </button>
          <button
            type="button"
            onClick={() => setEditing(false)}
            className="btn-outline text-xs py-2 px-4"
            disabled={isPending}
          >
            <X className="w-4 h-4" /> Cancelar
          </button>
        </div>
      </div>
    )
  }

  return (
    <div className="flex items-start justify-between p-4 border border-slate-100 dark:border-slate-850 rounded-xl hover:bg-slate-50/50 dark:hover:bg-slate-850/20 transition-all duration-200 gap-4">
      <div className="flex-1 min-w-0 space-y-1">
        <div className="flex items-center gap-2 flex-wrap">
          <p className="text-sm font-semibold text-slate-800 dark:text-slate-200">{tpl.title}</p>
          {tpl.productType && (
            <span className="text-[9px] font-bold uppercase tracking-wider bg-violet-50 text-violet-750 dark:bg-violet-950/40 dark:text-violet-400 border border-violet-100 dark:border-violet-900/40 px-1.5 py-0.5 rounded-md">
              {tpl.productType}
            </span>
          )}
        </div>
        <p className="text-xs text-slate-500 dark:text-slate-400 whitespace-pre-wrap leading-relaxed">
          {tpl.body}
        </p>
      </div>

      <div className="flex gap-1 shrink-0">
        <button
          type="button"
          onClick={() => setEditing(true)}
          className="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
          title="Editar Template"
        >
          <Pencil className="w-3.5 h-3.5" />
        </button>
        <ConfirmDialog
          title="Remover Template?"
          description="Tem certeza que deseja excluir permanentemente este template de solução?"
          onConfirm={async () => {
            await onDelete(tpl.id)
            toast.success('Template removido')
          }}
          trigger={
            <button
              type="button"
              className="text-slate-400 hover:text-rose-500 p-1.5 rounded hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors"
              title="Excluir Template"
            >
              <Trash2 className="w-3.5 h-3.5" />
            </button>
          }
        />
      </div>
    </div>
  )
}

export function TemplatesClient({ initialTemplates }: { initialTemplates: Template[] }) {
  const [templates, setTemplates] = useState(initialTemplates)
  const [creating, setCreating] = useState(false)
  const [form, setForm] = useState({ title: '', body: '', productType: '' })
  const [isPending, startTransition] = useTransition()

  function handleCreate() {
    if (!form.title.trim() || !form.body.trim()) {
      toast.error('Título e conteúdo são obrigatórios')
      return
    }
    startTransition(async () => {
      try {
        const tpl = await createTemplate({ ...form, productType: form.productType || null })
        setTemplates((prev) => [...prev, { id: tpl.id, title: tpl.title, body: tpl.body, productType: tpl.productType }])
        setForm({ title: '', body: '', productType: '' })
        setCreating(false)
        toast.success('Template criado com sucesso')
      } catch (err) {
        toast.error('Erro ao criar template')
      }
    })
  }

  async function handleUpdate(id: number, data: { title: string; body: string; productType: string | null }) {
    await updateTemplate(id, data)
    setTemplates((prev) => prev.map((t) => (t.id === id ? { ...t, ...data } : t)))
  }

  async function handleDelete(id: number) {
    await deleteTemplate(id)
    setTemplates((prev) => prev.filter((t) => t.id !== id))
  }

  return (
    <div className="p-6 space-y-6 max-w-4xl animate-fadeIn">
      <div className="flex items-center justify-between border-b border-slate-100 dark:border-slate-850 pb-4">
        <div>
          <h2 className="text-xl font-bold text-slate-900 dark:text-slate-100">Templates de Solução</h2>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Respostas e notas padronizadas para agilizar o encerramento de atendimentos no Kanban.
          </p>
        </div>
        {!creating && (
          <button type="button" onClick={() => setCreating(true)} className="btn-primary text-xs font-semibold">
            <Plus className="w-4 h-4" /> Novo Template
          </button>
        )}
      </div>

      {creating && (
        <div className="bg-white dark:bg-slate-900 border border-violet-500/40 dark:border-violet-500/20 rounded-xl p-5 shadow-premium space-y-4 animate-fadeIn">
          <div className="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-850">
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
              Adicionar Novo Template
            </h3>
            <button
              type="button"
              onClick={() => setCreating(false)}
              className="text-slate-400 hover:text-slate-650 dark:hover:text-slate-350 p-1 rounded hover:bg-slate-50 dark:hover:bg-slate-800"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1">
              <label className="field-label text-[10px]">Título do Template *</label>
              <input
                className="field-input text-xs"
                value={form.title}
                onChange={(e) => setForm((f) => ({ ...f, title: e.target.value }))}
                placeholder="Ex: Acordo Financeiro de Retenção"
                disabled={isPending}
              />
            </div>
            <div className="space-y-1">
              <label className="field-label text-[10px]">Vincular ao Produto (Opcional)</label>
              <input
                className="field-input text-xs"
                value={form.productType}
                onChange={(e) => setForm((f) => ({ ...f, productType: e.target.value }))}
                placeholder="Ex: Talk2, Host..."
                disabled={isPending}
              />
            </div>
          </div>

          <div className="space-y-1">
            <label className="field-label text-[10px]">Conteúdo da Resposta *</label>
            <textarea
              className="field-input min-h-28 text-xs font-sans resize-y"
              value={form.body}
              onChange={(e) => setForm((f) => ({ ...f, body: e.target.value }))}
              placeholder="Digite o texto padrão com instruções ou soluções a serem aplicadas..."
              disabled={isPending}
            />
          </div>

          <div className="flex gap-2">
            <button type="button" onClick={handleCreate} className="btn-primary text-xs py-2 px-4" disabled={isPending}>
              Criar Template
            </button>
            <button
              type="button"
              onClick={() => setCreating(false)}
              className="btn-outline text-xs py-2 px-4"
              disabled={isPending}
            >
              Cancelar
            </button>
          </div>
        </div>
      )}

      <div className="space-y-3">
        {templates.length === 0 ? (
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl p-10 text-center flex flex-col items-center justify-center space-y-3 shadow-premium">
            <FileText className="w-8 h-8 text-slate-350 dark:text-slate-650" />
            <p className="text-sm text-slate-400 dark:text-slate-500 italic">Nenhum template cadastrado.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-3">
            {templates.map((tpl) => (
              <TemplateRow key={tpl.id} tpl={tpl} onUpdate={handleUpdate} onDelete={handleDelete} />
            ))}
          </div>
        )}
      </div>
    </div>
  )
}

