import { PrismaClient } from '@prisma/client'

const prisma = new PrismaClient()

async function main() {
  const columns = [
    { name: 'Aberto', color: 'blue', type: 'open', order: 1 },
    { name: 'Em Andamento', color: 'violet', type: 'open', order: 2 },
    { name: 'Aguardando Cliente', color: 'yellow', type: 'open', order: 3 },
    { name: 'Retido', color: 'green', type: 'retained', order: 4 },
    { name: 'Churn', color: 'red', type: 'churn', order: 5 },
  ]

  for (const col of columns) {
    await prisma.kanbanColumn.upsert({
      where: { name: col.name },
      update: {},
      create: col,
    })
  }

  console.log('Seed completed: kanban columns created.')
}

main()
  .catch(console.error)
  .finally(() => prisma.$disconnect())
