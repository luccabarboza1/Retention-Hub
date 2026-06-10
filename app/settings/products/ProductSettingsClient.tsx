'use client'

import { useState, useTransition } from 'react'
import { Plus, Trash2, Pencil, Check, X, ShieldAlert, Award } from 'lucide-react'
import { toast } from 'sonner'
import { createProductPlanConfig, updateProductPlanConfig, deleteProductPlanConfig } from '@/actions/settings'
import { ConfirmDialog } from '@/components/ConfirmDialog'

type Config = { id: number; productType: string; planName: string; pricePerUnit: number; unitLabel: string }

const EMPTY = { productType: '', planName: '', pricePerUnit: 0, unitLabel: '' }

function ConfigRow({ cfg, onUpdate, onDelete }: {
  cfg: Config
  onUpdate: (id: number, data: Partial<Config>) => Promise<void>
  onDelete: (id: number) => Promise<void>
}) {
  const [editing, setEditing] = useState(false)
  const [form, setForm] = useState({ ...cfg })
  const [isPending, startTransition] = useTransition()

  function save() {
    if (!form.productType.trim() || !form.planName.trim() || !form.unitLabel.trim()) {
      toast.error('Todos os campos obrigatórios devem ser preenchidos')
      return
    }
    startTransition(async () => {
      await onUpdate(cfg.id, form)
      setEditing(false)
      toast.success('Plano atualizado com sucesso')
    })
  }

  if (editing) {
    return (
      <tr className="bg-violet-50/10 dark:bg-violet-950/5 border-l-2 border-violet-500 transition-all">
        <td className="px-4 py-3">
          <input
            className="field-input-sm"
            value={form.productType}
            onChange={(e) => setForm((f) => ({ ...f, productType: e.target.value }))}
            disabled={isPending}
          />
        </td>
        <td className="px-4 py-3">
          <input
            className="field-input-sm"
            value={form.planName}
            onChange={(e) => setForm((f) => ({ ...f, planName: e.target.value }))}
            disabled={isPending}
          />
        </td>
        <td className="px-4 py-3">
          <input
            type="number"
            step="0.01"
            className="field-input-sm"
            value={form.pricePerUnit}
            onChange={(e) => setForm((f) => ({ ...f, pricePerUnit: Number(e.target.value) }))}
            disabled={isPending}
          />
        </td>
        <td className="px-4 py-3">
          <input
            className="field-input-sm"
            value={form.unitLabel}
            onChange={(e) => setForm((f) => ({ ...f, unitLabel: e.target.value }))}
            disabled={isPending}
          />
        </td>
        <td className="px-4 py-3 text-right">
          <div className="flex items-center justify-end gap-1">
            <button
              type="button"
              onClick={save}
              className="text-emerald-600 hover:text-emerald-750 p-1.5 rounded hover:bg-emerald-50 dark:hover:bg-emerald-950/20 transition-colors"
              disabled={isPending}
              title="Confirmar"
            >
              <Check className="w-4 h-4" />
            </button>
            <button
              type="button"
              onClick={() => setEditing(false)}
              className="text-slate-400 hover:text-slate-650 p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
              disabled={isPending}
              title="Cancelar"
            >
              <X className="w-4 h-4" />
            </button>
          </div>
        </td>
      </tr>
    )
  }

  return (
    <tr className="hover:bg-slate-50/50 dark:hover:bg-slate-850/25 transition-colors duration-150">
      <td className="px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-355">{cfg.productType}</td>
      <td className="px-4 py-3 text-xs">
        <span className="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium py-0.5 px-2 rounded-md border border-slate-200/50 dark:border-slate-750">
          {cfg.planName}
        </span>
      </td>
      <td className="px-4 py-3 text-xs font-mono font-medium text-slate-800 dark:text-slate-200">
        R$ {cfg.pricePerUnit.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}
      </td>
      <td className="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">/{cfg.unitLabel}</td>
      <td className="px-4 py-3 text-right">
        <div className="flex items-center justify-end gap-1">
          <button
            type="button"
            onClick={() => setEditing(true)}
            className="text-slate-400 hover:text-slate-750 dark:hover:text-slate-200 p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            title="Editar Plano"
          >
            <Pencil className="w-3.5 h-3.5" />
          </button>
          <ConfirmDialog
            title="Excluir Plano?"
            description={`Tem certeza que deseja remover o plano "${cfg.planName}" do produto "${cfg.productType}"?`}
            onConfirm={async () => {
              await onDelete(cfg.id)
              toast.success('Plano removido')
            }}
            trigger={
              <button
                type="button"
                className="text-slate-400 hover:text-rose-500 p-1.5 rounded hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors"
                title="Excluir Plano"
              >
                <Trash2 className="w-3.5 h-3.5" />
              </button>
            }
          />
        </div>
      </td>
    </tr>
  )
}

