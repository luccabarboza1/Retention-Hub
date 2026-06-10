# Plano de Migração — Laravel → Next.js + Vercel

## Stack de Destino

| Camada | Tecnologia |
|---|---|
| Framework | **Next.js 15** (App Router) |
| Linguagem | **TypeScript** |
| API | **Route Handlers** (`app/api/`) — serverless |
| Frontend | **React** + shadcn/ui |
| CSS | **Tailwind CSS v4** |
| ORM | **Prisma** (`provider: "postgresql"`) |
| Banco | **Vercel Postgres** (Neon — PostgreSQL 16) |
| Fila | **Vercel Cron Jobs** + tabela `webhook_dispatch_queue` |
| Hosting | **Vercel** (deploy via `git push`) |

---

## Por que Vercel + Next.js

- Deploy via `git push` — sem FTP, ZipArchive, PHP workarounds
- Preview automático por PR
- Vercel Postgres (Neon) gerenciado sem custo de manutenção
- Tier gratuito adequado para uso interno
- Elimina todos os workarounds do Umbler (CSRF off, PATCH bloqueado, exec desabilitado)

---

## Banco de Dados: MariaDB → PostgreSQL

### Env vars do Vercel Postgres (geradas automaticamente)
```env
POSTGRES_PRISMA_URL          # para queries (com connection pooling)
POSTGRES_URL_NON_POOLING     # para migrations
```

### Prisma schema completo

