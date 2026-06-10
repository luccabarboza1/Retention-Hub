'use client'

import { useState, useRef, useEffect, useTransition } from 'react'
import { useRouter } from 'next/navigation'
import { Search, Loader2, Users, FolderOpen, MessageSquare, CornerDownLeft } from 'lucide-react'
import { globalSearch, type SearchResults } from '@/actions/search'

const EMPTY: SearchResults = { customers: [], cards: [], chats: [] }
const ANIM_MS = 200

export function GlobalSearch() {
  const router = useRouter()
  const [open, setOpen] = useState(false)
  const [render, setRender] = useState(false)
  const [show, setShow] = useState(false)
  const [query, setQuery] = useState('')
  const [results, setResults] = useState<SearchResults>(EMPTY)
  const [searching, setSearching] = useState(false)
  const [, startTransition] = useTransition()
  const inputRef = useRef<HTMLInputElement>(null)

  useEffect(() => {
    function onKey(e: KeyboardEvent) {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault()
        setOpen((o) => !o)
      }
      if (e.key === 'Escape') setOpen(false)
    }
    document.addEventListener('keydown', onKey)
    return () => document.removeEventListener('keydown', onKey)
  }, [])

  // Ciclo de entrada/saída: monta, anima a entrada no próximo frame; na saída
  // dispara a transição reversa e só desmonta após ela terminar.
  useEffect(() => {
    if (open) {
      setRender(true)
      let raf2 = 0
      const raf1 = requestAnimationFrame(() => {
        raf2 = requestAnimationFrame(() => setShow(true))
      })
      return () => {
        cancelAnimationFrame(raf1)
        cancelAnimationFrame(raf2)
      }
    }
    setShow(false)
    const t = setTimeout(() => {
      setRender(false)
      setQuery('')
      setResults(EMPTY)
      setSearching(false)
    }, ANIM_MS)
    return () => clearTimeout(t)
  }, [open])

  useEffect(() => {
    if (show) inputRef.current?.focus()
  }, [show])

  // Busca com debounce — mantém os resultados anteriores visíveis enquanto
  // busca, evitando flashes de "vazio"/"nenhum resultado" a cada tecla.
  useEffect(() => {
    const q = query.trim()
    if (q.length < 2) {
      setSearching(false)
      setResults(EMPTY)
      return
    }
    setSearching(true)
    const handle = setTimeout(() => {
      startTransition(async () => {
        const r = await globalSearch(q)
        setResults(r)
        setSearching(false)
      })
    }, 250)
    return () => clearTimeout(handle)
  }, [query])

  function go(href: string) {
    setOpen(false)
    router.push(href)
  }

  const total = results.customers.length + results.cards.length + results.chats.length
  const trimmed = query.trim()

  function onFirstResult() {
    const href = results.customers[0]
      ? `/customers/${results.customers[0].id}`
      : results.cards[0]
      ? `/cards/${results.cards[0].id}`
      : results.chats[0]
      ? `/chats/${results.chats[0].id}`
      : null
    if (href) go(href)
  }

  return (
    <>
      {/* Trigger — estilizado como item da sidebar */}
      <button
        type="button"
        onClick={() => setOpen(true)}
        title="Buscar (⌘K)"
        className="flex items-center h-10 w-full rounded-xl overflow-hidden text-slate-400 hover:bg-slate-800 hover:text-slate-100 transition-colors"
      >
        <span className="w-10 flex items-center justify-center shrink-0">
          <Search className="w-4 h-4" />
        </span>
        <span data-sidebar-label className="text-sm font-medium pr-2.5 text-left">
          Buscar
        </span>
      </button>

      {/* Command palette modal */}
      {render && (
        <div className="fixed inset-0 z-[100] flex items-start justify-center pt-[12vh] px-4" role="dialog" aria-modal="true">
          <div
            onClick={() => setOpen(false)}
            className={`absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity ease-out ${
              show ? 'opacity-100' : 'opacity-0'
            }`}
            style={{ transitionDuration: `${ANIM_MS}ms` }}
          />

          <div
            className={`relative w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden transition-all ease-out ${
              show ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-2 scale-95'
            }`}
            style={{ transitionDuration: `${ANIM_MS}ms` }}
          >
            <div className="relative border-b border-slate-100 dark:border-slate-800">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
              <input
                ref={inputRef}
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === 'Enter') {
                    e.preventDefault()
                    onFirstResult()
                  }
                }}
                placeholder="Buscar clientes, cards, chats..."
                className="w-full h-14 pl-11 pr-10 bg-transparent text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 outline-none"
              />
              {searching && (
                <Loader2 className="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 animate-spin text-slate-400" />
              )}
            </div>

            <div className="max-h-[60vh] overflow-y-auto">
              {trimmed.length < 2 ? (
                <p className="py-10 text-center text-sm text-slate-400">Digite ao menos 2 caracteres para buscar.</p>
              ) : total === 0 && searching ? (
                <p className="py-10 text-center text-sm text-slate-400">Buscando…</p>
              ) : total === 0 ? (
                <p className="py-10 text-center text-sm text-slate-400">Nenhum resultado para “{trimmed}”.</p>
              ) : (
                <div className="py-2">
                  {results.customers.length > 0 && (
                    <Group label="Clientes" icon={Users}>
                      {results.customers.map((c) => (
                        <ResultRow key={`cu-${c.id}`} onClick={() => go(`/customers/${c.id}`)}>
                          <p className="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{c.companyName}</p>
                          <p className="text-xs text-slate-400 truncate">
                            {c.clientName}
                            {c.email ? ` · ${c.email}` : ''}
                            {c.tier ? ` · ${c.tier}` : ''}
                          </p>
                        </ResultRow>
                      ))}
                    </Group>
                  )}

                  {results.cards.length > 0 && (
                    <Group label="Cards" icon={FolderOpen}>
                      {results.cards.map((c) => (
                        <ResultRow key={`ca-${c.id}`} onClick={() => go(`/cards/${c.id}`)}>
                          <p className="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">
                            #{c.id} · {c.contactReason || 'Sem motivo'}
                          </p>
                          <p className="text-xs text-slate-400 truncate">
                            {c.companyName} · {c.status}
                            {c.ombudsmanAgent ? ` · ${c.ombudsmanAgent}` : ''}
                          </p>
                        </ResultRow>
                      ))}
                    </Group>
                  )}

                  {results.chats.length > 0 && (
                    <Group label="Chats" icon={MessageSquare}>
                      {results.chats.map((c) => (
                        <ResultRow key={`ch-${c.id}`} onClick={() => go(`/chats/${c.id}`)}>
                          <p className="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">Chat {c.id}</p>
                          <p className="text-xs text-slate-400 truncate">
                            {c.companyName} · Card #{c.cardId}
                          </p>
                        </ResultRow>
                      ))}
                    </Group>
                  )}
                </div>
              )}
            </div>

            <div className="flex items-center gap-3 px-4 py-2 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400">
              <span className="flex items-center gap-1">
                <CornerDownLeft className="w-3 h-3" /> abrir
              </span>
              <span>Esc fecha</span>
            </div>
          </div>
        </div>
      )}
    </>
  )
}

function Group({
  label,
  icon: Icon,
  children,
}: {
  label: string
  icon: React.ComponentType<{ className?: string }>
  children: React.ReactNode
}) {
  return (
    <div className="px-2 pb-1">
      <div className="flex items-center gap-1.5 px-2 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">
        <Icon className="w-3 h-3" /> {label}
      </div>
      {children}
    </div>
  )
}

function ResultRow({ onClick, children }: { onClick: () => void; children: React.ReactNode }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="w-full text-left px-3 py-2 rounded-lg hover:bg-violet-50 dark:hover:bg-violet-950/20 transition-colors cursor-pointer"
    >
      {children}
    </button>
  )
}
