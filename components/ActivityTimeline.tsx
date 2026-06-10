import { formatDistanceToNow } from 'date-fns'
import { ptBR } from 'date-fns/locale'

type Log = {
  id: number
  action: string
  actor: string | null
  fromValue: string | null
  toValue: string | null
  createdAt: Date | string
}

const ACTION_LABELS: Record<string, string> = {
  created: 'Card criado',
  status: 'Status alterado',
  finished: 'Card encerrado',
  agent: 'Agente alterado',
  priority: 'Prioridade alterada',
  contact_reason: 'Motivo de contato alterado',
  responsible_team: 'Time responsável alterado',
  ticket_origin: 'Origem alterada',
  rating: 'Avaliação adicionada',
  deadline_at: 'Prazo alterado',
  ra_claim_link: 'Link RA atualizado',
  reason_details: 'Detalhes alterados',
  applied_solution: 'Solução aplicada alterada',
  tags: 'Tags atualizadas',
  note: 'Comentário adicionado',
  related_added: 'Card vinculado',
  related_removed: 'Vínculo removido',
}

export function ActivityTimeline({ logs }: { logs: Log[] }) {
  if (logs.length === 0) {
    return <p className="text-sm text-muted-foreground">Nenhuma atividade registrada.</p>
  }

  return (
    <div className="space-y-3">
      {logs.map((log) => (
        <div key={log.id} className="flex gap-3">
          <div className="w-1.5 h-1.5 rounded-full bg-[var(--brand-600)] mt-2 shrink-0" />
          <div className="flex-1 min-w-0">
            <div className="flex items-baseline justify-between gap-2">
              <p className="text-sm font-medium text-foreground">
                {ACTION_LABELS[log.action] ?? log.action}
              </p>
              <span className="text-xs text-muted-foreground shrink-0" suppressHydrationWarning>
                {formatDistanceToNow(new Date(log.createdAt), { locale: ptBR, addSuffix: true })}
              </span>
            </div>
            {log.actor && (
              <p className="text-xs text-muted-foreground">por {log.actor}</p>
            )}
            {(log.fromValue || log.toValue) && (
              <p className="text-xs text-muted-foreground mt-0.5">
                {log.fromValue && <span className="line-through opacity-60 mr-2">{log.fromValue}</span>}
                {log.toValue && <span>{log.toValue}</span>}
              </p>
            )}
          </div>
        </div>
      ))}
    </div>
  )
}