```prisma
datasource db {
  provider  = "postgresql"
  url       = env("POSTGRES_PRISMA_URL")
  directUrl = env("POSTGRES_URL_NON_POOLING")
}

generator client {
  provider = "prisma-client-js"
}

model Customer {
  id                       Int               @id @default(autoincrement())
  clientName               String            @map("client_name")
  companyName              String            @map("company_name")
  segment                  String?           @db.VarChar(100)
  companySize              String?           @map("company_size") @db.VarChar(50)
  instagramFollowersCount  Int               @default(0) @map("instagram_followers_count")
  email                    String?
  relatedEmails            Json?             @map("related_emails")
  monthlyFee               Decimal?          @map("monthly_fee") @db.Decimal(10, 2)
  contractedAt             DateTime?         @map("contracted_at") @db.Date
  canceledAt               DateTime?         @map("canceled_at") @db.Date
  tier                     String?           @db.VarChar(50)
  planName                 String?           @map("plan_name") @db.VarChar(100)
  createdBy                String?           @map("created_by") @db.VarChar(100)
  updatedBy                String?           @map("updated_by") @db.VarChar(100)
  deletedBy                String?           @map("deleted_by") @db.VarChar(100)
  createdAt                DateTime          @default(now()) @map("created_at")
  updatedAt                DateTime          @updatedAt @map("updated_at")
  deletedAt                DateTime?         @map("deleted_at")
  products                 Product[]
  cards                    Card[]
  tags                     CustomerTag[]
  @@map("customers")
}

model Product {
  id                  Int             @id @default(autoincrement())
  customerId          Int             @map("customer_id")
  externalId          String          @map("external_id")
  contractIdentifier  String?         @map("contract_identifier")
  productType         String          @map("product_type")   // 'Host' | 'Talk2'
  planName            String?         @map("plan_name") @db.VarChar(100)
  attendantsCount     Int?            @map("attendants_count")
  hostServices        Json?           @map("host_services")
  consumption         Decimal         @default(0) @db.Decimal(10, 2)
  status              String          @default("ativo")      // 'ativo' | 'cancelado'
  hasChatbot          Boolean         @default(false) @map("has_chatbot")
  hasAi               Boolean         @default(false) @map("has_ai")
  hasImplementation   Boolean         @default(false) @map("has_implementation")
  externalCreatedAt   DateTime?       @map("external_created_at")
  createdBy           String?         @map("created_by") @db.VarChar(100)
  updatedBy           String?         @map("updated_by") @db.VarChar(100)
  deletedBy           String?         @map("deleted_by") @db.VarChar(100)
  createdAt           DateTime        @default(now()) @map("created_at")
  updatedAt           DateTime        @updatedAt @map("updated_at")
  deletedAt           DateTime?       @map("deleted_at")
  customer            Customer        @relation(fields: [customerId], references: [id])
  cards               Card[]
  changes             ProductChange[]
  @@unique([externalId, productType], name: "uk_external_product")
  @@map("products")
}

model ProductChange {
  id               Int       @id @default(autoincrement())
  customerId       Int       @map("customer_id")
  productId        Int       @map("product_id")
  changeType       String    @map("change_type")  // 'upgrade'|'downgrade'|'churn'|'reactivation'
  deltaConsumption Decimal   @map("delta_consumption") @db.Decimal(10, 2)
  createdBy        String?   @map("created_by") @db.VarChar(100)
  updatedBy        String?   @map("updated_by") @db.VarChar(100)
  deletedBy        String?   @map("deleted_by") @db.VarChar(100)
  createdAt        DateTime  @default(now()) @map("created_at")
  updatedAt        DateTime  @updatedAt @map("updated_at")
  deletedAt        DateTime? @map("deleted_at")
  customer         Customer  @relation(fields: [customerId], references: [id])
  product          Product   @relation(fields: [productId], references: [id])
  @@map("product_changes")
}

model Card {
  id                           Int                @id @default(autoincrement())
  customerId                   Int                @map("customer_id")
  productId                    Int?               @map("product_id")
  status                       String             @default("Aberto") @db.VarChar(50)
  priority                     String             @default("normal")  // 'baixa'|'normal'|'alta'|'urgente'
  startedAt                    DateTime           @map("started_at")
  deadlineAt                   DateTime?          @map("deadline_at")
  finishedAt                   DateTime?          @map("finished_at")
  ticketOrigin                 String?            @map("ticket_origin") @db.VarChar(100)
  ombudsmanAgent               String?            @map("ombudsman_agent") @db.VarChar(100)
  raClaimLink                  String?            @map("ra_claim_link") @db.VarChar(500)
  rating                       Int?
  firstResponseHours           Decimal?           @map("first_response_hours") @db.Decimal(10, 2)
  raPublicResponseHours        Decimal?           @map("ra_public_response_hours") @db.Decimal(10, 2)
  usageTimePostOmbudsmanHours  Decimal?           @map("usage_time_post_ombudsman_hours") @db.Decimal(10, 2)
  contactReason                String?            @map("contact_reason") @db.VarChar(255)
  reasonDetails                String?            @map("reason_details")
  responsibleTeam              String?            @map("responsible_team") @db.VarChar(100)
  appliedSolution              String?            @map("applied_solution")
  createdBy                    String?            @map("created_by") @db.VarChar(100)
  updatedBy                    String?            @map("updated_by") @db.VarChar(100)
  deletedBy                    String?            @map("deleted_by") @db.VarChar(100)
  createdAt                    DateTime           @default(now()) @map("created_at")
  updatedAt                    DateTime           @updatedAt @map("updated_at")
  deletedAt                    DateTime?          @map("deleted_at")
  customer                     Customer           @relation(fields: [customerId], references: [id])
  product                      Product?           @relation(fields: [productId], references: [id])
  chats                        Chat[]
  comments                     CardComment[]
  tags                         CardTag[]
  activityLogs                 CardActivityLog[]
  relatedFrom                  RelatedCard[]      @relation("CardRelatedFrom")
  relatedTo                    RelatedCard[]      @relation("CardRelatedTo")
  @@map("cards")
}

model Chat {
  id                  String               @id            // string, não auto-increment
  ombudsmanCardId     Int                  @map("ombudsman_card_id")
  startedAt           DateTime?            @map("started_at")
  closedAt            DateTime?            @map("closed_at")
  firstResponseHours  Decimal?             @map("first_response_hours") @db.Decimal(10, 2)
  createdBy           String?              @map("created_by") @db.VarChar(100)
  updatedBy           String?              @map("updated_by") @db.VarChar(100)
  deletedBy           String?              @map("deleted_by") @db.VarChar(100)
  createdAt           DateTime             @default(now()) @map("created_at")
  updatedAt           DateTime             @updatedAt @map("updated_at")
  deletedAt           DateTime?            @map("deleted_at")
  card                Card                 @relation(fields: [ombudsmanCardId], references: [id])
  interactions        ChatAgentInteraction[]
  @@map("chats")
}

model ChatAgentInteraction {
  id           Int      @id @default(autoincrement())
  chatId       String   @map("chat_id")
  agent        String   @db.VarChar(100)
  interactedOn DateTime @map("interacted_on") @db.Date
  createdAt    DateTime @default(now()) @map("created_at")
  updatedAt    DateTime @updatedAt @map("updated_at")
  chat         Chat     @relation(fields: [chatId], references: [id])
  @@unique([chatId, agent, interactedOn])
  @@map("chat_agent_interactions")
}

model CardComment {
  id        Int      @id @default(autoincrement())
  cardId    Int      @map("card_id")
  author    String?  @db.VarChar(100)
  content   String                           // campo é 'content', não 'body'
  createdAt DateTime @default(now()) @map("created_at")
  updatedAt DateTime @updatedAt @map("updated_at")
  card      Card     @relation(fields: [cardId], references: [id])
  @@map("card_comments")
}

model CardActivityLog {
  id        Int      @id @default(autoincrement())
  cardId    Int      @map("card_id")
  actor     String?  @db.VarChar(100)
  action    String   @db.VarChar(50)
  fromValue String?  @map("from_value")
  toValue   String?  @map("to_value")
  createdAt DateTime @default(now()) @map("created_at")
  // SEM updatedAt — timestamps = false no Laravel
  card      Card     @relation(fields: [cardId], references: [id])
  @@map("card_activity_logs")
}

model RelatedCard {
  cardId        Int      @map("card_id")
  relatedCardId Int      @map("related_card_id")
  createdAt     DateTime @default(now()) @map("created_at")
  card          Card     @relation("CardRelatedFrom", fields: [cardId], references: [id])
  related       Card     @relation("CardRelatedTo", fields: [relatedCardId], references: [id])
  @@id([cardId, relatedCardId])
  @@map("related_cards")
}

model Tag {
  id        Int           @id @default(autoincrement())
  name      String
  type      String        // 'customer' | 'card'
  createdAt DateTime      @default(now()) @map("created_at")
  updatedAt DateTime      @updatedAt @map("updated_at")
  customers CustomerTag[]
  cards     CardTag[]
  @@unique([name, type])
  @@map("tags")
}

model CustomerTag {
  customerId Int      @map("customer_id")
  tagId      Int      @map("tag_id")
  customer   Customer @relation(fields: [customerId], references: [id])
  tag        Tag      @relation(fields: [tagId], references: [id])
  @@id([customerId, tagId])
  @@map("customer_tag")
}

model CardTag {
  cardId Int  @map("card_id")
  tagId  Int  @map("tag_id")
  card   Card @relation(fields: [cardId], references: [id])
  tag    Tag  @relation(fields: [tagId], references: [id])
  @@id([cardId, tagId])
  @@map("card_tag")
}

model KanbanColumn {
  id        Int      @id @default(autoincrement())
  name      String   @unique @db.VarChar(100)
  order     Int      @default(0)              // @db.SmallInt
  color     String   @default("gray") @db.VarChar(30)
  type      String   @default("aberto")       // 'aberto' | 'concluido'
  createdAt DateTime @default(now()) @map("created_at")
  updatedAt DateTime @updatedAt @map("updated_at")
  @@map("kanban_columns")
}

model WebhookSubscription {
  id           Int       @id @default(autoincrement())
  name         String    @db.VarChar(100)
  url          String    @db.VarChar(2048)
  triggerTypes Json      @map("trigger_types")  // string[]
  secret       String                            // criptografar em app, não no banco
  isActive     Boolean   @default(true) @map("is_active")
  description  String?
  createdBy    String?   @map("created_by") @db.VarChar(100)
  createdAt    DateTime  @default(now()) @map("created_at")
  updatedAt    DateTime  @updatedAt @map("updated_at")
  deletedAt    DateTime? @map("deleted_at")
  logs         WebhookDispatchLog[]
  @@map("webhook_subscriptions")
}

model WebhookDispatchLog {
  id             Int                  @id @default(autoincrement())
  subscriptionId Int                  @map("subscription_id")
  eventType      String               @map("event_type")
  eventEntityId  Int                  @map("event_entity_id")
  attemptNumber  Int                  @default(1) @map("attempt_number")
  maxAttempts    Int                  @default(5) @map("max_attempts")
  status         String               @default("pending")  // pending|success|failed|permanently_failed
  payload        Json
  targetUrl      String               @map("target_url") @db.VarChar(2048)
  httpStatus     Int?                 @map("http_status")
  responseBody   String?              @map("response_body")
  errorMessage   String?              @map("error_message") @db.VarChar(1000)
  dispatchedAt   DateTime?            @map("dispatched_at")
  respondedAt    DateTime?            @map("responded_at")
  nextRetryAt    DateTime?            @map("next_retry_at")
  createdAt      DateTime             @default(now()) @map("created_at")
  updatedAt      DateTime             @updatedAt @map("updated_at")
  subscription   WebhookSubscription  @relation(fields: [subscriptionId], references: [id])
  @@map("webhook_dispatch_logs")
}

model SolutionTemplate {
  id          Int      @id @default(autoincrement())
  title       String   @db.VarChar(100)
  body        String
  productType String?  @map("product_type") @db.VarChar(20)  // 'Talk2' | 'Host' | null
  createdAt   DateTime @default(now()) @map("created_at")
  updatedAt   DateTime @updatedAt @map("updated_at")
  @@map("solution_templates")
}

model ProductPlanConfig {
  id           Int      @id @default(autoincrement())
  productType  String   @map("product_type") @db.VarChar(20)
  planName     String   @map("plan_name") @db.VarChar(100)
  pricePerUnit Decimal  @default(0) @map("price_per_unit") @db.Decimal(10, 2)
  unitLabel    String   @default("unidade") @map("unit_label") @db.VarChar(50)
  createdAt    DateTime @default(now()) @map("created_at")
  updatedAt    DateTime @updatedAt @map("updated_at")
  @@map("product_plan_configs")
}

model AppSetting {
  key       String   @id @db.VarChar(100)
  value     String?
  createdAt DateTime @default(now()) @map("created_at")
  updatedAt DateTime @updatedAt @map("updated_at")
  @@map("app_settings")
}
```

