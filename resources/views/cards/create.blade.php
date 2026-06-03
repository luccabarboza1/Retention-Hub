@extends('layouts.app')
@section('title', 'Novo Card')
@section('header', 'Novo Atendimento de Ouvidoria')

@section('content')
<div class="max-w-2xl mx-auto" x-data="wizard()">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs mb-5 px-1">
        <a href="{{ route('board') }}" class="font-bold text-brand-600 dark:text-brand-400">← Board</a>
        <span class="text-slate-300 dark:text-slate-700">/</span>
        <span class="text-slate-500 dark:text-slate-400 font-medium">Novo Card</span>
    </div>

    {{-- Progress --}}
    <div class="flex items-center gap-2 mb-6">
        @php $steps = ['Cliente', 'Responsáveis', 'Detalhes']; @endphp
        @foreach($steps as $i => $label)
        <div class="flex items-center gap-2 flex-1">
            <div class="flex items-center gap-2">
                <div :class="step > {{ $i }} ? 'bg-brand-600 text-white' : (step === {{ $i }} ? 'bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 border-2 border-brand-500' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600')"
                     class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-extrabold transition-all duration-200 shrink-0">
                    <span x-show="step <= {{ $i }}" {{ $i > 0 ? 'x-cloak' : '' }}>{{ $i + 1 }}</span>
                    <svg x-show="step > {{ $i }}" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span :class="step === {{ $i }} ? 'text-slate-700 dark:text-slate-200 font-bold' : 'text-slate-400 dark:text-slate-600'"
                      class="text-xs hidden sm:block transition-colors">{{ $label }}</span>
            </div>
            @unless($loop->last)
            <div class="flex-1 h-px mx-2" :class="step > {{ $i }} ? 'bg-brand-400' : 'bg-slate-200 dark:bg-slate-800'"></div>
            @endunless
        </div>
        @endforeach
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('cards.store') }}" class="relative space-y-5">
        @csrf

        {{-- Step 0: Cliente e Status --}}
        <div x-show="step === 0" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium relative overflow-hidden animate-fadeIn space-y-5">
            <div class="absolute top-0 left-0 w-full h-[3px] bg-gradient-to-r from-brand-600 to-accent-indigo"></div>

            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Cliente e Status</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Identifique o cliente e defina o status inicial do card.</p>
            </div>

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
            </div>
        </div>

        {{-- Step 1: Responsáveis --}}
        <div x-show="step === 1" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium relative overflow-hidden animate-fadeIn space-y-5">
            <div class="absolute top-0 left-0 w-full h-[3px] bg-gradient-to-r from-brand-600 to-accent-indigo"></div>

            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Responsáveis e Origem</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Informe quem acompanhará o caso e a origem do ticket.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Ouvidor — managed combobox --}}
                @include('cards._managed_combobox', ['type' => 'ombudsman_agents', 'name' => 'ombudsman_agent', 'label' => 'Ouvidor Responsável', 'placeholder' => 'Selecione…', 'options' => $agents, 'old' => old('ombudsman_agent'), 'col' => '', 'freeText' => false])

                {{-- Origem do ticket — managed combobox --}}
                @include('cards._managed_combobox', ['type' => 'ticket_origins', 'name' => 'ticket_origin', 'label' => 'Origem do Ticket', 'placeholder' => 'Selecione…', 'options' => $origins, 'old' => old('ticket_origin'), 'col' => '', 'freeText' => false])

                {{-- Time responsável — managed combobox --}}
                @include('cards._managed_combobox', ['type' => 'responsible_teams', 'name' => 'responsible_team', 'label' => 'Time Responsável', 'placeholder' => 'Selecione…', 'options' => $teams, 'old' => old('responsible_team'), 'col' => 'md:col-span-2', 'freeText' => false])
            </div>
        </div>

        {{-- Step 2: Detalhes --}}
        <div x-show="step === 2" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium relative overflow-hidden animate-fadeIn space-y-5">
            <div class="absolute top-0 left-0 w-full h-[3px] bg-gradient-to-r from-brand-600 to-accent-indigo"></div>

            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Detalhes do Atendimento</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Insira o assunto e a descrição detalhada do atendimento.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
        </div>

        @if($errors->any())
        <div class="text-xs text-rose-600 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 rounded-xl px-4 py-3 font-semibold">
            {{ $errors->first() }}
        </div>
        @endif

        {{-- Navigation --}}
        <div class="flex justify-between mt-5">
            <button type="button" @click="prev()" x-show="step > 0" x-cloak
                    class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                ← Voltar
            </button>
            <div x-show="step === 0"></div>

            <div class="flex gap-3">
                <a href="{{ route('board') }}"
                   class="px-5 py-2.5 text-slate-400 dark:text-slate-600 text-xs font-semibold rounded-xl hover:text-slate-600 dark:hover:text-slate-400 transition-all flex items-center">
                    Cancelar
                </a>
                <button type="button" @click="next()" x-show="step < 2"
                        class="btn-primary">
                    Próximo →
                </button>
                <button type="submit" x-show="step === 2" x-cloak
                        class="btn-primary">
                    Criar Card de Ouvidoria
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function wizard() {
    return {
        step: 0,
        next() {
            // Validação simples do cliente ao avançar do step 0
            if (this.step === 0) {
                const customerSelect = document.querySelector('select[name="customer_id"]');
                if (customerSelect && !customerSelect.value) {
                    customerSelect.reportValidity();
                    return;
                }
            }
            if (this.step < 2) this.step++;
        },
        prev() {
            if (this.step > 0) this.step--;
        }
    };
}
</script>
@endsection
