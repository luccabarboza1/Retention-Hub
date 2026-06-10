'use server'

import { revalidatePath } from 'next/cache'
import { redirect } from 'next/navigation'
import { prisma } from '@/lib/db'
import { withAudit, withAuditUpdate } from '@/lib/audit'
import { syncCustomerTags } from '@/lib/tags'
import { fireWebhookEvent } from '@/lib/webhooks'
import { getSetting, SETTING_KEYS } from '@/lib/app-settings'

export async function createCustomer(formData: FormData) {
  const tags = formData.getAll('tags[]').map(String)
  const products = JSON.parse((formData.get('products') as string) ?? '[]')

  const customer = await prisma.customer.create({
    data: withAudit(
      {
        clientName: formData.get('clientName') as string,
        companyName: formData.get('companyName') as string,
        email: (formData.get('email') as string) || null,
        phone: (formData.get('phone') as string) || null,
        relatedEmails: formData.getAll('relatedEmails[]').map(String),
        monthlyFee: formData.get('monthlyFee') ? Number(formData.get('monthlyFee')) : null,
        contractedAt: formData.get('contractedAt') ? new Date(formData.get('contractedAt') as string) : null,
        canceledAt: formData.get('canceledAt') ? new Date(formData.get('canceledAt') as string) : null,
        instagramFollowersCount: Number(formData.get('instagramFollowersCount') ?? 0),
        segment: (formData.get('segment') as string) || null,
        companySize: (formData.get('companySize') as string) || null,
        tier: (formData.get('tier') as string) || null,
        planName: (formData.get('planName') as string) || null,
      },
      (formData.get('actor') as string) ?? 'system'
    ),
  })

  if (tags.length > 0) await syncCustomerTags(customer.id, tags)

  for (const p of products) {
    await prisma.product.create({
      data: withAudit({ ...p, customerId: customer.id }, 'system'),
    })
  }

  const fresh = await prisma.customer.findUniqueOrThrow({
    where: { id: customer.id },
    include: { tags: { include: { tag: true } }, products: true },
  })

  await fireWebhookEvent('customer.created', customer.id, {
    event: 'customer.created',
    timestamp: new Date().toISOString(),
    data: { ...fresh, tags: fresh.tags.map((t) => t.tag.name) },
  })

  redirect(`/customers/${customer.id}`)
}

export async function updateCustomer(id: number, formData: FormData) {
  const tags = formData.getAll('tags[]').map(String)
  const actor = (formData.get('actor') as string) ?? 'system'

  await prisma.customer.update({
    where: { id },
    data: withAuditUpdate(
      {
        clientName: (formData.get('clientName') as string) || undefined,
        companyName: (formData.get('companyName') as string) || undefined,
        email: (formData.get('email') as string) || null,
        phone: (formData.get('phone') as string) || null,
        relatedEmails: formData.getAll('relatedEmails[]').map(String),
        monthlyFee: formData.get('monthlyFee') ? Number(formData.get('monthlyFee')) : null,
        contractedAt: formData.get('contractedAt') ? new Date(formData.get('contractedAt') as string) : null,
        canceledAt: formData.get('canceledAt') ? new Date(formData.get('canceledAt') as string) : null,
        segment: (formData.get('segment') as string) || null,
        companySize: (formData.get('companySize') as string) || null,
        tier: (formData.get('tier') as string) || null,
        planName: (formData.get('planName') as string) || null,
      },
      actor
    ),
  })

  await syncCustomerTags(id, tags)

  const fresh = await prisma.customer.findUniqueOrThrow({
    where: { id },
    include: { tags: { include: { tag: true } }, products: true },
  })

  await fireWebhookEvent('customer.updated', id, {
    event: 'customer.updated',
    timestamp: new Date().toISOString(),
    data: { ...fresh, tags: fresh.tags.map((t) => t.tag.name) },
  })

  revalidatePath(`/customers/${id}`)
}

export async function deleteCustomer(id: number, actor = 'system') {
  const customer = await prisma.customer.findFirst({ where: { id, deletedAt: null } })
  if (!customer) return

  const now = new Date()
  await prisma.customer.update({
    where: { id },
    data: { deletedAt: now, deletedBy: actor },
  })

  await fireWebhookEvent('customer.deleted', id, {
    event: 'customer.deleted',
    timestamp: now.toISOString(),
    deleted_id: id,
    data: {
      id: customer.id,
      company_name: customer.companyName,
      client_name: customer.clientName,
      deleted_at: now.toISOString(),
    },
  })

  redirect('/customers')
}

export type CustomerLookupResult =
  | { status: 'ok'; data: Record<string, unknown> }
  | { status: 'not_configured' }
  | { status: 'error'; message: string }

export async function lookupCustomerByEmail(
  email: string,
  phone?: string | null
): Promise<CustomerLookupResult> {
  const url = await getSetting(SETTING_KEYS.customerLookupUrl)
  if (!url) return { status: 'not_configured' }

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email, phone: phone || undefined }),
    })
    if (!res.ok) {
      return { status: 'error', message: `O endpoint de lookup retornou um erro (${res.status}).` }
    }
    const data = (await res.json()) as Record<string, unknown>
    return { status: 'ok', data: { email, ...data } }
  } catch {
    return {
      status: 'error',
      message: 'Não foi possível conectar ao endpoint de lookup. Verifique se o workflow está ativo.',
    }
  }
}

export async function quickCreateCustomer(data: {
  companyName: string
  clientName: string
  email?: string | null
  phone?: string | null
  monthlyFee?: number | null
  tier?: string | null
  planName?: string | null
  segment?: string | null
  companySize?: string | null
  instagramFollowersCount?: number
  contractedAt?: string | null
  canceledAt?: string | null
}) {
  const customer = await prisma.customer.create({
    data: withAudit(
      {
        companyName: data.companyName,
        clientName: data.clientName,
        email: data.email || null,
        phone: data.phone || null,
        monthlyFee: data.monthlyFee ? Number(data.monthlyFee) : null,
        tier: data.tier || null,
        planName: data.planName || null,
        segment: data.segment || null,
        companySize: data.companySize || null,
        instagramFollowersCount: Number(data.instagramFollowersCount ?? 0),
        contractedAt: data.contractedAt ? new Date(data.contractedAt) : null,
        canceledAt: data.canceledAt ? new Date(data.canceledAt) : null,
      },
      'system'
    ),
  })
  return customer
}
