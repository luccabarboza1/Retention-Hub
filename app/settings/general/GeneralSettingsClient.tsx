'use client'

import { useState, useTransition } from 'react'
import { Plus, Trash2, GripVertical, HelpCircle, Save, ShieldCheck, ChevronDown, Code2 } from 'lucide-react'
import { toast } from 'sonner'
import {
  saveCardOptions,
  saveCustomerOptions,
  saveCustomerLookupUrl,
  saveChatLookupUrl,
  createKanbanColumn,
  updateKanbanColumn,
  deleteKanbanColumn,
} from '@/actions/settings'

type Column = { id: number; name: string; color: string; type: string }

type Props = {
  initialAgents: string[]
  initialOrigins: string[]
  initialTeams: string[]
  initialTiers: string[]
  initialSegments: string[]
  initialColumns: Column[]
  initialLookupUrl: string
  initialChatLookupUrl: string
}

const COLUMN_COLORS = ['slate', 'blue', 'violet', 'orange', 'green', 'red', 'yellow', 'pink']

const COLOR_METADATA: Record<string, { label: string; dot: string }> = {
  slate: { label: 'Slate', dot: 'bg-slate-500 ring-slate-500/20 shadow-[0_0_8px_rgba(100,116,139,0.4)]' },
  blue: { label: 'Blue / Sky', dot: 'bg-sky-500 ring-sky-500/20 shadow-[0_0_8px_rgba(56,189,248,0.4)]' },
  violet: { label: 'Violet', dot: 'bg-violet-500 ring-violet-500/20 shadow-[0_0_8px_rgba(139,92,246,0.4)]' },
  orange: { label: 'Orange', dot: 'bg-orange-500 ring-orange-500/20 shadow-[0_0_8px_rgba(249,115,22,0.4)]' },
  green: { label: 'Green / Emerald', dot: 'bg-emerald-500 ring-emerald-500/20 shadow-[0_0_8px_rgba(16,185,129,0.4)]' },
  red: { label: 'Red / Rose', dot: 'bg-rose-500 ring-rose-500/20 shadow-[0_0_8px_rgba(244,63,94,0.4)]' },
  yellow: { label: 'Yellow / Amber', dot: 'bg-amber-500 ring-amber-500/20 shadow-[0_0_8px_rgba(245,158,11,0.4)]' },
  pink: { label: 'Pink', dot: 'bg-pink-500 ring-pink-500/20 shadow-[0_0_8px_rgba(236,72,153,0.4)]' },
}

function ListEditor({
  title,
  description,
  items,
  onSave,
}: {
  title: string
  description?: string
  items: string[]
  onSave: (items: string[]) => Promise<void>
}) {
  const [list, setList] = useState(items)
  const [input, setInput] = useState('')
  const [isPending, startTransition] = useTransition()

  function add() {
    const v = input.trim()
    if (!v || list.includes(v)) return
    const next = [...list, v]
    setList(next)
    setInput('')
    startTransition(async () => {
      await onSave(next)
      toast.success('Opção adicionada com sucesso')
    })
  }

  function remove(item: string) {
    const next = list.filter((x) => x !== item)
    setList(next)
    startTransition(async () => {
      await onSave(next)
      toast.success('Opção removida')
    })
  }

  return (
    <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl p-5 shadow-premium hover:shadow-[0_4px_20px_rgba(124,58,237,0.05)] transition-all duration-200 flex flex-col h-full justify-between gap-4">
      <div className="space-y-1">
        <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-200">{title}</h3>
        {description && <p className="text-xs text-slate-400 dark:text-slate-500">{description}</p>}
      </div>

      <div className="flex-1">
        <div className="flex flex-wrap gap-1.5 min-h-[60px] content-start py-1">
          {list.map((item) => (
            <span
              key={item}
              className="inline-flex items-center gap-1.5 text-xs bg-slate-50 hover:bg-slate-100 dark:bg-slate-850 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-800 rounded-lg px-2.5 py-1 transition-all duration-150"
            >
              {item}
              <button
                type="button"
                onClick={() => remove(item)}
                className="text-slate-400 hover:text-rose-500 cursor-pointer transition-colors"
                title="Excluir"
              >
                ×
              </button>
            </span>
          ))}
          {list.length === 0 && (
            <p className="text-xs text-slate-400 dark:text-slate-500 italic">Nenhum item configurado.</p>
          )}
        </div>
      </div>

      <div className="flex gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/60">
        <input
          className="field-input text-xs"
          placeholder="Adicionar opção..."
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), add())}
          disabled={isPending}
        />
        <button
          type="button"
          onClick={add}
          className="btn-primary py-2 px-3 shrink-0"
          disabled={isPending || !input.trim()}
        >
          <Plus className="w-3.5 h-3.5" />
        </button>
      </div>
    </div>
  )
}

