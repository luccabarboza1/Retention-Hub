@extends('layouts.app')
@section('title', 'API Docs')
@section('header', 'Documentação da API')

@php
function badge(string $method): string {
    $map = [
        'GET'    => 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300',
        'POST'   => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'PUT'    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'PATCH'  => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
        'DELETE' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
    ];
    $cls = $map[$method] ?? 'bg-slate-100 text-slate-600';
    return "<span class=\"inline-block font-mono font-bold text-[10px] px-2 py-0.5 rounded {$cls}\">{$method}</span>";
}
@endphp

@section('content')
<div x-data="{ tab: 'auth' }" class="flex gap-6 min-h-full">

    {{-- ─── Sumário lateral ─── --}}
    <nav class="w-44 shrink-0 sticky top-0 self-start pt-1">
        <p class="text-[10px] font-bold text-slate-400 dark:text-slate-600 uppercase tracking-widest mb-2 px-3">Seções</p>
        @foreach([
            'auth'      => 'Autenticação',
            'customers' => 'Customers',
            'products'  => 'Products',
            'cards'     => 'Cards',
            'chats'     => 'Chats',
            'webhooks'  => 'Webhooks',
            'events'    => 'Eventos',
        ] as $key => $label)
        <button @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}'
                    ? 'bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 border-l-2 border-brand-500'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 border-l-2 border-transparent'"
                class="w-full text-left text-xs font-semibold px-3 py-2 rounded-r-lg transition-all duration-150">
            {{ $label }}
        </button>
        @endforeach
    </nav>

    {{-- ─── Conteúdo ─── --}}
    <div class="flex-1 min-w-0 space-y-4 pb-10">

        {{-- ══════════════ AUTENTICAÇÃO ══════════════ --}}
        <div x-show="tab === 'auth'" x-cloak class="space-y-4 animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100">Autenticação</h2>
            </div>

            {{-- Basic Auth --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-1">Basic Auth — proxy obrigatório</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Toda requisição passa pelo proxy OpenResty. Sem esse header a resposta é <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">401</code>.</p>
                <pre class="bg-slate-900 dark:bg-slate-950 text-slate-100 text-xs font-mono rounded-lg p-4 overflow-x-auto">Authorization: Basic dW1ibGVyOnRlc3RlaG9zcGVkYWdlbQ==
# umbler:testehospedagem</pre>
            </div>

            {{-- API Token --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-1">API Token — endpoints <code class="font-mono text-brand-600 dark:text-brand-400">/api/*</code></h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Aceito por qualquer um dos três métodos:</p>
                <pre class="bg-slate-900 dark:bg-slate-950 text-slate-100 text-xs font-mono rounded-lg p-4 overflow-x-auto"># Header (recomendado)
X-Api-Token: e3RQ6P5nYkxNAsIuoiJLOSZG08qcWKbagyBrCFMUXEtlh7v9

# Query string
GET /api/customers?api_token=e3RQ6P5nYkxNAsIuoiJLOSZG08qcWKbagyBrCFMUXEtlh7v9

# Bearer (quando não conflitar com proxy)
Authorization: Bearer e3RQ6P5nYkxNAsIuoiJLOSZG08qcWKbagyBrCFMUXEtlh7v9</pre>
            </div>

            {{-- Exemplo curl --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">Exemplo completo com curl</h3>
                <pre class="bg-slate-900 dark:bg-slate-950 text-slate-100 text-xs font-mono rounded-lg p-4 overflow-x-auto">curl https://rev-ops-dev.umbler.net/api/customers \
  -u "umbler:testehospedagem" \
  -H "X-Api-Token: e3RQ6P5nYkxNAsIuoiJLOSZG08qcWKbagyBrCFMUXEtlh7v9"</pre>
            </div>

            {{-- Health --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">
                    {!! badge('GET') !!}
                    <code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/health</code>
                    <span class="text-xs text-slate-400 dark:text-slate-500">— sem API token</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Verifica se a API está no ar. Requer apenas o Basic Auth do proxy.</p>
                <pre class="bg-slate-900 dark:bg-slate-950 text-slate-100 text-xs font-mono rounded-lg p-4">{ "status": "ok" }</pre>
            </div>
        </div>

        {{-- ══════════════ CUSTOMERS ══════════════ --}}
        <div x-show="tab === 'customers'" x-cloak class="space-y-4 animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100">Customers</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Clientes e empresas da plataforma.</p>
            </div>

            @php
            $customerFields = [
                ['client_name',               'string',  true,  'Nome do responsável'],
                ['company_name',              'string',  true,  'Nome da empresa'],
                ['email',                     'email',   false, ''],
                ['segment',                   'string',  false, 'Ex: E-commerce, SaaS'],
                ['company_size',              'string',  false, 'Ex: PME, Enterprise'],
                ['tier',                      'string',  false, 'gold | silver | bronze | premium | vip'],
                ['monthly_fee',               'numeric', false, 'MRR em R$'],
                ['plan_name',                 'string',  false, ''],
                ['channel_type',              'string',  false, ''],
                ['instagram_followers_count', 'integer', false, ''],
                ['contracted_at',             'date',    false, ''],
                ['canceled_at',               'date',    false, ''],
            ];
            @endphp

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('GET') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/customers</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Lista paginada de clientes.</p>
                <table class="w-full text-xs mb-3">
                    <thead><tr class="text-left text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-1 pr-4">Parâmetro</th><th class="pb-1 pr-4">Tipo</th><th class="pb-1">Descrição</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                        @foreach([['search','string','Filtra por company_name, client_name ou email'],['tier','string','gold | silver | bronze | premium | vip'],['segment','string','Segmento da empresa'],['per_page','integer','Itens por página (padrão: 20)']] as [$p,$t,$d])
                        <tr><td class="py-1.5 pr-4 font-mono text-brand-600 dark:text-brand-400">{{ $p }}</td><td class="pr-4 text-slate-400">{{ $t }}</td><td class="text-slate-500 dark:text-slate-400">{{ $d }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('GET') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/customers/{id}</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Retorna o cliente com <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">products</code> e <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">cards</code> inclusos.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-3">{!! badge('POST') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/customers</code></div>
                @include('_doc_fields', ['fields' => $customerFields])
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('PUT') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/customers/{id}</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Aceita os mesmos campos do POST (todos opcionais). Dispara o evento <span class="font-mono font-semibold text-slate-600 dark:text-slate-300">customer.updated</span>.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('DELETE') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/customers/{id}</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Soft delete. Retorna <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">204 No Content</code>.</p>
            </div>
        </div>

        {{-- ══════════════ PRODUCTS ══════════════ --}}
        <div x-show="tab === 'products'" x-cloak class="space-y-4 animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100">Products</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Instâncias de Host ou Talk2 contratadas por cliente.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('GET') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/products</code></div>
                <table class="w-full text-xs">
                    <thead><tr class="text-left text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800"><th class="pb-1 pr-4">Parâmetro</th><th class="pb-1 pr-4">Tipo</th><th class="pb-1">Descrição</th></tr></thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                        @foreach([['customer_id','integer','Filtra por cliente'],['product_type','string','Host | Talk2'],['status','string','ativo | cancelado'],['per_page','integer','Padrão: 20']] as [$p,$t,$d])
                        <tr><td class="py-1.5 pr-4 font-mono text-brand-600 dark:text-brand-400">{{ $p }}</td><td class="pr-4 text-slate-400">{{ $t }}</td><td class="text-slate-500 dark:text-slate-400">{{ $d }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('GET') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/products/{id}</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Retorna com <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">customer</code> e <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">changes</code> inclusos.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-3">{!! badge('POST') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/products</code></div>
                @php $productFields = [
                    ['customer_id','integer',true,'ID do cliente'],
                    ['external_id','string',true,'ID externo (Umbler)'],
                    ['product_type','string',true,'Host | Talk2'],
                    ['contract_identifier','string',false,''],
                    ['consumption','numeric',false,''],
                    ['status','string',false,'ativo | cancelado'],
                    ['has_chatbot','boolean',false,'Talk2: Chatbot Ativo'],
                    ['has_ai','boolean',false,'Talk2: Inteligência Artificial'],
                    ['has_implementation','boolean',false,'Talk2: Implementação Assistida'],
                    ['external_created_at','date',false,''],
                ]; @endphp
                @include('_doc_fields', ['fields' => $productFields])
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-3">{!! badge('PUT') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/products/{id}</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Alterações em <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">status</code> ou <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">consumption</code> geram automaticamente um registro em <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">product_changes</code>.</p>
                @php $productUpdateFields = [
                    ['contract_identifier','string',false,''],
                    ['consumption','numeric',false,''],
                    ['status','string',false,'ativo | cancelado'],
                    ['has_chatbot','boolean',false,'Talk2: Chatbot Ativo'],
                    ['has_ai','boolean',false,'Talk2: Inteligência Artificial'],
                    ['has_implementation','boolean',false,'Talk2: Implementação Assistida'],
                    ['external_created_at','date',false,''],
                ]; @endphp
                @include('_doc_fields', ['fields' => $productUpdateFields])
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('DELETE') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/products/{id}</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Soft delete. Retorna <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">204</code>.</p>
            </div>
        </div>

        {{-- ══════════════ CARDS ══════════════ --}}
        <div x-show="tab === 'cards'" x-cloak class="space-y-4 animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100">Cards</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Atendimentos de ouvidoria — unidade central do Kanban.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('GET') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/cards</code></div>
                <table class="w-full text-xs">
                    <thead><tr class="text-left text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800"><th class="pb-1 pr-4">Parâmetro</th><th class="pb-1 pr-4">Tipo</th><th class="pb-1">Descrição</th></tr></thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                        @foreach([['customer_id','integer','Filtra por cliente'],['status','string','Nome da etapa do Kanban'],['ombudsman_agent','string','Agente responsável'],['per_page','integer','Padrão: 20']] as [$p,$t,$d])
                        <tr><td class="py-1.5 pr-4 font-mono text-brand-600 dark:text-brand-400">{{ $p }}</td><td class="pr-4 text-slate-400">{{ $t }}</td><td class="text-slate-500 dark:text-slate-400">{{ $d }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('GET') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/cards/{id}</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Retorna com <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">customer</code>, <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">product</code> e <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">chats</code> inclusos.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-3">{!! badge('POST') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/cards</code><span class="text-xs text-slate-400">Dispara <span class="font-mono font-semibold text-slate-600 dark:text-slate-300">card.created</span></span></div>
                @php $cardCreateFields = [
                    ['customer_id','integer',true,'ID do cliente'],
                    ['started_at','date',true,'Data de abertura'],
                    ['product_id','integer',false,'ID do produto vinculado'],
                    ['status','string',false,'Nome da etapa (padrão: primeira coluna)'],
                    ['ticket_origin','string',false,'Ex: RA, Email, Telefone'],
                    ['ombudsman_agent','string',false,'Agente responsável'],
                    ['contact_reason','string',false,'Motivo resumido'],
                    ['reason_details','string',false,'Detalhamento'],
                    ['responsible_team','string',false,''],
                    ['ra_claim_link','url',false,'Link da reclamação no RA'],
                    ['is_sector_recurrent','boolean',false,''],
                ]; @endphp
                @include('_doc_fields', ['fields' => $cardCreateFields])
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-1">{!! badge('PUT') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/cards/{id}</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Dispara <span class="font-mono font-semibold text-slate-600 dark:text-slate-300">card.finished</span> quando <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">finished_at</code> é preenchido pela primeira vez; caso contrário dispara <span class="font-mono font-semibold text-slate-600 dark:text-slate-300">card.updated</span>.</p>
                @php $cardUpdateFields = [
                    ['status','string',false,'Nome da etapa'],
                    ['finished_at','date',false,'Preencher para encerrar o card'],
                    ['product_id','integer',false,''],
                    ['ticket_origin','string',false,''],
                    ['ombudsman_agent','string',false,''],
                    ['contact_reason','string',false,''],
                    ['reason_details','string',false,''],
                    ['responsible_team','string',false,''],
                    ['applied_solution','string',false,''],
                    ['ra_claim_link','url',false,''],
                    ['rating','integer',false,'1 a 5'],
                    ['first_response_hours','numeric',false,''],
                    ['ra_public_response_hours','numeric',false,''],
                    ['usage_time_post_ombudsman_hours','numeric',false,''],
                    ['is_sector_recurrent','boolean',false,''],
                ]; @endphp
                @include('_doc_fields', ['fields' => $cardUpdateFields])
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('DELETE') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/cards/{id}</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Soft delete. Retorna <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">204</code>.</p>
            </div>
        </div>

        {{-- ══════════════ CHATS ══════════════ --}}
        <div x-show="tab === 'chats'" x-cloak class="space-y-4 animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100">Chats</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Chats externos (Talk2 etc.) vinculados a um card.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('GET') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/cards/{cardId}/chats</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Lista todos os chats do card, ordenados por <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">created_at DESC</code>.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-3">{!! badge('POST') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/cards/{cardId}/chats</code></div>
                @php $chatFields = [
                    ['id','string',true,'ID externo do chat'],
                    ['started_at','date',false,''],
                    ['closed_at','date',false,''],
                ]; @endphp
                @include('_doc_fields', ['fields' => $chatFields])
                <pre class="bg-slate-900 dark:bg-slate-950 text-slate-100 text-xs font-mono rounded-lg p-4 mt-3 overflow-x-auto">{ "id": "chat_abc123", "ombudsman_card_id": 5, "started_at": "2024-03-10T14:00:00Z" }</pre>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('DELETE') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/cards/{cardId}/chats/{id}</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Retorna <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">204</code>.</p>
            </div>
        </div>

        {{-- ══════════════ WEBHOOKS ══════════════ --}}
        <div x-show="tab === 'webhooks'" x-cloak class="space-y-4 animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100">Webhooks</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Assinaturas para receber notificações de eventos.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('GET') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/webhooks</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Lista todas as assinaturas ativas (não deletadas).</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-1">{!! badge('POST') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/webhooks</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">O campo <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">secret</code> (64 chars) é gerado automaticamente e retornado <strong>apenas na criação</strong>. Guarde-o para validar assinaturas.</p>
                @php $webhookFields = [
                    ['name','string',true,'Nome identificador'],
                    ['url','url',true,'Endpoint que receberá os eventos'],
                    ['trigger_type','string',true,'card.created | card.updated | card.finished | customer.updated'],
                    ['description','string',false,''],
                    ['is_active','boolean',false,'Padrão: true'],
                ]; @endphp
                @include('_doc_fields', ['fields' => $webhookFields])
                <pre class="bg-slate-900 dark:bg-slate-950 text-slate-100 text-xs font-mono rounded-lg p-4 mt-3 overflow-x-auto">{
  "id": 1,
  "name": "n8n prod",
  "url": "https://meu-n8n.com/webhook/abc",
  "trigger_type": "card.created",
  "is_active": true,
  "secret": "xK9p...64chars"   // apenas na criação
}</pre>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-3">{!! badge('PUT') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/webhooks/{id}</code></div>
                @php $webhookUpdateFields = [
                    ['name','string',false,''],
                    ['url','url',false,''],
                    ['description','string',false,''],
                    ['is_active','boolean',false,''],
                ]; @endphp
                @include('_doc_fields', ['fields' => $webhookUpdateFields])
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-2">{!! badge('DELETE') !!}<code class="text-xs font-mono text-slate-700 dark:text-slate-300">/api/webhooks/{id}</code></div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Soft delete. Retorna <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">204</code>.</p>
            </div>
        </div>

        {{-- ══════════════ EVENTOS ══════════════ --}}
        <div x-show="tab === 'events'" x-cloak class="space-y-4 animate-fadeIn">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100">Eventos de Webhook</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Payload enviado via POST para a URL cadastrada quando um evento ocorre.</p>
            </div>

            @foreach([
                ['card.created',     'Disparado quando um novo card é criado via API.'],
                ['card.updated',     'Disparado quando um card é atualizado sem encerramento.'],
                ['card.finished',    'Disparado quando finished_at é preenchido pela primeira vez.'],
                ['customer.updated', 'Disparado quando um cliente é atualizado via API.'],
            ] as [$event, $desc])
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono font-bold text-sm text-slate-700 dark:text-slate-200">{{ $event }}</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">{{ $desc }}</p>
                <pre class="bg-slate-900 dark:bg-slate-950 text-slate-100 text-xs font-mono rounded-lg p-4 overflow-x-auto">{
  "event": "{{ $event }}",
  "fired_at": "2024-03-10T14:32:00Z",
  "payload": { ... }
}</pre>
            </div>
            @endforeach

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 p-5">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Retry policy</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Falhas são reenviadas com backoff exponencial:</p>
                <div class="flex flex-wrap gap-2 mb-2">
                    @foreach(['30s', '60s', '120s', '240s', '300s'] as $i => $delay)
                    <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                        {{ $i + 1 }}ª → {{ $delay }}
                    </span>
                    @endforeach
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Máximo de <strong class="text-slate-700 dark:text-slate-300">5 tentativas</strong>. Timeout por requisição: <strong class="text-slate-700 dark:text-slate-300">25s</strong>.</p>
            </div>
        </div>

    </div>{{-- /conteúdo --}}
</div>
@endsection