export function ProductSettingsClient({ initialConfigs }: { initialConfigs: Config[] }) {
  const [configs, setConfigs] = useState(initialConfigs)
  const [form, setForm] = useState(EMPTY)
  const [isPending, startTransition] = useTransition()

  function add() {
    if (!form.productType || !form.planName || !form.unitLabel) {
      toast.error('Tipo, Plano e Unidade são obrigatórios')
      return
    }
    startTransition(async () => {
      try {
        const cfg = await createProductPlanConfig(form)
        setConfigs((prev) => [...prev, { ...cfg, pricePerUnit: Number(cfg.pricePerUnit) }])
        setForm(EMPTY)
        toast.success('Plano de produto criado com sucesso')
      } catch (err) {
        toast.error('Erro ao criar plano de produto')
      }
    })
  }

  async function handleUpdate(id: number, data: Partial<Config>) {
    await updateProductPlanConfig(id, data)
    setConfigs((prev) => prev.map((c) => (c.id === id ? { ...c, ...data } : c)))
  }

  async function handleDelete(id: number) {
    await deleteProductPlanConfig(id)
    setConfigs((prev) => prev.filter((c) => c.id !== id))
  }

  return (
    <div className="p-6 space-y-6 max-w-5xl animate-fadeIn">
      <div>
        <h2 className="text-xl font-bold text-slate-900 dark:text-slate-100">Planos de Produto</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Configure as métricas de preço e unidades faturadas para os produtos da plataforma.
        </p>
      </div>

      <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl overflow-hidden shadow-premium">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b border-slate-100 dark:border-slate-850 bg-slate-50/50 dark:bg-slate-900/50">
                <th className="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-450 dark:text-slate-500">
                  Tipo de Produto
                </th>
                <th className="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-450 dark:text-slate-500">
                  Plano
                </th>
                <th className="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-450 dark:text-slate-500">
                  Preço por Unidade
                </th>
                <th className="px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-450 dark:text-slate-500">
                  Unidade de Medida
                </th>
                <th className="px-4 py-3 text-right" />
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 dark:divide-slate-850">
              {configs.map((cfg) => (
                <ConfigRow key={cfg.id} cfg={cfg} onUpdate={handleUpdate} onDelete={handleDelete} />
              ))}

              {/* Formulário Inline na última linha */}
              <tr className="bg-slate-50/30 dark:bg-slate-950/20 border-t border-slate-100 dark:border-slate-850">
                <td className="px-4 py-3">
                  <input
                    className="field-input-sm"
                    placeholder="Ex: Talk2, Host..."
                    value={form.productType}
                    onChange={(e) => setForm((f) => ({ ...f, productType: e.target.value }))}
                    disabled={isPending}
                  />
                </td>
                <td className="px-4 py-3">
                  <input
                    className="field-input-sm"
                    placeholder="Ex: Pro, Enterprise..."
                    value={form.planName}
                    onChange={(e) => setForm((f) => ({ ...f, planName: e.target.value }))}
                    disabled={isPending}
                  />
                </td>
                <td className="px-4 py-3">
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    className="field-input-sm"
                    placeholder="0,00"
                    value={form.pricePerUnit || ''}
                    onChange={(e) => setForm((f) => ({ ...f, pricePerUnit: Number(e.target.value) }))}
                    disabled={isPending}
                  />
                </td>
                <td className="px-4 py-3">
                  <input
                    className="field-input-sm"
                    placeholder="Ex: canal, ramal, gb..."
                    value={form.unitLabel}
                    onChange={(e) => setForm((f) => ({ ...f, unitLabel: e.target.value }))}
                    disabled={isPending}
                  />
                </td>
                <td className="px-4 py-3 text-right">
                  <button
                    type="button"
                    onClick={add}
                    className="btn-primary py-2 px-3 inline-flex items-center justify-center shrink-0"
                    disabled={isPending || !form.productType || !form.planName || !form.unitLabel}
                  >
                    <Plus className="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        {configs.length === 0 && (
          <div className="p-8 text-center flex flex-col items-center justify-center space-y-2 border-t border-slate-100 dark:border-slate-850">
            <Award className="w-8 h-8 text-slate-300 dark:text-slate-700" />
            <p className="text-xs text-slate-400 dark:text-slate-500 italic">Nenhum plano configurado no momento.</p>
          </div>
        )}
      </div>
    </div>
  )
}

