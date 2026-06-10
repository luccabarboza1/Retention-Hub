'use client'

import { useState, useTransition, useRef } from 'react'
import Link from 'next/link'
import {
  DndContext, DragEndEvent, DragOverEvent, DragStartEvent,
  MouseSensor, TouchSensor, useSensor, useSensors, DragOverlay,
  closestCorners, useDroppable, type Modifier,
} from '@dnd-kit/core'
import { SortableContext, useSortable, verticalListSortingStrategy } from '@dnd-kit/sortable'
import { CSS } from '@dnd-kit/utilities'
import { moveCard } from '@/actions/cards'
import { cn } from '@/lib/utils'

type Card = {
  id: number
  status: string
  priority: string
  contactReason: string | null
  ombudsmanAgent: string | null
  deadlineAt: Date | string | null
  startedAt: Date | string
  customer: { companyName: string }
  tags: string[]
}

type Column = {
  id: number
  name: string
  color: string
  type: string
}

type ColorConfig = {
  column: string
  dot: string
  cardBorder: string
  count: string
}

const COLOR_MAP: Record<string, ColorConfig> = {
  blue: {
    column: 'bg-sky-50/80 dark:bg-sky-950/20 border border-sky-200 dark:border-sky-900',
    dot: 'bg-sky-500 shadow-[0_0_8px_rgba(56,189,248,0.6)]',
    cardBorder: 'border-l-sky-400',
    count: 'bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-400',
  },
  yellow: {
    column: 'bg-amber-50/80 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900',
    dot: 'bg-amber-500 shadow-[0_0_8px_rgba(251,191,36,0.6)]',
    cardBorder: 'border-l-amber-400',
    count: 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400',
  },
  green: {
    column: 'bg-emerald-50/80 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900',
    dot: 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]',
    cardBorder: 'border-l-emerald-400',
    count: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400',
  },
  red: {
    column: 'bg-rose-50/80 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900',
    dot: 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.6)]',
    cardBorder: 'border-l-rose-400',
    count: 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400',
  },
  purple: {
    column: 'bg-purple-50/80 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-900',
    dot: 'bg-purple-500 shadow-[0_0_8px_rgba(168,85,247,0.6)]',
    cardBorder: 'border-l-purple-400',
    count: 'bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-400',
  },
  pink: {
    column: 'bg-pink-50/80 dark:bg-pink-950/20 border border-pink-200 dark:border-pink-900',
    dot: 'bg-pink-500 shadow-[0_0_8px_rgba(236,72,153,0.6)]',
    cardBorder: 'border-l-pink-400',
    count: 'bg-pink-100 text-pink-600 dark:bg-pink-900/40 dark:text-pink-400',
  },
  indigo: {
    column: 'bg-indigo-50/80 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900',
    dot: 'bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]',
    cardBorder: 'border-l-indigo-400',
    count: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400',
  },
  gray: {
    column: 'bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700',
    dot: 'bg-slate-400 shadow-[0_0_8px_rgba(148,163,184,0.6)]',
    cardBorder: 'border-l-slate-400',
    count: 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
  },
}

const PRIORITY_BADGE: Record<string, string> = {
  urgente: 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-200/50 dark:border-rose-900/50',
  alta: 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/50',
  normal: 'bg-sky-50 text-sky-700 dark:bg-sky-950/30 dark:text-sky-400 border border-sky-200/50 dark:border-sky-900/50',
  baixa: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/50',
}

const snapCenterToCursor: Modifier = ({ activatorEvent, draggingNodeRect, transform }) => {
  if (!draggingNodeRect || !activatorEvent) return transform
  const ax = activatorEvent instanceof MouseEvent ? activatorEvent.clientX
    : (typeof TouchEvent !== 'undefined' && activatorEvent instanceof TouchEvent)
      ? (activatorEvent.touches[0]?.clientX ?? 0) : 0
  const ay = activatorEvent instanceof MouseEvent ? activatorEvent.clientY
    : (typeof TouchEvent !== 'undefined' && activatorEvent instanceof TouchEvent)
      ? (activatorEvent.touches[0]?.clientY ?? 0) : 0
  return {
    ...transform,
    x: transform.x + (ax - draggingNodeRect.left) - draggingNodeRect.width / 2,
    y: transform.y + (ay - draggingNodeRect.top) - draggingNodeRect.height / 2,
  }
}

function deadlineBadge(deadlineAt: Date | string | null) {
  if (!deadlineAt) return null
  const dl = new Date(deadlineAt)
  const hoursLeft = (dl.getTime() - Date.now()) / 3_600_000
  if (hoursLeft < 0) {
    return (
      <span className="text-[10px] uppercase font-bold tracking-wide bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-200/50 dark:border-rose-900/50 px-1.5 py-0.5 rounded shrink-0">
        Vencido
      </span>
    )
  }
  if (hoursLeft < 24) {
    return (
      <span className="text-[10px] uppercase font-bold tracking-wide bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/50 px-1.5 py-0.5 rounded shrink-0">
        Vence hoje
      </span>
    )
  }
  const days = Math.ceil(hoursLeft / 24)
  return (
    <span className="text-[10px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 rounded shrink-0">
      {days}d
    </span>
  )
}

