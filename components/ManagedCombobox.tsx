'use client'

import { useState, useRef, useEffect, useId } from 'react'
import { Check, ChevronDown, Plus, X } from 'lucide-react'
import { cn } from '@/lib/utils'

type Props = {
  label?: string
  value: string
  onChange: (value: string) => void
  options: string[]
  onOptionsChange?: (options: string[]) => void
  placeholder?: string
  allowManage?: boolean
  className?: string
}

export function ManagedCombobox({
  label,
  value,
  onChange,
  options,
  onOptionsChange,
  placeholder = 'Selecionar...',
  allowManage = true,
  className,
}: Props) {
  const id = useId()
  const [open, setOpen] = useState(false)
  const [search, setSearch] = useState('')
  const [managing, setManaging] = useState(false)
  const [newOption, setNewOption] = useState('')
  const containerRef = useRef<HTMLDivElement>(null)
  const searchRef = useRef<HTMLInputElement>(null)

  useEffect(() => {
    if (open && searchRef.current) searchRef.current.focus()
  }, [open])

  useEffect(() => {
    function onClickOutside(e: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setOpen(false)
        setManaging(false)
        setSearch('')
      }
    }
    document.addEventListener('mousedown', onClickOutside)
    return () => document.removeEventListener('mousedown', onClickOutside)
  }, [])

  const filtered = options.filter((o) =>
    o.toLowerCase().includes(search.toLowerCase())
  )

  function select(option: string) {
    onChange(option)
    setOpen(false)
    setSearch('')
  }

  function addOption() {
    const trimmed = newOption.trim()
    if (!trimmed || options.includes(trimmed)) return
    onOptionsChange?.([...options, trimmed])
    setNewOption('')
  }

  function removeOption(opt: string) {
    onOptionsChange?.(options.filter((o) => o !== opt))
    if (value === opt) onChange('')
  }

  return (
    <div ref={containerRef} className={cn('relative', className)}>
      {label && (
        <label htmlFor={id} className="field-label">{label}</label>
      )}
      <button
        id={id}
        type="button"
        onClick={() => { setOpen((p) => !p); setManaging(false) }}
        className={cn(
          'field-input flex items-center justify-between text-left cursor-pointer',
          !value && 'text-slate-400 dark:text-slate-500'
        )}
      >
        <span className="truncate">{value || placeholder}</span>
        <ChevronDown className={cn('w-4 h-4 text-slate-400 shrink-0 transition-transform', open && 'rotate-180')} />
      </button>

      {open && (
        <div className="absolute z-50 mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl animate-fadeIn overflow-hidden">
          {/* Search */}
          <div className="p-2 border-b border-slate-100 dark:border-slate-800">
            <input
              ref={searchRef}
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Buscar..."
              className="w-full text-sm px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 placeholder:text-slate-400 outline-none focus:border-violet-400"
              onKeyDown={(e) => {
                if (e.key === 'Escape') setOpen(false)
                if (e.key === 'Enter' && filtered.length === 1) select(filtered[0])
              }}
            />
          </div>

          {/* Options */}
          {!managing && (
            <ul className="max-h-48 overflow-y-auto py-1">
              {filtered.length === 0 && (
                <li className="px-3 py-2 text-sm text-slate-400 text-center">Nenhum resultado</li>
              )}
              {filtered.map((opt) => (
                <li key={opt}>
                  <button
                    type="button"
                    onClick={() => select(opt)}
                    className="w-full flex items-center justify-between px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors"
                  >
                    <span>{opt}</span>
                    {value === opt && <Check className="w-3.5 h-3.5 text-violet-600" />}
                  </button>
                </li>
              ))}
            </ul>
          )}

          {/* Manage panel */}
          {managing && onOptionsChange && (
            <div className="p-2 space-y-2 max-h-48 overflow-y-auto">
              <div className="flex flex-wrap gap-1.5">
                {options.map((opt) => (
                  <span key={opt} className="inline-flex items-center gap-1 pl-2.5 pr-1.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-200">
                    <span className="truncate max-w-[120px]">{opt}</span>
                    <button
                      type="button"
                      onClick={() => removeOption(opt)}
                      className="text-slate-400 hover:text-rose-500 transition-colors shrink-0 p-0.5 rounded-full hover:bg-slate-200 dark:hover:bg-slate-700"
                    >
                      <X className="w-3 h-3" />
                    </button>
                  </span>
                ))}
              </div>
              <div className="flex gap-1.5 pt-1">
                <input
                  value={newOption}
                  onChange={(e) => setNewOption(e.target.value)}
                  placeholder="Nova opção..."
                  className="flex-1 text-sm px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 placeholder:text-slate-400 outline-none focus:border-violet-400"
                  onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); addOption() } }}
                />
                <button
                  type="button"
                  onClick={addOption}
                  className="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-violet-600 text-white text-xs font-medium hover:bg-violet-700 transition-colors"
                >
                  <Plus className="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          )}

          {/* Footer */}
          {allowManage && onOptionsChange && (
            <div className="border-t border-slate-100 dark:border-slate-800 p-1.5">
              <button
                type="button"
                onClick={() => setManaging((p) => !p)}
                className="w-full text-xs text-violet-600 dark:text-violet-400 hover:text-violet-700 py-1 font-medium transition-colors"
              >
                {managing ? 'Voltar à lista' : 'Gerenciar opções'}
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
