# Retention Hub — Contexto do Projeto

## O que é

**Retention Hub** é uma plataforma interna de Ouvidoria e Retenção da Umbler. Permite ao time de CS gerenciar atendimentos de clientes insatisfeitos, documentar ações, acompanhar o fluxo de retenção via Kanban e buscar histórico.

---

## Infraestrutura

| Item | Detalhe |
|---|---|
| Servidor | Umbler Shared Hosting |
| URL | https://rev-ops-dev.umbler.net |
| Basic Auth proxy | `umbler` / `testehospedagem` (openresty — não é possível desativar) |
| PHP | 8.2.28 (servidor) |
| Framework | Laravel 10 |
| Banco | MariaDB 12 em `n8n-workflows.umbler.com:3300` |
| Database | `db_ombudsman` |
| DB User | `ombudsman_user` |
| FTP | `alderaan06.umbler.host:21` user `rev-ops-dev` |

### Limitações do servidor Umbler
- `exec()`, `shell_exec()` e similares **desabilitados** — não é possível rodar `php artisan` via SSH
- Extensão PHP `psr` v1.0.0 instalada em C level — conflita com Monolog 3. **Fix aplicado**: `Monolog\Logger` foi patched localmente em `vendor/monolog/monolog/src/Monolog/Logger.php` removendo os type hints `string|\Stringable` dos 9 métodos PSR-3
- CSRF desabilitado intencionalmente no middleware `web` (proxy Basic Auth substitui a proteção)

### Como fazer deploy
```powershell
# Ferramenta de upload
curl.exe --user "rev-ops-dev:SENHA_FTP" -T "arquivo_local" "ftp://alderaan06.umbler.host/caminho/remoto"

# PHP e Composer locais (para gerar vendor/)
& "C:\PHP81\php.exe" "C:\PHP81\composer.phar" install --no-dev --optimize-autoloader

# Rodar migrations (precisa subir artisan.php temporariamente)
# 1. Upload public/artisan.php
# 2. Acessar https://rev-ops-dev.umbler.net/artisan.php?token=rh-setup-2024&cmd=migrate
# 3. Deletar artisan.php via FTP
```

---

## Stack

- **Backend**: Laravel 10 + PHP 8.2
- **Frontend**: Blade + Alpine.js (CDN) + Tailwind CSS (CDN Play)
- **Drag & Drop**: SortableJS (CDN)
- **Fontes**: Plus Jakarta Sans, Fira Code (Google Fonts)
- **Design System**: cores `brand-*` (violeta) + `slate-*`, dark mode via classe `.dark`

---

## Banco de Dados — Tabelas

| Tabela | Descrição |
|---|---|
| `customers` | Clientes/empresas |
| `products` | Produtos contratados (Host/Talk2) por cliente |
| `product_changes` | Histórico de mudanças em produtos |
| `cards` | Atendimentos de ouvidoria (Kanban cards) |
| `chats` | Chats vinculados a um card (ID string, externo) |
| `webhook_subscriptions` | Assinaturas de webhook de clientes |
| `webhook_dispatch_logs` | Log de envios de webhook |
| `jobs` | Queue jobs (Laravel) |
| `kanban_columns` | Etapas do Kanban (editáveis pelo painel) |
| `card_comments` | Comentários/notas internas por card |

### Campos importantes de `cards`
`customer_id`, `product_id`, `status` (string = nome da etapa), `started_at`, `finished_at`, `ombudsman_agent`, `ticket_origin`, `contact_reason`, `reason_details`, `responsible_team`, `applied_solution`, `ra_claim_link`, `rating` (1–5), `is_sector_recurrent`

---

## Arquitetura do Projeto

```
app/
  Http/
    Controllers/
      Api/          ← REST API (autenticada por Bearer token)
        CustomerController, ProductController, CardController,
        ChatController, WebhookSubscriptionController
      Web/          ← Interface web (Blade)
        BoardController, CardWebController, CustomerWebController,
        SearchController
    Middleware/
      StaticTokenAuth  ← Checa Authorization: Bearer TOKEN
                          ou X-Api-Token: TOKEN ou ?api_token=TOKEN
  Models/
    Customer, Product, ProductChange, Card, Chat,
    KanbanColumn, CardComment, WebhookSubscription, WebhookDispatchLog
  Events/  ← CardCreated, CardUpdated, CardFinished, CustomerUpdated
  Jobs/    ← DispatchWebhookJob (retry exponencial)
  Listeners/ ← WebhookDispatchListener
resources/views/
  layouts/app.blade.php   ← Layout principal
  board.blade.php          ← Kanban com drag&drop
  search.blade.php         ← Busca universal
  cards/
    show.blade.php, create.blade.php
  customers/
    index.blade.php, show.blade.php, create.blade.php, cards.blade.php
  components/
    back-button.blade.php  ← Componente reutilizável de botão voltar
```

