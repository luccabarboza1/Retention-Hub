@extends('layouts.app')
@section('title', $q ? "Busca: $q" : 'Central de Busca')
@section('header', 'Resultados da Busca')

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
<div class="max-w-4xl mx-auto space-y-6">

    @if(strlen($q) < 2)
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-16 text-center shadow-premium animate-fadeIn">
        <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600 flex items-center justify-center text-3xl mx-auto mb-4 border border-slate-100 dark:border-slate-850">
            🔍
        </div>
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Central de Pesquisa Inteligente</h3>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">Digite pelo menos 2 caracteres na barra de busca superior para localizar registros em tempo real.</p>
    </div>
    @else

    @php 
        $total = $customers->count() + $cards->count() + $chats->count(); 
    @endphp

    {{-- Search Toolbar Info --}}
    <div class="flex items-center justify-between shrink-0 bg-slate-50/60 dark:bg-slate-900/60 p-4 rounded-xl border border-slate-100/80 dark:border-slate-800/80 backdrop-blur-sm">
        <div>
            <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Resultado da Pesquisa</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                Localizados <span class="font-bold text-brand-600 dark:text-brand-450 font-mono text-sm px-1.5 py-0.5 rounded-md bg-brand-50 dark:bg-brand-900/30">{{ $total }}</span> termo(s) para a consulta "<span class="font-bold text-brand-755 dark:text-brand-400 italic">{{ $q }}</span>"
            </p>
        </div>
    </div>

    @if($total === 0)
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-16 text-center shadow-premium animate-fadeIn">
        <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600 flex items-center justify-center text-2xl mx-auto mb-3">
            📭
        </div>
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Nenhum resultado encontrado</h3>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-xs mx-auto">Não encontramos correspondência para o termo informado. Verifique a ortografia ou tente palavras-chave mais genéricas.</p>
    </div>
    @endif

    {{-- 1. Clientes Results --}}
    @if($customers->count())
    <div class="space-y-3 animate-fadeIn">
        <h3 class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-1.5 px-1">
            👥 Clientes Encontrados ({{ $customers->count() }})
        </h3>
        <div class="grid grid-cols-1 gap-3">
            @foreach($customers as $c)
            @php
                $tColorKey = strtolower($c->tier ?? '');
                $tGrad = $tierColors[$tColorKey] ?? $tierColors['standard'];
            @endphp
            <a href="{{ route('customers.show', $c) }}"
               class="flex items-center justify-between bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-4 shadow-sm hover:shadow-premium-hover hover:border-brand-300 dark:hover:border-brand-700 transition-all duration-300 group">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-slate-900 dark:bg-slate-800 text-white font-extrabold text-xs flex items-center justify-center uppercase shrink-0">
                        {{ strtoupper(substr($c->company_name, 0, 1)) }}
                    </span>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ $c->company_name }}</h4>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold uppercase mt-0.5">Resp: {{ $c->client_name }}@if($c->email) · {{ $c->email }}@endif</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @if($c->tier)
                    <span class="text-[8px] uppercase tracking-wider px-2 py-0.5 rounded-full bg-gradient-to-r {{ $tGrad }}">
                        {{ $c->tier }}
                    </span>
                    @endif
                    @if($c->plan_name)
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-lg border border-slate-200/40 dark:border-slate-700/65">
                        {{ $c->plan_name }}
                    </span>
                    @endif
                    <span class="text-slate-300 dark:text-slate-655 group-hover:text-brand-500 dark:group-hover:text-brand-400 transition-colors ml-1">→</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 2. Atendimentos Results (Cards) --}}
    @if($cards->count())
    <div class="space-y-3 animate-fadeIn mt-6">
        <h3 class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-1.5 px-1">
            📥 Cards de Ouvidoria ({{ $cards->count() }})
        </h3>
        <div class="grid grid-cols-1 gap-3">
            @foreach($cards as $card)
            <a href="{{ route('cards.show', $card) }}"
               class="flex items-center justify-between bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-4 shadow-sm hover:shadow-premium-hover hover:border-brand-300 dark:hover:border-brand-700 transition-all duration-300 group">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[9px] font-bold font-mono text-slate-400 dark:text-slate-505 uppercase">#{{ $card->id }}</span>
                        <span class="text-[9px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold px-2 py-0.5 rounded-full uppercase">
                            {{ $card->status }}
                        </span>
                        @if($card->customer)
                        <span class="text-[11px] font-bold text-slate-505 dark:text-slate-400 truncate max-w-[120px]">{{ $card->customer->company_name }}</span>
                        @endif
                    </div>
                    <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate leading-snug group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                        {{ $card->contact_reason ?: 'Sem motivo geral cadastrado' }}
                    </h4>
                    @if($card->ombudsman_agent)
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mt-1">Ouvidor: {{ $card->ombudsman_agent }}</p>
                    @endif
                </div>
                <div class="shrink-0 ml-3 text-right">
                    <p class="text-[10px] font-semibold text-slate-400 dark:text-slate-500">{{ $card->started_at->format('d/m/Y') }}</p>
                    <span class="text-slate-300 dark:text-slate-655 group-hover:text-brand-500 dark:group-hover:text-brand-400 transition-colors block mt-1">→</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 3. Chats Results --}}
    @if($chats->count())
    <div class="space-y-3 animate-fadeIn mt-6">
        <h3 class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center gap-1.5 px-1">
            💬 Histórico de Chats Relacionados ({{ $chats->count() }})
        </h3>
        <div class="grid grid-cols-1 gap-3">
            @foreach($chats as $chat)
            <a href="{{ $chat->card ? route('cards.show', $chat->card) : '#' }}"
               class="flex items-center justify-between bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-4 shadow-sm hover:shadow-premium-hover hover:border-brand-300 dark:hover:border-brand-700 transition-all duration-300 group">
                <div>
                    <h4 class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 leading-snug group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                        ID: {{ $chat->id }}
                    </h4>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold mt-1">
                        Abertura: {{ $chat->started_at?->format('d/m/Y H:i') }}
                        @if($chat->card?->customer) · Empresa: {{ $chat->card->customer->company_name }} @endif
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    @if($chat->closed_at)
                    <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 px-2.5 py-0.5 rounded-full">Encerrado</span>
                    @else
                    <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 px-2.5 py-0.5 rounded-full animate-pulse">Ativo</span>
                    @endif
                    <span class="text-slate-300 dark:text-slate-655 group-hover:text-brand-500 dark:group-hover:text-brand-400 transition-colors">→</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @endif
</div>
@endsection
