'use client'

import { useState, useTransition, useEffect } from 'react'
import { useRouter } from 'next/navigation'
import { ChevronRight, ChevronLeft, AlertTriangle, Search, Check, Globe, MessageSquare } from 'lucide-react'
import { cn } from '@/lib/utils'
import { getCardCount, getProductsForCustomer, createCard } from '@/actions/cards'
import { quickCreateCustomer } from '@/actions/customers'
import { lookupContact, type ChatLookupData } from '@/actions/chats'
import { saveCardOptions } from '@/actions/settings'
import { ManagedCombobox } from '@/components/ManagedCombobox'
import { DatePicker } from '@/components/DatePicker'
import { TagInput } from '@/components/TagInput'
import { toast } from 'sonner'

type Column = { id: number; name: string }
type Customer = { id: number; companyName: string; clientName: string }
type Product = { id: number; productType: string; planName: string | null; externalId: string }
type Options = {
  agents: string[]
  origins: string[]
  teams: string[]
}

interface CardWizardProps {
  columns: Column[]
  customers: Customer[]
  options: Options
  allTags: string[]
}

export function CardWizard({
  columns,
  customers,
  options: initialOptions,
  allTags,
}: CardWizardProps) {
  const router = useRouter()
  const [step, setStep] = useState(0)
  const [isPending, startTransition] = useTransition()
  const [reincidence, setReincidence] = useState<{ total: number; open: number } | null>(null)

  // Options state to handle inline management
  const [options, setOptions] = useState(initialOptions)
  const [localCustomers, setLocalCustomers] = useState<Customer[]>(customers)
  const [customerSearch, setCustomerSearch] = useState('')
  const [lookupEmail, setLookupEmail] = useState('')
  const [lookupResult, setLookupResult] = useState<any | null>(null)
  const [lookupPhone, setLookupPhone] = useState<string | null>(null)
  const [searchingLookup, setSearchingLookup] = useState(false)
  const [conversations, setConversations] = useState<ChatLookupData[]>([])
  const [selectedChatIds, setSelectedChatIds] = useState<Set<string>>(new Set())

  const [form, setForm] = useState({
    customerId: '',
    productId: '',
    startedAt: new Date().toISOString().slice(0, 10),
    priority: 'normal',
    ombudsmanAgent: '',
    ticketOrigin: '',
    responsibleTeam: '',
    contactReason: '',
    reasonDetails: '',
    deadlineAt: '',
    tags: [] as string[],
    raClaimLink: '',
  })

  const [products, setProducts] = useState<Product[]>([])

  function set(key: string, value: any) {
    setForm((f) => ({ ...f, [key]: value }))
  }

  async function onCustomerChange(id: string) {
    set('customerId', id)
    set('productId', '')
    if (!id) {
      setReincidence(null)
      setProducts([])
      return
    }
    const [count, prods] = await Promise.all([
      getCardCount(Number(id)),
      getProductsForCustomer(Number(id)),
    ])
    setReincidence(count)
    setProducts(prods)
  }

  async function handleExternalLookup() {
    if (!lookupEmail.trim()) return
    setSearchingLookup(true)
    setLookupResult(null)
    try {
      const res = await lookupContact(lookupEmail)

      if (res.customer.status === 'not_configured') {
        toast.error('Lookup de cliente não configurado nas Configurações.')
        return
      }
      if (res.customer.status === 'error') {
        toast.error(res.customer.message)
        return
      }

      setLookupResult(res.customer.data)
      setLookupPhone(res.phone)
      const withId = res.chats.filter((c) => c.id)
      setConversations(withId)
      setSelectedChatIds(new Set(withId.map((c) => String(c.id))))

      if (withId.length > 0) {
        toast.success(`Cliente encontrado! ${withId.length} conversa(s) localizada(s).`)
      } else if (res.chatStatus === 'error') {
        toast.success('Cliente encontrado. (Falha ao buscar conversas no Umbler Talk.)')
      } else {
        toast.success('Cliente encontrado na base externa!')
      }
    } catch {
      toast.error('Erro ao buscar cliente.')
    } finally {
      setSearchingLookup(false)
    }
  }

  function toggleChat(id: string) {
    setSelectedChatIds((prev) => {
      const next = new Set(prev)
      if (next.has(id)) next.delete(id)
      else next.add(id)
      return next
    })
  }

  async function handleImportLookup() {
    if (!lookupResult) return
    startTransition(async () => {
      try {
        const newCust = await quickCreateCustomer({
          companyName: lookupResult.companyName || lookupResult.company_name || 'Empresa Importada',
          clientName: lookupResult.clientName || lookupResult.client_name || 'Contato Importado',
          email: lookupResult.email || lookupEmail,
          phone: lookupPhone || lookupResult.phone || lookupResult.telefone || null,
          monthlyFee: lookupResult.monthlyFee || lookupResult.monthly_fee || null,
          tier: lookupResult.tier || null,
          planName: lookupResult.planName || lookupResult.plan_name || null,
          segment: lookupResult.segment || null,
          companySize: lookupResult.companySize || lookupResult.company_size || null,
          instagramFollowersCount: lookupResult.instagramFollowersCount || 0,
          contractedAt: lookupResult.contractedAt || null,
          canceledAt: lookupResult.canceledAt || null,
        })
        const mapped = {
          id: newCust.id,
          companyName: newCust.companyName,
          clientName: newCust.clientName,
        }
        setLocalCustomers((prev) => [mapped, ...prev])
        await onCustomerChange(String(newCust.id))
        setLookupResult(null)
        setLookupEmail('')
        toast.success('Cliente importado e selecionado!')
      } catch (err) {
        toast.error('Falha ao importar cliente.')
      }
    })
  }

  function handleOptionChange(type: 'agents' | 'origins' | 'teams', list: string[]) {
    setOptions((prev) => ({ ...prev, [type]: list }))
    startTransition(async () => {
      await saveCardOptions(type, list)
    })
  }

  function validateStep() {
    if (step === 0 && !form.customerId) return false
    return true
  }

  function handleSubmit() {
    const chats = conversations
      .filter((c) => c.id && selectedChatIds.has(String(c.id)))
      .map((c) => ({
        id: String(c.id),
        startedAt: c.startedAt ?? null,
        closedAt: c.closedAt ?? null,
        firstResponseHours: c.firstResponseHours ?? null,
        agents: c.agents ?? c.interactions?.map((i) => i.agent) ?? [],
      }))

    startTransition(async () => {
      await createCard({
        customerId: Number(form.customerId),
        productId: form.productId ? Number(form.productId) : null,
        startedAt: form.startedAt,
        priority: form.priority,
        ombudsmanAgent: form.ombudsmanAgent || null,
        ticketOrigin: form.ticketOrigin || null,
        responsibleTeam: form.responsibleTeam || null,
        contactReason: form.contactReason || null,
        reasonDetails: form.reasonDetails || null,
        deadlineAt: form.deadlineAt || null,
        chats,
        // syncCardTags will run on redirect or inside createCard
      })
    })
  }

  const steps = ['Cliente', 'Responsáveis', 'Detalhes']

  const filteredCustomers = localCustomers.filter(
    (c) =>
      c.companyName.toLowerCase().includes(customerSearch.toLowerCase()) ||
      c.clientName.toLowerCase().includes(customerSearch.toLowerCase())
  )

  const selectedCustomer = localCustomers.find((c) => String(c.id) === form.customerId)

  return (
    <div className="max-w-3xl mx-auto bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-6 shadow-premium">
      {/* Progress Bar */}
      <div className="mb-8 relative">
        <div className="flex items-center justify-between">
          {steps.map((s, i) => (
            <div key={s} className="flex flex-col items-center relative z-10">
              <div
                className={cn(
                  'w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold transition-all duration-200',
                  i < step
                    ? 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white'
                    : i === step
                    ? 'border-2 border-violet-600 bg-white dark:bg-slate-900 text-violet-600 shadow-md shadow-violet-600/10'
                    : 'border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-400'
                )}
              >
                {i + 1}
              </div>
              <span
                className={cn(
                  'text-xs mt-2 font-medium',
                  i === step ? 'text-violet-600 dark:text-violet-400' : 'text-slate-400 dark:text-slate-500'
                )}
              >
                {s}
              </span>
            </div>
          ))}
        </div>
        {/* Progress Line */}
        <div className="absolute top-4 left-4 right-4 h-[2px] bg-slate-100 dark:bg-slate-800 -z-0">
          <div
            className="h-full bg-gradient-to-r from-violet-600 to-indigo-500 transition-all duration-300"
            style={{ width: `${(step / (steps.length - 1)) * 100}%` }}
          />
        </div>
      </div>

      {/* Step 1: Cliente */}
      {step === 0 && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {/* Local Selection */}
            <div className="space-y-4">
              <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100">Selecionar Cliente Existente</h3>

              <div>
                <label className="field-label">Buscar e Selecionar</label>
                <div className="relative mb-2">
                  <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                  <input
                    type="text"
                    className="field-input pl-9"
                    placeholder="Nome da empresa ou contato..."
                    value={customerSearch}
                    onChange={(e) => setCustomerSearch(e.target.value)}
                  />
                </div>

                <div className="border border-slate-200 dark:border-slate-800 rounded-xl max-h-[200px] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                  {filteredCustomers.map((c) => (
                    <button
                      key={c.id}
                      type="button"
                      onClick={() => onCustomerChange(String(c.id))}
                      className={cn(
                        'w-full flex items-center justify-between px-3 py-2 text-sm text-left hover:bg-violet-50/50 dark:hover:bg-violet-950/10 transition-colors',
                        form.customerId === String(c.id) && 'bg-violet-50 dark:bg-violet-950/20 text-violet-600 dark:text-violet-400 font-medium'
                      )}
                    >
                      <div>
                        <p className="font-medium text-slate-800 dark:text-slate-200">{c.companyName}</p>
                        <p className="text-xs text-slate-400 dark:text-slate-500">{c.clientName}</p>
                      </div>
                      {form.customerId === String(c.id) && <Check className="w-4 h-4 text-violet-600" />}
                    </button>
                  ))}
                  {filteredCustomers.length === 0 && (
                    <p className="p-4 text-center text-xs text-slate-400">Nenhum cliente encontrado.</p>
                  )}
                </div>
              </div>
            </div>

            {/* External Lookup */}
            <div className="space-y-4 border-t md:border-t-0 md:border-l border-slate-200 dark:border-slate-800 md:pl-6 pt-6 md:pt-0">
              <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <Globe className="w-4 h-4 text-violet-600" />
                Importar via Lookup Externo
              </h3>

              <div className="flex gap-2">
                <input
                  type="email"
                  className="field-input"
                  placeholder="email@cliente.com"
                  value={lookupEmail}
                  onChange={(e) => setLookupEmail(e.target.value)}
                />
                <button
                  type="button"
                  onClick={handleExternalLookup}
                  className="btn-outline shrink-0"
                  disabled={searchingLookup}
                >
                  {searchingLookup ? 'Buscando...' : 'Buscar'}
                </button>
              </div>

              {lookupResult && (
                <div className="p-3.5 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-xl space-y-3">
                  <div>
                    <h4 className="font-semibold text-sm text-slate-800 dark:text-slate-200">
                      {lookupResult.companyName || lookupResult.company_name || 'Sem Empresa'}
                    </h4>
                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                      {lookupResult.clientName || lookupResult.client_name || 'Sem Contato'}
                    </p>
                  </div>
                  <button
                    type="button"
                    onClick={handleImportLookup}
                    className="btn-primary w-full text-xs py-2 justify-center"
                  >
                    Importar e Selecionar
                  </button>
                </div>
              )}
            </div>
          </div>

          {selectedCustomer && (
            <div className="p-3 bg-violet-50 dark:bg-violet-950/20 border border-violet-100 dark:border-violet-900/50 rounded-xl flex items-center justify-between">
              <div>
                <p className="text-xs text-slate-400">Cliente Selecionado:</p>
                <p className="text-sm font-semibold text-violet-600 dark:text-violet-400">{selectedCustomer.companyName}</p>
              </div>
              <button
                type="button"
                onClick={() => onCustomerChange('')}
                className="text-xs text-rose-500 hover:underline"
              >
                Remover
              </button>
            </div>
          )}

          {conversations.length > 0 && (
            <div className="border border-slate-200 dark:border-slate-800 rounded-xl p-4 space-y-3 bg-slate-50/40 dark:bg-slate-950/20">
              <div className="flex items-center gap-2">
                <MessageSquare className="w-4 h-4 text-violet-600" />
                <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100">
                  Conversas encontradas ({conversations.length})
                </h3>
                <span className="text-[10px] text-slate-400">
                  {selectedChatIds.size} selecionada(s) para vincular
                </span>
              </div>

              <div className="divide-y divide-slate-100 dark:divide-slate-800 max-h-[240px] overflow-y-auto">
                {conversations.map((c) => {
                  const id = String(c.id)
                  const checked = selectedChatIds.has(id)
                  const duration =
                    c.startedAt && c.closedAt
                      ? ((new Date(c.closedAt).getTime() - new Date(c.startedAt).getTime()) / 3600000).toFixed(1)
                      : null
                  const agents = c.agents ?? c.interactions?.map((i) => i.agent) ?? []
                  return (
                    <label
                      key={id}
                      className="flex items-start gap-3 py-2.5 cursor-pointer first:pt-0 last:pb-0"
                    >
                      <input
                        type="checkbox"
                        checked={checked}
                        onChange={() => toggleChat(id)}
                        className="mt-0.5 w-4 h-4 rounded text-violet-600 border-slate-300 focus:ring-violet-500 cursor-pointer shrink-0"
                      />
                      <div className="min-w-0 flex-1">
                        <p className="text-xs font-mono font-semibold text-slate-800 dark:text-slate-200 truncate">
                          {id}
                        </p>
                        <p className="text-[10px] text-slate-400 mt-0.5">
                          {c.startedAt ? new Date(c.startedAt).toLocaleString('pt-BR') : 'Sem data'}
                          {c.firstResponseHours != null && ` · 1ª resp: ${Number(c.firstResponseHours).toFixed(1)}h`}
                          {duration && ` · duração: ${duration}h`}
                          {agents.length > 0 && ` · ${agents.join(', ')}`}
                        </p>
                      </div>
                    </label>
                  )
                })}
              </div>
            </div>
          )}

          {reincidence && reincidence.open > 0 && (
            <div className="flex items-start gap-2.5 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/60 shadow-[0_0_12px_rgba(245,158,11,0.06)] animate-fadeIn">
              <AlertTriangle className="w-5 h-5 text-amber-500 shrink-0 mt-0.5" />
              <div>
                <p className="text-sm font-semibold text-amber-800 dark:text-amber-300">
                  Cliente possui cards em aberto!
                </p>
                <p className="text-xs text-amber-700/90 dark:text-amber-400 mt-0.5">
                  Este cliente possui <strong>{reincidence.open}</strong> card(s) ativo(s) na ouvidoria (total de {reincidence.total} no histórico).
                </p>
              </div>
            </div>
          )}

          <div>
            <label className="field-label">Data de Abertura *</label>
            <DatePicker
              value={form.startedAt}
              onChange={(val) => set('startedAt', val)}
            />
          </div>
        </div>
      )}

      {/* Step 2: Responsáveis */}
      {step === 1 && (
        <div className="space-y-4">
          <ManagedCombobox
            label="Agente Responsável"
            value={form.ombudsmanAgent}
            onChange={(val) => set('ombudsmanAgent', val)}
            options={options.agents}
            onOptionsChange={(list) => handleOptionChange('agents', list)}
            placeholder="Escolher agente..."
          />

          <ManagedCombobox
            label="Origem do Ticket"
            value={form.ticketOrigin}
            onChange={(val) => set('ticketOrigin', val)}
            options={options.origins}
            onOptionsChange={(list) => handleOptionChange('origins', list)}
            placeholder="Escolher origem..."
          />

          <ManagedCombobox
            label="Time Responsável"
            value={form.responsibleTeam}
            onChange={(val) => set('responsibleTeam', val)}
            options={options.teams}
            onOptionsChange={(list) => handleOptionChange('teams', list)}
            placeholder="Escolher time..."
          />

          <div>
            <label className="field-label">Prioridade</label>
            <div className="select-wrap">
              <select
                className="field-input"
                value={form.priority}
                onChange={(e) => set('priority', e.target.value)}
              >
                <option value="baixa">Baixa</option>
                <option value="normal">Normal</option>
                <option value="alta">Alta</option>
                <option value="urgente">Urgente</option>
              </select>
            </div>
          </div>

          <div>
            <label className="field-label">Prazo</label>
            <DatePicker
              value={form.deadlineAt}
              onChange={(val) => set('deadlineAt', val)}
              placeholder="Selecionar data limite..."
            />
          </div>
        </div>
      )}

      {/* Step 3: Detalhes */}
      {step === 2 && (
        <div className="space-y-4">
          <div>
            <label className="field-label">Motivo do Contato</label>
            <input
              type="text"
              className="field-input"
              value={form.contactReason}
              onChange={(e) => set('contactReason', e.target.value)}
              placeholder="Ex: Reclamação de cobrança / Instabilidade"
            />
          </div>

          <div>
            <label className="field-label">Descrição / Detalhes</label>
            <textarea
              className="field-input min-h-[100px] resize-y"
              value={form.reasonDetails}
              onChange={(e) => set('reasonDetails', e.target.value)}
              placeholder="Escreva os detalhes do atendimento..."
            />
          </div>

          {products.length > 0 && (
            <div>
              <label className="field-label">Produto Vinculado</label>
              <div className="select-wrap">
                <select
                  className="field-input"
                  value={form.productId}
                  onChange={(e) => set('productId', e.target.value)}
                >
                  <option value="">Sem produto específico</option>
                  {products.map((p) => (
                    <option key={p.id} value={p.id}>
                      {p.productType} — {p.planName || p.externalId}
                    </option>
                  ))}
                </select>
              </div>
            </div>
          )}

          <div>
            <label className="field-label">Link RA (Reclame Aqui)</label>
            <input
              type="url"
              className="field-input"
              value={form.raClaimLink}
              onChange={(e) => set('raClaimLink', e.target.value)}
              placeholder="https://www.reclameaqui.com.br/..."
            />
          </div>

          <div>
            <label className="field-label">Tags do Card</label>
            <TagInput
              value={form.tags}
              onChange={(val) => set('tags', val)}
              suggestions={allTags}
              placeholder="Adicionar etiquetas..."
            />
          </div>
        </div>
      )}

      {/* Navigation Buttons */}
      <div className="flex items-center justify-between mt-8 pt-4 border-t border-slate-100 dark:border-slate-800">
        <button
          type="button"
          className="btn-outline"
          onClick={() => (step > 0 ? setStep(step - 1) : router.back())}
          disabled={isPending}
        >
          <ChevronLeft className="w-4 h-4" />
          {step === 0 ? 'Cancelar' : 'Anterior'}
        </button>

        {step < steps.length - 1 ? (
          <button
            type="button"
            className="btn-primary"
            disabled={!validateStep() || isPending}
            onClick={() => setStep(step + 1)}
          >
            Próximo
            <ChevronRight className="w-4 h-4" />
          </button>
        ) : (
          <button
            type="button"
            className="btn-primary"
            disabled={!form.customerId || isPending}
            onClick={handleSubmit}
          >
            {isPending ? 'Criando...' : 'Criar Card'}
          </button>
        )}
      </div>
    </div>
  )
}
