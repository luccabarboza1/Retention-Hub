@extends('layouts.app')
@section('title', 'Etiquetas')
@section('header', 'Configurações')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ tab: 'customer', newName: '', adding: false }">

    <div class="flex items-center gap-2 text-xs mb-1 px-1">
        <a href="{{ route('settings.index') }}" class="font-bold text-brand-600 dark:text-brand-400">← Configurações</a>
        <span class="text-slate-300 dark:text-slate-700">/</span>
        <span class="text-slate-400 dark:text-slate-500 font-medium">Etiquetas</span>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50 rounded-xl px-4 py-3 text-xs font-semibold text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
        <span>✓</span> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800/50 rounded-xl px-4 py-3 text-xs font-semibold text-rose-700 dark:text-rose-400 flex items-center gap-2">
        <span>✕</span> {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium flex items-center justify-between">
        <div>
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Etiquetas</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Gerencie as etiquetas usadas em clientes e cards.</p>
        </div>
        <button @click="adding = !adding" class="btn-primary text-xs px-4 py-2.5">+ Nova Etiqueta</button>
    </div>

    {{-- Form: Nova etiqueta --}}
    <div x-show="adding" x-cloak
         class="bg-white dark:bg-slate-900 rounded-2xl border border-brand-200 dark:border-brand-900/60 p-5 shadow-premium animate-fadeIn">
        <form method="POST" action="{{ route('settings.tags.store') }}" class="flex gap-3 items-end">
            @csrf
            <div class="flex-1">
                <label class="field-label">Nome da etiqueta</label>
                <input type="text" name="name" x-model="newName" required
                       placeholder="Ex: VIP, Urgente, Retenção…"
                       class="field-input">
            </div>
            <div class="w-44">
                <label class="field-label">Tipo</label>
                <div class="select-wrap">
                    <select name="type" class="field-input font-semibold">
                        <option value="customer">Cliente</option>
                        <option value="card">Card</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 pb-0.5">
                <button type="submit" class="btn-primary text-xs px-4 py-2.5">Criar</button>
                <button type="button" @click="adding = false; newName = ''"
                        class="text-xs px-4 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-500 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    Cancelar
                </button>
            </div>
        </form>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-2">
        <button @click="tab = 'customer'"
                :class="tab === 'customer' ? 'bg-brand-600 text-white shadow-glow-brand' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'"
                class="text-xs font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-1.5">
            👥 Clientes
            <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded-full"
                  :class="tab === 'customer' ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-800'">
                {{ $customerTags->count() }}
            </span>
        </button>
        <button @click="tab = 'card'"
                :class="tab === 'card' ? 'bg-brand-600 text-white shadow-glow-brand' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'"
                class="text-xs font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-1.5">
            📋 Cards
            <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded-full"
                  :class="tab === 'card' ? 'bg-white/20' : 'bg-slate-100 dark:bg-slate-800'">
                {{ $cardTags->count() }}
            </span>
        </button>
    </div>

    {{-- Lista: Clientes --}}
    <div x-show="tab === 'customer'" x-cloak class="animate-fadeIn">
        @if($customerTags->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-8 text-center shadow-premium">
            <p class="text-sm text-slate-400 dark:text-slate-500">Nenhuma etiqueta de cliente criada.</p>
        </div>
        @else
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-premium overflow-hidden">
            @foreach($customerTags as $tag)
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-50 dark:border-slate-800/50 last:border-0 group">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $tag->name }}</span>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                        {{ $tag->customers_count }} {{ $tag->customers_count === 1 ? 'cliente' : 'clientes' }}
                    </span>
                </div>
                <form method="POST" action="{{ route('settings.tags.destroy', $tag) }}"
                      data-confirm-title="Remover etiqueta"
                      data-confirm-msg="{{ $tag->name }} — será removida de {{ $tag->customers_count }} {{ $tag->customers_count === 1 ? 'cliente' : 'clientes' }}."
                      @submit.prevent="$dispatch('open-confirm', { title: $el.dataset.confirmTitle, message: $el.dataset.confirmMsg, form: $el })">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-rose-500 transition-all text-sm px-2 py-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/20">
                        ×
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Lista: Cards --}}
    <div x-show="tab === 'card'" x-cloak class="animate-fadeIn">
        @if($cardTags->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-8 text-center shadow-premium">
            <p class="text-sm text-slate-400 dark:text-slate-500">Nenhuma etiqueta de card criada.</p>
        </div>
        @else
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-premium overflow-hidden">
            @foreach($cardTags as $tag)
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-50 dark:border-slate-800/50 last:border-0 group">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $tag->name }}</span>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                        {{ $tag->cards_count }} {{ $tag->cards_count === 1 ? 'card' : 'cards' }}
                    </span>
                </div>
                <form method="POST" action="{{ route('settings.tags.destroy', $tag) }}"
                      data-confirm-title="Remover etiqueta"
                      data-confirm-msg="{{ $tag->name }} — será removida de {{ $tag->cards_count }} {{ $tag->cards_count === 1 ? 'card' : 'cards' }}."
                      @submit.prevent="$dispatch('open-confirm', { title: $el.dataset.confirmTitle, message: $el.dataset.confirmMsg, form: $el })">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-rose-500 transition-all text-sm px-2 py-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/20">
                        ×
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
