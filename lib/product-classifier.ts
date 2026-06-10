type ProductSnapshot = {
  status: string
  consumption: number | string
}

type ChangeType = 'upgrade' | 'downgrade' | 'churn' | 'reactivation'

export function classifyProductChange(
  original: ProductSnapshot,
  updated: ProductSnapshot
): ChangeType | null {
  if (original.status === 'ativo' && updated.status === 'cancelado') return 'churn'
  if (original.status === 'cancelado' && updated.status === 'ativo') return 'reactivation'
  const delta = Number(updated.consumption) - Number(original.consumption)
  if (delta > 0) return 'upgrade'
  if (delta < 0) return 'downgrade'
  return null
}
