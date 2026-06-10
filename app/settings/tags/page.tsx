import { prisma } from '@/lib/db'
import { TagSettingsClient } from './TagSettingsClient'

export default async function TagSettingsPage() {
  const tags = await prisma.tag.findMany({ orderBy: [{ type: 'asc' }, { name: 'asc' }] })

  return (
    <TagSettingsClient
      initialTags={tags.map((t) => ({ id: t.id, name: t.name, type: t.type }))}
    />
  )
}