### Queries MySQL-específicas → PostgreSQL

| MySQL (atual) | PostgreSQL equivalente |
|---|---|
| `FIELD(priority, 'urgente', 'alta', 'normal', 'baixa')` | `CASE WHEN priority='urgente' THEN 1 WHEN priority='alta' THEN 2 WHEN priority='normal' THEN 3 ELSE 4 END` |
| `JSON_CONTAINS(related_emails, '"x"')` | `related_emails @> '["x"]'::jsonb` |
| `TIMESTAMPDIFF(MINUTE, started_at, closed_at)` | `EXTRACT(EPOCH FROM (closed_at - started_at)) / 60` |
| `whereJsonContains('trigger_types', 'card.created')` | `trigger_types @> '["card.created"]'::jsonb` |

---

## Fila de Webhooks → Vercel Cron

### vercel.json
```json
{
  "crons": [
    {
      "path": "/api/cron/process-webhooks",
      "schedule": "*/5 * * * *"
    }
  ]
}
```

### Lógica do handler `/api/cron/process-webhooks`

Replica exatamente o comportamento do `cron_worker.php` atual:

```typescript
// app/api/cron/process-webhooks/route.ts
export async function GET(request: Request) {
  // Validar CRON_SECRET para segurança
  const authHeader = request.headers.get('authorization')
  if (authHeader !== `Bearer ${process.env.CRON_SECRET}`) {
    return Response.json({ error: 'Unauthorized' }, { status: 401 })
  }

  // Buscar logs pendentes e com next_retry_at <= now
  const pending = await prisma.webhookDispatchLog.findMany({
    where: {
      status: { in: ['pending', 'failed'] },
      OR: [{ nextRetryAt: null }, { nextRetryAt: { lte: new Date() } }],
    },
    include: { subscription: true },
    take: 50,  // processar em lotes
  })

  for (const log of pending) {
    await dispatchWebhook(log)
  }

  return Response.json({ processed: pending.length })
}
```

