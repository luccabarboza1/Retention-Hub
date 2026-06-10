import { prisma } from '@/lib/db'
import { ProductSettingsClient } from './ProductSettingsClient'

export default async function ProductSettingsPage() {
  const configs = await prisma.productPlanConfig.findMany({
    orderBy: [{ productType: 'asc' }, { planName: 'asc' }],
  })

  return (
    <ProductSettingsClient
      initialConfigs={configs.map((c) => ({
        id: c.id,
        productType: c.productType,
        planName: c.planName,
        pricePerUnit: Number(c.pricePerUnit),
        unitLabel: c.unitLabel,
      }))}
    />
  )
}
