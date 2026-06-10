import { prisma } from '@/lib/db'
import { getSettingJson, SETTING_KEYS } from '@/lib/app-settings'
import { createCustomer } from '@/actions/customers'
import { CustomerForm } from '@/components/CustomerForm'

export default async function CustomerCreatePage() {
  const [tiers, segments, allTags] = await Promise.all([
    getSettingJson<string[]>(SETTING_KEYS.customerTiers, []),
    getSettingJson<string[]>(SETTING_KEYS.customerSegments, []),
    prisma.tag.findMany({ where: { type: 'customer' }, orderBy: { name: 'asc' } }),
  ])

  return (
    <div className="p-6 max-w-4xl mx-auto space-y-6">
      <div>
        <h1 className="text-xl font-semibold leading-none">Novo Cliente</h1>
        <p className="text-xs text-slate-500 mt-1">Crie uma nova conta com dados de contato, financeiros e produtos.</p>
      </div>

      <CustomerForm
        isCreate
        tiers={tiers}
        segments={segments}
        allTags={allTags.map((t) => t.name)}
        action={createCustomer}
      />
    </div>
  )
}