### Implementação do dispatchWebhook (equivalente ao DispatchWebhookJob)

```typescript
const RETRY_DELAYS = [30, 60, 120, 240, 300]  // segundos, índice = attempt - 1

async function dispatchWebhook(log: WebhookDispatchLog & { subscription: WebhookSubscription }) {
  const payloadJson = JSON.stringify(log.payload)
  const signature = await hmacSha256(payloadJson, log.subscription.secret)

  const dispatchedAt = new Date()
  let httpStatus: number | null = null
  let responseBody: string | null = null
  let errorMessage: string | null = null

  try {
    const res = await fetch(log.targetUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Umbler-Signature': `hmac-sha256=${signature}`,
        'X-Umbler-Event': log.eventType,
      },
      body: payloadJson,
      signal: AbortSignal.timeout(25_000),
    })

    httpStatus = res.status
    responseBody = (await res.text()).slice(0, 4000)

    if (res.ok) {
      await prisma.webhookDispatchLog.update({
        where: { id: log.id },
        data: { status: 'success', httpStatus, responseBody, respondedAt: new Date() },
      })
      return
    }
  } catch (e) {
    errorMessage = String(e).slice(0, 1000)
  }

  const nextAttempt = log.attemptNumber + 1
  const isPermanentlyFailed = log.attemptNumber >= log.maxAttempts

  await prisma.webhookDispatchLog.update({
    where: { id: log.id },
    data: {
      status: isPermanentlyFailed ? 'permanently_failed' : 'failed',
      httpStatus,
      responseBody,
      errorMessage,
      attemptNumber: nextAttempt,
      respondedAt: new Date(),
      nextRetryAt: isPermanentlyFailed
        ? null
        : new Date(Date.now() + RETRY_DELAYS[log.attemptNumber - 1] * 1000),
    },
  })
}

// HMAC-SHA256 com Web Crypto API (Edge runtime compatible)
async function hmacSha256(data: string, secret: string): Promise<string> {
  const enc = new TextEncoder()
  const key = await crypto.subtle.importKey('raw', enc.encode(secret), { name: 'HMAC', hash: 'SHA-256' }, false, ['sign'])
  const sig = await crypto.subtle.sign('HMAC', key, enc.encode(data))
  return Array.from(new Uint8Array(sig)).map(b => b.toString(16).padStart(2, '0')).join('')
}
```

