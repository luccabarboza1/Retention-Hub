import type { Metadata } from 'next'
import { Inter } from 'next/font/google'
import './globals.css'
import { Sidebar } from '@/components/Sidebar'
import { Toaster } from '@/components/ui/sonner'
import { ThemeScript } from '@/components/ThemeScript'
import { PageTransition } from '@/components/PageTransition'

const inter = Inter({ subsets: ['latin'] })

export const metadata: Metadata = {
  title: 'Retention Hub',
  description: 'Plataforma de Ouvidoria e Retenção — Umbler',
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="pt-BR" suppressHydrationWarning>
      <head>
        <ThemeScript />
      </head>
      <body className={`${inter.className} antialiased bg-slate-100 dark:bg-slate-950 flex h-screen overflow-hidden`}>
        {/* Background orbs */}
        <div className="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-violet-200/10 blur-[120px] pointer-events-none" />
        <div className="absolute bottom-[-10%] right-[-5%] w-[40%] h-[40%] rounded-full bg-indigo-200/10 blur-[100px] pointer-events-none" />

        <div className="flex h-full w-full p-3 gap-3 relative z-10">
          <Sidebar />
          {/* backdrop-blur must be on a child div, NOT on the container wrapper itself.
              backdrop-filter creates a new containing block for position:fixed children
              (DragOverlay uses position:fixed), which causes it to be offset by the wrapper's position.
              Thus, we use an absolute sibling div for the background.
              To prevent the background from scrolling (which causes a visible division at the bottom),
              the outer wrapper does not scroll, and the nested main element handles the scrolling. */}
          <div className="flex-1 relative rounded-2xl border border-slate-200/60 dark:border-slate-700/40 shadow-premium overflow-hidden flex flex-col">
            <div className="absolute inset-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl pointer-events-none -z-10" aria-hidden="true" />
            <main className="flex-1 overflow-y-auto relative">
              <PageTransition>{children}</PageTransition>
            </main>
          </div>
        </div>

        <Toaster richColors />
      </body>
    </html>
  )
}