function AgentAvatar({ name }: { name: string }) {
  const initials = name.split(' ').map((p) => p[0]).join('').slice(0, 2).toUpperCase()
  return (
    <span
      title={name}
      className="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gradient-to-tr from-violet-600 to-indigo-500 text-white text-[9px] font-bold shrink-0"
    >
      {initials}
    </span>
  )
}

function CardItem({ card, colColor = 'gray', isDragging = false, dragRef }: {
  card: Card
  colColor?: string
  isDragging?: boolean
  dragRef?: (node: HTMLDivElement | null) => void
}) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging: isSortDragging } =
    useSortable({ id: card.id })

  // When rendering inside DragOverlay (isDragging=true), useSortable still returns the full
  // drag delta as transform. DragOverlay already handles positioning with that same delta,
  // so applying it here would double the movement — causing the pointer to drift off-center.
  const style = isDragging
    ? undefined
    : { transform: CSS.Transform.toString(transform), transition, opacity: isSortDragging ? 0.4 : 1 }

  const colorCfg = COLOR_MAP[colColor] ?? COLOR_MAP.gray

  return (
    <div
      ref={(node) => { setNodeRef(node); dragRef?.(node) }}
      style={style}
      data-card-root
      {...attributes}
      {...listeners}
      className={cn(
        'bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700',
        'shadow-sm hover:shadow-[0_4px_20px_rgba(124,58,237,0.1)] hover:border-violet-300 dark:hover:border-violet-700',
        'hover:-translate-y-0.5 transition-all duration-200',
        'border-l-4 cursor-grab active:cursor-grabbing select-none p-3',
        colorCfg.cardBorder,
        isDragging && 'shadow-2xl ring-2 ring-violet-400/30'
      )}
    >
      <div className="flex items-start justify-between gap-2 mb-1.5">
        <Link
          href={`/cards/${card.id}`}
          onClick={(e) => e.stopPropagation()}
          className="text-sm font-medium text-slate-800 dark:text-slate-100 hover:text-violet-600 dark:hover:text-violet-400 leading-snug line-clamp-2"
        >
          {card.contactReason || `Card #${card.id}`}
        </Link>
        <span className={cn('text-[9px] px-1.5 py-0.5 rounded font-semibold uppercase tracking-wide shrink-0', PRIORITY_BADGE[card.priority] ?? PRIORITY_BADGE.normal)}>
          {card.priority}
        </span>
      </div>

      <p className="text-xs text-slate-500 dark:text-slate-400 mb-2 truncate">{card.customer.companyName}</p>

      {card.tags.length > 0 && (
        <div className="flex flex-wrap gap-1 mb-2">
          {card.tags.slice(0, 3).map((t) => (
            <span key={t} className="text-[10px] px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
              {t}
            </span>
          ))}
        </div>
      )}

      <div className="flex items-center justify-between gap-2">
        {card.ombudsmanAgent ? (
          <div className="flex items-center gap-1.5 min-w-0">
            <AgentAvatar name={card.ombudsmanAgent} />
            <span className="text-[10px] text-slate-400 truncate">{card.ombudsmanAgent}</span>
          </div>
        ) : (
          <span className="text-[10px] text-slate-300 dark:text-slate-600">Sem agente</span>
        )}
        {deadlineBadge(card.deadlineAt)}
      </div>
    </div>
  )
}

function DroppableColumn({
  col, cfg, count, items, columnCards,
}: {
  col: Column
  cfg: ColorConfig
  count: number
  items: number[]
  columnCards: Card[]
}) {
  const { setNodeRef, isOver } = useDroppable({ id: col.name })

  return (
    <div className={cn(
      'flex flex-col w-72 shrink-0 rounded-xl transition-all duration-150',
      cfg.column,
      isOver && 'ring-2 ring-violet-400/50 brightness-[0.98]'
    )}>
      <div className="flex items-center justify-between px-3 py-2.5">
        <div className="flex items-center gap-2">
          <span className={cn('w-2 h-2 rounded-full shrink-0', cfg.dot)} />
          <h3 className="text-sm font-semibold text-slate-700 dark:text-slate-200">{col.name}</h3>
        </div>
        <span className={cn('text-xs rounded-full px-2 py-0.5 font-medium', cfg.count)}>
          {count}
        </span>
      </div>
      <SortableContext id={col.name} items={items} strategy={verticalListSortingStrategy}>
        <div ref={setNodeRef} className="flex flex-col gap-2 p-2 min-h-[7.5rem] flex-1">
          {columnCards.map((card) => (
            <CardItem key={card.id} card={card} colColor={col.color} />
          ))}
        </div>
      </SortableContext>
    </div>
  )
}

