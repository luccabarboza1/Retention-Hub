import { prisma } from '@/lib/db'

export async function GET(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const [total, open] = await Promise.all([
    prisma.card.count({ where: { customerId: Number(id), deletedAt: null } }),
    prisma.card.count({ where: { customerId: Number(id), deletedAt: null, finishedAt: null } }),
  ])
  return Response.json({ total, open })
}
