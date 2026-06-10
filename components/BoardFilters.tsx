'use client'

import { useRouter, useSearchParams } from 'next/navigation'

interface BoardFiltersProps {
  tags: string[]
  currentTag?: string
  currentPriority?: string
}

export function BoardFilters({ tags, currentTag = '', currentPriority = '' }: BoardFiltersProps) {
  const router = useRouter()
  const searchParams = useSearchParams()

  function handleFilterChange(key: string, value: string) {
    const params = new URLSearchParams(searchParams.toString())
    if (value) {
      params.set(key, value)
    } else {
      params.delete(key)
    }
    // Reset page if applicable
    params.delete('page')
    router.push(`?${params.toString()}`)
  }

  return (
    <div className="flex items-center gap-2">
      <div className="select-wrap">
        <select
          value={currentTag}
          onChange={(e) => handleFilterChange('tag', e.target.value)}
          className="field-input-sm min-w-[140px]"
        >
          <option value="">Todas as Tags</option>
          {tags.map((t) => (
            <option key={t} value={t}>
              {t}
            </option>
          ))}
        </select>
      </div>

      <div className="select-wrap">
        <select
          value={currentPriority}
          onChange={(e) => handleFilterChange('priority', e.target.value)}
          className="field-input-sm min-w-[140px]"
        >
          <option value="">Todas as Prioridades</option>
          <option value="baixa">Baixa</option>
          <option value="normal">Normal</option>
          <option value="alta">Alta</option>
          <option value="urgente">Urgente</option>
        </select>
      </div>
    </div>
  )
}
