@extends('layouts.app')
@section('title', 'Configuração de Planos')
@section('header', 'Configurações de Produto')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ adding: false, editingId: null }">

    <div class="flex items-center gap-2 text-xs mb-1 px-1">
        <a href="{{ route('settings.index') }}" class="font-bold text-brand-600 dark:text-brand-400">← Configurações</a>
        <span class="text-slate-300 dark:text-slate-700">/</span>
        <span class="text-slate-400 dark:text-slate-500 font-medium">Planos de Produto</span>
    </div>

    {{-- Header --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium flex items-center justify-between">
        <div>
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Planos & Preços</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Configure os planos disponíveis e seus valores por unidade.</p>
        </div>
        <button @click="adding = !adding" class="btn-primary text-xs px-4 py-2.5">+ Novo Plano</button>
    </div>

    {{-- Form: Novo plano --}}
    <div x-show="adding" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl border border-brand-200 dark:border-brand-900/60 p-6 shadow-premium animate-fadeIn">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">Criar Plano</h3>
        <form method="POST" action="{{ route('settings.products.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Tipo de Produto</label>
                    <div class="select-wrap">
                        <select name="product_type" class="field-input" required>
                            <option value="Talk2">Talk2</option>
                            <option value="Host">Host</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="field-label">Nome do Plano</label>
                    <input type="text" name="plan_name" class="field-input" placeholder="Ex: Enterprise, Professional" required>
                </div>
                <div>
                    <label class="field-label">Preço por Unidade (R$)</label>
                    <input type="number" name="price_per_unit" step="0.01" min="0" class="field-input font-mono" required>
                </div>
                <div>
                    <label class="field-label">Label da Unidade</label>
                    <input type="text" name="unit_label" class="field-input" placeholder="Ex: por atendente, por GB" value="por atendente" required>
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="submit" class="btn-primary text-xs px-5 py-2.5">Salvar</button>
                <button type="button" @click="adding = false"
                        class="text-xs px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-500 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    Cancelar
                </button>
            </div>
        </form>
    </div>

    {{-- Tabela de planos --}}
    @foreach(['Talk2', 'Host'] as $type)
    @php $typePlans = $plans->where('product_type', $type); @endphp
    @if($typePlans->count())
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-premium overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
            @if($type === 'Talk2')
            <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400 border border-violet-200 dark:border-violet-800">Talk2</span>
            @else
            <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-800">Host</span>
            @endif
        </div>
        @foreach($typePlans as $plan)
        <div class="px-5 py-4 border-b border-slate-50 dark:border-slate-800/50 last:border-0" x-data="{ editing: false }">
            <div class="flex items-center justify-between">
                <div x-show="!editing">
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $plan->plan_name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-mono">
                        R$ {{ number_format($plan->price_per_unit, 2, ',', '.') }}
                        <span class="text-slate-400 dark:text-slate-600">{{ $plan->unit_label }}</span>
                    </p>
                </div>
                <div x-show="editing" x-cloak class="flex-1 mr-4">
                    <form method="POST" action="{{ route('settings.products.update', $plan) }}" class="grid grid-cols-3 gap-2 items-end">
                        @csrf @method('PATCH')
                        <div>
                            <label class="field-label">Nome</label>
                            <input type="text" name="plan_name" value="{{ $plan->plan_name }}" class="field-input" required>
                        </div>
                        <div>
                            <label class="field-label">Preço/Unidade</label>
                            <input type="number" name="price_per_unit" step="0.01" min="0" value="{{ $plan->price_per_unit }}" class="field-input font-mono" required>
                        </div>
                        <div>
                            <label class="field-label">Label</label>
                            <input type="text" name="unit_label" value="{{ $plan->unit_label }}" class="field-input" required>
                        </div>
                        <div class="col-span-3 flex gap-2 mt-1">
                            <button type="submit" class="btn-primary text-[10px] px-3 py-1.5">Salvar</button>
                            <button type="button" @click="editing = false"
                                    class="text-[10px] px-3 py-1.5 border border-slate-200 dark:border-slate-700 text-slate-500 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
                <div class="flex gap-1 shrink-0">
                    <button @click="editing = !editing"
                            class="text-[10px] font-bold text-slate-400 hover:text-brand-600 px-2 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        ✏️ Editar
                    </button>
                    <form method="POST" action="{{ route('settings.products.destroy', $plan) }}"
                          data-confirm-title="Remover plano"
                          data-confirm-msg="{{ $plan->plan_name }} ({{ $plan->product_type }}) — esta ação é irreversível."
                          @submit.prevent="$dispatch('open-confirm', { title: $el.dataset.confirmTitle, message: $el.dataset.confirmMsg, form: $el })">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="text-[10px] font-bold text-slate-400 hover:text-rose-600 px-2 py-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @endforeach

    @if($plans->isEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-8 text-center shadow-premium">
        <p class="text-sm text-slate-400 dark:text-slate-500">Nenhum plano configurado ainda.</p>
        <p class="text-xs text-slate-300 dark:text-slate-600 mt-1">Clique em "+ Novo Plano" para começar.</p>
    </div>
    @endif

</div>
@endsection
