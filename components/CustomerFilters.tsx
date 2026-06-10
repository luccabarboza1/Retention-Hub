'use client'

import { useRouter, useSearchParams } from 'next/navigation'
import { Search } from 'lucide-react'
import { useState, useEffect } from 'react'

interface CustomerFiltersProps {
  tags: string[]
  currentTag?: string
  currentSearch?: string
}

export function CustomerFilters({ tags, currentTag = '', currentSearch = '' }: CustomerFiltersProps) {
  const router = useRouter()
  const searchParams = useSearchParams()
  const [q, setQ] = useState(currentSearch)

  useEffect(() => {
    setQ(currentSearch)
  }, [currentSearch])

  function applyFilters(tagVal: string, searchVal: string) {
    const params = new URLSearchParams(searchParams.toString())
    if (tagVal) {
      params.set('tag', tagVal)
    } else {
      params.delete('tag')
    }
    if (searchVal.trim()) {
      params.set('q', searchVal.trim())
    } else {
      params.delete('q')
    }
    params.delete('page')
    router.push(`?${params.toString()}`)
  }

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault()
        applyFilters(currentTag, q)
      }}
      className="flex flex-wrap items-center gap-3"
    >
      <div className="relative flex-grow md:flex-grow-0 max-w-sm w-full md:w-[280px]">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
        <input
          type="text"
          className="field-input-sm pl-9"
          placeholder="Buscar empresa, contato, email..."
          value={q}
          onChange={(e) => setQ(e.target.value)}
        />
      </div>

      <div className="select-wrap">
        <select
          value={currentTag}
          onChange={(e) => applyFilters(e.target.value, q)}
          className="field-input-sm min-w-[150px]"
        >
          <option value="">Todas as Tags</option>
          {tags.map((t) => (
            <option key={t} value={t}>
              {t}
            </option>
          ))}
        </select>
      </div>

      <button type="submit" className="btn-outline btn-sm font-semibold">
        Buscar
      </button>

      {(currentSearch || currentTag) && (
        <button
          type="button"
          onClick={() => {
            setQ('')
            router.push('/customers')
          }}
          className="text-xs text-rose-500 hover:underline font-medium ml-2"
        >
          Limpar Filtros
        </button>
      )}
    </form>
  )
}
