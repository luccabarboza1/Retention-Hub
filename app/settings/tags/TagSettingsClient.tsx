'use client'

import { useState, useTransition } from 'react'
import { Plus, Trash2, Tag, User, Layers, Info } from 'lucide-react'
import { toast } from 'sonner'
import { createTag, deleteTag } from '@/actions/settings'

type TagType = { id: number; name: string; type: string }

export function TagSettingsClient({ initialTags }: { initialTags: TagType[] }) {
  const [tags, setTags] = useState(initialTags)
  const [newName, setNewName] = useState('')
  const [newType, setNewType] = useState<'customer' | 'card'>('customer')
  const [isPending, startTransition] = useTransition()

  function add() {
    const name = newName.trim()
    if (!name) return
    startTransition(async () => {
      try {
        const tag = await createTag(name, newType)
        if (!tags.find((t) => t.id === tag.id)) {
          setTags((prev) =>
            [...prev, { id: tag.id, name: tag.name, type: tag.type }].sort((a, b) => a.name.localeCompare(b.name))
          )
        }
        setNewName('')
        toast.success('Etiqueta criada com sucesso')
      } catch (err) {
        toast.error('Erro ao criar etiqueta')
      }
    })
  }

  function remove(id: number) {
    startTransition(async () => {
      try {
        await deleteTag(id)
        setTags((prev) => prev.filter((t) => t.id !== id))
        toast.success('Etiqueta removida')
      } catch (err) {
        toast.error('Erro ao remover etiqueta')
      }
    })
  }

  const customerTags = tags.filter((t) => t.type === 'customer')
  const cardTags = tags.filter((t) => t.type === 'card')

  return (
    <div className="p-6 space-y-6 max-w-4xl animate-fadeIn">
      <div>
        <h2 className="text-xl font-bold text-slate-900 dark:text-slate-100">Gerenciador de Etiquetas</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Crie e remova marcadores personalizados para segmentar seus clientes e categorizar seus atendimentos.
        </p>
      </div>

      {/* Form Criar Tag */}
      <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl p-5 shadow-premium space-y-4">
        <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
          Nova Etiqueta
        </h3>
        <div className="flex gap-3 flex-wrap items-end">
          <div className="flex-1 min-w-[200px] space-y-1">
            <label className="field-label text-[10px]">Nome do Marcador</label>
            <input
              className="field-input-sm"
              placeholder="Ex: churn_risk, ra_reclame_aqui, vip..."
              value={newName}
              onChange={(e) => setNewName(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), add())}
              disabled={isPending}
            />
          </div>

          <div className="w-48 space-y-1">
            <label className="field-label text-[10px]">Contexto / Aplicação</label>
            <div className="select-wrap">
              <select
                className="field-input-sm"
                value={newType}
                onChange={(e) => setNewType(e.target.value as 'customer' | 'card')}
                disabled={isPending}
              >
                <option value="customer">Clientes (Ficha Cadastral)</option>
                <option value="card">Cards (Kanban / Ouvidoria)</option>
              </select>
            </div>
          </div>

          <button
            type="button"
            onClick={add}
            className="btn-primary btn-sm shrink-0"
            disabled={isPending || !newName.trim()}
          >
            <Plus className="w-4 h-4" /> Criar Etiqueta
          </button>
        </div>
      </div>

      {/* Grid de Listagem das Tags */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {[
          {
            type: 'customer',
            label: 'Etiquetas de Cliente',
            desc: 'Aplicadas no cadastro do cliente para segmentação comercial ou financeira.',
            icon: <User className="w-4 h-4 text-violet-600 dark:text-violet-400" />,
            list: customerTags,
            badgeStyle: 'bg-violet-50 text-violet-750 dark:bg-violet-950/20 dark:text-violet-400 border-violet-200/50 dark:border-violet-850',
          },
          {
            type: 'card',
            label: 'Etiquetas de Card',
            desc: 'Utilizadas no atendimento e no Kanban de ouvidoria para classificar motivos ou urgências.',
            icon: <Tag className="w-4 h-4 text-emerald-650 dark:text-emerald-400" />,
            list: cardTags,
            badgeStyle: 'bg-emerald-50 text-emerald-750 dark:bg-emerald-950/20 dark:text-emerald-400 border-emerald-200/50 dark:border-emerald-850',
          },
        ].map(({ label, desc, icon, list, badgeStyle }) => (
          <div key={label} className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl p-5 shadow-premium flex flex-col justify-between min-h-[350px]">
            <div className="space-y-3">
              <div className="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-850">
                <span className="p-1.5 rounded-lg bg-slate-50 dark:bg-slate-850">
                  {icon}
                </span>
                <div>
                  <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-200">{label}</h3>
                  <p className="text-[11px] text-slate-450 dark:text-slate-500 mt-0.5 leading-relaxed">{desc}</p>
                </div>
              </div>

              {list.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-12 text-center space-y-2">
                  <Layers className="w-8 h-8 text-slate-300 dark:text-slate-700" />
                  <p className="text-xs text-slate-400 dark:text-slate-500 italic">Nenhuma etiqueta cadastrada.</p>
                </div>
              ) : (
                <div className="flex flex-wrap gap-1.5 max-h-[220px] overflow-y-auto pr-1">
                  {list.map((tag) => (
                    <span
                      key={tag.id}
                      className={`inline-flex items-center gap-1.5 text-xs font-medium border rounded-lg px-2.5 py-1 ${badgeStyle} animate-fadeIn`}
                    >
                      {tag.name}
                      <button
                        type="button"
                        onClick={() => remove(tag.id)}
                        className="opacity-70 hover:opacity-100 hover:text-rose-500 transition-all cursor-pointer"
                        title="Excluir etiqueta"
                        disabled={isPending}
                      >
                        ×
                      </button>
                    </span>
                  ))}
                </div>
              )}
            </div>

            <div className="pt-3 border-t border-slate-100 dark:border-slate-850 flex items-center gap-1.5 text-[10px] text-slate-400 dark:text-slate-500">
              <Info className="w-3.5 h-3.5 shrink-0" />
              <span>Total: {list.length} {list.length === 1 ? 'etiqueta' : 'etiquetas'}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