---

## API REST

**Base URL**: `https://rev-ops-dev.umbler.net/api`

**Autenticação** (Basic Auth do proxy openresty sempre necessária):
- Header: `Authorization: Basic dW1ibGVyOnRlc3RlaG9zcGVkYWdlbQ==`
- API token (um dos três métodos):
  - Header: `X-Api-Token: e3RQ6P5nYkxNAsIuoiJLOSZG08qcWKbagyBrCFMUXEtlh7v9`
  - Query: `?api_token=e3RQ6P5nYkxNAsIuoiJLOSZG08qcWKbagyBrCFMUXEtlh7v9`
  - Header Bearer (se não houver conflito com o proxy): `Authorization: Bearer TOKEN`

**Endpoints**:
```
GET    /api/health
GET    /api/customers
POST   /api/customers
GET    /api/customers/{id}
PUT    /api/customers/{id}
DELETE /api/customers/{id}
GET    /api/products
POST   /api/products
...    (CRUD completo para products, cards, chats, webhooks)
```

---

## Interface Web

**URL base**: `https://rev-ops-dev.umbler.net`

| Rota | Descrição |
|---|---|
| `/` | Board Kanban com drag&drop |
| `/customers` | Lista de clientes com busca |
| `/customers/create` | Novo cliente |
| `/customers/{id}` | Detalhe + edição de cliente |
| `/customers/{id}/cards` | Histórico de cards do cliente |
| `/cards/create` | Novo card |
| `/cards/{id}` | Detalhe do card (edição, chats, comentários) |
| `/search?q=termo` | Busca universal |

**Features do Kanban**:
- Drag & drop entre colunas (persiste via POST `/cards/{id}/move`)
- Etapas editáveis: renomear, mudar cor, criar, deletar
- Cores disponíveis: blue, yellow, green, red, purple, pink, indigo, gray

---

## Dark Mode

- Implementado via classe `.dark` no `<html>`
- Toggle no rodapé da sidebar (lua/sol)
- Persiste em `localStorage` chave `rh-theme`
- Detecta preferência do sistema na primeira visita
- Sidebar colapsável — persiste em `localStorage` chave `rh-sidebar`

---

## Credenciais e Tokens

| Item | Valor |
|---|---|
| API Token | `e3RQ6P5nYkxNAsIuoiJLOSZG08qcWKbagyBrCFMUXEtlh7v9` |
| APP_KEY | `base64:4nHHXh6xQDtqXY9TMs2IOqMxUat6eYGMMa+7CS4u6Uw=` |
| Basic Auth proxy | `umbler:testehospedagem` |
| FTP senha | `H4+*u%qYu]Tir-2` |
| DB senha | `senhamuitodificil123` |

---

## Git

- Repositório: https://github.com/luccabarboza1/Retention-Hub.git
- Branch principal: `main`
- Railway também conectado (mas deploy principal é Umbler)

---

## Problemas Conhecidos e Soluções Aplicadas

1. **Monolog vs extensão PSR C-level**: patched `vendor/monolog/.../Logger.php` removendo `string|\Stringable` type hints
2. **CSRF 419**: `VerifyCsrfToken` removido do middleware `web` — Basic Auth do proxy substitui
3. **drag&drop status**: usa POST `/cards/{id}/move` (não PATCH) para evitar bloqueio do proxy
4. **`Authorization` header conflito**: API aceita `X-Api-Token` e `?api_token` além do Bearer padrão
5. **Configs Laravel faltando**: adicionados `view.php`, `cache.php`, `logging.php`, `auth.php`, `session.php`, `filesystems.php`, `cors.php`, `hashing.php`
6. **`RouteServiceProvider` ausente**: criado `app/Providers/RouteServiceProvider.php` e registrado em `config/app.php`
