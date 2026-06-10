'use client'

import * as React from 'react'
import { format } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import { Calendar as CalendarIcon, ChevronLeft, ChevronRight, ChevronDown } from 'lucide-react'
import { DayPicker } from 'react-day-picker'
import 'react-day-picker/dist/style.css'
import type { DropdownProps } from 'react-day-picker'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { cn } from '@/lib/utils'

function StyledDropdown({ options, value, onChange }: DropdownProps) {
  const [open, setOpen] = React.useState(false)
  const rootRef = React.useRef<HTMLDivElement>(null)

  React.useEffect(() => {
    if (!open) return
    const handler = (e: MouseEvent) => {
      if (rootRef.current && !rootRef.current.contains(e.target as Node)) {
        setOpen(false)
      }
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [open])

  const selected = options?.find((o) => String(o.value) === String(value))

  const select = (optionValue: number) => {
    onChange?.({
      target: { value: String(optionValue) },
    } as React.ChangeEvent<HTMLSelectElement>)
    setOpen(false)
  }

  return (
    <div ref={rootRef} className="relative">
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        className="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-[rgba(248,250,252,0.5)] dark:bg-[rgba(30,41,59,0.5)] border border-slate-200 dark:border-slate-700/60 rounded-lg px-2 h-8 cursor-pointer hover:border-violet-400 dark:hover:border-violet-700 transition-colors"
      >
        <span className="truncate">{selected?.label}</span>
        <ChevronDown className="w-3 h-3 text-slate-400 dark:text-slate-505 shrink-0" />
      </button>
      {open && (
        <div className="absolute top-full left-0 mt-1 z-50 max-h-48 overflow-y-auto min-w-full w-max bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-lg shadow-xl py-1 animate-fadeIn">
          {options?.map((option) => {
            const isSelected = String(option.value) === String(value)
            return (
              <button
                key={option.value}
                type="button"
                disabled={option.disabled}
                onClick={() => select(option.value)}
                className={cn(
                  'block w-full text-left px-3 py-1.5 text-xs font-medium transition-colors cursor-pointer',
                  isSelected
                    ? 'bg-violet-50 dark:bg-violet-950/30 text-violet-700 dark:text-violet-300 font-semibold'
                    : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-850',
                  option.disabled && 'opacity-40 cursor-not-allowed'
                )}
              >
                {option.label}
              </button>
            )
          })}
        </div>
      )}
    </div>
  )
}

interface DatePickerProps {
  value?: string | Date | null
  onChange?: (date: string) => void
  placeholder?: string
  className?: string
  size?: 'sm' | 'md'
  startMonth?: Date
  endMonth?: Date
}

export function DatePicker({
  value,
  onChange,
  placeholder = 'Selecionar data...',
  className,
  size = 'md',
  startMonth,
  endMonth,
}: DatePickerProps) {
  const [selectedDate, setSelectedDate] = React.useState<Date | undefined>(
    value ? new Date(value) : undefined
  )
  const [open, setOpen] = React.useState(false)

  React.useEffect(() => {
    setSelectedDate(value ? new Date(value) : undefined)
  }, [value])

  const handleSelect = (date: Date | undefined) => {
    setSelectedDate(date)
    setOpen(false)
    if (date && onChange) {
      // Avoid time zone shifts by creating YYYY-MM-DD string
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      onChange(`${year}-${month}-${day}`)
    } else if (onChange) {
      onChange('')
    }
  }

  const currentYear = new Date().getFullYear()
  const defaultStartMonth = React.useMemo(() => new Date(currentYear - 30, 0), [currentYear])
  const defaultEndMonth = React.useMemo(() => new Date(currentYear + 10, 11), [currentYear])

  const pickerStartMonth = startMonth || defaultStartMonth
  const pickerEndMonth = endMonth || defaultEndMonth

  const isSm = size === 'sm'

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <style dangerouslySetInnerHTML={{ __html: `
        .rdp-root {
          --rdp-day-width: 32px !important;
          --rdp-day-height: 32px !important;
          --rdp-day_button-width: 32px !important;
          --rdp-day_button-height: 32px !important;
        }
      ` }} />
      <PopoverTrigger
        type="button"
        className={cn(
          isSm ? 'field-input-sm w-full' : 'field-input w-full',
          'flex items-center gap-2 text-left justify-between cursor-pointer focus:border-violet-650 focus:ring-4 focus:ring-violet-600/10 transition-all',
          !selectedDate && 'text-slate-400 dark:text-slate-555',
          className
        )}
      >
        <span className="truncate text-xs font-medium">
          {selectedDate
            ? format(selectedDate, 'dd/MM/yyyy', { locale: ptBR })
            : placeholder}
        </span>
        <CalendarIcon className="w-3.5 h-3.5 text-slate-400 shrink-0" />
      </PopoverTrigger>
      <PopoverContent className="w-[260px] p-3 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl shadow-xl z-50 animate-fadeIn">
        <DayPicker
          mode="single"
          selected={selectedDate}
          onSelect={handleSelect}
          locale={ptBR}
          className="p-0 border-none"
          showOutsideDays
          fixedWeeks={true}
          captionLayout="dropdown"
          startMonth={pickerStartMonth}
          endMonth={pickerEndMonth}
          components={{
            Dropdown: StyledDropdown,
            Chevron: ({ orientation, ...props }) => {
              if (orientation === 'left') {
                return <ChevronLeft className="h-4 w-4" />
              }
              return <ChevronRight className="h-4 w-4" />
            }
          }}
          classNames={{
            months: 'flex flex-col sm:flex-row space-y-4 sm:space-x-4 sm:space-y-0',
            month: 'space-y-4',
            month_caption: 'flex justify-between pt-1 relative items-center px-1',
            caption_label: 'text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center justify-between w-full gap-1 pointer-events-none',
            dropdowns: 'flex items-center gap-1.5 justify-center w-full',
            chevron: 'w-3 h-3 text-slate-400 dark:text-slate-505 fill-current shrink-0',
            nav: 'hidden',
            button_previous: 'h-6 w-6 bg-transparent p-0 opacity-70 hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center border border-slate-200 dark:border-slate-800 text-slate-650 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-855 cursor-pointer',
            button_next: 'h-6 w-6 bg-transparent p-0 opacity-70 hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center border border-slate-200 dark:border-slate-800 text-slate-650 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-855 cursor-pointer',
            month_grid: 'w-full border-collapse space-y-1',
            weekdays: 'flex mt-2',
            weekday: 'text-slate-400 dark:text-slate-550 rounded-md w-8 h-8 font-semibold text-[9px] uppercase tracking-wider text-center flex items-center justify-center',
            week: 'flex w-full mt-1.5',
            day: 'text-center text-xs p-0 relative focus-within:relative focus-within:z-20 w-8 h-8 flex items-center justify-center',
            day_button: 'w-8 h-8 p-0 font-normal rounded-lg hover:bg-slate-50 dark:hover:bg-slate-850 transition-all flex items-center justify-center text-slate-700 dark:text-slate-350 cursor-pointer hover:scale-105',
            selected: 'bg-violet-650! text-white! font-semibold hover:bg-violet-700! dark:bg-violet-600! dark:text-white! hover:dark:bg-violet-750!',
            today: 'border border-violet-500/50 dark:border-violet-500/30 text-violet-600 dark:text-violet-400 font-bold bg-violet-50/50 dark:bg-violet-950/20',
            outside: 'text-slate-350 dark:text-slate-650 opacity-40',
            disabled: 'text-slate-300 dark:text-slate-750 opacity-30 cursor-not-allowed',
            hidden: 'invisible',
          }}
        />
        
        <div className="flex items-center justify-between border-t border-slate-100 dark:border-slate-850 pt-2 mt-2 gap-2">
          <button
            type="button"
            onClick={() => handleSelect(undefined)}
            className="text-[11px] font-semibold text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 px-2 py-1 rounded-lg transition-colors cursor-pointer"
          >
            Limpar
          </button>
          <div className="flex gap-1">
            <button
              type="button"
              onClick={() => handleSelect(new Date())}
              className="text-[11px] font-semibold text-violet-600 dark:text-violet-400 hover:bg-violet-50 dark:hover:bg-violet-950/30 px-2 py-1 rounded-lg transition-colors cursor-pointer"
            >
              Hoje
            </button>
            <button
              type="button"
              onClick={() => {
                const tomorrow = new Date()
                tomorrow.setDate(tomorrow.getDate() + 1)
                handleSelect(tomorrow)
              }}
              className="text-[11px] font-semibold text-violet-600 dark:text-violet-400 hover:bg-violet-50 dark:hover:bg-violet-950/30 px-2 py-1 rounded-lg transition-colors cursor-pointer"
            >
              Amanhã
            </button>
          </div>
        </div>
      </PopoverContent>
    </Popover>
  )
}
