'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import { usePathname } from 'next/navigation'
import {
  LayoutDashboard, KanbanSquare, Users, MessageSquare, BarChart2,
  Settings, ChevronLeft, ChevronRight, Moon, Sun, ShieldCheck,
} from 'lucide-react'
import { cn } from '@/lib/utils'
import { GlobalSearch } from '@/components/GlobalSearch'

const NAV = [
  { href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
  { href: '/', label: 'Board', icon: KanbanSquare },
  { href: '/customers', label: 'Clientes', icon: Users },
  { href: '/chats', label: 'Chats', icon: MessageSquare },
  { href: '/reports', label: 'Relatórios', icon: BarChart2 },
]

export function Sidebar() {
  const pathname = usePathname()
  const [collapsed, setCollapsed] = useState(false)
  const [dark, setDark] = useState(false)

  useEffect(() => {
    setCollapsed(document.documentElement.classList.contains('sidebar-collapsed'))
    setDark(document.documentElement.classList.contains('dark'))
  }, [])

  function toggleCollapse() {
    const next = !document.documentElement.classList.contains('sidebar-collapsed')
    // Delay label fade-in until sidebar has room; fade-out immediately when collapsing
    document.documentElement.style.setProperty('--sidebar-label-delay', next ? '0s' : '0.12s')
    document.documentElement.classList.add('sidebar-transitioning')
    document.documentElement.classList.toggle('sidebar-collapsed', next)
    localStorage.setItem('rh-sidebar', next ? '1' : '0')
    setCollapsed(next)
    setTimeout(() => {
      document.documentElement.classList.remove('sidebar-transitioning')
      document.documentElement.style.removeProperty('--sidebar-label-delay')
    }, 250)
  }

  function toggleDark() {
    const next = !dark
    setDark(next)
    document.documentElement.classList.add('theme-transitioning')
    document.documentElement.classList.toggle('dark', next)
    localStorage.setItem('rh-theme', next ? 'dark' : 'light')
    setTimeout(() => document.documentElement.classList.remove('theme-transitioning'), 350)
  }

  return (
    <aside
      data-sidebar
      className="flex flex-col bg-slate-900 rounded-2xl border border-slate-800 shrink-0"
    >
      {/* Logo */}
      <div className="flex items-center h-14 px-3 border-b border-slate-800 overflow-hidden">
        <div className="w-10 flex items-center justify-center shrink-0">
          <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-violet-900/40">
            <span className="text-white font-bold text-sm">R</span>
          </div>
        </div>
        <div data-sidebar-label className="flex flex-col min-w-0 pr-2.5">
          <span className="font-semibold text-sm text-white leading-none">Retention Hub</span>
          <span className="text-[10px] text-slate-400 mt-0.5">Umbler</span>
        </div>
      </div>

      {/* Nav */}
      <nav className="flex-1 py-3 space-y-0.5 px-3">
        <GlobalSearch />
        <div className="h-px bg-slate-800 my-2" />
        {NAV.map(({ href, label, icon: Icon }) => {
          const active = href === '/' ? pathname === '/' : pathname.startsWith(href)
          return (
            <Link
              key={href}
              href={href}
              title={label}
              className={cn(
                'flex items-center h-10 rounded-xl overflow-hidden',
                active
                  ? 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-md shadow-violet-900/30'
                  : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'
              )}
            >
              <span className="w-10 flex items-center justify-center shrink-0">
                <Icon className="w-4 h-4" />
              </span>
              <span data-sidebar-label className="text-sm font-medium pr-2.5">{label}</span>
            </Link>
          )
        })}
      </nav>

      {/* Footer */}
      <div className="px-3 pb-3 space-y-0.5 border-t border-slate-800 pt-2">
        <Link
          href="/settings"
          title="Configurações"
          className={cn(
            'flex items-center h-10 rounded-xl overflow-hidden',
            pathname.startsWith('/settings')
              ? 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-md shadow-violet-900/30'
              : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'
          )}
        >
          <span className="w-10 flex items-center justify-center shrink-0">
            <Settings className="w-4 h-4" />
          </span>
          <span data-sidebar-label className="text-sm font-medium pr-2.5">Configurações</span>
        </Link>

        <button
          onClick={toggleDark}
          title={dark ? 'Modo claro' : 'Modo escuro'}
          className="flex items-center justify-start h-10 w-full rounded-xl text-slate-400 hover:bg-slate-800 hover:text-slate-100 overflow-hidden"
        >
          <span className="w-10 flex items-center justify-center shrink-0">
            {dark ? <Sun className="w-4 h-4" /> : <Moon className="w-4 h-4" />}
          </span>
          <span data-sidebar-label className="text-sm font-medium pr-2.5">
            {dark ? 'Modo claro' : 'Modo escuro'}
          </span>
        </button>

        <button
          onClick={toggleCollapse}
          title={collapsed ? 'Expandir' : 'Recolher'}
          className="flex items-center justify-start h-10 w-full rounded-xl text-slate-400 hover:bg-slate-800 hover:text-slate-100 overflow-hidden"
        >
          <span className="w-10 flex items-center justify-center shrink-0">
            {collapsed ? <ChevronRight className="w-4 h-4" /> : <ChevronLeft className="w-4 h-4" />}
          </span>
          <span data-sidebar-label className="text-sm font-medium pr-2.5">Recolher</span>
        </button>

        {/* Status indicator */}
        <div className="flex items-center h-10 overflow-hidden">
          <span className="w-10 flex items-center justify-center shrink-0">
            <ShieldCheck className="w-3.5 h-3.5 text-emerald-400" />
          </span>
          <span data-sidebar-label className="text-[10px] text-slate-500 pr-2.5">Ambiente seguro</span>
        </div>
      </div>
    </aside>
  )
}
