@extends('layouts.app')
@section('title', $customer->company_name)
@section('header', 'Perfil Corporativo')

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
$tColorKey = strtolower($customer->tier ?? '');
$tGrad = $tierColors[$tColorKey] ?? $tierColors['standard'];
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ editing: false }">

    {{-- Header Action Deck --}}
    <div class="flex items-center justify-between bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-850 rounded-2xl p-5 shadow-premium shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.index') }}" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 flex items-center justify-center transition-all font-bold" title="Voltar">
                ←
            </a>
            <div>
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Cliente Registrado</span>
                <h2 class="text-lg font-extrabold text-slate-800 dark:text-slate-100 tracking-tight leading-none mt-0.5">{{ $customer->company_name }}</h2>
            </div>
        </div>
        
        <div class="flex gap-2">
            <a href="{{ route('cards.create', ['customer_id' => $customer->id]) }}"
               class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-glow-brand transition-all flex items-center gap-1.5 hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Novo Card
            </a>
            <button @click="editing = !editing"
                    class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-750 text-xs font-bold uppercase tracking-wider rounded-xl transition-all flex items-center"
                    x-text="editing ? 'Cancelar Edição' : 'Editar Cliente'">
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- LEFT & CENTER: Client Details / Forms (Span 2) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. View Mode Profile Deck --}}
            <div x-show="!editing" class="space-y-6 animate-fadeIn">
                
                {{-- Identificação & Contrato --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium space-y-6">
                    
                    {{-- Section: Identificação --}}
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-100 dark:border-slate-800 pb-2 flex items-center gap-2">
                            👤 Informações de Identificação
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Razão Social / Empresa</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">{{ $customer->company_name }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Responsável Principal</p>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300 mt-1">{{ $customer->client_name }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">E-mail Corporativo</p>
                                <p class="text-sm font-semibold text-brand-600 dark:text-brand-400 mt-1 underline">{{ $customer->email ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Segmento de Mercado</p>
                                <p class="text-sm font-bold text-slate-600 dark:text-slate-400 mt-1 bg-slate-50 dark:bg-slate-800 border border-slate-200/40 dark:border-slate-700/60 px-2.5 py-0.5 rounded-lg inline-block">
                                    {{ $customer->segment ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Plano e Contrato --}}
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-100 dark:border-slate-800 pb-2 flex items-center gap-2">
                            📄 Detalhes de Contrato & MRR
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Plano</p>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-350 mt-1">{{ $customer->plan_name ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Classificação (Tier)</p>
                                <div class="mt-1">
                                    @if($customer->tier)
                                    <span class="text-[9px] uppercase tracking-wider px-2.5 py-1 rounded-full bg-gradient-to-r {{ $tGrad }}">
                                        {{ $customer->tier }}
                                    </span>
                                    @else
                                    <p class="text-sm font-bold text-slate-400 dark:text-slate-500 mt-1">—</p>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Faturamento Recorrente (MRR)</p>
                                <p class="text-base font-extrabold text-emerald-600 dark:text-emerald-400 font-mono mt-0.5">
                                    {{ $customer->monthly_fee ? 'R$ ' . number_format($customer->monthly_fee, 2, ',', '.') : '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Canal de Aquisição</p>
                                <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mt-1">{{ $customer->channel_type ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Data de Contratação</p>
                                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 mt-1">
                                    📅 {{ $customer->contracted_at?->format('d/m/Y') ?? '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Status do Contrato</p>
                                @if($customer->canceled_at)
                                <span class="inline-block mt-1 bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-455 border border-rose-100 dark:border-rose-900/40 text-[10px] font-bold px-2.5 py-0.5 rounded-full">
                                    Cancelado em {{ $customer->canceled_at->format('d/m/Y') }}
                                </span>
                                @else
                                <span class="inline-block mt-1 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40 text-[10px] font-bold px-2.5 py-0.5 rounded-full">
                                    Ativo
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Section: Infos adicionais --}}
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-100 dark:border-slate-800 pb-2 flex items-center gap-2">
                            🏢 Dados Corporativos Complementares
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Porte da Empresa</p>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-350 mt-1">{{ $customer->company_size ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Seguidores no Instagram</p>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-350 mt-1 font-mono">
                                    {{ $customer->instagram_followers_count ? number_format($customer->instagram_followers_count, 0, ',', '.') : '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Products Cards Deck --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium">
                    <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4 border-b border-slate-100 dark:border-slate-800 pb-2">
                        🔌 Soluções & Produtos Contratados
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Product: Chatbot --}}
                        <div class="border rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between min-h-[110px]
                            {{ $customer->has_chatbot 
                                ? 'bg-gradient-to-br from-blue-50/50 to-white dark:from-blue-950/10 dark:to-slate-900 border-blue-200/60 dark:border-blue-900/40 shadow-sm' 
                                : 'bg-slate-50/30 dark:bg-slate-950/20 border-slate-200 dark:border-slate-800 border-dashed text-slate-400 dark:text-slate-600' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-lg">🤖</span>
                                @if($customer->has_chatbot)
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.6)] animate-pulse"></span>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold tracking-tight text-slate-700 dark:text-slate-300">Chatbot Inteligente</h4>
                                <p class="text-[9px] uppercase tracking-wider font-bold mt-1
                                    {{ $customer->has_chatbot ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}">
                                    {{ $customer->has_chatbot ? 'Contratado' : 'Não Contratado' }}
                                </p>
                            </div>
                        </div>

                        {{-- Product: IA --}}
                        <div class="border rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between min-h-[110px]
                            {{ $customer->has_ai 
                                ? 'bg-gradient-to-br from-purple-50/50 to-white dark:from-purple-950/10 dark:to-slate-900 border-purple-200/60 dark:border-purple-900/40 shadow-sm' 
                                : 'bg-slate-50/30 dark:bg-slate-950/20 border-slate-200 dark:border-slate-800 border-dashed text-slate-400 dark:text-slate-600' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-lg">✨</span>
                                @if($customer->has_ai)
                                <span class="w-2.5 h-2.5 rounded-full bg-purple-500 shadow-[0_0_8px_rgba(168,85,247,0.6)] animate-pulse"></span>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold tracking-tight text-slate-700 dark:text-slate-300">Inteligência Artificial</h4>
                                <p class="text-[9px] uppercase tracking-wider font-bold mt-1
                                    {{ $customer->has_ai ? 'text-purple-600 dark:text-purple-400' : 'text-slate-400' }}">
                                    {{ $customer->has_ai ? 'Contratado' : 'Não Contratado' }}
                                </p>
                            </div>
                        </div>

                        {{-- Product: Implementation --}}
                        <div class="border rounded-2xl p-4 transition-all duration-300 flex flex-col justify-between min-h-[110px]
                            {{ $customer->has_implementation 
                                ? 'bg-gradient-to-br from-emerald-50/50 to-white dark:from-emerald-950/10 dark:to-slate-900 border-emerald-200/60 dark:border-emerald-900/40 shadow-sm' 
                                : 'bg-slate-50/30 dark:bg-slate-950/20 border-slate-200 dark:border-slate-800 border-dashed text-slate-400 dark:text-slate-600' }}">
                            <div class="flex items-center justify-between">
                                <span class="text-lg">🚀</span>
                                @if($customer->has_implementation)
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)] animate-pulse"></span>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold tracking-tight text-slate-700 dark:text-slate-300">Implementação Assistida</h4>
                                <p class="text-[9px] uppercase tracking-wider font-bold mt-1
                                    {{ $customer->has_implementation ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                    {{ $customer->has_implementation ? 'Contratado' : 'Não Contratado' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Products Instances Deck --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium"
                     x-data="{ addingProduct: false, editingProduct: null }">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                            📦 Instâncias de Produto (Host / Talk2)
                        </h3>
                        <button @click="addingProduct = !addingProduct"
                                class="text-[10px] font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 hover:bg-brand-100/50 px-3 py-1.5 rounded-lg transition-all">
                            + Adicionar Produto
                        </button>
                    </div>

                    {{-- Form: Adicionar Produto --}}
                    <div x-show="addingProduct" x-cloak class="mb-5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <h4 class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-3">Novo Produto</h4>
                        @include('customers._product_form', [
                            'action'       => route('products.store', $customer),
                            'method'       => 'POST',
                            'planConfigs'  => $planConfigs,
                            'cancelAlpine' => 'addingProduct = false',
                        ])
                    </div>

                    {{-- Grid de produtos --}}
                    @if($customer->products->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($customer->products as $product)
                        <div class="border rounded-xl p-4 bg-white dark:bg-slate-900 border-slate-100 dark:border-slate-800 space-y-2"
                             x-data="{ editing: false }">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @if($product->product_type === 'Host')
                                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-800">
                                        Host
                                    </span>
                                    @else
                                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400 border border-violet-200 dark:border-violet-800">
                                        Talk2
                                    </span>
                                    @endif
                                    @if($product->status === 'ativo')
                                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40">
                                        Ativo
                                    </span>
                                    @else
                                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40">
                                        Cancelado
                                    </span>
                                    @endif
                                </div>
                                <div class="flex gap-1">
                                    <button @click="editing = !editing"
                                            class="text-[10px] font-bold text-slate-500 hover:text-brand-600 px-2 py-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                        ✏️
                                    </button>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}"
                                          onsubmit="return confirm('Excluir este produto?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="text-[10px] font-bold text-slate-400 hover:text-rose-600 px-2 py-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono">{{ $product->external_id }}</p>
                                @if($product->contract_identifier)
                                <p class="text-[10px] text-slate-400 dark:text-slate-500">Contrato: {{ $product->contract_identifier }}</p>
                                @endif
                                @if($product->consumption !== null)
                                <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                    R$ {{ number_format($product->consumption, 2, ',', '.') }}
                                </p>
                                @endif
                            </div>

                            {{-- Form inline de edição --}}
                            <div x-show="editing" x-cloak class="pt-2 border-t border-slate-100 dark:border-slate-800">
                                @include('customers._product_form', [
                                    'action'       => route('products.update', $product),
                                    'method'       => 'PATCH',
                                    'product'      => $product,
                                    'planConfigs'  => $planConfigs,
                                    'cancelAlpine' => 'editing = false',
                                ])
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-slate-400 dark:text-slate-500 italic text-center py-4">Nenhum produto registrado</p>
                    @endif
                </div>

            </div>

            {{-- 2. Edit Mode Workspace --}}
            <div x-show="editing" x-cloak class="bg-white dark:bg-slate-900 border border-brand-200 dark:border-brand-900/80 p-6 shadow-premium animate-fadeIn relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-[4px] bg-brand-500"></div>
                
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 mb-5 tracking-tight flex items-center gap-2">
                    🛠️ Painel de Edição de Conta
                </h3>
                
                <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-6">
                    @csrf @method('PATCH')
                    @include('customers._form', ['customer' => $customer])

                    @if($errors->any())
                    <div class="text-xs text-rose-600 bg-rose-50 border border-rose-100 rounded-xl px-4 py-3 font-semibold">
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <div class="flex gap-3 border-t border-slate-100 dark:border-slate-800 pt-5">
                        <button type="submit"
                                class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-glow-brand transition-all">
                            Salvar Alterações
                        </button>
                        <button type="button" @click="editing = false"
                                class="px-6 py-3 border border-slate-200 dark:border-slate-750 text-slate-600 dark:text-slate-400 font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>

        </div>

        {{-- RIGHT COLUMN: Ombudsman Activity (Span 1) --}}
        <div class="space-y-6">

            {{-- 1. Ombudsman Summary Stats --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-5 shadow-premium">
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4 flex items-center justify-between">
                    🚨 Ouvidoria Ativa
                </h3>
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between text-xs font-medium border-b border-slate-50 dark:border-slate-800 pb-2">
                        <span class="text-slate-500">Total de Históricos</span>
                        <span class="font-bold text-slate-800 dark:text-slate-250 font-mono">{{ $customer->cards_count }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs font-medium">
                        <span class="text-slate-500">Tickets Abertos</span>
                        @if($customer->open_cards_count > 0)
                        <span class="bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 text-rose-700 dark:text-rose-400 text-[10px] font-bold font-mono px-2.5 py-0.5 rounded-full shadow-sm animate-pulse">
                            {{ $customer->open_cards_count }} caso(s)
                        </span>
                        @else
                        <span class="text-slate-300 dark:text-slate-650 font-mono">0</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('customers.cards', $customer) }}"
                   class="mt-4 block text-center text-xs font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 hover:bg-brand-100/50 dark:hover:bg-brand-900/50 py-2.5 rounded-xl transition-all">
                    Acessar Linha do Tempo →
                </a>
            </div>

            {{-- 2. Recent Cards Tracker --}}
            @if($recentCards->count())
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-5 shadow-premium">
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">⏳ Últimas Ocorrências</h3>
                
                <div class="space-y-3 relative before:absolute before:left-3 before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-100 dark:before:bg-slate-800">
                    @foreach($recentCards as $card)
                    <div class="relative pl-6 animate-fadeIn">
                        <div class="absolute left-2.5 top-2 w-[5px] h-[5px] rounded-full bg-brand-500 shadow-glow-brand ring-4 ring-white dark:ring-slate-900 shrink-0"></div>
                        <a href="{{ route('cards.show', $card) }}"
                           class="group block text-xs hover:bg-slate-50/50 dark:hover:bg-slate-800/40 p-2 rounded-lg border border-transparent hover:border-slate-100 dark:hover:border-slate-800 transition-all">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-800 dark:text-slate-250 truncate group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                                    {{ $card->contact_reason ?? 'Sem motivo registrado' }}
                                </span>
                                <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 shrink-0">#{{ $card->id }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-[10px] text-slate-400 dark:text-slate-500 font-semibold uppercase mt-1">
                                <span class="text-slate-550">{{ $card->status }}</span>
                                <span>·</span>
                                <span>{{ $card->started_at->format('d/m/Y') }}</span>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 3. Product Changes History --}}
            @if($recentChanges->count() > 0)
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-5 shadow-premium rounded-2xl">
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">📊 Histórico de Produtos</h3>
                <div class="space-y-2.5">
                    @foreach($recentChanges as $change)
                    @php
                        $changeIcons = [
                            'upgrade'      => ['icon' => '↑', 'color' => 'text-emerald-600 dark:text-emerald-400', 'bg' => 'bg-emerald-50 dark:bg-emerald-950/20'],
                            'downgrade'    => ['icon' => '↓', 'color' => 'text-amber-600 dark:text-amber-400',    'bg' => 'bg-amber-50 dark:bg-amber-950/20'],
                            'churn'        => ['icon' => '●', 'color' => 'text-rose-600 dark:text-rose-400',       'bg' => 'bg-rose-50 dark:bg-rose-950/20'],
                            'reactivation' => ['icon' => '✓', 'color' => 'text-teal-600 dark:text-teal-400',      'bg' => 'bg-teal-50 dark:bg-teal-950/20'],
                        ];
                        $ci = $changeIcons[$change->change_type] ?? ['icon' => '·', 'color' => 'text-slate-400', 'bg' => 'bg-slate-50 dark:bg-slate-800'];
                    @endphp
                    <div class="flex items-start gap-2.5 text-xs">
                        <span class="w-6 h-6 rounded-full {{ $ci['bg'] }} {{ $ci['color'] }} font-bold text-[11px] flex items-center justify-center shrink-0 mt-0.5">
                            {{ $ci['icon'] }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-700 dark:text-slate-300 truncate">
                                {{ $change->product->product_type ?? '—' }} · {{ $change->product->external_id ?? '—' }}
                            </p>
                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                @if($change->delta_consumption !== null)
                                <span class="font-mono text-[10px] {{ $change->delta_consumption >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} font-bold">
                                    {{ $change->delta_consumption >= 0 ? '+' : '' }}R$ {{ number_format($change->delta_consumption, 2, ',', '.') }}
                                </span>
                                <span class="text-slate-300 dark:text-slate-600">·</span>
                                @endif
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-semibold">{{ $change->change_type }}</span>
                                <span class="text-slate-300 dark:text-slate-600">·</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $change->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 4. Profile Registry Metadata --}}
            <div class="bg-slate-950 text-slate-500 rounded-2xl p-5 border border-slate-900 shadow-premium text-[11px] space-y-1.5 dark:bg-slate-950 dark:text-slate-650 dark:border-slate-900">
                <p>Criado em: {{ $customer->created_at->format('d/m/Y H:i') }}</p>
                @if($customer->updated_at != $customer->created_at)
                <p>Atualizado em: {{ $customer->updated_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>

        </div>

    </div>
</div>
@endsection