### Disparar webhook ao criar/atualizar (substitui Events/Listeners)

```typescript
// lib/webhooks.ts
export async function fireWebhookEvent(eventType: string, entityId: number, payload: object) {
  const subscriptions = await prisma.webhookSubscription.findMany({
    where: {
      isActive: true,
      deletedAt: null,
      // trigger_types contém o eventType OU '*'
    },
  })

  // Filtrar em JS (PostgreSQL jsonb @> pode ser usado com Prisma raw)
  const matching = subscriptions.filter(s => {
    const types = s.triggerTypes as string[]
    return types.includes(eventType) || types.includes('*')
  })

  for (const sub of matching) {
    await prisma.webhookDispatchLog.create({
      data: {
        subscriptionId: sub.id,
        eventType,
        eventEntityId: entityId,
        payload,
        targetUrl: sub.url,
        status: 'pending',
        maxAttempts: 5,
      },
    })
  }
}
```

---

## Autenticação

### Middleware Next.js (`middleware.ts`)
```typescript
export function middleware(request: NextRequest) {
  // Rotas /api/* (exceto /api/cron e /api/health)
  if (request.nextUrl.pathname.startsWith('/api/')) {
    const token =
      request.headers.get('x-api-token') ??
      request.nextUrl.searchParams.get('api_token') ??
      request.headers.get('authorization')?.replace('Bearer ', '')

    if (token !== process.env.API_ACCESS_TOKEN) {
      return Response.json({ message: 'Unauthorized.' }, { status: 401 })
    }
  }
  // Rotas web: Basic Auth simples ou sem autenticação (uso interno)
}
```