export function GeneralSettingsClient({
  initialAgents,
  initialOrigins,
  initialTeams,
  initialTiers,
  initialSegments,
  initialColumns,
  initialLookupUrl,
  initialChatLookupUrl,
}: Props) {
  const [columns, setColumns] = useState(initialColumns)
  const [lookupUrl, setLookupUrl] = useState(initialLookupUrl)
  const [chatLookupUrl, setChatLookupUrl] = useState(initialChatLookupUrl)
  const [newCol, setNewCol] = useState({ name: '', color: 'slate', type: 'open' })
  const [showLookupFormat, setShowLookupFormat] = useState(false)
  const [showChatLookupFormat, setShowChatLookupFormat] = useState(false)
  const [isPending, startTransition] = useTransition()

  function addColumn() {
    if (!newCol.name.trim()) return
    startTransition(async () => {
      const col = await createKanbanColumn(newCol)
      setColumns((c) => [...c, col])
      setNewCol({ name: '', color: 'slate', type: 'open' })
      toast.success('Coluna criada com sucesso')
    })
  }

  function removeColumn(id: number) {
    startTransition(async () => {
      try {
        await deleteKanbanColumn(id)
        setColumns((c) => c.filter((col) => col.id !== id))
        toast.success('Coluna removida')
      } catch (e) {
        toast.error(e instanceof Error ? e.message : 'Erro ao remover coluna')
      }
    })
  }

  function saveLookupUrl() {
    startTransition(async () => {
      await saveCustomerLookupUrl(lookupUrl)
      toast.success('URL de Lookup salva com sucesso')
    })
  }

  function saveChatUrl() {
    startTransition(async () => {
      await saveChatLookupUrl(chatLookupUrl)
      toast.success('URL de Lookup de Chat salva com sucesso')
    })
  }

  return (
    <div className="p-6 space-y-8 max-w-5xl animate-fadeIn">
      <div>
        <h2 className="text-xl font-bold text-slate-900 dark:text-slate-100">Configurações Gerais</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Gerencie as opções do sistema, colunas do Kanban de atendimento e endpoints de integração externa.
        </p>
      </div>

      {/* Grid de Opções Básicas */}
      <div className="space-y-4">
        <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
          Opções do Kanban & Atendimento
        </h3>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <ListEditor
            title="Agentes de Atendimento"
            description="Membros do time de Ouvidoria / CS"
            items={initialAgents}
            onSave={(opts) => saveCardOptions('agents', opts)}
          />
          <ListEditor
            title="Origens de Contato"
            description="De onde o cliente veio"
            items={initialOrigins}
            onSave={(opts) => saveCardOptions('origins', opts)}
          />
          <ListEditor
            title="Times Envolvidos"
            description="Departamentos que apoiam a resolução"
            items={initialTeams}
            onSave={(opts) => saveCardOptions('teams', opts)}
          />
        </div>
      </div>

      <div className="space-y-4">
        <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
          Parâmetros do Cliente
        </h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <ListEditor
            title="Tiers de Cliente"
            description="Níveis de importância ou classificação do cliente"
            items={initialTiers}
            onSave={(opts) => saveCustomerOptions('tiers', opts)}
          />
          <ListEditor
            title="Segmentos de Mercado"
            description="Setor comercial de atuação da empresa"
            items={initialSegments}
            onSave={(opts) => saveCustomerOptions('segments', opts)}
          />
        </div>
      </div>

      {/* Kanban Columns */}
      <div className="space-y-4">
        <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
          Colunas do Quadro Kanban
        </h3>
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl p-5 shadow-premium space-y-4">
          <div className="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-850">
            <div>
              <h4 className="text-sm font-semibold text-slate-800 dark:text-slate-200">Estrutura de Estágios</h4>
              <p className="text-xs text-slate-450 dark:text-slate-500 mt-0.5">
                Defina o fluxo de atendimento da Ouvidoria e o mapeamento de status
              </p>
            </div>
          </div>

          <div className="space-y-1">
            {columns.map((col, index) => {
              const colorInfo = COLOR_METADATA[col.color] || COLOR_METADATA.slate
              return (
                <div
                  key={col.id}
                  className="flex items-center gap-3 py-2 px-3 hover:bg-slate-50/60 dark:hover:bg-slate-850/40 rounded-lg transition-all"
                >
                  <GripVertical className="w-3.5 h-3.5 text-slate-400 dark:text-slate-650 cursor-grab shrink-0" />
                  <span className={`w-3 h-3 rounded-full shrink-0 ring-4 ${colorInfo.dot}`} />
                  <span className="text-sm font-medium text-slate-700 dark:text-slate-300 flex-1">{col.name}</span>
                  <div className="flex items-center gap-2">
                    <span className="text-[10px] uppercase tracking-wide font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded-full">
                      {col.type}
                    </span>
                    <button
                      type="button"
                      onClick={() => removeColumn(col.id)}
                      className="text-slate-400 hover:text-rose-500 p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors cursor-pointer"
                      title="Excluir Coluna"
                      disabled={isPending}
                    >
                      <Trash2 className="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              )
            })}
            {columns.length === 0 && (
              <p className="text-sm text-slate-400 dark:text-slate-500 italic py-4 text-center">
                Nenhuma coluna cadastrada. Crie uma abaixo.
              </p>
            )}
          </div>

          {/* Form para adicionar coluna */}
          <div className="flex flex-wrap items-end gap-3 p-4 bg-slate-50/50 dark:bg-slate-900/10 border-t border-slate-100 dark:border-slate-800">
            <div className="flex-1 min-w-[200px] space-y-1">
              <label className="field-label text-[10px]">Nome da Coluna</label>
              <input
                type="text"
                placeholder="Ex: Em Análise"
                className="field-input-sm"
                value={newCol.name}
                onChange={(e) => setNewCol((c) => ({ ...c, name: e.target.value }))}
                onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), addColumn())}
                disabled={isPending}
              />
            </div>

            <div className="w-32 space-y-1">
              <label className="field-label text-[10px]">Cor / Glow</label>
              <div className="select-wrap">
                <select
                  className="field-input-sm"
                  value={newCol.color}
                  onChange={(e) => setNewCol((c) => ({ ...c, color: e.target.value }))}
                  disabled={isPending}
                >
                  {COLUMN_COLORS.map((color) => {
                    const meta = COLOR_METADATA[color] || { label: color }
                    return (
                      <option key={color} value={color}>
                        {meta.label}
                      </option>
                    )
                  })}
                </select>
              </div>
            </div>

            <div className="w-32 space-y-1">
              <label className="field-label text-[10px]">Mapeamento</label>
              <div className="select-wrap">
                <select
                  className="field-input-sm"
                  value={newCol.type}
                  onChange={(e) => setNewCol((c) => ({ ...c, type: e.target.value }))}
                  disabled={isPending}
                >
                  <option value="open">Aberto (open)</option>
                  <option value="closed">Fechado (closed)</option>
                  <option value="retained">Retido (retained)</option>
                  <option value="churn">Cancelado (churn)</option>
                </select>
              </div>
            </div>

            <button
              type="button"
              onClick={addColumn}
              className="btn-primary btn-sm shrink-0"
              disabled={isPending || !newCol.name.trim()}
            >
              <Plus className="w-4 h-4" /> Criar Coluna
            </button>
          </div>
        </div>
      </div>

      {/* Customer Lookup URL */}
      <div className="space-y-4">
        <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
          Integrações & API de Busca
        </h3>
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl p-5 shadow-premium space-y-4">
          <div className="flex items-start gap-3 bg-violet-50/50 dark:bg-violet-950/20 border border-violet-100 dark:border-violet-900/40 rounded-xl p-4">
            <HelpCircle className="w-5 h-5 text-violet-650 dark:text-violet-400 shrink-0 mt-0.5" />
            <div className="space-y-1">
              <h4 className="text-xs font-bold text-violet-850 dark:text-violet-300">Como funciona o Lookup?</h4>
              <p className="text-xs text-violet-750 dark:text-violet-400/90 leading-relaxed">
                Ao criar um card, você pode buscar os dados completos do cliente diretamente em um sistema de billing
                ou CRM parceiro. O endpoint especificado abaixo deve retornar um JSON válido com os dados da empresa.
              </p>
            </div>
          </div>

          <div className="space-y-1.5">
            <label className="field-label">URL de Consulta (Endpoint Externo)</label>
            <div className="flex gap-2">
              <input
                type="url"
                className="field-input flex-1 font-mono text-xs"
                placeholder="https://suaapi.com/api/customers?email="
                value={lookupUrl}
                onChange={(e) => setLookupUrl(e.target.value)}
                disabled={isPending}
              />
              <button
                type="button"
                onClick={saveLookupUrl}
                className="btn-primary shrink-0 text-xs font-semibold flex items-center gap-1.5"
                disabled={isPending}
              >
                <Save className="w-3.5 h-3.5" /> Salvar URL
              </button>
            </div>
            <p className="text-[10px] text-slate-400 dark:text-slate-500">
              O e-mail digitado é enviado num POST <code className="font-mono">{'{ "email": "..." }'}</code> para o endpoint configurado.
            </p>
          </div>

          {/* Nota contraível: formato de retorno esperado */}
          <div className="border border-slate-200/60 dark:border-slate-800 rounded-xl overflow-hidden">
            <button
              type="button"
              onClick={() => setShowLookupFormat((v) => !v)}
              className="w-full flex items-center justify-between gap-2 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-850/40 transition-colors cursor-pointer"
            >
              <span className="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                <Code2 className="w-4 h-4 text-violet-600 dark:text-violet-400 shrink-0" />
                Formato de retorno esperado
              </span>
              <ChevronDown
                className={`w-4 h-4 text-slate-400 shrink-0 transition-transform ${showLookupFormat ? 'rotate-180' : ''}`}
              />
            </button>

            {showLookupFormat && (
              <div className="px-4 pb-4 pt-1 space-y-3 border-t border-slate-100 dark:border-slate-850 animate-fadeIn">
                <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  O endpoint deve responder <strong>200 OK</strong> com um JSON de objeto único. Cada campo presente
                  preenche automaticamente o formulário de criação de cliente; campos ausentes ou <code className="font-mono">null</code> são
                  ignorados. Aceita tanto <code className="font-mono">camelCase</code> quanto <code className="font-mono">snake_case</code>.
                </p>

                <pre className="text-[11px] font-mono leading-relaxed bg-slate-900 dark:bg-slate-950 text-slate-200 rounded-lg p-4 overflow-x-auto">
{`{
  "companyName": "Umbler Cloud",      // ou company_name
  "clientName": "Lucca Barboza",      // ou client_name
  "email": "lucca@empresa.com",
  "phone": "+55 51 99999-0000",       // ou telefone — usado p/ buscar conversas
  "segment": "Tecnologia",
  "companySize": "10-50 funcionários", // ou company_size
  "monthlyFee": 499.90,                // ou monthly_fee (número)
  "tier": "Enterprise",
  "planName": "Cloud Pro 2",           // ou plan_name
  "instagramFollowersCount": 1200,     // ou instagram_followers_count
  "contractedAt": "2024-01-15",        // ou contracted_at (YYYY-MM-DD)
  "canceledAt": null,                  // ou canceled_at
  "relatedEmails": [                   // ou related_emails (array)
    "financeiro@empresa.com",
    "ti@empresa.com"
  ]
}`}
                </pre>

                <ul className="text-[11px] text-slate-500 dark:text-slate-400 space-y-1 list-disc pl-4">
                  <li>Todos os campos são opcionais — retorne apenas o que tiver.</li>
                  <li>Datas no formato <code className="font-mono">YYYY-MM-DD</code>; valores monetários e contagens como número.</li>
                  <li><code className="font-mono">relatedEmails</code> deve ser um array de strings.</li>
                  <li>Respostas com status diferente de 2xx são tratadas como erro e exibidas ao usuário.</li>
                </ul>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Chat Lookup URL (Umbler Talk) */}
      <div className="space-y-4">
        <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
          Integração de Conversas (Umbler Talk)
        </h3>
        <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl p-5 shadow-premium space-y-4">
          <div className="flex items-start gap-3 bg-violet-50/50 dark:bg-violet-950/20 border border-violet-100 dark:border-violet-900/40 rounded-xl p-4">
            <HelpCircle className="w-5 h-5 text-violet-650 dark:text-violet-400 shrink-0 mt-0.5" />
            <div className="space-y-1">
              <h4 className="text-xs font-bold text-violet-850 dark:text-violet-300">Como funciona?</h4>
              <p className="text-xs text-violet-750 dark:text-violet-400/90 leading-relaxed">
                Ao buscar um contato na criação de um card, o sistema usa o telefone retornado pelo lookup de
                cliente para consultar este endpoint e trazer as conversas do Umbler Talk, que podem ser
                vinculadas ao card.
              </p>
            </div>
          </div>

          <div className="space-y-1.5">
            <label className="field-label">URL de Consulta de Conversas (Endpoint Externo)</label>
            <div className="flex gap-2">
              <input
                type="url"
                className="field-input flex-1 font-mono text-xs"
                placeholder="https://suaapi.com/umbler-talk/conversas"
                value={chatLookupUrl}
                onChange={(e) => setChatLookupUrl(e.target.value)}
                disabled={isPending}
              />
              <button
                type="button"
                onClick={saveChatUrl}
                className="btn-primary shrink-0 text-xs font-semibold flex items-center gap-1.5"
                disabled={isPending}
              >
                <Save className="w-3.5 h-3.5" /> Salvar URL
              </button>
            </div>
            <p className="text-[10px] text-slate-400 dark:text-slate-500">
              Recebe um POST <code className="font-mono">{'{ "query": "<telefone ou e-mail>" }'}</code> e retorna as conversas do contato.
            </p>
          </div>

          {/* Nota contraível: formato de retorno esperado */}
          <div className="border border-slate-200/60 dark:border-slate-800 rounded-xl overflow-hidden">
            <button
              type="button"
              onClick={() => setShowChatLookupFormat((v) => !v)}
              className="w-full flex items-center justify-between gap-2 px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-850/40 transition-colors cursor-pointer"
            >
              <span className="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                <Code2 className="w-4 h-4 text-violet-600 dark:text-violet-400 shrink-0" />
                Formato de retorno esperado
              </span>
              <ChevronDown
                className={`w-4 h-4 text-slate-400 shrink-0 transition-transform ${showChatLookupFormat ? 'rotate-180' : ''}`}
              />
            </button>

            {showChatLookupFormat && (
              <div className="px-4 pb-4 pt-1 space-y-3 border-t border-slate-100 dark:border-slate-850 animate-fadeIn">
                <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  O endpoint deve responder <strong>200 OK</strong> com um <strong>array de conversas</strong> (ou um objeto
                  com a chave <code className="font-mono">chats</code>). Cada conversa precisa de um <code className="font-mono">id</code> (ID
                  externo do chat). Os demais campos preenchem as métricas do chat vinculado.
                </p>

                <pre className="text-[11px] font-mono leading-relaxed bg-slate-900 dark:bg-slate-950 text-slate-200 rounded-lg p-4 overflow-x-auto">
{`[
  {
    "id": "abc123",                    // obrigatório (ID do chat)
    "startedAt": "2026-06-10T14:30:00Z", // início (ISO)
    "closedAt": "2026-06-10T15:10:00Z",  // fim — duração é calculada
    "firstResponseHours": 0.5,           // tempo de 1ª resposta (horas)
    "agents": ["Lucca", "Maria"]         // ou "interactions": [{ "agent": "...", "interactedOn": "..." }]
  }
]`}
                </pre>

                <ul className="text-[11px] text-slate-500 dark:text-slate-400 space-y-1 list-disc pl-4">
                  <li><code className="font-mono">id</code> é obrigatório; conversas sem id são ignoradas.</li>
                  <li>A <strong>duração</strong> é derivada de <code className="font-mono">closedAt − startedAt</code>.</li>
                  <li><code className="font-mono">agents</code> (array de nomes) ou <code className="font-mono">interactions</code> são aceitos.</li>
                  <li>Datas em ISO 8601; status diferente de 2xx é tratado como erro.</li>
                </ul>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}

