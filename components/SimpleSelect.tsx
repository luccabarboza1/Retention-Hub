'use client'

import { useState, useRef, useEffect } from 'react'
import { ChevronDown } from 'lucide-react'
import { cn } from '@/lib/utils'

export interface SelectOption {
  value: string
  label: string
}

interface SimpleSelectProps {
  value: string
  onChange: (value: string) => void
  options: (string | SelectOption)[]
  placeholder?: string
  size?: 'sm' | 'md'
  className?: string
}

export function SimpleSelect({
  value,
  onChange,
  options,
  placeholder = 'Selecionar...',
  size = 'md',
  className,
}: SimpleSelectProps) {
  const [open, setOpen] = useState(false)
  const ref = useRef<HTMLDivElement>(null)

  const normalized = options.map((o) =>
    typeof o === 'string' ? { value: o, label: o } : o
  )

  const selected = normalized.find((o) => o.value === value)

  useEffect(() => {
    function handleOutside(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false)
    }
    function handleEscape(e: KeyboardEvent) {
      if (e.key === 'Escape') setOpen(false)
    }
    if (open) {
      document.addEventListener('mousedown', handleOutside)
      document.addEventListener('keydown', handleEscape)
    }
    return () => {
      document.removeEventListener('mousedown', handleOutside)
      document.removeEventListener('keydown', handleEscape)
    }
  }, [open])

  return (
    <div ref={ref} className={cn('relative', className)}>
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        className={cn(
          size === 'sm' ? 'field-input-sm' : 'field-input',
          'flex items-center justify-between gap-2 cursor-pointer w-full text-left',
          !selected && 'text-slate-400 dark:text-slate-500'
        )}
      >
        <span className="truncate">{selected?.label ?? placeholder}</span>
        <ChevronDown
          className={cn(
            'shrink-0 text-slate-400 transition-transform duration-150',
            size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4',
            open && 'rotate-180'
          )}
        />
      </button>

      {open && (
        <div className="absolute left-0 top-full mt-1 z-50 min-w-full bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl shadow-xl overflow-hidden animate-fadeIn">
          <div className="max-h-52 overflow-y-auto py-1">
            {normalized.map((opt) => (
              <button
                key={opt.value}
                type="button"
                onClick={() => {
                  onChange(opt.value)
                  setOpen(false)
                }}
                className={cn(
                  'w-full text-left px-3 py-2 text-xs font-medium transition-colors cursor-pointer',
                  opt.value === value
                    ? 'text-violet-600 dark:text-violet-400 bg-violet-50/60 dark:bg-violet-950/20 font-semibold'
                    : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/80'
                )}
              >
                {opt.label}
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
