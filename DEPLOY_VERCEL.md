# Deploy na Vercel — Checklist & Dados

> Documento vivo para reunir tudo que precisamos **na hora de subir o Retention Hub na Vercel**.
> Ainda não temos acesso à conta — preencha os campos `(preencher)` conforme as credenciais forem geradas.
> **Não commitar valores secretos reais.** Use este doc como roteiro; os segredos vão no painel da Vercel / `.env` local (que está no `.gitignore`).

Última revisão: 2026-06-10

---

## 1. Decisão de plano (ler antes de tudo)

| Plano | Cron Jobs | Impacto no nosso caso |
|---|---|---|
| **Hobby** | Máx. **1 execução/dia**; agendamentos de minutos são ignorados | Entrega de webhook continua **imediata** (ver §5), mas o **retry de falhas** só acontece 1x/dia |
| **Pro** | Agendamento livre (ex.: `*/5 * * * *`) | Retry de falhas a cada 5 min |

- O caminho feliz dos webhooks **não depende do cron** (usamos `after()` — §5), então o limite do Hobby **não** atrasa a entrega normal.
- O cron é só **rede de segurança** para reprocessar disparos que falharam ou que a função não concluiu.
- **Decisão:** `(preencher: Hobby ou Pro?)`
- Fluid Compute (padrão na Vercel) é **necessário** para o `after()` manter a função viva após a resposta — confirmar que está ativo no projeto.

---

## 2. Banco de dados (PostgreSQL)

> ⚠️ "Vercel Postgres" foi **descontinuado**. Hoje o Postgres vem pelo **Vercel Marketplace** (Neon, Prisma Postgres, Supabase, etc.). Ao instalar a integração, as variáveis de conexão são injetadas automaticamente no projeto.

- Provider escolhido: `(preencher: Neon / Prisma Postgres / Supabase / outro)`
- Nome do banco / projeto: `(preencher)`
- Região: `(preencher — idealmente mesma região das Functions)`
- O Prisma usa **duas** URLs (pooled + direta). Confirmar que a integração popula:
  - `POSTGRES_PRISMA_URL` (pooled, usado em runtime)
  - `POSTGRES_URL_NON_POOLING` (direta, usado em migrations)
  - Se a integração usar outros nomes (ex.: `DATABASE_URL`), criar aliases no painel apontando para esses dois nomes.

### Migrations
Há migrations versionadas em `prisma/migrations/` (`20260609172020_init`).
- No primeiro deploy, rodar: `npx prisma migrate deploy` (não usar `db push` em produção).
- `postinstall` já roda `prisma generate` automaticamente no build.
- Seed (opcional, dados de opções/colunas): `npm run db:seed`.

---

## 3. Variáveis de ambiente

Preencher na Vercel em **Project → Settings → Environment Variables** (escopos: Production / Preview).

| Variável | Usada em | Como gerar / obter | Valor (preencher fora do git) |
|---|---|---|---|
| `POSTGRES_PRISMA_URL` | Prisma (runtime) | Integração de banco do Marketplace | `(auto pela integração)` |
| `POSTGRES_URL_NON_POOLING` | Prisma (migrations) | Integração de banco do Marketplace | `(auto pela integração)` |
| `API_ACCESS_TOKEN` | `proxy.ts` — auth de todas as rotas `/api/*` | Gerar string aleatória forte (`openssl rand -hex 32`) | `(preencher)` |
| `CRON_SECRET` | `app/api/cron/process-webhooks` — auth do cron | Gerar string aleatória forte. A Vercel injeta `Authorization: Bearer <CRON_SECRET>` automaticamente nas execuções de cron | `(preencher)` |
| `CUSTOMER_LOOKUP_URL` | **Não lido pelo código** (ver nota) | — | — |
| `ENCRYPTION_KEY` | **Não lido pelo código** (ver nota) | — | — |

### Notas importantes sobre env
- **`CUSTOMER_LOOKUP_URL` (env) não é usada.** A URL de lookup do n8n é configurada pela **interface**, em `Configurações → Geral → Integrações & API de Busca`, e salva no banco (`app_settings.customer_lookup_url`). Está no `.env.example` apenas por herança do projeto PHP. Não precisa preencher na Vercel.
- **`ENCRYPTION_KEY` não é usada (ainda).** Estava no plano de migração para criptografar o `secret` dos webhooks, mas hoje o campo `WebhookSubscription.secret` é salvo em texto plano. Se/quando implementarmos a criptografia, voltar aqui.
- Após mudar qualquer env var na Vercel, é preciso **redeploy** para ter efeito.

