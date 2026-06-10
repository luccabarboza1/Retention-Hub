# CLAUDE.md — Retention Hub (Next.js)

## O que é este projeto

Reescrita do **Retention Hub** — plataforma interna de Ouvidoria e Retenção da Umbler — de Laravel/PHP para Next.js/TypeScript.

A especificação completa está em dois documentos na raiz:
- `CONTEXT.md` — fonte da verdade sobre o sistema atual (schema, rotas, lógica de negócio, componentes)
- `MIGRACAO_NODEJS.md` — plano de migração, schema Prisma completo, equivalências de código

**Leia esses dois documentos antes de qualquer tarefa.** Eles contêm tudo: schema de banco, validações, lógica de negócio, payloads de webhook, componentes Alpine e seus equivalentes React.

---

## Stack

| Camada | Tecnologia |
|---|---|
| Framework | Next.js 15 (App Router) |
| Linguagem | TypeScript (strict) |
| ORM | Prisma + Vercel Postgres (PostgreSQL) |
| UI | shadcn/ui + Tailwind CSS v4 |
| Auth | Middleware simples com `API_ACCESS_TOKEN` |
| Fila | Vercel Cron Jobs |
| Deploy | Vercel |

---

## Estrutura do projeto

```
app/
  layout.tsx                    ← sidebar, dark mode, shell
  page.tsx                      ← board Kanban
  dashboard/page.tsx
  cards/create/page.tsx         ← wizard 3 steps
  cards/[id]/page.tsx
  chats/page.tsx
  chats/[id]/page.tsx
  customers/page.tsx
  customers/create/page.tsx
  customers/[id]/page.tsx
  customers/[id]/cards/page.tsx
  reports/page.tsx
  settings/...
  api/
    customers/route.ts
    customers/[id]/route.ts
    products/route.ts
    products/[id]/route.ts
    cards/route.ts
    cards/[id]/route.ts
    cards/[id]/chats/route.ts
    cards/[id]/chats/[chatId]/route.ts
    chats/[chatId]/interactions/route.ts
    webhooks/route.ts
    webhooks/[id]/route.ts
    health/route.ts
    cron/process-webhooks/route.ts
components/
  ManagedCombobox.tsx
  ManagedTagInput.tsx
  CustomSelect.tsx
  FilterSelect.tsx
  EmailTagsInput.tsx
  ConfirmModal.tsx
  KanbanBoard.tsx
  CardWizard.tsx
  ActivityTimeline.tsx
  DatePicker.tsx
lib/
  db.ts                         ← PrismaClient singleton
  webhooks.ts                   ← fireWebhookEvent
  audit.ts                      ← withAudit(data, actor)
  app-settings.ts               ← getSetting / setSetting
  product-classifier.ts         ← classifyProductChange
prisma/
  schema.prisma                 ← schema completo em MIGRACAO_NODEJS.md
```

---

## Regras de implementação

- **TypeScript strict** — sem `any` sem justificativa
- **Sem comentários** exceto quando o motivo for não-óbvio
- **Sem abstrações prematuras** — só generalizar quando houver 3+ usos reais
- **Sem tratamento de erro para casos impossíveis** — apenas em boundaries externos
- **Server Components por padrão** — `"use client"` só onde necessário (interatividade)
- **Route Handlers para API** — sem lógica de negócio inline, extrair para `lib/`
- **Prisma via singleton** — nunca instanciar `new PrismaClient()` fora de `lib/db.ts`
- **Soft delete** — sempre filtrar `deletedAt: null` em queries de listagem
- **Audit actor** — sempre passar `createdBy`/`updatedBy` nas mutations, extraído do header `X-Actor` (default: `'system'`)

---

## Design system

- Cor principal: `brand-*` (violeta — `#7c3aed` = `brand-600`)
- Dark mode via classe `.dark` no `<html>` + `localStorage['rh-theme']`
- Sidebar colapsável + `localStorage['rh-sidebar']` = `'1'` colapsado
- Inputs: classe `.field-input` (border, rounded-xl, padding, focus ring violeta)
- Labels: classe `.field-label` (uppercase, text-xs, tracking-wide, slate-400)
- Tailwind config deve incluir a cor `brand` mapeada para violeta

---

## Ordem de implementação (seguir nesta sequência)

1. Setup: `npx create-next-app`, Prisma, Vercel Postgres
2. `prisma/schema.prisma` — schema completo (copiar de MIGRACAO_NODEJS.md)
3. `lib/db.ts`, `lib/audit.ts`, `lib/app-settings.ts`, `lib/product-classifier.ts`
4. `middleware.ts` — auth por `X-Api-Token`
5. API Route Handlers — customers, products, cards, chats, webhooks
6. `lib/webhooks.ts` + `app/api/cron/process-webhooks/route.ts` + `vercel.json`
7. `app/layout.tsx` — sidebar, shell, dark mode
8. Componentes compartilhados
9. Board/Kanban
10. Cards (wizard + detalhe)
11. Customers
12. Chats
13. Reports + CSV export
14. Settings

---

## Variáveis de ambiente necessárias

```env
POSTGRES_PRISMA_URL=
POSTGRES_URL_NON_POOLING=
API_ACCESS_TOKEN=
ENCRYPTION_KEY=
CRON_SECRET=
CUSTOMER_LOOKUP_URL=
```
