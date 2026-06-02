@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- ── Busca Universal ── --}}
    <form action="{{ route('dashboard') }}" method="GET">
        <div class="relative group">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-slate-500 transition-colors group-focus-within:text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
            </svg>
            <input name="q" value="{{ $q }}" autofocus
                   placeholder="Buscar clientes, cards, chats, e-mails relacionados…"
                   autocomplete="off"
                   class="w-full pl-11 pr-4 py-3 text-sm border border-slate-200 dark:border-slate-700 rounded-2xl bg-white dark:bg-slate-900 shadow-premium hover:shadow-premium-hover focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none transition-all dark:text-slate-200 dark:placeholder-slate-500 font-medium">
        </div>
    </form>

    {{-- ── Resultados da busca ── --}}
    @if(strlen($q) >= 2)
    <div class="space-y-4 animate-fadeIn">
        <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
            Resultados para "{{ $q }}"
        </p>

        @if($customers->count())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-premium overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Clientes</span>
                <span class="text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded-full">{{ $customers->count() }}</span>
            </div>
            @foreach($customers as $c)
            <a href="{{ route('customers.show', $c) }}"
               class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors border-b border-slate-50 dark:border-slate-800/50 last:border-0">
                <div>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $c->company_name }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $c->client_name }}{{ $c->email ? ' · ' . $c->email : '' }}</p>
                </div>
                @if($c->tier)
                <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">{{ $c->tier }}</span>
                @endif
            </a>
            @endforeach
        </div>
        @endif

        @if($cards->count())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-premium overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cards de Ouvidoria</span>
                <span class="text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded-full">{{ $cards->count() }}</span>
            </div>
            @foreach($cards as $card)
            <a href="{{ route('cards.show', $card) }}"
               class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors border-b border-slate-50 dark:border-slate-800/50 last:border-0">
                <div>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $card->contact_reason ?? 'Sem título' }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $card->customer?->company_name }} · {{ $card->status }}</p>
                </div>
                <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500">#{{ $card->id }}</span>
            </a>
            @endforeach
        </div>
        @endif

        @if($customers->isEmpty() && $cards->isEmpty() && $chats->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-8 text-center shadow-premium">
            <p class="text-sm text-slate-400 dark:text-slate-500">Nenhum resultado para "{{ $q }}"</p>
        </div>
        @endif
    </div>

    @else

    {{-- ── Métricas principais ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Clientes ativos --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Clientes Ativos</p>
            <p class="text-3xl font-extrabold text-slate-800 dark:text-slate-100 mt-1 font-mono">{{ number_format($activeCustomers) }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">de {{ number_format($totalCustomers) }} total
                @if($newThisMonth > 0)
                · <span class="text-emerald-600 dark:text-emerald-400 font-semibold">+{{ $newThisMonth }} este mês</span>
                @endif
            </p>
        </div>

        {{-- Cards abertos --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Ouvidorias Abertas</p>
            <p class="text-3xl font-extrabold mt-1 font-mono {{ $openCards > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-100' }}">
                {{ number_format($openCards) }}
            </p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">de {{ number_format($totalCards) }} total</p>
        </div>

        {{-- Taxa de retenção --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Taxa de Retenção</p>
            @if($retentionRate !== null)
            <p class="text-3xl font-extrabold mt-1 font-mono {{ $retentionRate >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($retentionRate >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                {{ $retentionRate }}%
            </p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $retainedCards }} retidos · {{ $churnCards }} churn</p>
            @else
            <p class="text-3xl font-extrabold text-slate-300 dark:text-slate-700 mt-1">—</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Sem dados suficientes</p>
            @endif
        </div>

        {{-- MRR --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">MRR Total</p>
            <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                R$ {{ number_format($totalMrr, 0, ',', '.') }}
            </p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">receita recorrente mensal</p>
        </div>
    </div>

    {{-- ── Linha 2: Produtos ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Produtos Ativos</p>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400">Talk2</span>
                        {{ $talk2Products }} instâncias
                    </span>
                    <span class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">{{ $totalAttendants }} atendentes</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400">Host</span>
                        {{ $hostProducts }} instâncias
                    </span>
                </div>
                <div class="border-t border-slate-100 dark:border-slate-800 pt-2 mt-2 flex items-center justify-between">
                    <span class="text-xs text-slate-400 dark:text-slate-500">Total</span>
                    <span class="text-sm font-extrabold text-slate-800 dark:text-slate-100 font-mono">{{ $activeProducts }}</span>
                </div>
            </div>
        </div>

        {{-- ── Atividade recente ── --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Atividade Recente</h3>
                <a href="{{ route('board') }}" class="text-[10px] font-bold text-brand-600 dark:text-brand-400 hover:underline">Ver Board →</a>
            </div>
            @if($recentCards->count())
            <div class="space-y-2">
                @foreach($recentCards as $card)
                <a href="{{ route('cards.show', $card) }}"
                   class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                            {{ $card->contact_reason ?? 'Sem título' }}
                        </p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                            {{ $card->customer?->company_name }} · {{ $card->started_at->format('d/m/Y') }}
                        </p>
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full ml-3 shrink-0
                        {{ in_array($card->status, ['Retido']) ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' :
                           ($card->status === 'Churn' ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' :
                           'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400') }}">
                        {{ $card->status }}
                    </span>
                </a>
                @endforeach
            </div>
            @else
            <p class="text-xs text-slate-400 dark:text-slate-500 text-center py-4">Nenhum card ainda.</p>
            @endif
        </div>
    </div>

    @if($recentChanges->count())
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium">
        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">📊 Últimas Alterações de Produto</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($recentChanges as $change)
            @php
                $icons = ['upgrade'=>['↑','emerald'],'downgrade'=>['↓','amber'],'churn'=>['●','rose'],'reactivation'=>['✓','teal']];
                [$icon, $color] = $icons[$change->change_type] ?? ['·','slate'];
            @endphp
            <div class="flex items-start gap-2.5 text-xs p-2.5 rounded-xl bg-slate-50/50 dark:bg-slate-800/30">
                <span class="w-6 h-6 rounded-full bg-{{ $color }}-50 dark:bg-{{ $color }}-950/20 text-{{ $color }}-600 dark:text-{{ $color }}-400 font-bold text-[11px] flex items-center justify-center shrink-0">{{ $icon }}</span>
                <div class="min-w-0">
                    <p class="font-bold text-slate-700 dark:text-slate-300 truncate">{{ $change->customer?->company_name ?? '—' }}</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                        {{ $change->product?->product_type }} · {{ $change->change_type }}
                        @if($change->delta_consumption)
                        · <span class="font-mono {{ $change->delta_consumption >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $change->delta_consumption >= 0 ? '+' : '' }}R$ {{ number_format(abs($change->delta_consumption), 2, ',', '.') }}
                        </span>
                        @endif
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif {{-- end if not searching --}}
</div>
@endsection
