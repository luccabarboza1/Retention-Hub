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

                {{-- Etapa Kanban --}}
                @include('cards._managed_combobox', [
                    'name' => 'status',
                    'label' => 'Etapa Kanban',
                    'placeholder' => 'Selecione…',
                    'options' => $statuses,
                    'old' => old('status', request('status', $statuses[0])),
                    'col' => '',
                    'freeText' => false
                ])

                <div>
                    <label class="field-label">Data de Abertura <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="started_at" value="{{ old('started_at', now()->format('Y-m-d\TH:i')) }}" required class="field-input">
                </div>

                {{-- Ouvidor — managed combobox --}}
                @include('cards._managed_combobox', ['type' => 'ombudsman_agents', 'name' => 'ombudsman_agent', 'label' => 'Ouvidor Responsável', 'placeholder' => 'Selecione…', 'options' => $agents, 'old' => old('ombudsman_agent'), 'col' => '', 'freeText' => false])

                {{-- Origem do ticket — managed combobox --}}
                @include('cards._managed_combobox', ['type' => 'ticket_origins', 'name' => 'ticket_origin', 'label' => 'Origem do Ticket', 'placeholder' => 'Selecione…', 'options' => $origins, 'old' => old('ticket_origin'), 'col' => '', 'freeText' => false])

                {{-- Time responsável — managed combobox --}}
                @include('cards._managed_combobox', ['type' => 'responsible_teams', 'name' => 'responsible_team', 'label' => 'Time Responsável', 'placeholder' => 'Selecione…', 'options' => $teams, 'old' => old('responsible_team'), 'col' => 'md:col-span-2', 'freeText' => false])

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
