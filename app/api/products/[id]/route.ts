import { prisma } from '@/lib/db'
import { Prisma } from '@prisma/client'
import { getActor, withAuditUpdate } from '@/lib/audit'
import { classifyProductChange } from '@/lib/product-classifier'
import { z } from 'zod/v4'

const updateSchema = z.object({
  contractIdentifier: z.string().optional().nullable(),
  productType: z.enum(['Host', 'Talk2']).optional(),
  planName: z.string().max(100).optional().nullable(),
  attendantsCount: z.number().int().optional().nullable(),
  hostServices: z.array(z.string()).optional().nullable(),
  consumption: z.number().min(0).optional(),
  status: z.enum(['ativo', 'cancelado']).optional(),
  hasChatbot: z.boolean().optional(),
  hasAi: z.boolean().optional(),
  hasImplementation: z.boolean().optional(),
})

export async function GET(_req: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const product = await prisma.product.findFirst({
    where: { id: Number(id), deletedAt: null },
    include: {
      customer: { select: { id: true, companyName: true } },
      changes: { where: { deletedAt: null }, orderBy: { createdAt: 'desc' }, take: 5 },
    },
  })
  if (!product) return Response.json({ message: 'Not found.' }, { status: 404 })
  return Response.json(product)
}

export async function PUT(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const product = await prisma.product.findFirst({ where: { id: Number(id), deletedAt: null } })
  if (!product) return Response.json({ message: 'Not found.' }, { status: 404 })

  const body = await request.json()
  const parsed = updateSchema.safeParse(body)
  if (!parsed.success) return Response.json({ errors: parsed.error.issues }, { status: 422 })

  const actor = getActor(request)

  const updated = await prisma.product.update({
    where: { id: Number(id) },
    data: withAuditUpdate({
      ...parsed.data,
      hostServices: parsed.data.hostServices === null ? Prisma.DbNull : parsed.data.hostServices,
    }, actor),
  })

  const changeType = classifyProductChange(
    { status: product.status, consumption: Number(product.consumption) },
    { status: updated.status, consumption: Number(updated.consumption) }
  )

  if (changeType) {
    await prisma.productChange.create({
      data: {
        customerId: product.customerId,
        productId: product.id,
        changeType,
        deltaConsumption: Number(updated.consumption) - Number(product.consumption),
        createdBy: actor,
        updatedBy: actor,
      },
    })
  }

  return Response.json(updated)
}

export async function DELETE(request: Request, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params
  const product = await prisma.product.findFirst({ where: { id: Number(id), deletedAt: null } })
  if (!product) return Response.json({ message: 'Not found.' }, { status: 404 })

  const actor = getActor(request)
  await prisma.product.update({
    where: { id: Number(id) },
    data: { deletedAt: new Date(), deletedBy: actor },
  })

  return new Response(null, { status: 204 })
}
