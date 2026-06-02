@extends('layouts.app')
@section('title', 'Novo Card')
@section('header', 'Novo Atendimento de Ouvidoria')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs px-1">
        <a href="{{ route('board') }}" class="font-bold text-brand-600 dark:text-brand-400">← Board</a>
        <span class="text-slate-300 dark:text-slate-700">/</span>
        <span class="text-slate-500 dark:text-slate-400 font-medium">Novo Card de Ouvidoria</span>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium relative overflow-hidden animate-fadeIn">
        <div class="absolute top-0 left-0 w-full h-[3px] bg-gradient-to-r from-brand-600 to-accent-indigo"></div>

        <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 tracking-tight mb-1">Formulário de Ouvidoria</h3>
        <p class="text-xs text-slate-400 dark:text-slate-500 mb-6">Preencha os dados do atendimento. Campos com <span class="text-rose-500">*</span> são obrigatórios.</p>

        <form method="POST" action="{{ route('cards.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Cliente --}}
                <div class="md:col-span-2">
                    <label class="field-label">Cliente Relacionado <span class="text-rose-500">*</span></label>
                    <div class="select-wrap">
                        <select name="customer_id" required class="field-input text-slate-800 dark:text-slate-100 font-semibold">
                            <option value="">Selecione a empresa…</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id', request('customer_id')) == $c->id ? 'selected' : '' }}>
                                {{ $c->company_name }} — {{ $c->client_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Etapa + Data --}}
                <div>
                    <label class="field-label">Etapa Kanban <span class="text-rose-500">*</span></label>
                    <div class="select-wrap">
                        <select name="status" required class="field-input font-semibold text-slate-800 dark:text-slate-100">
                            @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ old('status', request('status', $statuses[0])) === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="field-label">Data de Abertura <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="started_at" value="{{ old('started_at', now()->format('Y-m-d\TH:i')) }}" required class="field-input">
                </div>

                {{-- Ouvidor — combobox --}}
                <div x-data="combobox(@json($agents->values()), '{{ old('ombudsman_agent') }}')" class="relative" @click.outside="open = false">
                    <label class="field-label">Ouvidor Responsável</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input="filter()" @focus="open = true"
                               @keydown.arrow-down.prevent="nav(1)" @keydown.arrow-up.prevent="nav(-1)"
                               @keydown.enter.prevent="confirm()" @keydown.escape="open = false"
                               placeholder="Selecione ou digite…" class="field-input pr-8">
                        <button type="button" @click="open = !open" tabindex="-1"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <input type="hidden" name="ombudsman_agent" x-model="value">
                    <div x-show="open && filtered.length" x-cloak
                         class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-auto max-h-44">
                        <template x-for="(opt, i) in filtered" :key="opt">
                            <div @click="select(opt)" :class="hi === i ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                                 class="px-4 py-2.5 text-sm cursor-pointer transition-colors" x-text="opt"></div>
                        </template>
                    </div>
                </div>

                {{-- Origem do ticket — combobox --}}
                <div x-data="combobox(@json($origins->values()), '{{ old('ticket_origin') }}')" class="relative" @click.outside="open = false">
                    <label class="field-label">Origem do Ticket</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input="filter()" @focus="open = true"
                               @keydown.arrow-down.prevent="nav(1)" @keydown.arrow-up.prevent="nav(-1)"
                               @keydown.enter.prevent="confirm()" @keydown.escape="open = false"
                               placeholder="Ex: RA, WhatsApp, Email…" class="field-input pr-8">
                        <button type="button" @click="open = !open" tabindex="-1"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <input type="hidden" name="ticket_origin" x-model="value">
                    <div x-show="open && filtered.length" x-cloak
                         class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-auto max-h-44">
                        <template x-for="(opt, i) in filtered" :key="opt">
                            <div @click="select(opt)" :class="hi === i ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                                 class="px-4 py-2.5 text-sm cursor-pointer transition-colors" x-text="opt"></div>
                        </template>
                    </div>
                </div>

                {{-- Time responsável — combobox --}}
                <div x-data="combobox(@json($teams->values()), '{{ old('responsible_team') }}')" class="relative md:col-span-2" @click.outside="open = false">
                    <label class="field-label">Time Responsável</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input="filter()" @focus="open = true"
                               @keydown.arrow-down.prevent="nav(1)" @keydown.arrow-up.prevent="nav(-1)"
                               @keydown.enter.prevent="confirm()" @keydown.escape="open = false"
                               placeholder="Ex: CS, Suporte, Comercial…" class="field-input pr-8">
                        <button type="button" @click="open = !open" tabindex="-1"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <input type="hidden" name="responsible_team" x-model="value">
                    <div x-show="open && filtered.length" x-cloak
                         class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-auto max-h-44">
                        <template x-for="(opt, i) in filtered" :key="opt">
                            <div @click="select(opt)" :class="hi === i ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                                 class="px-4 py-2.5 text-sm cursor-pointer transition-colors" x-text="opt"></div>
                        </template>
                    </div>
                </div>

                {{-- Título --}}
                <div class="md:col-span-2">
                    <label class="field-label">Título</label>
                    <input type="text" name="contact_reason" value="{{ old('contact_reason') }}"
                           class="field-input font-semibold text-slate-800 dark:text-slate-100" placeholder="Resumo da situação em uma linha">
                </div>

                {{-- Descrição --}}
                <div class="md:col-span-2">
                    <label class="field-label">Descrição</label>
                    <textarea name="reason_details" rows="4" class="field-input resize-none leading-relaxed"
                              placeholder="Contexto detalhado, histórico, tickets de suporte relacionados…">{{ old('reason_details') }}</textarea>
                </div>

            </div>

            @if($errors->any())
            <div class="text-xs text-rose-600 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 rounded-xl px-4 py-3 font-semibold">
                {{ $errors->first() }}
            </div>
            @endif

            <div class="flex gap-3 border-t border-slate-100 dark:border-slate-800 pt-5">
                <button type="submit" class="btn-primary">Criar Card de Ouvidoria</button>
                <a href="{{ route('board') }}" class="px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex items-center">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
