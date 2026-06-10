import { prisma } from '@/lib/db'
import { getActor, withAudit } from '@/lib/audit'
import { z } from 'zod/v4'

const createSchema = z.object({
  customerId: z.number().int(),
  externalId: z.string().min(1),
  contractIdentifier: z.string().optional().nullable(),
  productType: z.enum(['Host', 'Talk2']),
  planName: z.string().max(100).optional().nullable(),
  attendantsCount: z.number().int().optional().nullable(),
  hostServices: z.array(z.string()).optional().nullable(),
  consumption: z.number().min(0).optional(),
  status: z.enum(['ativo', 'cancelado']).optional(),
  hasChatbot: z.boolean().optional(),
  hasAi: z.boolean().optional(),
  hasImplementation: z.boolean().optional(),
  externalCreatedAt: z.string().optional().nullable(),
})

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url)
  const customerId = searchParams.get('customer_id')
  const productType = searchParams.get('product_type')
  const status = searchParams.get('status')

  const where: Record<string, unknown> = { deletedAt: null }
  if (customerId) where.customerId = Number(customerId)
  if (productType) where.productType = productType
  if (status) where.status = status

  const products = await prisma.product.findMany({
    where,
    include: { customer: { select: { id: true, companyName: true } } },
    orderBy: { createdAt: 'desc' },
  })

  return Response.json(products)
}

export async function POST(request: Request) {
  const body = await request.json()
  const parsed = createSchema.safeParse(body)
  if (!parsed.success) {
    return Response.json({ errors: parsed.error.issues }, { status: 422 })
  }

  const actor = getActor(request)
  const { hostServices, ...rest } = parsed.data

  const product = await prisma.product.create({
    data: withAudit(
      {
        ...rest,
        hostServices: hostServices ?? undefined,
        externalCreatedAt: rest.externalCreatedAt ? new Date(rest.externalCreatedAt) : null,
      },
      actor
    ),
  })

  return Response.json(product, { status: 201 })
}
