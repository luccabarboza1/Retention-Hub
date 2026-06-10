'use client'

import { useState, useTransition } from 'react'
import { Trash2 } from 'lucide-react'

interface Props {
  title: string
  description?: string
  onConfirm: () => Promise<void> | void
  trigger?: React.ReactNode
}

export function ConfirmDialog({ title, description, onConfirm, trigger }: Props) {
  const [open, setOpen] = useState(false)
  const [pending, startTransition] = useTransition()

  function handleConfirm() {
    startTransition(async () => {
      await onConfirm()
      setOpen(false)
    })
  }

  return (
    <>
      <span onClick={() => setOpen(true)} className="cursor-pointer">
        {trigger ?? <Trash2 className="w-4 h-4" />}
      </span>

      {open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
          <div className="absolute inset-0 bg-black/50" onClick={() => setOpen(false)} />
          <div className="relative bg-card border border-border rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl">
            <h2 className="text-base font-semibold mb-1">{title}</h2>
            {description && (
              <p className="text-sm text-muted-foreground mb-4">{description}</p>
            )}
            <div className="flex justify-end gap-2 mt-4">
              <button
                type="button"
                className="btn-outline"
                onClick={() => setOpen(false)}
                disabled={pending}
              >
                Cancelar
              </button>
              <button
                type="button"
                className="btn-primary bg-destructive hover:bg-destructive/90"
                onClick={handleConfirm}
                disabled={pending}
              >
                {pending ? 'Aguarde...' : 'Confirmar'}
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  )
}
