'use client'

import { useRouter, useSearchParams } from 'next/navigation'
import { Search } from 'lucide-react'
import { useState, useEffect } from 'react'
import { DatePicker } from '@/components/DatePicker'


interface ChatFiltersProps {
  currentSearch?: string
  currentStatus?: string
  currentStart?: string
  currentEnd?: string
}

export function ChatFilters({
  currentSearch = '',
  currentStatus = '',
  currentStart = '',
  currentEnd = '',
}: ChatFiltersProps) {
  const router = useRouter()
  const searchParams = useSearchParams()

  const [q, setQ] = useState(currentSearch)
  const [status, setStatus] = useState(currentStatus)
  const [start, setStart] = useState(currentStart)
  const [end, setEnd] = useState(currentEnd)

  useEffect(() => {
    setQ(currentSearch)
    setStatus(currentStatus)
    setStart(currentStart)
    setEnd(currentEnd)
  }, [currentSearch, currentStatus, currentStart, currentEnd])

  function applyFilters(e?: React.FormEvent) {
    if (e) e.preventDefault()
    const params = new URLSearchParams(searchParams.toString())

    if (q.trim()) params.set('q', q.trim())
    else params.delete('q')

    if (status) params.set('status', status)
    else params.delete('status')

    if (start) params.set('start', start)
    else params.delete('start')

    if (end) params.set('end', end)
    else params.delete('end')

    params.delete('page')
    router.push(`?${params.toString()}`)
  }

  return (
    <form onSubmit={applyFilters} className="flex flex-wrap items-center gap-3">
      {/* Search text */}
      <div className="relative flex-grow md:flex-grow-0 max-w-sm w-full md:w-[260px]">
        <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
        <input
          type="text"
          className="field-input-sm pl-9"
          placeholder="Buscar chat ID ou cliente..."
          value={q}
          onChange={(e) => setQ(e.target.value)}
        />
      </div>

      {/* Status select */}
      <div className="select-wrap">
        <select
          value={status}
          onChange={(e) => {
            setStatus(e.target.value)
            const params = new URLSearchParams(searchParams.toString())
            if (e.target.value) params.set('status', e.target.value)
            else params.delete('status')
            params.delete('page')
            router.push(`?${params.toString()}`)
          }}
          className="field-input-sm min-w-[130px]"
        >
          <option value="">Todos Status</option>
          <option value="open">Abertos</option>
          <option value="closed">Fechados</option>
        </select>
      </div>

      {/* Date range inputs */}
      <div className="flex items-center gap-2 text-xs text-slate-400">
        <span>Período:</span>
        <DatePicker
          size="sm"
          className="w-[130px]"
          value={start}
          onChange={(val) => setStart(val)}
        />
        <span>até</span>
        <DatePicker
          size="sm"
          className="w-[130px]"
          value={end}
          onChange={(val) => setEnd(val)}
        />
      </div>

      <button type="submit" className="btn-outline btn-sm font-semibold">
        Filtrar
      </button>

      {(currentSearch || currentStatus || currentStart || currentEnd) && (
        <button
          type="button"
          onClick={() => {
            setQ('')
            setStatus('')
            setStart('')
            setEnd('')
            router.push('/chats')
          }}
          className="text-xs text-rose-500 hover:underline font-medium ml-2"
        >
          Limpar
        </button>
      )}
    </form>
  )
}
