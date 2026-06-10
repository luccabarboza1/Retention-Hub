# Retention Hub — Contexto Completo do Projeto

## O que é

**Retention Hub** é uma plataforma interna de Ouvidoria e Retenção da Umbler. Permite ao time de CS gerenciar atendimentos de clientes insatisfeitos, documentar ações, acompanhar o fluxo de retenção via Kanban e buscar histórico.

---

## Infraestrutura

| Item | Detalhe |
|---|---|
| Servidor | Umbler Shared Hosting (Linux) |
| URL | https://rev-ops-dev.umbler.net |
| Basic Auth proxy | `umbler` / `testehospedagem` (openresty — não pode ser desativado) |
| PHP servidor | 8.2.28 |
| PHP local | 8.1.34 em `C:\PHP81\php.exe` |
| Composer local | `C:\PHP81\composer.phar` |
| Framework | Laravel 10 |
| Banco | MariaDB 12 em `n8n-workflows.umbler.com:3300` |
| Database | `db_ombudsman` |
| DB User | `ombudsman_user` |
| FTP host | `alderaan06.umbler.host:21` |
| FTP user | `rev-ops-dev` |
| Laravel root no servidor | `/home/defaultwebsite` |

### Limitações do servidor Umbler
- `exec()`, `shell_exec()` e similares **desabilitados** — não é possível rodar `php artisan` via SSH
- Extensão PHP `psr` v1.0.0 instalada em C level — conflita com Monolog 3. **Fix aplicado**: `vendor/monolog/monolog/src/Monolog/Logger.php` patched removendo os type hints `string|\Stringable` dos 9 métodos PSR-3
- CSRF desabilitado intencionalmente — `VerifyCsrfToken` removido do middleware `web` (proxy Basic Auth substitui)
- Drag & drop usa `POST /cards/{id}/move` em vez de `PATCH` (proxy bloqueia PATCH sem CSRF)

---

## Credenciais e Tokens

| Item | Valor |
|---|---|
| API Token | `e3RQ6P5nYkxNAsIuoiJLOSZG08qcWKbagyBrCFMUXEtlh7v9` |
| APP_KEY | `base64:4nHHXh6xQDtqXY9TMs2IOqMxUat6eYGMMa+7CS4u6Uw=` |
| Basic Auth proxy | `umbler:testehospedagem` |
| Basic Auth (Base64) | `dW1ibGVyOnRlc3RlaG9zcGVkYWdlbQ==` |
| FTP senha | `H4+*u%qYu]Tir-2` |
| DB senha | `senhamuitodificil123` |
| Setup/deploy token | `rh-setup-2024` |

---

## Workflow de Deploy

**IMPORTANTE**: `Compress-Archive` do PowerShell não preserva caminhos no zip. Sempre usar PHP para criar o zip.

```bash
# 1. Criar o zip com estrutura de diretórios preservada (Bash/Git Bash)
/c/PHP81/php.exe -r "
\$files = ['resources/views/foo.blade.php', 'app/Models/Bar.php'];
\$zip = new ZipArchive();
\$zip->open('deploy.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
foreach (\$files as \$f) { \$zip->addFile(\$f, \$f); }
\$zip->close();
"

# 2. Upload via FTP
curl.exe --user "rev-ops-dev:H4+*u%qYu]Tir-2" -T "deploy.zip" "ftp://alderaan06.umbler.host/deploy.zip"

# 3. Extrair no servidor
curl.exe -s -H "Authorization: Basic dW1ibGVyOnRlc3RlaG9zcGVkYWdlbQ==" \
  "https://rev-ops-dev.umbler.net/deploy_apply.php?token=rh-setup-2024"
```

### Utilitários HTTP no servidor (`public/`)
| Arquivo | Função |
|---|---|
| `deploy_apply.php` | Extrai `deploy.zip` em `/home/defaultwebsite/` e remove o zip |
| `cron_worker.php` | Processa a fila de webhooks — chamado a cada 5 min pelo cron da Umbler |
| `queue_check.php` | Diagnóstico: exibe últimos jobs pendentes/falhos na tabela `jobs` |

**Comando do cron_worker.php** (acesso via `?token=rh-setup-2024`):
```
php artisan queue:work --stop-when-empty --max-time 270 --queue webhooks,default --no-ansi --tries 1
```

---

