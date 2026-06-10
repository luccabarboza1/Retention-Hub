export function withAudit<T extends object>(
  data: T,
  actor: string
): T & { createdBy: string; updatedBy: string } {
  return { ...data, createdBy: actor, updatedBy: actor }
}

export function withAuditUpdate<T extends object>(
  data: T,
  actor: string
): T & { updatedBy: string } {
  return { ...data, updatedBy: actor }
}

export function getActor(request: Request): string {
  return new Headers(request.headers).get('x-actor') ?? 'system'
}
