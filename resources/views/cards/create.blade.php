@extends('layouts.app')
@section('title', 'Novo Card')
@section('header', 'Novo Atendimento de Ouvidoria')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Actions / Back --}}
    <div class="flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-slate-800/30 px-4 py-3 rounded-xl border border-slate-100 dark:border-slate-800/80 backdrop-blur-sm">
        <div class="flex items-center gap-2 text-xs">
            <a href="{{ route('board') }}" class="font-bold text-brand-600 dark:text-brand-400 flex items-center gap-1">
                ← Voltar ao Board
            </a>
            <span class="text-slate-300 dark:text-slate-700">/</span>
            <span class="text-slate-500 dark:text-slate-400 font-medium">Novo Card de Ouvidoria</span>
        </div>
    </div>

    {{-- Centered form card --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium relative overflow-hidden animate-fadeIn">
        <div class="absolute top-0 left-0 w-full h-[4px] bg-gradient-to-r from-brand-600 to-accent-indigo animate-mesh"></div>
        
        <div class="mb-6">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 tracking-tight">Formulário de Ouvidoria</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 font-medium">Crie um card para monitorar e propor soluções de retenção para uma conta em insatisfação.</p>
        </div>

        <form method="POST" action="{{ route('cards.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Cliente Relacionado <span class="text-rose-500">*</span></label>
                    <select name="customer_id" required class="field-input font-semibold text-slate-800 dark:text-slate-100">
                        <option value="">Selecione a Empresa...</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id', request('customer_id')) == $c->id ? 'selected' : '' }}>
                            {{ $c->company_name }} — {{ $c->client_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Etapa Kanban <span class="text-rose-500">*</span></label>
                    <select name="status" required class="field-input font-semibold">
                        @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ old('status', request('status', $statuses[0])) === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Data de Abertura <span class="text-rose-500">*</span></label>
                    <input type="datetime-local" name="started_at" value="{{ old('started_at', now()->format('Y-m-d\TH:i')) }}" required
                           class="field-input font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Agente Responsável (Ouvidor)</label>
                    <input type="text" name="ombudsman_agent" value="{{ old('ombudsman_agent') }}"
                           class="field-input" placeholder="Nome do Ouvidor">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Origem do Ticket / Canal</label>
                    <input type="text" name="ticket_origin" value="{{ old('ticket_origin') }}"
                           class="field-input" placeholder="Ex: Reclame Aqui, Chat, E-mail">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Time de Origem Responsável</label>
                    <input type="text" name="responsible_team" value="{{ old('responsible_team') }}"
                           class="field-input" placeholder="Ex: Customer Success, Suporte Comercial">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Motivo Geral do Contato</label>
                    <input type="text" name="contact_reason" value="{{ old('contact_reason') }}"
                           class="field-input font-semibold" placeholder="Resumo simples da queixa em uma única linha">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Detalhes Contextuais do Caso</label>
                    <textarea name="reason_details" rows="4"
                              class="field-input resize-none leading-relaxed"
                              placeholder="Forneça detalhes adicionais como número de tickets de suporte, histórico de conversas ou razões específicas..."></textarea>
                </div>
            </div>

            @if($errors->any())
            <div class="text-xs text-rose-600 bg-rose-50 border border-rose-100 rounded-xl px-4 py-3 font-semibold">
                {{ $errors->first() }}
            </div>
            @endif

            <div class="flex gap-3 border-t border-slate-100 dark:border-slate-850 pt-5">
                <button type="submit" class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-glow-brand transition-all hover:-translate-y-0.5">Criar Card de Ouvidoria</button>
                <a href="{{ route('board') }}" class="px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-655 dark:text-slate-400 font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex items-center">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