### Audit Actor
No Laravel, `app('audit.actor')` retorna o header `X-Actor` (default: 'api').

No Next.js: passar o actor como parâmetro nas funções de criação/atualização:
```typescript
const actor = request.headers.get('x-actor') ?? 'system'
await prisma.card.create({ data: { ...data, createdBy: actor, updatedBy: actor } })
```

---

## Estrutura de Pastas Next.js

```
app/
  layout.tsx                    ← layouts/app.blade.php (sidebar, dark mode)
  page.tsx                      ← board/index (Kanban)
  dashboard/page.tsx            ← dashboard
  search/page.tsx               ← search
  cards/
    create/page.tsx             ← cards/create (wizard)
    [id]/page.tsx               ← cards/show
  chats/
    page.tsx                    ← chats/index
    [id]/page.tsx               ← chats/show
  customers/
    page.tsx                    ← customers/index
    create/page.tsx             ← customers/create
    [id]/page.tsx               ← customers/show
    [id]/cards/page.tsx         ← customers/cards
  reports/page.tsx              ← reports/index
  settings/
    page.tsx                    ← settings/index
    general/page.tsx
    tags/page.tsx
    templates/page.tsx
    webhooks/page.tsx
    products/page.tsx
  api/
    customers/route.ts          ← CustomerController (GET, POST)
    customers/[id]/route.ts     ← (GET, PUT, DELETE)
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
    cron/
      process-webhooks/route.ts ← cron_worker.php
components/
  ui/                           ← shadcn/ui components
  ManagedCombobox.tsx           ← _managed_combobox.blade.php
  ManagedTagInput.tsx           ← _tag_input_managed.blade.php
  CustomSelect.tsx              ← _custom_select.blade.php
  FilterSelect.tsx              ← _filter_select.blade.php
  EmailTagsInput.tsx            ← emailTags Alpine
  ConfirmModal.tsx              ← confirmModal Alpine
  OptionDeleteModal.tsx         ← optionDeleteModal Alpine
  KanbanBoard.tsx               ← board drag & drop
  CardWizard.tsx                ← wizard Alpine (3 steps)
  ActivityTimeline.tsx          ← activity log com expand
  DatePicker.tsx                ← Flatpickr → react-day-picker ou similar
lib/
  db.ts                         ← PrismaClient singleton
  webhooks.ts                   ← fireWebhookEvent
  audit.ts                      ← withAudit helper
  app-settings.ts               ← AppSetting.get/set com cache
  product-classifier.ts         ← ProductChangeClassifier
prisma/
  schema.prisma
```

---

## Mapeamento Laravel → Next.js

