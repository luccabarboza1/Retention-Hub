'use client'

import { useState, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import {
  ChevronDown,
  ChevronUp,
  X,
  Plus,
  Trash2,
  Building,
  DollarSign,
  Layers,
  History,
  AlertTriangle,
  FolderOpen,
  Search,
  Loader2,
  Wand2,
  CheckCircle2,
  Settings,
} from 'lucide-react'
import { cn } from '@/lib/utils'
import { toast } from 'sonner'
import { TagInput } from '@/components/TagInput'
import { DatePicker } from '@/components/DatePicker'
import { ConfirmDialog } from '@/components/ConfirmDialog'
import { Badge } from '@/components/ui/badge'
import { ManagedCombobox } from '@/components/ManagedCombobox'
import { saveCustomerOptions } from '@/actions/settings'
import { lookupCustomerByEmail } from '@/actions/customers'

type Product = {
  id?: number
  productType: string
  planName: string | null
  externalId: string
  status: string
  consumption: number
}

type ProductChange = {
  id: number
  changeType: string
  deltaConsumption: any
  createdAt: string
  product: { productType: string; planName: string | null }
}

type CardSummary = {
  id: number
  status: string
  contactReason: string | null
  priority: string
  createdAt: string
}

type Customer = {
  id?: number
  companyName: string
  clientName: string
  email: string | null
  phone: string | null
  relatedEmails: string[]
  monthlyFee: any
  tier: string | null
  planName: string | null
  segment: string | null
  companySize: string | null
  instagramFollowersCount: number
  contractedAt: string | null
  canceledAt: string | null
  products?: Product[]
}

interface CustomerFormProps {
  customer?: Customer
  tiers: string[]
  segments: string[]
  allTags: string[]
  isCreate?: boolean
  recentCards?: CardSummary[]
  productChanges?: ProductChange[]
  action: (formData: FormData) => Promise<void> | void
  onDelete?: () => Promise<void> | void
}

export function CustomerForm({
  customer,
  tiers,
  segments,
  allTags,
  isCreate = false,
  recentCards = [],
  productChanges = [],
  action,
  onDelete,
}: CustomerFormProps) {
  const router = useRouter()
  const [isPending, startTransition] = useTransition()

  // Collapsible sections state
  const [sections, setSections] = useState({
    main: true,
    finance: true,
    products: true,
    tags: true,
  })

  // Form states
  const [companyName, setCompanyName] = useState(customer?.companyName ?? '')
  const [clientName, setClientName] = useState(customer?.clientName ?? '')
  const [email, setEmail] = useState(customer?.email ?? '')
  const [phone, setPhone] = useState(customer?.phone ?? '')
  const [segment, setSegment] = useState(customer?.segment ?? '')
  const [companySize, setCompanySize] = useState(customer?.companySize ?? '')
  const [monthlyFee, setMonthlyFee] = useState(customer?.monthlyFee?.toString() ?? '')
  const [tier, setTier] = useState(customer?.tier ?? '')
  const [planName, setPlanName] = useState(customer?.planName ?? '')
  const [instagramFollowers, setInstagramFollowers] = useState(customer?.instagramFollowersCount ?? 0)
  const [contractedAt, setContractedAt] = useState(customer?.contractedAt ? new Date(customer.contractedAt).toISOString().slice(0, 10) : '')
  const [canceledAt, setCanceledAt] = useState(customer?.canceledAt ? new Date(customer.canceledAt).toISOString().slice(0, 10) : '')
  const [customerTags, setCustomerTags] = useState<string[]>(
    customer && (customer as any).tags
      ? (customer as any).tags.map((t: any) => t.tag?.name || t.name || t)
      : []
  )
  const [segmentsState, setSegmentsState] = useState(segments)
  const [tiersState, setTiersState] = useState(tiers)
  const [activeTab, setActiveTab] = useState<'cadastro' | 'financeiro' | 'produtos' | 'atividades'>('cadastro')

  // Related emails state
  const [relatedEmails, setRelatedEmails] = useState<string[]>(customer?.relatedEmails ?? [])
  const [newEmail, setNewEmail] = useState('')

  // Products state (for create mode)
  const [localProducts, setLocalProducts] = useState<Product[]>(customer?.products ?? [])
  const [showAddProduct, setShowAddProduct] = useState(false)
  const [newProduct, setNewProduct] = useState({
    productType: 'Talk2',
    planName: '',
    externalId: '',
    status: 'ativo',
    consumption: '0',
  })

  // Lookup (n8n) state — only used in create mode
  const [lookupEmail, setLookupEmail] = useState('')
  const [lookupLoading, setLookupLoading] = useState(false)
  const [lookupError, setLookupError] = useState('')
  const [lookupNotConfigured, setLookupNotConfigured] = useState(false)
  const [lookupFilled, setLookupFilled] = useState(false)

  function pick(data: Record<string, unknown>, ...keys: string[]): unknown {
    for (const k of keys) {
      if (data[k] != null) return data[k]
    }
    return undefined
  }

  function toDateInput(value: unknown): string {
    if (!value) return ''
    const d = new Date(String(value))
    return isNaN(d.getTime()) ? '' : d.toISOString().slice(0, 10)
  }

  function fillFromLookup(data: Record<string, unknown>) {
    const companyVal = pick(data, 'companyName', 'company_name')
    if (companyVal != null) setCompanyName(String(companyVal))
    const clientVal = pick(data, 'clientName', 'client_name')
    if (clientVal != null) setClientName(String(clientVal))
    const emailVal = pick(data, 'email')
    if (emailVal != null) setEmail(String(emailVal))
    const phoneVal = pick(data, 'phone', 'telefone', 'phoneNumber', 'phone_number')
    if (phoneVal != null) setPhone(String(phoneVal))
    const segmentVal = pick(data, 'segment')
    if (segmentVal != null) setSegment(String(segmentVal))
    const sizeVal = pick(data, 'companySize', 'company_size')
    if (sizeVal != null) setCompanySize(String(sizeVal))
    const feeVal = pick(data, 'monthlyFee', 'monthly_fee')
    if (feeVal != null) setMonthlyFee(String(feeVal))
    const tierVal = pick(data, 'tier')
    if (tierVal != null) setTier(String(tierVal))
    const planVal = pick(data, 'planName', 'plan_name')
    if (planVal != null) setPlanName(String(planVal))
    const igVal = pick(data, 'instagramFollowersCount', 'instagram_followers_count')
    if (igVal != null) setInstagramFollowers(Number(igVal) || 0)
    const contractedVal = pick(data, 'contractedAt', 'contracted_at')
    if (contractedVal != null) setContractedAt(toDateInput(contractedVal))
    const canceledVal = pick(data, 'canceledAt', 'canceled_at')
    if (canceledVal != null) setCanceledAt(toDateInput(canceledVal))
    const emails = pick(data, 'relatedEmails', 'related_emails')
    if (Array.isArray(emails)) setRelatedEmails(emails.map(String))
  }

  async function handleLookup() {
    const trimmed = lookupEmail.trim()
    if (!trimmed) return
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
      setLookupNotConfigured(false)
      setLookupFilled(false)
      setLookupError('Informe um e-mail válido (ex: joao@empresa.com).')
      return
    }
    setLookupLoading(true)
    setLookupError('')
    setLookupNotConfigured(false)
    setLookupFilled(false)
    try {
      const result = await lookupCustomerByEmail(trimmed)
      if (result.status === 'not_configured') {
        setLookupNotConfigured(true)
        return
      }
      if (result.status === 'error') {
        setLookupError(result.message)
        return
      }
      fillFromLookup(result.data)
      setLookupFilled(true)
    } catch {
      setLookupError('Não foi possível buscar os dados. Tente novamente.')
    } finally {
      setLookupLoading(false)
    }
  }

  function toggleSection(sec: keyof typeof sections) {
    setSections((prev) => ({ ...prev, [sec]: !prev[sec] }))
  }

  function handleAddEmail(e: React.MouseEvent) {
    e.preventDefault()
    const trimmed = newEmail.trim()
    if (trimmed && !relatedEmails.includes(trimmed)) {
      setRelatedEmails((prev) => [...prev, trimmed])
      setNewEmail('')
    }
  }

  function handleRemoveEmail(emailToRemove: string) {
    setRelatedEmails((prev) => prev.filter((e) => e !== emailToRemove))
  }

  // Add Product to list (local if create, API if edit)
  async function handleAddProductClick() {
    const trimmedId = newProduct.externalId.trim()
    if (!trimmedId) {
      toast.error('Insira o ID Externo do produto.')
      return
    }

    const payload = {
      productType: newProduct.productType,
      planName: newProduct.planName || null,
      externalId: trimmedId,
      status: newProduct.status,
      consumption: Number(newProduct.consumption) || 0,
    }

    if (isCreate) {
      setLocalProducts((prev) => [...prev, payload])
      setShowAddProduct(false)
      setNewProduct({
        productType: 'Talk2',
        planName: '',
        externalId: '',
        status: 'ativo',
        consumption: '0',
      })
      toast.success('Produto adicionado temporariamente.')
    } else {
      startTransition(async () => {
        try {
          const res = await fetch('/api/products', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              ...payload,
              customerId: customer?.id,
            }),
          })
          if (!res.ok) throw new Error()
          const prodObj = await res.json()
          setLocalProducts((prev) => [...prev, prodObj])
          setShowAddProduct(false)
          setNewProduct({
            productType: 'Talk2',
            planName: '',
            externalId: '',
            status: 'ativo',
            consumption: '0',
          })
          toast.success('Produto criado com sucesso!')
          router.refresh()
        } catch {
          toast.error('Erro ao adicionar produto.')
        }
      })
    }
  }

  async function handleRemoveProduct(prodId: number | undefined, index: number) {
    if (isCreate) {
      setLocalProducts((prev) => prev.filter((_, i) => i !== index))
      toast.success('Produto removido da lista.')
    } else if (prodId) {
      startTransition(async () => {
        try {
          const res = await fetch(`/api/products/${prodId}`, {
            method: 'DELETE',
          })
          if (!res.ok) throw new Error()
          setLocalProducts((prev) => prev.filter((p) => p.id !== prodId))
          toast.success('Produto removido.')
          router.refresh()
        } catch {
          toast.error('Erro ao remover produto.')
        }
      })
    }
  }

  function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    const fd = new FormData()
    fd.append('companyName', companyName)
    fd.append('clientName', clientName)
    fd.append('email', email)
    fd.append('phone', phone)
    relatedEmails.forEach((email) => fd.append('relatedEmails[]', email))
    fd.append('segment', segment)
    fd.append('companySize', companySize)
    fd.append('monthlyFee', monthlyFee)
    fd.append('tier', tier)
    fd.append('planName', planName)
    fd.append('instagramFollowersCount', String(instagramFollowers))
    fd.append('contractedAt', contractedAt)
    fd.append('canceledAt', canceledAt)
    customerTags.forEach((tag) => fd.append('tags[]', tag))

    if (isCreate) {
      fd.append('products', JSON.stringify(localProducts))
    }

    startTransition(async () => {
      try {
        await action(fd)
        toast.success(isCreate ? 'Cliente criado!' : 'Cliente atualizado!')
      } catch (err) {
        toast.error('Erro ao enviar dados.')
      }
    })
  }

  return (
    <div className="space-y-6">
      {/* Header Selector Tabs */}
      <div className="flex border-b border-slate-250 dark:border-slate-800 gap-1 bg-white dark:bg-slate-900 p-1.5 rounded-xl shadow-premium">
        {[
          { id: 'cadastro', label: 'Cadastro Geral' },
          { id: 'financeiro', label: 'Financeiro & Contrato' },
          { id: 'produtos', label: `Produtos (${localProducts.length})` },
          ...(!isCreate ? [{ id: 'atividades', label: 'Histórico & Atividades' }] : []),
        ].map((t) => (
          <button
            key={t.id}
            type="button"
            onClick={() => setActiveTab(t.id as any)}
            className={cn(
              'px-4 py-2 text-xs font-bold uppercase tracking-wider transition-all cursor-pointer rounded-lg',
              activeTab === t.id
                ? 'bg-violet-50 text-violet-750 dark:bg-violet-950/30 dark:text-violet-400'
                : 'text-slate-400 hover:text-slate-655 dark:hover:text-slate-350'
            )}
          >
            {t.label}
          </button>
        ))}
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        {activeTab === 'cadastro' && (
          <div className="space-y-6 animate-fadeIn">
            {/* Lookup automático via n8n (apenas na criação) */}
            {isCreate && (
              <div className="bg-violet-50/60 dark:bg-violet-950/20 border border-violet-100 dark:border-violet-900/40 rounded-2xl p-5 space-y-3">
                <div className="flex items-center gap-2">
                  <Wand2 className="w-4 h-4 text-violet-600 dark:text-violet-400 shrink-0" />
                  <div>
                    <p className="text-xs font-extrabold text-violet-750 dark:text-violet-300 uppercase tracking-wider">
                      Preenchimento automático
                    </p>
                    <p className="text-[10px] text-violet-600/70 dark:text-violet-400/80 mt-0.5">
                      Digite o e-mail do cliente e busque os dados no sistema externo configurado.
                    </p>
                  </div>
                </div>

                <div className="flex gap-2">
                  <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                    <input
                      type="email"
                      value={lookupEmail}
                      onChange={(e) => setLookupEmail(e.target.value)}
                      onKeyDown={(e) => {
                        if (e.key === 'Enter') {
                          e.preventDefault()
                          handleLookup()
                        }
                      }}
                      placeholder="email@empresa.com.br"
                      disabled={lookupLoading}
                      className="field-input !pl-9"
                    />
                  </div>
                  <button
                    type="button"
                    onClick={handleLookup}
                    disabled={!lookupEmail.trim() || lookupLoading}
                    className="btn-primary shrink-0 whitespace-nowrap disabled:opacity-50"
                  >
                    {lookupLoading ? (
                      <>
                        <Loader2 className="w-3.5 h-3.5 animate-spin" /> Buscando...
                      </>
                    ) : (
                      <>
                        <Wand2 className="w-3.5 h-3.5" /> Preencher automaticamente
                      </>
                    )}
                  </button>
                </div>

                {lookupNotConfigured && (
                  <div className="flex items-center justify-between gap-2 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/50 rounded-lg px-3 py-2">
                    <p className="text-xs text-amber-700 dark:text-amber-400 font-semibold flex items-center gap-1.5">
                      <Settings className="w-3.5 h-3.5 shrink-0" /> URL de lookup não configurada.
                    </p>
                    <Link
                      href="/settings/general"
                      target="_blank"
                      className="text-[10px] font-bold text-amber-700 dark:text-amber-400 underline whitespace-nowrap hover:text-amber-900 dark:hover:text-amber-300"
                    >
                      Configurar →
                    </Link>
                  </div>
                )}

                {lookupError && !lookupNotConfigured && (
                  <p className="text-xs text-rose-600 dark:text-rose-400 font-semibold">{lookupError}</p>
                )}

                {lookupFilled && (
                  <p className="text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1.5">
                    <CheckCircle2 className="w-3.5 h-3.5" /> Dados preenchidos! Revise e continue.
                  </p>
                )}
              </div>
            )}

            {/* Section 1: Dados Principais */}
            <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-4">
              <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-850">
                <Building className="w-4 h-4 text-violet-600" />
                Dados Principais
              </h3>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="field-label">Nome da Empresa *</label>
                  <input
                    type="text"
                    required
                    className="field-input"
                    value={companyName}
                    onChange={(e) => setCompanyName(e.target.value)}
                    placeholder="Ex: Umbler Cloud"
                  />
                </div>

                <div>
                  <label className="field-label">Nome do Contato Principal *</label>
                  <input
                    type="text"
                    required
                    className="field-input"
                    value={clientName}
                    onChange={(e) => setClientName(e.target.value)}
                    placeholder="Ex: Lucca Barboza"
                  />
                </div>

                <div>
                  <label className="field-label">E-mail de Contato *</label>
                  <input
                    type="email"
                    required
                    className="field-input"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="Ex: lucca@empresa.com"
                  />
                </div>

                <div>
                  <label className="field-label">Telefone / WhatsApp</label>
                  <input
                    type="tel"
                    className="field-input"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    placeholder="Ex: +55 51 99999-0000"
                  />
                </div>

                <div>
                  <label className="field-label">Tamanho da Empresa</label>
                  <input
                    className="field-input"
                    value={companySize}
                    onChange={(e) => setCompanySize(e.target.value)}
                    placeholder="Ex: 10-50 funcionários"
                  />
                </div>

                <ManagedCombobox
                  label="Segmento"
                  value={segment}
                  onChange={setSegment}
                  options={segmentsState}
                  onOptionsChange={(opts) => {
                    setSegmentsState(opts)
                    startTransition(async () => {
                      await saveCustomerOptions('segments', opts)
                      toast.success('Opções de Segmento atualizadas')
                    })
                  }}
                  placeholder="Selecionar segmento..."
                />
              </div>

              {/* Related Emails Chips */}
              <div className="sm:col-span-2 space-y-2 pt-2">
                <label className="field-label">E-mails Relacionados / Secundários</label>
                <div className="flex gap-2">
                  <input
                    type="email"
                    className="field-input-sm flex-1"
                    placeholder="Adicionar e-mail secundário..."
                    value={newEmail}
                    onChange={(e) => setNewEmail(e.target.value)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter') {
                        e.preventDefault()
                        const mail = newEmail.trim()
                        if (mail && !relatedEmails.includes(mail)) {
                          setRelatedEmails([...relatedEmails, mail])
                          setNewEmail('')
                        }
                      }
                    }}
                  />
                  <button
                    type="button"
                    onClick={() => {
                      const mail = newEmail.trim()
                      if (mail && !relatedEmails.includes(mail)) {
                        setRelatedEmails([...relatedEmails, mail])
                        setNewEmail('')
                      }
                    }}
                    className="btn-primary btn-sm shrink-0"
                  >
                    Adicionar
                  </button>
                </div>

                <div className="flex flex-wrap gap-1.5 mt-2">
                  {relatedEmails.map((email) => (
                    <span
                      key={email}
                      className="inline-flex items-center gap-1 bg-slate-50 border border-slate-200/60 dark:bg-slate-850 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs px-2.5 py-0.5 rounded-full"
                    >
                      {email}
                      <button
                        type="button"
                        onClick={() => setRelatedEmails(relatedEmails.filter((x) => x !== email))}
                        className="text-slate-400 hover:text-rose-500 transition-colors ml-0.5 cursor-pointer font-bold"
                      >
                        ×
                      </button>
                    </span>
                  ))}
                </div>
              </div>
            </div>

            {/* Section 3: Marcadores (Tags) */}
            <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-3">
              <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-850">
                <Layers className="w-4 h-4 text-violet-650" />
                Marcadores do Cliente
              </h3>
              <TagInput
                value={customerTags}
                onChange={setCustomerTags}
                suggestions={allTags}
                placeholder="Vincular etiquetas ao cliente..."
              />
            </div>
          </div>
        )}

        {activeTab === 'financeiro' && (
          <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-4 animate-fadeIn">
            <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-850">
              <DollarSign className="w-4 h-4 text-violet-650" />
              Dados Financeiros & Contrato
            </h3>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="field-label">Mensalidade / MRR (R$)</label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  className="field-input"
                  value={monthlyFee}
                  onChange={(e) => setMonthlyFee(e.target.value)}
                  placeholder="0.00"
                />
              </div>

              <div>
                <label className="field-label">Plano</label>
                <input
                  type="text"
                  className="field-input"
                  value={planName}
                  onChange={(e) => setPlanName(e.target.value)}
                  placeholder="Ex: Cloud Pro 2"
                />
              </div>

              <ManagedCombobox
                label="Tier"
                value={tier}
                onChange={setTier}
                options={tiersState}
                onOptionsChange={(opts) => {
                  setTiersState(opts)
                  startTransition(async () => {
                    await saveCustomerOptions('tiers', opts)
                    toast.success('Opções de Tier atualizadas')
                  })
                }}
                placeholder="Selecionar tier..."
              />

              <div>
                <label className="field-label">Seguidores no Instagram</label>
                <input
                  type="number"
                  min="0"
                  className="field-input"
                  value={instagramFollowers}
                  onChange={(e) => setInstagramFollowers(Number(e.target.value) || 0)}
                />
              </div>

              <div>
                <label className="field-label">Data de Contratação</label>
                <DatePicker
                  value={contractedAt}
                  onChange={setContractedAt}
                  placeholder="Selecionar data de início..."
                />
              </div>

              <div>
                <label className="field-label">Data de Cancelamento</label>
                <DatePicker
                  value={canceledAt}
                  onChange={setCanceledAt}
                  placeholder="Selecionar data de cancelamento..."
                />
              </div>
            </div>
          </div>
        )}

        {activeTab === 'produtos' && (
          <div className="space-y-6 animate-fadeIn">
            {/* Section 4: Produtos */}
            <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-4">
              <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-850">
                <Layers className="w-4 h-4 text-violet-650" />
                Produtos Vinculados ({localProducts.length})
              </h3>

              {/* Product Add trigger */}
              {!showAddProduct ? (
                <button
                  type="button"
                  onClick={() => setShowAddProduct(true)}
                  className="btn-outline text-xs font-semibold py-1.5 cursor-pointer"
                >
                  <Plus className="w-3.5 h-3.5" /> Adicionar Produto
                </button>
              ) : (
                <div className="p-4 bg-slate-50 dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-700/60 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                  <div>
                    <label className="field-label text-[10px]">Tipo do Produto</label>
                    <div className="select-wrap">
                      <select
                        className="field-input-sm"
                        value={newProduct.productType}
                        onChange={(e) => setNewProduct((p) => ({ ...p, productType: e.target.value }))}
                      >
                        <option value="Talk2">Talk2 (Chatbot/Atendimento)</option>
                        <option value="Host">Host (Cloud/Sites)</option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <label className="field-label text-[10px]">Plano do Produto</label>
                    <input
                      type="text"
                      className="field-input-sm"
                      placeholder="Ex: Start / Pro"
                      value={newProduct.planName || ''}
                      onChange={(e) => setNewProduct((p) => ({ ...p, planName: e.target.value }))}
                    />
                  </div>

                  <div>
                    <label className="field-label text-[10px]">ID Externo *</label>
                    <input
                      type="text"
                      className="field-input-sm"
                      placeholder="Ex: uid-12345"
                      value={newProduct.externalId}
                      onChange={(e) => setNewProduct((p) => ({ ...p, externalId: e.target.value }))}
                    />
                  </div>

                  <div>
                    <label className="field-label text-[10px]">Status</label>
                    <div className="select-wrap">
                      <select
                        className="field-input-sm"
                        value={newProduct.status}
                        onChange={(e) => setNewProduct((p) => ({ ...p, status: e.target.value }))}
                      >
                        <option value="ativo">Ativo</option>
                        <option value="cancelado">Cancelado</option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <label className="field-label text-[10px]">Consumo (R$)</label>
                    <input
                      type="number"
                      step="0.01"
                      className="field-input-sm"
                      value={newProduct.consumption}
                      onChange={(e) => setNewProduct((p) => ({ ...p, consumption: e.target.value }))}
                    />
                  </div>

                  <div className="sm:col-span-2 lg:col-span-1 flex items-end gap-1.5">
                    <button
                      type="button"
                      onClick={handleAddProductClick}
                      className="btn-primary btn-sm flex-1 justify-center shrink-0"
                    >
                      Salvar Produto
                    </button>
                    <button
                      type="button"
                      onClick={() => setShowAddProduct(false)}
                      className="btn-outline btn-sm shrink-0"
                    >
                      Cancelar
                    </button>
                  </div>
                </div>
              )}

              {/* Products Table */}
              <div className="border border-slate-100 dark:border-slate-800 rounded-xl overflow-hidden bg-slate-50/20">
                <table className="w-full text-xs text-left">
                  <thead className="border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                      <th className="px-3 py-2 text-[10px] uppercase font-bold text-slate-400">Tipo</th>
                      <th className="px-3 py-2 text-[10px] uppercase font-bold text-slate-400">Plano</th>
                      <th className="px-3 py-2 text-[10px] uppercase font-bold text-slate-400">Status</th>
                      <th className="px-3 py-2 text-[10px] uppercase font-bold text-slate-400">Consumo</th>
                      <th className="px-3 py-2 text-[10px] uppercase font-bold text-slate-400 text-right">Ações</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100 dark:divide-slate-850">
                    {localProducts.map((p, idx) => (
                      <tr key={p.id || idx}>
                        <td className="px-3 py-2 font-semibold text-slate-800 dark:text-slate-200">{p.productType}</td>
                        <td className="px-3 py-2 text-slate-550">{p.planName || '—'}</td>
                        <td className="px-3 py-2">
                          <span
                            className={cn(
                              'px-1.5 py-0.5 rounded-full font-bold uppercase text-[9px]',
                              p.status === 'ativo'
                                ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-450'
                                : 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-405'
                            )}
                          >
                            {p.status}
                          </span>
                        </td>
                        <td className="px-3 py-2 font-medium">
                          R$ {Number(p.consumption).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}
                        </td>
                        <td className="px-3 py-2 text-right">
                          <button
                            type="button"
                            onClick={() => handleRemoveProduct(p.id, idx)}
                            className="text-slate-400 hover:text-rose-500 p-1 rounded-lg transition-colors cursor-pointer"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        </td>
                      </tr>
                    ))}
                    {localProducts.length === 0 && (
                      <tr>
                        <td colSpan={5} className="p-4 text-center text-slate-400 text-xs">
                          Nenhum produto cadastrado.
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'atividades' && !isCreate && (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fadeIn">
            {/* Cards Recentes */}
            <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-3">
              <div className="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-850">
                <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                  <FolderOpen className="w-4 h-4 text-violet-650" />
                  Cards Recentes
                </h3>
                {customer?.id && (
                  <Link
                    href={`/customers/${customer.id}/cards`}
                    className="text-xs text-violet-600 dark:text-violet-400 hover:underline"
                  >
                    Ver histórico completo
                  </Link>
                )}
              </div>

              <div className="divide-y divide-slate-100 dark:divide-slate-850">
                {recentCards.map((c) => (
                  <div key={c.id} className="py-2.5 flex items-center justify-between gap-3 first:pt-0 last:pb-0">
                    <div className="min-w-0">
                      <Link
                        href={`/cards/${c.id}`}
                        className="text-xs font-semibold text-slate-855 dark:text-slate-250 hover:text-violet-650 truncate block"
                      >
                        {c.contactReason || `Card #${c.id}`}
                      </Link>
                      <p className="text-[10px] text-slate-400">
                        Criado em: {new Date(c.createdAt).toLocaleDateString('pt-BR')}
                      </p>
                    </div>
                    <Badge variant="outline" className="text-[9px]">
                      {c.status}
                    </Badge>
                  </div>
                ))}
                {recentCards.length === 0 && (
                  <p className="text-xs text-slate-400 py-2">Nenhum card registrado para este cliente.</p>
                )}
              </div>
            </div>

            {/* Mudanças de Produto */}
            <div className="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-5 shadow-premium space-y-3">
              <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-850">
                <History className="w-4 h-4 text-violet-650" />
                Mudanças de Consumo Recentes
              </h3>

              <div className="divide-y divide-slate-100 dark:divide-slate-850">
                {productChanges.map((ch) => {
                  const isPositive = Number(ch.deltaConsumption) >= 0
                  return (
                    <div key={ch.id} className="py-2.5 flex items-center justify-between gap-3 first:pt-0 last:pb-0">
                      <div className="min-w-0">
                        <p className="text-xs font-semibold text-slate-855 dark:text-slate-250 truncate">
                          {ch.product.productType} — {ch.product.planName || 'Plano'}
                        </p>
                        <p className="text-[10px] text-slate-450 uppercase tracking-wide mt-0.5">
                          Ação: {ch.changeType}
                        </p>
                      </div>
                      <div className="text-right shrink-0">
                        <span
                          className={cn(
                            'text-xs font-bold px-2 py-0.5 rounded-full border',
                            isPositive
                              ? 'bg-emerald-50 text-emerald-700 border-emerald-250 dark:bg-emerald-950/20 dark:text-emerald-450'
                              : 'bg-rose-50 text-rose-700 border-rose-250 dark:bg-rose-950/20 dark:text-rose-405'
                          )}
                        >
                          {isPositive ? '+' : ''}
                          R$ {Number(ch.deltaConsumption).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}
                        </span>
                        <p className="text-[9px] text-slate-400 mt-1">
                          {new Date(ch.createdAt).toLocaleDateString('pt-BR')}
                        </p>
                      </div>
                    </div>
                  )
                })}
                {productChanges.length === 0 && (
                  <p className="text-xs text-slate-400 py-2">Sem histórico de alterações de consumo.</p>
                )}
              </div>
            </div>
          </div>
        )}

        {/* Action Buttons */}
        <div className="flex items-center justify-between border-t border-slate-150 dark:border-slate-800 pt-4 bg-white dark:bg-slate-900 p-4 rounded-xl shadow-premium">
          {onDelete ? (
            <ConfirmDialog
              title="Excluir Conta?"
              description="Esta ação marcará a conta como excluída e ela não aparecerá na lista principal."
              onConfirm={onDelete}
              trigger={
                <button
                  type="button"
                  className="btn-outline border-rose-200 hover:bg-rose-50 text-rose-600 dark:border-rose-900/40 dark:hover:bg-rose-950/20 cursor-pointer"
                >
                  Excluir Cliente
                </button>
              }
            />
          ) : (
            <div />
          )}

          <div className="flex gap-2">
            <button
              type="button"
              onClick={() => router.back()}
              className="btn-outline cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              className="btn-primary cursor-pointer"
              disabled={isPending}
            >
              {isCreate ? 'Criar Cliente' : 'Salvar Alterações'}
            </button>
          </div>
        </div>
      </form>
    </div>
  )
}
