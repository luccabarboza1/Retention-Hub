'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { cn } from '@/lib/utils'

const NAV = [
  { href: '/settings/general', label: 'Geral' },
  { href: '/settings/tags', label: 'Etiquetas' },
  { href: '/settings/templates', label: 'Templates' },
  { href: '/settings/products', label: 'Produtos' },
  { href: '/settings/webhooks', label: 'Webhooks' },
]

export default function SettingsLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname()

  return (
    <div className="flex h-full">
      <aside className="w-48 border-r border-slate-200/60 dark:border-slate-800 shrink-0 p-4 space-y-1 bg-slate-50/20 dark:bg-slate-900/10">
        <p className="field-label mb-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Configurações</p>
        {NAV.map((item) => {
          const active = pathname === item.href
          return (
            <Link
              key={item.href}
              href={item.href}
              className={cn(
                'block px-3 py-2 rounded-xl text-sm font-medium transition-all duration-150',
                active
                  ? 'bg-violet-50 text-violet-700 dark:bg-violet-950/30 dark:text-violet-400'
                  : 'text-slate-500 dark:text-slate-450 hover:bg-slate-100/60 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200'
              )}
            >
              {item.label}
            </Link>
          )
        })}
      </aside>
      <main className="flex-1 overflow-auto bg-white dark:bg-slate-900">{children}</main>
    </div>
  )
}