---

## 4. Cron Jobs

Configurado em `vercel.json`:
```json
{ "crons": [{ "path": "/api/cron/process-webhooks", "schedule": "*/5 * * * *" }] }
```
- No **Hobby**, esse `*/5` degrada para 1x/dia automaticamente (não quebra — só reduz a frequência de retry).
- No **Pro**, roda a cada 5 min como escrito.
- A rota valida o header `Authorization: Bearer ${CRON_SECRET}`. Sem `CRON_SECRET` setado, o cron retorna 401.
- **Não** precisamos de cron externo — a entrega imediata cobre o caminho feliz.

---

## 5. Arquitetura de entrega de webhooks (contexto para quem subir)

Mudamos do modelo antigo (cron na hospedagem fazia todo o envio) para **outbox + envio imediato**:

1. Evento ocorre (ex.: `customer.created`) → `fireWebhookEvent` (`lib/webhooks.ts`) grava as entregas em `webhook_dispatch_logs` com status `pending` (durabilidade).
2. Na mesma request, `after()` dispara o POST **logo após a resposta ao usuário** — entrega sub-segundo, sem travar o usuário, sem depender do cron.
3. POST assinado com HMAC-SHA256 (`X-Umbler-Signature`), timeout 25s. Sucesso → `success`; falha → `failed` com backoff `[30,60,120,240,300]s` até `maxAttempts=5` → `permanently_failed`.
4. O cron (§4) é **backstop**: só pega `pending` com mais de 2 min (janela de carência que evita disparo duplicado com o `after()`) e `failed` com retry vencido.

**Dependência:** o `after()` precisa do **Fluid Compute** (padrão na Vercel). Se a invocação morrer antes de concluir, a linha fica `pending` no outbox e o cron entrega depois — nunca se perde o evento, no pior caso volta ao comportamento de polling.

---

## 6. Passos do primeiro deploy

1. Importar o repositório na Vercel (framework detectado: **Next.js**).
2. Instalar a integração de **Postgres** (Marketplace) e linká-la ao projeto.
3. Conferir/ajustar nomes das env vars de banco (§2) e adicionar `API_ACCESS_TOKEN` + `CRON_SECRET` (§3).
4. Garantir que **Fluid Compute** está ativo.
5. Rodar as migrations: `npx prisma migrate deploy` (via terminal com as URLs de produção, ou um passo de build/post-deploy).
6. (Opcional) `npm run db:seed` para popular opções iniciais.
7. Deploy de produção.

---

## 7. Verificação pós-deploy (smoke test)

- [ ] App abre e a sidebar/topbar carregam.
- [ ] **Busca universal** (topbar / ⌘K) retorna clientes, cards e chats.
- [ ] Criar cliente funciona; com `customer_lookup_url` configurada na UI, o **"Preencher automaticamente"** popula o form a partir do n8n.
- [ ] Cadastrar um **webhook** apontando para um endpoint de teste (ex.: webhook.site) com um trigger ativo.
- [ ] Criar um cliente → o webhook chega **na hora** (validação do `after()`).
- [ ] Verificar a assinatura `X-Umbler-Signature` no destino (HMAC com o `secret` da subscription).
- [ ] Forçar uma falha (URL inválida) e confirmar que vira `failed` com `nextRetryAt`, e que o cron reprocessa.
- [ ] Bater manualmente no cron e confirmar 200:
  ```bash
  curl -H "Authorization: Bearer <CRON_SECRET>" https://<app>.vercel.app/api/cron/process-webhooks
  ```
- [ ] Rotas `/api/*` sem `x-api-token` retornam 401 (auth do `proxy.ts`).

---

## 8. Dados do ambiente (preencher quando tiver acesso)

- Org/Team Vercel: `(preencher)`
- Nome do projeto Vercel: `(preencher)`
- Domínio de produção: `(preencher)`
- URL do workflow n8n (lookup): `(preencher — vai na UI, não em env)`
- Responsável pelo deploy: `(preencher)`
