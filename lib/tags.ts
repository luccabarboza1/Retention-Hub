import { prisma } from './db'

export async function syncCustomerTags(customerId: number, tagNames: string[]): Promise<void> {
  const tags = await Promise.all(
    tagNames.map((name) =>
      prisma.tag.upsert({
        where: { name_type: { name, type: 'customer' } },
        create: { name, type: 'customer' },
        update: {},
      })
    )
  )
  await prisma.customerTag.deleteMany({ where: { customerId } })
  if (tags.length > 0) {
    await prisma.customerTag.createMany({
      data: tags.map((t) => ({ customerId, tagId: t.id })),
    })
  }
}

export async function syncCardTags(cardId: number, tagNames: string[]): Promise<void> {
  const tags = await Promise.all(
    tagNames.map((name) =>
      prisma.tag.upsert({
        where: { name_type: { name, type: 'card' } },
        create: { name, type: 'card' },
        update: {},
      })
    )
  )
  await prisma.cardTag.deleteMany({ where: { cardId } })
  if (tags.length > 0) {
    await prisma.cardTag.createMany({
      data: tags.map((t) => ({ cardId, tagId: t.id })),
    })
  }
}
