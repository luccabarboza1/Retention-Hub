'use client'

import { usePathname } from 'next/navigation'

export function PageTransition({ children }: { children: React.ReactNode }) {
  const pathname = usePathname()
  // key={pathname} causes React to remount the div on each navigation,
  // replaying the CSS animation without any JS timing logic.
  return (
    <div key={pathname} className="animate-page-in">
      {children}
    </div>
  )
}
