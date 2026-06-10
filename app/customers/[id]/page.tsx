import { notFound } from 'next/navigation'
import Link from 'next/link'
import { prisma } from '@/lib/db'
import { getSettingJson, SETTING_KEYS } from '@/lib/app-settings'
import { deleteCustomer } from '@/actions/customers'
import { ChevronLeft, Plus } from 'lucide-react'
import { CustomerForm } from '@/components/CustomerForm'

export default async function CustomerPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params

  const customer = await prisma.customer.findFirst({
    where: { id: Number(id), deletedAt: null },
    include: {
      tags: { include: { tag: true } },
      products: {
        where: { deletedAt: null },
        include: {
          changes: {
            orderBy: { createdAt: 'desc' },
            take: 5,
          },
        },
      },
    },
  })

  if (!customer) notFound()

  const [recentCards, allTags, tiers, segments] = await Promise.all([
    prisma.card.findMany({
      where: { customerId: Number(id), deletedAt: null },
      orderBy: { createdAt: 'desc' },
      take: 6,
      select: { id: true, status: true, contactReason: true, priority: true, createdAt: true },
    }),
    prisma.tag.findMany({ where: { type: 'customer' }, orderBy: { name: 'asc' } }),
    getSettingJson<string[]>(SETTING_KEYS.customerTiers, []),
    getSettingJson<string[]>(SETTING_KEYS.customerSegments, []),
  ])

  // Extract changes
  const productChanges = customer.products.flatMap((p) =>
    p.changes.map((ch) => ({
      id: ch.id,
      changeType: ch.changeType,
      deltaConsumption: ch.deltaConsumption,
      createdAt: ch.createdAt.toISOString(),
      product: { productType: p.productType, planName: p.planName },
    }))
  )
  productChanges.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())

  // Serialize customer decimals and dates
  const serializedCustomer = {
    id: customer.id,
    companyName: customer.companyName,
    clientName: customer.clientName,
    email: customer.email,
    phone: customer.phone,
    relatedEmails: customer.relatedEmails ? (customer.relatedEmails as string[]) : [],
    monthlyFee: customer.monthlyFee ? Number(customer.monthlyFee) : null,
    tier: customer.tier,
    planName: customer.planName,
    segment: customer.segment,
    companySize: customer.companySize,
    instagramFollowersCount: customer.instagramFollowersCount,
    contractedAt: customer.contractedAt ? customer.contractedAt.toISOString() : null,
    canceledAt: customer.canceledAt ? customer.canceledAt.toISOString() : null,
    tags: customer.tags.map((t) => ({ id: t.tag.id, name: t.tag.name })),
    products: customer.products.map((p) => ({
      id: p.id,
      productType: p.productType,
      planName: p.planName,
      externalId: p.externalId,
      status: p.status,
      consumption: Number(p.consumption),
    })),
  }

  const serializedRecentCards = recentCards.map((c) => ({
    id: c.id,
    status: c.status,
    contactReason: c.contactReason,
    priority: c.priority,
    createdAt: c.createdAt.toISOString(),
  }))

  return (
    <div className="p-6 max-w-5xl mx-auto space-y-6">
      <div className="flex items-center gap-3 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 p-4 rounded-2xl shadow-premium">
        <Link href="/customers" className="text-slate-400 hover:text-slate-650 transition-colors">
          <ChevronLeft className="w-5 h-5" />
        </Link>
        <div className="flex-1">
          <h1 className="text-lg font-bold text-slate-850 dark:text-slate-100 leading-none">
            {customer.companyName}
          </h1>
          <p className="text-xs text-slate-450 dark:text-slate-400 mt-1">
            Visualizar e editar dados cadastrais do cliente.
          </p>
        </div>
        <Link href={`/cards/create?customerId=${customer.id}`} className="btn-primary">
          <Plus className="w-4 h-4" />
          Novo Card
        </Link>
      </div>

      <CustomerForm
        customer={serializedCustomer}
        tiers={tiers}
        segments={segments}
        allTags={allTags.map((t) => t.name)}
        recentCards={serializedRecentCards}
        productChanges={productChanges}
        action={async (fd) => {
          'use server'
          const { updateCustomer } = await import('@/actions/customers')
          await updateCustomer(Number(id), fd)
        }}
        onDelete={async () => {
          'use server'
          await deleteCustomer(Number(id))
        }}
      />
    </div>
  )
}