| Laravel | Next.js |
|---|---|
| Controller Web (HTML) | `page.tsx` (Server Component) + Server Actions |
| Controller Web (AJAX JSON) | `app/api/*/route.ts` Route Handler |
| Controller API | `app/api/*/route.ts` Route Handler |
| Eloquent Model | Prisma model + `lib/db.ts` |
| `$model->syncTags([...])` | `prisma.tag.upsert` + delete/create em `cardTag` |
| `$model->isFinished()` | `KanbanColumn.findFirst({ where: { type: 'concluido' } })` |
| `HasAudit` trait | Helper `withAudit(data, actor)` que injeta createdBy/updatedBy |
| `AppSetting::get/set` | `lib/app-settings.ts` com `unstable_cache` ou Redis |
| `KanbanColumn::finishedNames()` | Query cacheada das colunas type='concluido' |
| `ProductChangeClassifier` | `lib/product-classifier.ts` |
| Event → Listener → Job | `fireWebhookEvent()` → cria `webhook_dispatch_logs` → Cron processa |
| Alpine `wizard()` | React `useState(step)` + validação por step |
| Alpine `managedCombobox()` | `<ManagedCombobox>` com Combobox do shadcn/ui |
| Alpine `managedTagInput()` | `<ManagedTagInput>` com fetch para `/api/settings/tags` |
| Alpine `appShell()` | `layout.tsx` com `useState` + `useEffect` para localStorage |
| `FIELD(priority, ...)` MySQL | `ORDER BY CASE WHEN priority='urgente' THEN 1 ...` |
| `TIMESTAMPDIFF(MINUTE, ...)` MySQL | `EXTRACT(EPOCH FROM (...)) / 60` PostgreSQL |
| SortableJS drag & drop | `@dnd-kit/core` ou `react-beautiful-dnd` |
| Flatpickr | `react-day-picker` ou `vaul` + input controlado |
| `Cache::rememberForever()` | `unstable_cache()` do Next.js ou `lru-cache` em memória |

---

## Lógica de Negócio Crítica a Preservar

### 1. Ordenação de cards no Kanban
```sql
-- MySQL atual:
ORDER BY FIELD(priority, 'urgente', 'alta', 'normal', 'baixa'), started_at DESC

-- PostgreSQL:
ORDER BY
  CASE priority WHEN 'urgente' THEN 1 WHEN 'alta' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END,
  started_at DESC
```

### 2. isFinished() no Card
```typescript
// Precisa buscar colunas com type='concluido' do banco
const finishedColumns = await prisma.kanbanColumn.findMany({ where: { type: 'concluido' } })
const isFinished = finishedColumns.some(c => c.name === card.status)
```

### 3. Vínculo bidirecional de cards relacionados
```typescript
// Ao vincular A → B, gravar A→B e B→A
await prisma.$transaction([
  prisma.relatedCard.upsert({ where: { cardId_relatedCardId: { cardId: a, relatedCardId: b } }, create: ..., update: {} }),
  prisma.relatedCard.upsert({ where: { cardId_relatedCardId: { cardId: b, relatedCardId: a } }, create: ..., update: {} }),
])
// Ao desvincular: deletar ambos
```

### 4. ProductChangeClassifier
```typescript
export function classifyProductChange(original: { status: string; consumption: number }, updated: { status: string; consumption: number }) {
  if (original.status === 'ativo' && updated.status === 'cancelado') return 'churn'
  if (original.status === 'cancelado' && updated.status === 'ativo') return 'reactivation'
  const delta = Number(updated.consumption) - Number(original.consumption)
  if (delta > 0) return 'upgrade'
  if (delta < 0) return 'downgrade'
  return null
}
```

### 5. AppSetting cache
```typescript
// lib/app-settings.ts
export async function getSetting(key: string, fallback?: string): Promise<string | null> {
  // Usar unstable_cache do Next.js com revalidação por tag
  const setting = await prisma.appSetting.findUnique({ where: { key } })
  return setting?.value ?? fallback ?? null
}
```

### 6. Activity Log — todas as 17 actions
Ao atualizar um card, comparar campos antes/depois e criar um `CardActivityLog` por campo alterado. Ver lista completa no CONTEXT.md seção `card_activity_logs`.

### 7. Webhook secret
- Ao criar subscription: gerar `crypto.randomBytes(64).toString('hex')` (64 chars hex = 128 chars)
- Armazenar criptografado (AES-256 ou similar, usando `ENCRYPTION_KEY` env)
- Exibir apenas uma vez na criação (flash `webhook_created`)

---

## Migração de Dados (MariaDB → PostgreSQL)

### Estratégia recomendada
1. Script Node.js lê de MariaDB via `mysql2` e insere no PostgreSQL via Prisma
2. Rodar com banco novo em staging, validar, depois produção
3. Rollback: manter Umbler rodando até validação completa

