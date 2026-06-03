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
    'standard' => 'from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 text-slate-600 dark:text-slate-300 font-bold',
];
$tColorKey = strtolower($customer->tier ?? '');
$tGrad = $tierColors[$tColorKey] ?? $tierColors['standard'];
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header Action Deck --}}
    <div class="flex items-center justify-between bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-premium shrink-0">
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
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- LEFT & CENTER: Client Details / Forms (Span 2) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Edit Form Workspace (Editable by default) --}}
            <form method="POST" action="{{ route('customers.update', $customer) }}"
                  x-data="{ changed: false }"
                  @input="changed = true"
                  @change="changed = true"
                  class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium space-y-6 relative">
                @csrf @method('PATCH')
                
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                        👤 Informações do Cliente
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Altere os campos abaixo para atualizar o perfil corporativo em tempo real.</p>
                </div>

                @include('customers._form', ['customer' => $customer])

                @if($errors->any())
                <div class="text-xs text-rose-600 bg-rose-50 border border-rose-100 rounded-xl px-4 py-3 font-semibold">
                    {{ $errors->first() }}
                </div>
                @endif

                <div x-show="changed" x-cloak class="flex gap-3 border-t border-slate-100 dark:border-slate-800 pt-5 animate-fadeIn">
                    <button type="submit"
                            class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-glow-brand transition-all">
                        Salvar Alterações
                    </button>
                    <button type="button" @click="window.location.reload()"
                            class="px-6 py-3 border border-slate-200 dark:border-slate-750 text-slate-600 dark:text-slate-400 font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                        Descartar
                    </button>
                </div>
            </form>



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
                                          data-confirm-title="Excluir produto"
                                          data-confirm-msg="{{ $product->product_type }} · {{ $product->external_id }} — esta ação é irreversível."
                                          @submit.prevent="$dispatch('open-confirm', { title: $el.dataset.confirmTitle, message: $el.dataset.confirmMsg, form: $el })">
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

                            @if($product->product_type === 'Talk2')
                            <div class="flex flex-wrap gap-1 pt-1.5 border-t border-slate-100 dark:border-slate-850 mt-1.5">
                                <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full flex items-center gap-1
                                    {{ $product->has_chatbot ? 'bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40' : 'bg-slate-50/50 dark:bg-slate-850 text-slate-400 dark:text-slate-600 border border-slate-100/50 dark:border-slate-800/40 border-dashed' }}">
                                    🤖 Chatbot
                                </span>
                                <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full flex items-center gap-1
                                    {{ $product->has_ai ? 'bg-purple-50 dark:bg-purple-950/20 text-purple-700 dark:text-purple-400 border border-purple-100 dark:border-purple-900/40' : 'bg-slate-50/50 dark:bg-slate-850 text-slate-400 dark:text-slate-600 border border-slate-100/50 dark:border-slate-800/40 border-dashed' }}">
                                    ✨ IA
                                </span>
                                <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full flex items-center gap-1
                                    {{ $product->has_implementation ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40' : 'bg-slate-50/50 dark:bg-slate-850 text-slate-400 dark:text-slate-600 border border-slate-100/50 dark:border-slate-800/40 border-dashed' }}">
                                    🚀 Impl.
                                </span>
                            </div>
                            @endif

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

        {{-- RIGHT COLUMN: Ombudsman Activity (Span 1) --}}
        <div class="space-y-6">

            {{-- 1. Ombudsman Summary Stats --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-5 rounded-2xl shadow-premium">
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
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-5 shadow-premium rounded-2xl">
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

            {{-- Danger Zone: Delete Client --}}
            <div class="bg-rose-50/10 dark:bg-rose-950/5 border border-rose-100 dark:border-rose-900/40 rounded-2xl p-5 shadow-sm space-y-3">
                <div>
                    <h4 class="text-xs font-extrabold text-rose-700 dark:text-rose-455 uppercase tracking-wider flex items-center gap-1">
                        ⚠️ Zona de Perigo
                    </h4>
                    <p class="text-[10px] text-rose-600/70 dark:text-rose-500 mt-1 leading-relaxed">Excluir este cliente irá ocultá-lo e remover seus produtos e atendimentos associados de forma definitiva.</p>
                </div>
                <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                      onsubmit="return confirm('Deseja realmente excluir este cliente? Esta ação ocultará o cliente e seus registros.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm">
                        Excluir Cliente
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>
@endsection
