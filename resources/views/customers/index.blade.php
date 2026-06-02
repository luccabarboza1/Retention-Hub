@extends('layouts.app')
@section('title', 'Clientes Ativos')
@section('header', 'Clientes Ativos')

@php
// Mapeamento de Tiers para gradientes estéticos
$tierColors = [
    'gold' => 'from-amber-400 to-amber-600 text-white font-bold',
    'silver' => 'from-slate-300 to-slate-400 text-slate-800 font-bold',
    'bronze' => 'from-orange-300 to-orange-500 text-white font-bold',
    'premium' => 'from-brand-500 to-accent-indigo text-white font-bold shadow-glow-brand',
    'vip' => 'from-rose-500 to-pink-600 text-white font-bold shadow-sm',
    'standard' => 'from-slate-100 to-slate-200 text-slate-600 font-bold',
];
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Executive Summary metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-premium flex items-center gap-4 relative overflow-hidden">
            <div class="absolute top-[-20px] right-[-20px] w-16 h-16 rounded-full bg-brand-500/5 blur-lg pointer-events-none"></div>
            <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center text-lg font-bold shrink-0">
                👥
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total de Clientes</p>
                <p class="text-xl font-extrabold text-slate-800 dark:text-slate-100 font-mono mt-0.5">{{ $customers->total() }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-premium flex items-center gap-4 relative overflow-hidden">
            <div class="absolute top-[-20px] right-[-20px] w-16 h-16 rounded-full bg-amber-500/5 blur-lg pointer-events-none"></div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg font-bold shrink-0">
                ⭐
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Clientes Premium</p>
                <p class="text-xl font-extrabold text-slate-800 dark:text-slate-100 font-mono mt-0.5">
                    {{ $customers->filter(fn($c) => in_array(strtolower($c->tier ?? ''), ['gold', 'premium', 'vip']))->count() }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-premium flex items-center gap-4 relative overflow-hidden">
            <div class="absolute top-[-20px] right-[-20px] w-16 h-16 rounded-full bg-rose-500/5 blur-lg pointer-events-none"></div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-450 flex items-center justify-center text-lg font-bold shrink-0 animate-pulse">
                🚨
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Ouvidorias Ativas</p>
                <p class="text-xl font-extrabold text-slate-800 dark:text-slate-100 font-mono mt-0.5">
                    {{ $customers->sum('open_cards_count') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Control Deck: Search + Create Actions --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-premium shrink-0 flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
        
        <form method="GET" class="flex-1 flex gap-2 max-w-2xl">
            <div class="relative flex-1 group">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 dark:text-slate-500 transition-colors group-focus-within:text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <input name="q" value="{{ $search }}" placeholder="Busque por empresa, responsável ou e-mail..."
                       class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50/50 dark:bg-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800 focus:bg-white dark:focus:bg-slate-800 focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none transition-all dark:text-slate-250 dark:placeholder-slate-500">
            </div>
            <button class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-glow-brand transition-all">
                Buscar
            </button>
            @if($search)
            <a href="{{ route('customers.index') }}" class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex items-center justify-center">
                Limpar
            </a>
            @endif
        </form>

        <a href="{{ route('customers.create') }}"
           class="flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-md hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Novo Cliente
        </a>
    </div>

    {{-- Clean Glassmorphic Table-List --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-premium border border-slate-100 dark:border-slate-800 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-950/30 border-b border-slate-100 dark:border-slate-800 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                    <th class="px-6 py-4">Empresa / Cliente</th>
                    <th class="px-6 py-4">Responsável</th>
                    <th class="px-6 py-4">Plano</th>
                    <th class="px-6 py-4">Categoria (Tier)</th>
                    <th class="px-6 py-4 text-center">Total Cards</th>
                    <th class="px-6 py-4 text-center">Casos Abertos</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                @forelse($customers as $c)
                @php
                    $tColorKey = strtolower($c->tier ?? '');
                    $tGrad = $tierColors[$tColorKey] ?? $tierColors['standard'];
                @endphp
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-500 to-accent-indigo text-white font-extrabold text-sm flex items-center justify-center shadow-sm uppercase shrink-0">
                                {{ strtoupper(substr($c->company_name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <a href="{{ route('customers.show', $c) }}" class="font-bold text-sm text-slate-800 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition-colors block truncate">
                                    {{ $c->company_name }}
                                </a>
                                @if($c->email)
                                <span class="text-[10px] text-slate-400 dark:text-slate-550 font-medium block truncate">{{ $c->email }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ $c->client_name }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/60 border border-slate-200/40 dark:border-slate-700/60 px-2.5 py-1 rounded-lg">
                            {{ $c->plan_name ?? 'Nenhum' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($c->tier)
                        <span class="text-[9px] uppercase tracking-wider px-2.5 py-1 rounded-full bg-gradient-to-r {{ $tGrad }}">
                            {{ $c->tier }}
                        </span>
                        @else
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-xs font-mono font-bold text-slate-500 dark:text-slate-455">{{ $c->cards_count }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($c->open_cards_count > 0)
                        <span class="bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 text-rose-700 dark:text-rose-400 text-xs font-bold font-mono px-2.5 py-1 rounded-full shadow-sm animate-pulse">
                            {{ $c->open_cards_count }} ativo(s)
                        </span>
                        @else
                        <span class="text-xs text-slate-300 dark:text-slate-600 font-medium font-mono">0</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('customers.cards', $c) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-600 hover:text-brand-700 hover:underline">
                            Ver Histórico
                            <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 bg-slate-50/20 dark:bg-slate-900/10">
                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-lg mx-auto mb-3">🔍</div>
                        <p class="text-sm font-semibold">Nenhum cliente cadastrado ou encontrado.</p>
                        <p class="text-xs text-slate-400 mt-1">Experimente alterar os termos de busca ou cadastrar uma nova conta.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Clean Elegant Pagination --}}
    @if($customers->hasPages())
    <div class="mt-4 shrink-0 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-premium flex justify-center">
        {{ $customers->links() }}
    </div>
    @endif
</div>
@endsection
