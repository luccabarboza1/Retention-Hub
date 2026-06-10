import { prisma } from '@/lib/db'
import { TemplatesClient } from './TemplatesClient'

export default async function TemplatesPage() {
  const templates = await prisma.solutionTemplate.findMany({
    orderBy: [{ productType: 'asc' }, { title: 'asc' }],
  })

  return (
    <TemplatesClient
      initialTemplates={templates.map((t) => ({
        id: t.id,
        title: t.title,
        body: t.body,
        productType: t.productType,
      }))}
    />
  )
}
