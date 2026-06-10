'use client'

import { useState, useRef, useEffect } from 'react'
import { X } from 'lucide-react'
import { cn } from '@/lib/utils'

interface TagInputProps {
  value: string[]
  onChange: (value: string[]) => void
  suggestions: string[]
  placeholder?: string
  className?: string
}

export function TagInput({
  value,
  onChange,
  suggestions,
  placeholder = 'Adicionar tag...',
  className,
}: TagInputProps) {
  const [inputValue, setInputValue] = useState('')
  const [isOpen, setIsOpen] = useState(false)
  const [focusedIndex, setFocusedIndex] = useState(-1)
  const containerRef = useRef<HTMLDivElement>(null)
  const inputRef = useRef<HTMLInputElement>(null)

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setIsOpen(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  const filteredSuggestions = suggestions.filter(
    (tag) =>
      tag.toLowerCase().includes(inputValue.toLowerCase()) &&
      !value.includes(tag)
  )

  const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      e.preventDefault()
      if (focusedIndex >= 0 && focusedIndex < filteredSuggestions.length) {
        addTag(filteredSuggestions[focusedIndex])
      } else if (inputValue.trim()) {
        addTag(inputValue.trim())
      }
    } else if (e.key === 'ArrowDown') {
      e.preventDefault()
      setIsOpen(true)
      setFocusedIndex((prev) =>
        prev < filteredSuggestions.length - 1 ? prev + 1 : 0
      )
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      setFocusedIndex((prev) =>
        prev > 0 ? prev - 1 : filteredSuggestions.length - 1
      )
    } else if (e.key === 'Escape') {
      setIsOpen(false)
      setFocusedIndex(-1)
    } else if (e.key === 'Backspace' && !inputValue && value.length > 0) {
      removeTag(value[value.length - 1])
    }
  }

  const addTag = (tag: string) => {
    if (!tag) return
    const normalized = tag.trim()
    if (normalized && !value.includes(normalized)) {
      onChange([...value, normalized])
    }
    setInputValue('')
    setFocusedIndex(-1)
    setIsOpen(false)
    inputRef.current?.focus()
  }

  const removeTag = (tagToRemove: string) => {
    onChange(value.filter((t) => t !== tagToRemove))
  }

  return (
    <div ref={containerRef} className={cn('relative w-full', className)}>
      <div
        onClick={() => inputRef.current?.focus()}
        className="field-input flex flex-wrap items-center gap-1.5 min-h-[42px] cursor-text"
      >
        {value.map((tag) => (
          <span
            key={tag}
            className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300 border border-violet-100 dark:border-violet-900/50"
          >
            {tag}
            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation()
                removeTag(tag)
              }}
              className="text-violet-400 hover:text-violet-600 dark:hover:text-violet-200 transition-colors p-0.5 rounded-full"
            >
              <X className="w-3 h-3" />
            </button>
          </span>
        ))}
        <input
          ref={inputRef}
          type="text"
          value={inputValue}
          onChange={(e) => {
            setInputValue(e.target.value)
            setIsOpen(true)
            setFocusedIndex(-1)
          }}
          onKeyDown={handleKeyDown}
          onFocus={() => setIsOpen(true)}
          placeholder={value.length === 0 ? placeholder : ''}
          className="flex-1 min-w-[100px] border-none p-0 bg-transparent text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-0"
        />
      </div>

      {isOpen && (inputValue || filteredSuggestions.length > 0) && (
        <div className="absolute z-50 mt-1 w-full max-h-48 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-xl animate-fadeIn">
          <ul className="py-1">
            {filteredSuggestions.map((tag, index) => (
              <li key={tag}>
                <button
                  type="button"
                  onClick={() => addTag(tag)}
                  className={cn(
                    'w-full text-left px-3.5 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-violet-50 dark:hover:bg-violet-950/20 transition-colors flex items-center justify-between',
                    index === focusedIndex && 'bg-violet-50 dark:bg-violet-950/20'
                  )}
                >
                  <span>{tag}</span>
                  <span className="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider">Existente</span>
                </button>
              </li>
            ))}
            {inputValue.trim() && !value.includes(inputValue.trim()) && !suggestions.includes(inputValue.trim()) && (
              <li>
                <button
                  type="button"
                  onClick={() => addTag(inputValue)}
                  className={cn(
                    'w-full text-left px-3.5 py-2 text-sm text-violet-600 dark:text-violet-400 font-medium hover:bg-violet-50 dark:hover:bg-violet-950/20 transition-colors flex items-center justify-between',
                    focusedIndex === -1 && inputValue.trim() && 'bg-violet-50/50 dark:bg-violet-950/10'
                  )}
                >
                  <span>Criar &quot;{inputValue.trim()}&quot;</span>
                  <span className="text-[10px] text-violet-400 dark:text-violet-500 uppercase tracking-wider">Nova Tag</span>
                </button>
              </li>
            )}
          </ul>
        </div>
      )}
    </div>
  )
}