export function KanbanBoard({
  columns,
  cardsByColumn,
}: {
  columns: Column[]
  cardsByColumn: Record<string, Card[]>
}) {
  const [cards, setCards] = useState(cardsByColumn)
  const [activeCard, setActiveCard] = useState<Card | null>(null)
  const [activeColColor, setActiveColColor] = useState('gray')
  const [, startTransition] = useTransition()

  const overlayCardRef = useRef<HTMLDivElement>(null)
  const dragCleanupRef = useRef<(() => void) | null>(null)

  const sensors = useSensors(
    useSensor(MouseSensor, { activationConstraint: { distance: 8 } }),
    useSensor(TouchSensor, { activationConstraint: { delay: 250, tolerance: 5 } })
  )

  // Returns the column name that contains a given card id
  function findColumnOfCard(cardId: number): string | null {
    for (const [col, list] of Object.entries(cards)) {
      if (list.some((c) => c.id === cardId)) return col
    }
    return null
  }

  // `over.id` can be either a column name (string) or a card id (number)
  function resolveTargetColumn(overId: string | number): string | null {
    if (typeof overId === 'string') {
      // Check if it matches a column name directly
      const col = columns.find((c) => c.name === overId)
      if (col) return col.name
    }
    // Otherwise treat as a card id and find its column
    return findColumnOfCard(Number(overId))
  }

  function onDragStart({ active, activatorEvent }: DragStartEvent) {
    for (const [colName, list] of Object.entries(cards)) {
      const found = list.find((c) => c.id === active.id)
      if (found) {
        setActiveCard(found)
        const col = columns.find((c) => c.name === colName)
        setActiveColColor(col?.color ?? 'gray')
        break
      }
    }

    // Track horizontal velocity and update overlay transform directly (no re-render)
    const startX = activatorEvent instanceof MouseEvent
      ? activatorEvent.clientX
      : (typeof TouchEvent !== 'undefined' && activatorEvent instanceof TouchEvent)
        ? activatorEvent.touches[0]?.clientX ?? 0
        : 0
    let lastX = startX
    let lastT = performance.now()
    let vel = 0

    const onMouseMove = (e: MouseEvent) => {
      const now = performance.now()
      const dt = now - lastT
      if (dt > 0) {
        vel = vel * 0.6 + ((e.clientX - lastX) / dt * 18) * 0.4
        lastX = e.clientX
        lastT = now
      }
      const tilt = Math.max(-14, Math.min(14, vel))
      if (overlayCardRef.current) {
        overlayCardRef.current.style.transform = `rotate(${tilt}deg)`
      }
    }

    window.addEventListener('mousemove', onMouseMove)
    dragCleanupRef.current = () => window.removeEventListener('mousemove', onMouseMove)
  }

  function onDragOver({ active, over }: DragOverEvent) {
    if (!over) return
    const fromCol = findColumnOfCard(Number(active.id))
    const toCol = resolveTargetColumn(over.id)

    if (!fromCol || !toCol || fromCol === toCol) return

    setCards((prev) => {
      const card = prev[fromCol].find((c) => c.id === active.id)!
      return {
        ...prev,
        [fromCol]: prev[fromCol].filter((c) => c.id !== active.id),
        [toCol]: [...prev[toCol], { ...card, status: toCol }],
      }
    })
  }

  function onDragEnd({ active, over }: DragEndEvent) {
    setActiveCard(null)
    dragCleanupRef.current?.()
    dragCleanupRef.current = null
    if (!over) return

    const toCol = resolveTargetColumn(over.id)
    if (!toCol) return

    startTransition(() => {
      moveCard(Number(active.id), toCol)
    })
  }

  return (
    <DndContext
      id="kanban-board"
      sensors={sensors}
      collisionDetection={closestCorners}
      modifiers={[snapCenterToCursor]}
      autoScroll={false}
      onDragStart={onDragStart}
      onDragOver={onDragOver}
      onDragEnd={onDragEnd}
    >
      <div className="flex gap-3 h-full items-start">
        {columns.map((col) => {
          const cfg = COLOR_MAP[col.color] ?? COLOR_MAP.gray
          const columnCards = cards[col.name] ?? []
          return (
            <DroppableColumn
              key={col.name}
              col={col}
              cfg={cfg}
              count={columnCards.length}
              items={columnCards.map((c) => c.id)}
              columnCards={columnCards}
            />
          )
        })}
      </div>
      <DragOverlay dropAnimation={null}>
        {activeCard && (
          <CardItem
            card={activeCard}
            colColor={activeColColor}
            isDragging
            dragRef={(node) => {
              overlayCardRef.current = node
              if (node) {
                node.style.transformOrigin = '50% 50%'
                node.style.transform = 'rotate(0deg)'
              }
            }}
          />
        )}
      </DragOverlay>
    </DndContext>
  )
}