### Tipos que precisam de atenção
| MySQL | PostgreSQL | Ação |
|---|---|---|
| `json` | `jsonb` | Automático |
| `tinyint(1)` (bool) | `boolean` | Converter 0/1 → false/true |
| `decimal(10,2)` | `NUMERIC(10,2)` | Direto |
| `enum(...)` | `VARCHAR` com check | Mapear valores |
| `string` PK (chats.id) | `TEXT` | Direto |
| `softDeletes` (`deleted_at`) | `TIMESTAMP` nullable | Direto |

---

## Variáveis de Ambiente (Vercel)

```env
# Banco (geradas pelo Vercel Postgres)
POSTGRES_PRISMA_URL=
POSTGRES_URL_NON_POOLING=

# App
API_ACCESS_TOKEN=e3RQ6P5nYkxNAsIuoiJLOSZG08qcWKbagyBrCFMUXEtlh7v9
ENCRYPTION_KEY=           # para criptografar secrets de webhook
CRON_SECRET=              # para autenticar chamadas do Vercel Cron
CUSTOMER_LOOKUP_URL=      # URL do n8n para lookup de cliente

# (opcional) Redis para cache
REDIS_URL=
```

---

## Escopo Estimado

| Área | Esforço |
|---|---|
| Setup Vercel + Postgres + Prisma + migrations | 0,5 dia |
| Migração de dados MariaDB → PostgreSQL | 1–2 dias |
| Auth middleware + audit helper | 0,5 dia |
| API Route Handlers (5 recursos) | 3–5 dias |
| Vercel Cron + webhook dispatch | 1–2 dias |
| fireWebhookEvent (substitui Events/Listeners/Jobs) | 1 dia |
| Layout base (sidebar, dark mode, design system) | 2–3 dias |
| Componentes compartilhados (ManagedCombobox, TagInput, etc.) | 3–4 dias |
| Board/Kanban + drag & drop | 3–4 dias |
| Cards (wizard create, show/edit completo) | 4–5 dias |
| Customers (list, create, show/edit + products) | 3–4 dias |
| Chats (list, show) | 1–2 dias |
| Reports + export CSV | 2–3 dias |
| Settings (todas as seções) | 2–3 dias |
| **Total estimado** | **~27–40 dias** |

---

## Ordem de Implementação

1. Setup: criar repo Next.js, conectar Vercel, provisionar Postgres, configurar Prisma
2. Schema Prisma + `prisma migrate dev`
3. Script de migração de dados (MariaDB → PostgreSQL)
4. `lib/db.ts`, `lib/audit.ts`, `lib/app-settings.ts`, `lib/product-classifier.ts`
5. Auth middleware (`middleware.ts`)
6. API Route Handlers — `/api/customers`, `/api/products`, `/api/cards`, `/api/chats`, `/api/webhooks`
7. `lib/webhooks.ts` + Vercel Cron (`/api/cron/process-webhooks`)
8. Layout base (`app/layout.tsx`) — sidebar, dark mode, design tokens
9. Componentes compartilhados — ManagedCombobox, ManagedTagInput, ConfirmModal, DatePicker
10. Board/Kanban — drag & drop com dnd-kit
11. Cards — wizard de criação, página de detalhe com auto-save
12. Customers — lista, criação, detalhe com produtos
13. Chats — lista e detalhe
14. Reports — filtros, tabela, CSV export
15. Settings — tags, templates, webhooks, opções, planos
16. Smoke tests + cutover (atualizar URL interna)

---

## O que não muda

- Contratos da API (mesmos paths, mesmas respostas JSON)
- Token de autenticação
- Schema de dados (mesmas tabelas, mesmos relacionamentos)
- Lógica de negócio (reincidência, SLA, audit log, webhooks)
- Design system (cores brand-violet #7c3aed, tipografia, dark mode via `.dark`)
- Assinatura de webhook (`X-Umbler-Signature: hmac-sha256=...`, `X-Umbler-Event`)