## Stack Tecnológico

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 10 + PHP 8.2 |
| Frontend | Blade templates + Alpine.js 3.x (CDN) |
| CSS | Tailwind CSS + arquivo `public/css/app.css` compilado |
| Date picker | Flatpickr (CDN) + locale `pt` |
| Drag & Drop | SortableJS 1.15.3 (CDN) |
| Fontes | Inter, Plus Jakarta Sans, Fira Code (Google Fonts CDN) |
| Cores | `brand-*` (violeta: #7c3aed) + `slate-*`, dark mode via `.dark` no `<html>` |
| Timezone | `America/Sao_Paulo` |
| Locale | `pt_BR` |

---

## Configuração da Aplicação

| Config | Env var | Default |
|---|---|---|
| `api_access_token` | `API_ACCESS_TOKEN` | — |
| `customer_lookup_url` | `CUSTOMER_LOOKUP_URL` | — |
| `webhook_http_timeout` | — (hardcoded) | `25` segundos |
| Queue connection | `QUEUE_CONNECTION` | `database` |
| Queue table | — | `jobs` |

### Audit Actor (AppServiceProvider)
- HTTPS forçado para requisições via umbler.net ou `FORCE_HTTPS=true`
- Container binding `audit.actor` retorna valor do header `X-Actor` (default: `'api'` para API, `'system'` para jobs)
- Usado pelo trait `HasAudit` para preencher `created_by`, `updated_by`, `deleted_by`

---

## Banco de Dados — Schema Completo

### `customers`
| Coluna | Tipo | Notas |
|---|---|---|
| `id` | bigint PK | auto-increment |
| `client_name` | string | nome do contato |
| `company_name` | string | nome da empresa |
| `segment` | string(100) nullable | |
| `company_size` | string(50) nullable | |
| `instagram_followers_count` | int | default 0 |
| `email` | string nullable | |
| `related_emails` | json nullable | array de emails relacionados |
| `monthly_fee` | decimal(10,2) nullable | |
| `contracted_at` | date nullable | |
| `canceled_at` | date nullable | |
| `tier` | string(50) nullable | |
| `plan_name` | string(100) nullable | |
| `created_by` / `updated_by` / `deleted_by` | string(100) nullable | auditoria via HasAudit |
| `created_at` / `updated_at` | timestamps | |
| `deleted_at` | softDelete | |

### `products`
| Coluna | Tipo | Notas |
|---|---|---|
| `id` | bigint PK | |
| `customer_id` | FK customers | |
| `external_id` | string | ID externo do produto |
| `contract_identifier` | string nullable | identificador de contrato |
| `product_type` | enum: `'Host'`, `'Talk2'` | |
| `plan_name` | string(100) nullable | |
| `attendants_count` | int nullable | qtd atendentes (Talk2) |
| `host_services` | json nullable | serviços Host: `[email, dominio, hospedagem]` |
| `consumption` | decimal(10,2) | default 0.00 |
| `status` | enum: `'ativo'`, `'cancelado'` | default `'ativo'` |
| `has_chatbot` | boolean | default false |
| `has_ai` | boolean | default false |
| `has_implementation` | boolean | default false |
| `external_created_at` | datetime nullable | |
| `created_by` / `updated_by` / `deleted_by` | string(100) nullable | |
| `created_at` / `updated_at` | timestamps | |
| `deleted_at` | softDelete | |

Índice único: `(external_id, product_type)` como `uk_external_product`

### `product_changes`
| Coluna | Tipo | Notas |
|---|---|---|
| `id` | bigint PK | |
| `customer_id` | FK customers | |
| `product_id` | FK products | |
| `change_type` | enum: `'upgrade'`, `'downgrade'`, `'churn'`, `'reactivation'` | |
| `delta_consumption` | decimal(10,2) | delta vs valor anterior |
| `created_by` / `updated_by` / `deleted_by` | string(100) nullable | |
| `created_at` / `updated_at` | timestamps | |
| `deleted_at` | softDelete | |

### `cards`
| Coluna | Tipo | Notas |
|---|---|---|
| `id` | bigint PK | |
| `customer_id` | FK customers CASCADE | |
| `product_id` | FK products nullable | |
| `status` | string(50) | nome da coluna Kanban, ex: `'Aberto'` |
| `priority` | enum: `'baixa'`, `'normal'`, `'alta'`, `'urgente'` | default `'normal'` |
| `started_at` | datetime | data de abertura |
| `deadline_at` | datetime nullable | prazo |
| `finished_at` | datetime nullable | data de encerramento |
| `ticket_origin` | string(100) nullable | ex: `'E-mail'`, `'WhatsApp'` |
| `ombudsman_agent` | string(100) nullable | ouvidor responsável |
| `ra_claim_link` | string(500) nullable | link reclamação RA |
| `rating` | int nullable | avaliação 1–5 |
| `first_response_hours` | decimal(10,2) nullable | SLA primeira resposta |
| `ra_public_response_hours` | decimal(10,2) nullable | SLA resposta pública RA |
| `usage_time_post_ombudsman_hours` | decimal(10,2) nullable | uso pós-ouvidoria |
| `contact_reason` | string(255) nullable | motivo do contato |
| `reason_details` | text nullable | descrição detalhada |
| `responsible_team` | string(100) nullable | time responsável |
| `applied_solution` | text nullable | solução aplicada |
| `created_by` / `updated_by` / `deleted_by` | string(100) nullable | |
| `created_at` / `updated_at` | timestamps | |
| `deleted_at` | softDelete | |

Índices: `customer_id`, `product_id`, `status`

### `chats`
| Coluna | Tipo | Notas |
|---|---|---|
| `id` | string PK | ID externo do chat (não auto-increment) |
| `ombudsman_card_id` | FK cards CASCADE | |
| `started_at` | datetime nullable | |
| `closed_at` | datetime nullable | |
| `first_response_hours` | decimal(10,2) nullable | |
| `created_by` / `updated_by` / `deleted_by` | string(100) nullable | |
| `created_at` / `updated_at` | timestamps | |
| `deleted_at` | softDelete | |

### `card_comments`
| Coluna | Tipo | Notas |
|---|---|---|
| `id` | bigint PK | |
| `card_id` | FK cards CASCADE | |
| `author` | string(100) nullable | |
| `content` | text | campo é `content`, não `body` |
| `created_at` / `updated_at` | timestamps | |

### `kanban_columns`
| Coluna | Tipo | Notas |
|---|---|---|
| `id` | bigint PK | |
| `name` | string(100) unique | nome da etapa |
| `order` | smallint unsigned | posição no board |
| `color` | string(30) | `blue`, `yellow`, `green`, `red`, `purple`, `pink`, `indigo`, `gray` |
| `type` | enum: `'aberto'`, `'concluido'` | default `'aberto'` |
| `created_at` / `updated_at` | timestamps | |

Seed padrão: Aberto (blue, 1, aberto), Em Andamento (yellow, 2, aberto), Retido (green, 3, aberto), Churn (red, 4, concluido)

### `tags`
| Coluna | Tipo | |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | |
| `type` | string | `'customer'` ou `'card'` |
| `created_at` / `updated_at` | timestamps | |

Unique: `(name, type)`

Pivôs: `customer_tag` (customer_id, tag_id — PK composta), `card_tag` (card_id, tag_id — PK composta)

### `chat_agent_interactions`
| Coluna | Tipo | |
|---|---|---|
| `id` | bigint PK | |
| `chat_id` | string FK chats CASCADE | |
| `agent` | string(100) | |
| `interacted_on` | date | |

Unique: `(chat_id, agent, interacted_on)` — usa `firstOrCreate` para evitar duplicatas

### `solution_templates`
| Coluna | Tipo | |
|---|---|---|
| `id` | bigint PK | |
| `title` | string(100) | |
| `body` | text | |
| `product_type` | string(20) nullable | `'Talk2'`, `'Host'`, ou null (genérico) |
| `created_at` / `updated_at` | timestamps | |

### `card_activity_logs`
| Coluna | Tipo | |
|---|---|---|
| `id` | bigint PK | |
| `card_id` | FK cards CASCADE | |
| `actor` | string(100) nullable | quem realizou a ação |
| `action` | string(50) | ver lista abaixo |
| `from_value` | text nullable | valor anterior |
| `to_value` | text nullable | valor novo |
| `created_at` | datetime | sem `updated_at` (`$timestamps = false`) |

**Ações registradas** (via `CardActivityLog::label()` com match/switch):
| action | Quando é criado |
|---|---|
| `created` | Criação do card |
| `status` | Mudança de coluna Kanban |
| `finished` | Transição aberto → encerrado |
| `agent` | Troca de agente (`ombudsman_agent`) |
| `priority` | Mudança de prioridade |
| `contact_reason` | Mudança do motivo de contato |
| `responsible_team` | Mudança de time responsável |
| `ticket_origin` | Mudança de origem |
| `rating` | Avaliação adicionada/alterada |
| `deadline_at` | Prazo adicionado/alterado |
| `ra_claim_link` | Link RA adicionado/alterado |
| `reason_details` | Detalhes alterados |
| `applied_solution` | Solução aplicada alterada |
| `tags` | Tags sincronizadas |
| `note` | Comentário adicionado |
| `related_added` | Card vinculado |
| `related_removed` | Vínculo removido |

### `related_cards`
| Coluna | Tipo | |
|---|---|---|
| `card_id` | FK cards CASCADE | |
| `related_card_id` | FK cards CASCADE | |
| `created_at` | datetime | |

PK composta: `(card_id, related_card_id)`. Vínculo bidirecional: ao vincular A↔B, duas linhas gravadas.

### `webhook_subscriptions`
| Coluna | Tipo | |
|---|---|---|
| `id` | bigint PK | |
| `name` | string(100) | nome descritivo |
| `url` | string(2048) | URL de destino |
| `trigger_types` | json | array de event strings (ou `["*"]` para todos) |
| `secret` | text (encrypted) | oculto na resposta API |
| `is_active` | boolean | default true |
| `description` | text nullable | |
| `created_by` | string(100) nullable | |
| `created_at` / `updated_at` | timestamps | |
| `deleted_at` | softDelete | |

Índice: `(is_active, deleted_at)`

Event types válidos: `card.created`, `card.updated`, `card.finished`, `card.deleted`, `customer.created`, `customer.updated`, `customer.deleted`, `*`

### `webhook_dispatch_logs`
| Coluna | Tipo | |
|---|---|---|
| `id` | bigint PK | |
| `subscription_id` | bigint | |
| `event_type` | string | ex: `'card.created'` |
| `event_entity_id` | bigint | ID da entidade |
| `attempt_number` | tinyint | 1–5 |
| `max_attempts` | tinyint | sempre 5 |
| `status` | enum | `pending`, `success`, `failed`, `permanently_failed` |
| `payload` | json | payload completo enviado |
| `target_url` | string(2048) | URL do destino |
| `http_status` | smallint nullable | código HTTP da resposta |
| `response_body` | text nullable | truncado em 4000 chars |
| `error_message` | string(1000) nullable | truncado em 1000 chars |
| `dispatched_at` | datetime nullable | |
| `responded_at` | datetime nullable | |
| `next_retry_at` | datetime nullable | null se não vai retry |
| `created_at` / `updated_at` | timestamps | |

Índices: `(subscription_id, status)`, `(status, next_retry_at)`, `(event_type, event_entity_id)`

### `jobs` / `failed_jobs` / `job_batches`
Tabelas padrão do Laravel Queue. Processadas pelo `cron_worker.php`.

### `app_settings`
| Coluna | Tipo | |
|---|---|---|
| `key` | string(100) PK | |
| `value` | text nullable | |

Método `AppSetting::get(key, default)` — usa `Cache::rememberForever()`.
Método `AppSetting::set(key, value)` — persiste e limpa o cache.

Keys usadas: `card_ombudsman_agents`, `card_ticket_origins`, `card_responsible_teams`, `customer_tiers`, `customer_segments`, `customer_lookup_url`

### `product_plan_configs`
| Coluna | Tipo | |
|---|---|---|
| `id` | bigint PK | |
| `product_type` | string(20) | `'Talk2'` ou `'Host'` |
| `plan_name` | string(100) | |
| `price_per_unit` | decimal(10,2) | |
| `unit_label` | string(50) | ex: `'por atendente'` |

---

## Arquitetura — Controllers

### Trait `HasAudit`
Aplicado em: Customer, Card, Product, ProductChange, Chat, WebhookSubscription

- `creating`: seta `created_by` e `updated_by` via `app('audit.actor')`
- `updating`: seta `updated_by`
- `deleting` (soft): seta `deleted_by` via query direta no DB

### Web Controllers (`app/Http/Controllers/Web/`)

#### **DashboardController** — `/dashboard`
- KPIs: `totalCustomers`, `activeCustomers`, `newThisMonth`, `totalCards`, `openCards`, `closedCards`, `closureRate`, `totalMrr`
- `kanbanCols` + `cardsByColumn` (por status)
- `workloadByAgent` — agentes com cards abertos, ordenados por volume
- `oldestOpenCards` (6 mais antigos), `unassignedCards` (8 sem agente), `recentCards` (6 mais recentes)
- Busca universal quando `q` ≥ 2 chars: customers (company_name, client_name, email, plan_name, tier, related_emails JSON), cards (contact_reason, ombudsman_agent, reason_details, applied_solution, responsible_team, ticket_origin, ID numérico), chats (ID)

#### **SearchController** — `/search`
- Mesma lógica de busca do Dashboard, view separada

#### **BoardController** — `/`
- Carrega colunas + cards com filtros de `tag` e `priority`
- `storeColumn()`: valida name (unique), color, type; calcula `order = max+1`
- `updateColumn()`: se `name` mudar, faz bulk update em `cards.status`
- `destroyColumn()`: impede exclusão se há cards

#### **CardWebController** — `/cards`
- `show()`: carrega card + comentários + opções (agents/origins/teams do AppSetting) + allTags + contagens do cliente + templates + activityLog (limit 50) + relatedCards
- `store()`: status inicial = primeira coluna; cria card; syncTags; log `created`; event `CardCreated`
- `update()`: detecta campos alterados → cria entrada no activity_log por campo alterado; se transição → concluido: seta `finished_at`; event `CardFinished` ou `CardUpdated`; aceita AJAX (retorna `{ok: true}`) e form normal
- `updateStatus()` e `moveStatus()`: atualizam status + finished_at + log `status` + events
- `storeComment()`: cria comentário + log `note`
- `addRelated()` / `removeRelated()`: sincronização bidirecional em `related_cards`
- `destroy()`: event `CardDeleted` → soft delete

#### **ChatWebController** — `/chats`
- `index()`: lista chats com filtros (q, status=open/closed, date_from, date_to); KPIs: `totalChats`, `openChats`, `avgResponse` (AVG first_response_hours), `avgDuration` (AVG TIMESTAMPDIFF em minutos / 60); paginado 30/página
- `show()`: detalhe do chat com card.customer e card.product
- `destroy()`: soft delete

#### **CustomerWebController** — `/customers`
- `index()`: lista com filtros (q, tag); conta cards totais e abertos por cliente; paginado 30/página
- `show()`: conta cards; produtos com últimas 5 mudanças; 5 cards recentes; 8 mudanças recentes de produto; `formOptions()` helper
- `store()`: cria customer + produtos (se enviados) + syncTags; event `CustomerCreated`
- `update()`: syncTags; event `CustomerUpdated`
- `destroy()`: event `CustomerDeleted` → soft delete
- `cardCount()`: retorna JSON `{total, open}` (AJAX)
- `formOptions()`: retorna tiers, plans, segments, sizes, planConfigs, allTags (com cache via AppSetting)

#### **CustomerLookupController** — `POST /customers/lookup-email`
- Faz POST para URL configurada em `app_settings.customer_lookup_url` (n8n)
- Retorna 503 se URL não configurada; 422 se n8n retornar erro; 200 com dados do cliente

#### **ProductWebController** — `/products`
- `store()`: validação dinâmica por tipo (Talk2 vs Host); calcula consumption para Talk2 (plan.price_per_unit × attendants_count)
- `update()`: recalcula consumption; usa `ProductChangeClassifier` para detectar upgrade/downgrade/churn/reactivation; cria `ProductChange` se mudança detectada
- Flash: `"Produto adicionado/atualizado/removido."`

#### **ReportController** — `/reports`
- Filtros: `date_from` (default: 29 dias atrás), `date_to` (default: hoje), `origin`, `agent`
- KPIs: `totalInPeriod`, `openCards`, `avgRating`, `avgResponse`, `avgPostOmb`
- Distribuição de avaliações: array counts [1–5]
- Breakdowns: `byTeam`, `byReason` (top 10), `byOrigin`, `byProduct` (JOIN products), `byTier` (JOIN customers)
- Séries temporais diárias: `cardsTimelineData`, `responseTimelineData`, `postOmbTimelineData`
- Análise de reincidência por origem (histórica, sem filtro de período)
- Volume diário por agente (top 5)
- Matriz Team × Reason (top 8 razões, todos times)
- `export()`: CSV stream com BOM UTF-8 + ponto-e-vírgula; 18 colunas; ordenação por `date_from`

#### **GeneralSettingsController** — `/settings`
- `saveCardOptions(type)`: salva lista de opções para ombudsman_agents, ticket_origins, responsible_teams em AppSetting
- `saveCustomerOptions(type)`: salva para tiers, segments
- `checkUsage()`: conta entidades usando um valor de opção antes de excluir
- `deleteAndReplace()`: substitui ou limpa um valor de opção em todos os registros; remove da AppSetting

#### **TagSettingsController** — `/settings/tags`
- `store()`: verifica duplicata case-insensitive; cria ou retorna existente; suporta AJAX (JSON) e HTML
- `destroy()`: detach de todos os customers/cards; delete; suporta AJAX e HTML

#### **WebhookSettingsController** — `/settings/webhooks`
- `store()`: deduplica trigger_types; gera secret com `Str::random(64)`; flash especial `webhook_created` com `{id, secret, name}` (mostrado uma única vez)

#### **SolutionTemplateController** — `/settings/templates`
CRUD simples. Ordenado por `product_type + title`.

#### **ProductSettingsController** — `/settings/products`
CRUD de `product_plan_configs`.

### API Controllers (`app/Http/Controllers/Api/`)
Autenticação: middleware `api.token` (StaticTokenAuth)

Token aceito via:
1. `Authorization: Bearer <token>`
2. `X-Api-Token: <token>`
3. `?api_token=<token>`

Resposta 401: `{"message": "Unauthorized."}`

| Controller | Endpoints |
|---|---|
| CustomerController | CRUD em `/api/customers` + filtros search/tier/segment |
| ProductController | CRUD em `/api/products` + filtros customer_id/product_type/status |
| CardController | CRUD em `/api/cards` + filtros customer_id/status/ombudsman_agent |
| ChatController | CRUD em `/api/cards/{cardId}/chats` |
| ChatAgentInteractionController | POST `/api/chats/{chatId}/interactions` (firstOrCreate) |
| WebhookSubscriptionController | CRUD em `/api/webhooks` |

---

## Eventos e Jobs

### Eventos (app/Events/)

Todos implementam `toWebhookPayload()` que retorna o payload enviado aos webhooks.

| Evento | trigger_type | Quando é disparado |
|---|---|---|
| `CardCreated` | `card.created` | `CardWebController@store`, `Api/CardController@store` |
| `CardUpdated` | `card.updated` | `CardWebController@update/updateStatus/moveStatus`, `Api/CardController@update` |
| `CardFinished` | `card.finished` | Transição de status → coluna tipo `concluido` |
| `CardDeleted` | `card.deleted` | `CardWebController@destroy`, `Api/CardController@destroy` |
| `CustomerCreated` | `customer.created` | `CustomerWebController@store` |
| `CustomerUpdated` | `customer.updated` | `CustomerWebController@update`, `Api/CustomerController@update` |
| `CustomerDeleted` | `customer.deleted` | `CustomerWebController@destroy` |

**Payload completo** (CardCreated/Updated/Finished):
```json
{
  "event": "card.created",
  "timestamp": "ISO8601",
  "data": {
    "id": 1,
    "customer_id": 1,
    "customer": { "...todos campos...", "tags": [] },
    "product": { "...todos campos..." },
    "tags": ["nome-da-tag"],
    "...todos campos do card..."
  }
}
```

**Payload de deleção** (CardDeleted/CustomerDeleted):
```json
{
  "event": "card.deleted",
  "timestamp": "ISO8601",
  "deleted_id": 1,
  "data": {
    "id": 1,
    "contact_reason": "...",
    "status": "...",
    "customer_id": 1,
    "customer_name": "...",
    "deleted_at": "ISO8601"
  }
}
```

### DispatchWebhookJob (app/Jobs/)

- Queue: `webhooks` | Tries: 1 (retries gerenciados manualmente) | Timeout: 40s
- HTTP POST com headers:
  - `X-Umbler-Signature: hmac-sha256=<HMAC-SHA256(json_payload, secret)>`
  - `X-Umbler-Event: <event_type>`
  - `Content-Type: application/json`
  - Timeout HTTP: 25s
- Retry exponencial: delays 30, 60, 120, 240, 300 segundos (máx 5 tentativas)
- Ao esgotar tentativas: status `permanently_failed`
- Tudo logado em `webhook_dispatch_logs`

### WebhookDispatchListener (app/Listeners/)

- Escuta todos os 7 eventos
- Busca subscriptions ativas onde `trigger_types` contém o `triggerType` do evento **OU** `'*'`
- Despacha um `DispatchWebhookJob` por subscription

---

## Services

### ProductChangeClassifier (`app/Services/`)

Compara estado anterior vs atual do produto:
- `'churn'`: status `ativo` → `cancelado`
- `'reactivation'`: status `cancelado` → `ativo`
- `'upgrade'`: consumption aumentou (delta > 0)
- `'downgrade'`: consumption diminuiu (delta < 0)
- `null`: nenhuma mudança relevante

---

## Design System

### Classes CSS globais (`resources/css/app.css`)

```css
.field-input   { border, border-radius 0.75rem, padding 0.625rem 1rem, font-size 0.875rem }
.field-input:focus { border-color: #7c3aed, box-shadow rgba(124,58,237,0.08) }
.field-label   { font-size 0.75rem, uppercase, letter-spacing 0.05em, color #94a3b8 }
.select-wrap   { position: relative } /* adiciona chevron via ::after */
.btn-primary   { violeta, hover, shadow }
.btn-ghost     { violeta ghost, bg #f5f3ff }
```

### Flatpickr
Campos com `data-fp="date"` ou `data-fp="datetime"` são inicializados automaticamente no `DOMContentLoaded`. Locale PT.

---

## Alpine.js — Funções Globais (`layouts/app.blade.php`)

### `appShell()`
- `collapsed` → persiste `localStorage['rh-sidebar']` (`'1'` = colapsado)
- `isDark` → persiste `localStorage['rh-theme']`
- `toggleDark()` → adiciona classe `theme-transitioning` (200ms), alterna `.dark` no `<html>`

### `managedCombobox(type, saveUrl, opts, initVal)`
- Input com gerenciamento inline de opções
- Navegação por teclado (↑↓ Enter Escape)
- Ao selecionar: dispatcha evento `change` + evento `card-save` (para auto-save do form)
- Botão "Gerenciar" → painel inline para add/remove opções
- Auto-persiste via POST `saveUrl` (ex: `/settings/card-options/{type}`)
- `freeText: true` (padrão) permite digitar valor livre

### `combobox(opts, init)`
Filtro simples sem persistência.

### `customSelect(initial)`
Dropdown estático para uso em forms com submit explícito.
- Lê opções via `data-opts='@json($array)'` + `JSON.parse($el.dataset.opts)`
- Dispatcha `card-save` ao selecionar

### `emailTags(initial)`
Input de e-mails em chips. Separadores: Enter/Tab/vírgula. Valida regex antes de adicionar.

### `managedTagInput(type, currentNames, allTagObjects)`
- Chips com botão ×
- Autocomplete das tags existentes
- Cria tags inline via `POST /settings/tags` → `{ok, id, name}`
- Remove tags via `DELETE /settings/tags/{id}`
- Hidden inputs para submit do form

### `tagInput(initial, suggestions)`
Tags livres sem persistência.

### `confirmModal()` / `optionDeleteModal()`
Modais globais. Disparados via:
```js
window.dispatchEvent(new CustomEvent('open-confirm', { detail: { title, message, form } }))
window.dispatchEvent(new CustomEvent('open-option-delete', { detail: { ... } }))
```

### `wizard()` — `cards/create.blade.php`
3 steps: Cliente (0) → Responsáveis (1) → Detalhes (2)
- `next()`: valida `customer_id` obrigatório no step 0
- `_checkReincidence(customerId)`: `GET /customers/{id}/card-count` → `{total, open}` → exibe banner amarelo

### Board Drag & Drop — `board.blade.php`
```js
Sortable.create(col, {
    group: 'cards', animation: 180,
    ghostClass: 'opacity-20', dragClass: 'shadow-2xl',
    draggable: '[data-card-id]',
    onEnd: (evt) => {
        // POST /cards/{cardId}/move
        // FormData: status={columnName}
        // Header: X-CSRF-TOKEN
    }
})
```

Board Alpine component `board()`:
- `showColModal`, `editColId`, `editColName`, `editColColor`, `editColType`
- `editCol(id, name, color, type)` → preenche modal de edição

### Card Show Auto-save — `cards/show.blade.php`
```js
{
    saving: false,
    save() {
        // PATCH this.$refs.cardForm
        // Debounced 1000ms em @input
        // Imediato em @change
        // Disparado por @card-save.window (de managed combobox/customSelect)
    }
}
```

### Mapa de cores Kanban
```js
// Por color string:
blue   → bg-blue-50/cardBorder, yellow → bg-amber-50, green → bg-emerald-50
red    → bg-rose-50, purple → bg-purple-50, pink → bg-pink-50
indigo → bg-indigo-50, gray → bg-slate-50
```

### Badge de deadline (board)
```
hoursLeft < 0  → "Vencido" (vermelho)
hoursLeft < 24 → "Vence hoje" (amarelo)
else           → "{daysLeft}d restantes" (verde)
```

---

## Views — Parciais e Componentes

### Parciais raiz (`resources/views/`)

**`_custom_select.blade.php`** — Dropdown sem auto-submit. Params: `$name`, `$options`, `$current`, `$placeholder`, `$width`

**`_filter_select.blade.php`** — Variante que submete o form automaticamente ao selecionar.

**`_tag_input.blade.php`** — Tags livres. Params: `$fieldName`, `$currentTags`, `$label`, `$placeholder`

**`_tag_input_managed.blade.php`** — Tags gerenciadas. Params: `$type`, `$fieldName`, `$currentTags`, `$allTags`, `$label`

**`_tag_input_ui.blade.php`** — UI compartilhada pelos dois tag inputs.

### Componentes Blade (`resources/views/components/`)

**`back-button.blade.php`** — `<x-back-button :href="route('...')" />`. Param `$label` (default: `'Voltar'`).

### `cards/`

**`_managed_combobox.blade.php`** — Params: `$type`, `$name`, `$label`, `$placeholder`, `$options`, `$old`, `$col`, `$freeText` (bool), `$saveUrl` (optional)

### `customers/`

**`_form.blade.php`** — Formulário compartilhado create/show.

**`_managed_combobox.blade.php`** — Variante para campos de cliente.

**`_product_form.blade.php`** — Sub-formulário de produtos. Alpine state: `ptype`, `planConfigs`, `planPrice`, `attendants`, computed `total = planPrice × attendants`

---

## Rotas Web Completas

```
GET    /dashboard                          DashboardController@index           dashboard
GET    /search                             SearchController@__invoke           search
GET    /docs                               (view 'docs')                       docs

GET    /                                   BoardController@index               board
POST   /columns                            BoardController@storeColumn         columns.store
PATCH  /columns/{column}                   BoardController@updateColumn        columns.update
DELETE /columns/{column}                   BoardController@destroyColumn       columns.destroy

GET    /cards/create                       CardWebController@create            cards.create
POST   /cards                              CardWebController@store             cards.store
GET    /cards/{card}                       CardWebController@show              cards.show
PATCH  /cards/{card}                       CardWebController@update            cards.update
DELETE /cards/{card}                       CardWebController@destroy           cards.destroy
PATCH  /cards/{card}/status                CardWebController@updateStatus      cards.update-status
POST   /cards/{card}/move                  CardWebController@moveStatus        cards.move (AJAX JSON)
POST   /cards/{card}/comments              CardWebController@storeComment      cards.comments.store
DELETE /cards/{card}/comments/{comment}    CardWebController@destroyComment    cards.comments.destroy
POST   /cards/{card}/chats                 CardWebController@storeChat         cards.chats.store
POST   /cards/{card}/chats/{chat}          CardWebController@updateChat        cards.chats.update (AJAX JSON)
PATCH  /cards/{card}/chats/{chat}/close    CardWebController@closeChat         cards.chats.close
POST   /cards/{card}/related               CardWebController@addRelated        cards.related.add
DELETE /cards/{card}/related/{related}     CardWebController@removeRelated     cards.related.remove

GET    /chats                              ChatWebController@index             chats.index
GET    /chats/{chat}                       ChatWebController@show              chats.show
DELETE /chats/{chat}                       ChatWebController@destroy           chats.destroy

POST   /customers/lookup-email             CustomerLookupController@lookup     customers.lookup (AJAX JSON)
GET    /customers                          CustomerWebController@index         customers.index
GET    /customers/create                   CustomerWebController@create        customers.create
POST   /customers                          CustomerWebController@store         customers.store
GET    /customers/{customer}               CustomerWebController@show          customers.show
PATCH  /customers/{customer}               CustomerWebController@update        customers.update
DELETE /customers/{customer}               CustomerWebController@destroy       customers.destroy
GET    /customers/{customer}/cards         CustomerWebController@cards         customers.cards
GET    /customers/{customer}/card-count    CustomerWebController@cardCount     customers.card-count (AJAX JSON)

POST   /customers/{customer}/products      ProductWebController@store          products.store
PATCH  /products/{product}                 ProductWebController@update         products.update
DELETE /products/{product}                 ProductWebController@destroy        products.destroy

GET    /reports                            ReportController@index              reports.index
GET    /reports/export                     ReportController@export             reports.export (CSV stream)

GET    /settings                           (view 'settings.index')             settings.index
GET    /settings/general                   GeneralSettingsController@index     settings.general
POST   /settings/general                   GeneralSettingsController@update    settings.general.update
GET    /settings/tags                      TagSettingsController@index         settings.tags
POST   /settings/tags                      TagSettingsController@store         settings.tags.store
DELETE /settings/tags/{tag}                TagSettingsController@destroy       settings.tags.destroy
GET    /settings/templates                 SolutionTemplateController@index    settings.templates
POST   /settings/templates                 SolutionTemplateController@store    settings.templates.store
PATCH  /settings/templates/{template}      SolutionTemplateController@update   settings.templates.update
DELETE /settings/templates/{template}      SolutionTemplateController@destroy  settings.templates.destroy
GET    /settings/webhooks                  WebhookSettingsController@index     settings.webhooks
POST   /settings/webhooks                  WebhookSettingsController@store     settings.webhooks.store
PATCH  /settings/webhooks/{webhook}        WebhookSettingsController@update    settings.webhooks.update
DELETE /settings/webhooks/{webhook}        WebhookSettingsController@destroy   settings.webhooks.destroy
GET    /settings/products                  ProductSettingsController@index     settings.products
POST   /settings/products                  ProductSettingsController@store     settings.products.store
PATCH  /settings/products/{plan}           ProductSettingsController@update    settings.products.update
DELETE /settings/products/{plan}           ProductSettingsController@destroy   settings.products.destroy
POST   /settings/card-options/{type}       GeneralSettingsController@saveCardOptions    settings.card-options (AJAX JSON)
POST   /settings/customer-options/{type}   GeneralSettingsController@saveCustomerOptions settings.customer-options (AJAX JSON)
POST   /settings/options/check-usage       GeneralSettingsController@checkUsage         settings.options.check-usage (AJAX JSON)
POST   /settings/options/delete-and-replace GeneralSettingsController@deleteAndReplace  settings.options.delete-and-replace (AJAX JSON)
```

### AJAX JSON — Endpoints Internos

| Rota | Retorno |
|---|---|
| `POST /customers/lookup-email` | JSON dados do cliente (n8n) |
| `POST /cards/{card}/move` | `{ok, status}` |
| `PATCH /cards/{card}` (Accept: json) | `{ok: true}` |
| `POST /cards/{card}/chats/{chat}` | `{ok: true}` |
| `GET /customers/{customer}/card-count` | `{total, open}` |
| `POST /settings/card-options/{type}` | `{ok, options}` |
| `POST /settings/customer-options/{type}` | `{ok, options}` |
| `POST /settings/options/check-usage` | `{count, entityType}` |
| `POST /settings/options/delete-and-replace` | `{ok: true}` |
| `POST /settings/tags` | `{ok, id, name}` (201 ou 200) |
| `DELETE /settings/tags/{tag}` | `{ok: true}` |

---

## API REST

**Base URL**: `https://rev-ops-dev.umbler.net/api`

**Autenticação** (Basic Auth do proxy sempre necessária):
```
Authorization: Basic dW1ibGVyOnRlc3RlaG9zcGVkYWdlbQ==
X-Api-Token: e3RQ6P5nYkxNAsIuoiJLOSZG08qcWKbagyBrCFMUXEtlh7v9
```

### Validações completas da API

**POST /api/customers:**
```
client_name, company_name: required|string|max:255
email: nullable|email|unique:customers,email
contracted_at, canceled_at: nullable|date
monthly_fee: nullable|numeric|min:0
instagram_followers_count: nullable|integer|min:0
segment, company_size: nullable|string
tier, plan_name: nullable|string
```

**POST /api/cards:**
```
customer_id: required|exists:customers,id
product_id: nullable|exists:products,id
started_at: required|date
status: nullable|string|max:50
ticket_origin, ombudsman_agent, responsible_team: nullable|string|max:100
ra_claim_link: nullable|url|max:500
contact_reason: nullable|string|max:255
reason_details: nullable|string
```

**POST /api/webhooks:**
```
name: required|string|max:100
url: required|url|max:2048
trigger_types: required|array|min:1 (cada item: Enum WebhookTrigger)
description: nullable|string
is_active: nullable|boolean
```
→ Gera secret: `Str::random(64)`. Secret retornado apenas no POST (criação).

---

## Sidebar — Navegação

1. **Dashboard** → `route('dashboard')`
2. **Board** → `route('board')`
3. **Clientes** → `route('customers.index')`
4. **Chats** → `route('chats.index')`
5. **Relatórios** → `route('reports.index')`
6. **Configurações** → `route('settings.index')` (rodapé)

---

## Dark Mode e Persistência

- Dark mode via classe `.dark` no `<html>`
- `localStorage['rh-theme']`: `'dark'` | `'light'`
- `localStorage['rh-sidebar']`: `'1'` = colapsado
- Classes pré-aplicadas no `<head>`: `sidebar-pre-collapsed` / `sidebar-pre-expanded`
- Transição suave: classe temporária `theme-transitioning` (200ms)

---

## Git

- Repositório: https://github.com/luccabarboza1/Retention-Hub.git
- Branch principal: `main`
- Git user: `luccafrb`

---

## Problemas Conhecidos e Soluções

1. **Monolog vs extensão PSR C-level**: patched `vendor/monolog/.../Logger.php` removendo type hints nos 9 métodos PSR-3.
2. **CSRF 419**: `VerifyCsrfToken` removido do middleware `web`.
3. **Drag & drop usa POST**: proxy openresty bloqueia PATCH → drag & drop usa `POST /cards/{id}/move`.
4. **`Authorization` header conflito**: API aceita `X-Api-Token` e `?api_token` para evitar conflito com Basic Auth.
5. **`@json()` dentro de `x-data` quebra Alpine**: usar `data-opts='@json($array)'` + `JSON.parse($el.dataset.opts)`.
6. **`Compress-Archive` PowerShell**: não preserva caminhos. Sempre usar PHP `ZipArchive`.
7. **Migrations via HTTP**: criar `public/run_migration.php` temporário, deploy, acessar via HTTP, deletar via FTP DELE.
