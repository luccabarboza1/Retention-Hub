@extends('layouts.app')
@section('title', 'Card #' . $card->id)
@section('header', 'Workspace do Card #' . $card->id)

@php
$tierColors = [
    'gold'     => 'from-amber-400 to-amber-600 text-white',
    'silver'   => 'from-slate-300 to-slate-400 text-slate-800',
    'bronze'   => 'from-orange-300 to-orange-500 text-white',
    'premium'  => 'from-brand-500 to-accent-indigo text-white',
    'vip'      => 'from-rose-500 to-pink-600 text-white',
    'standard' => 'from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 text-slate-600 dark:text-slate-300',
];
$tColorKey = strtolower($card->customer->tier ?? '');
$tGrad = $tierColors[$tColorKey] ?? $tierColors['standard'];
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-slate-800/50 px-4 py-3 rounded-xl border border-slate-100 dark:border-slate-700/80 backdrop-blur-sm">
        <div class="flex items-center gap-2 text-xs">
            <x-back-button :href="route('board')" label="Board" />
            <span class="text-slate-300 dark:text-slate-600">/</span>
            <a href="{{ route('customers.show', $card->customer) }}" class="text-slate-500 dark:text-slate-400 hover:text-brand-600 font-medium transition-colors">
                {{ $card->customer->company_name }}
            </a>
            <span class="text-slate-300 dark:text-slate-600">/</span>
            <span class="text-slate-400 dark:text-slate-500 font-mono">Card #{{ $card->id }}</span>
        </div>
        <div class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
            Última alteração: {{ $card->updated_at->diffForHumans() }}
        </div>
    </div>

    {{-- Stepper de etapas --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-700 p-6 shadow-premium relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-[3px] bg-gradient-to-r from-brand-500 to-accent-indigo"></div>
        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-4">Fluxo de Etapa</h3>
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            @foreach($statuses as $index => $s)
            @php
                $isActive = $card->status === $s;
                $activeIdx = array_search($card->status, $statuses);
                $isPassed  = $activeIdx !== false && $activeIdx > $index;
            @endphp
            <div class="flex-1 flex items-center gap-3">
                <form method="POST" action="{{ route('cards.update-status', $card) }}" class="flex-1">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $s }}">
                    <button type="submit" class="w-full text-left flex items-center gap-3 p-3 rounded-xl border transition-all duration-300
                        {{ $isActive  ? 'bg-brand-50 dark:bg-brand-900/30 border-brand-200 dark:border-brand-700 text-brand-900 dark:text-brand-300 font-bold'
                         : ($isPassed ? 'bg-slate-50 dark:bg-slate-800 border-slate-100 dark:border-slate-700 text-slate-600 dark:text-slate-400'
                                      : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500 hover:border-brand-200') }}">
                        <span class="w-6 h-6 rounded-lg text-xs font-bold flex items-center justify-center shrink-0
                            {{ $isActive  ? 'bg-brand-600 text-white'
                             : ($isPassed ? 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'
                                          : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500') }}">
                            {{ $isPassed ? '✓' : ($index + 1) }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold truncate">{{ $s }}</p>
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold uppercase mt-0.5">
                                {{ $isActive ? 'Atual' : ($isPassed ? 'Concluído' : 'Aguardando') }}
                            </p>
                        </div>
                    </button>
                </form>
                @if(!$loop->last)
                <div class="hidden md:block w-8 h-[2px] shrink-0 {{ $isPassed ? 'bg-brand-500' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Grid principal --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        {{-- Coluna principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Ficha --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-700 p-6 shadow-premium">
                <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-6">📄 Ficha de Atendimento</h3>
                <form method="POST" action="{{ route('cards.update', $card) }}" class="space-y-5">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="field-label">Motivo do Contato</label>
                            <input type="text" name="contact_reason" value="{{ old('contact_reason', $card->contact_reason) }}"
                                   placeholder="Ex: Insatisfeito com estabilidade..." class="field-input">
                        </div>
                        {{-- Agente Responsável --}}
                        @include('cards._managed_combobox', [
                            'type' => 'ombudsman_agents',
                            'name' => 'ombudsman_agent',
                            'label' => 'Agente Responsável',
                            'placeholder' => 'Selecione…',
                            'options' => $agents,
                            'old' => old('ombudsman_agent', $card->ombudsman_agent),
                            'col' => '',
                            'freeText' => false
                        ])

                        {{-- Time Responsável --}}
                        @include('cards._managed_combobox', [
                            'type' => 'responsible_teams',
                            'name' => 'responsible_team',
                            'label' => 'Time Responsável',
                            'placeholder' => 'Selecione…',
                            'options' => $teams,
                            'old' => old('responsible_team', $card->responsible_team),
                            'col' => '',
                            'freeText' => false
                        ])

                        {{-- Origem do Ticket --}}
                        @include('cards._managed_combobox', [
                            'type' => 'ticket_origins',
                            'name' => 'ticket_origin',
                            'label' => 'Origem do Ticket',
                            'placeholder' => 'Selecione…',
                            'options' => $origins,
                            'old' => old('ticket_origin', $card->ticket_origin),
                            'col' => '',
                            'freeText' => false
                        ])

                        {{-- Avaliação --}}
                        <div>
                            <label class="field-label">Avaliação (1–5)</label>
                            <div class="select-wrap">
                                <select name="rating" class="field-input font-semibold text-slate-800 dark:text-slate-100">
                                    <option value="">Sem avaliação</option>
                                    @for($i=1; $i<=5; $i++)
                                    <option value="{{ $i }}" {{ old('rating', $card->rating) == $i ? 'selected' : '' }}>
                                        {{ str_repeat('★', $i) }}{{ str_repeat('☆', 5-$i) }} ({{ $i }}/5)
                                    </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Início</label>
                            <input type="datetime-local" name="started_at"
                                   value="{{ old('started_at', $card->started_at->format('Y-m-d\TH:i')) }}" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Encerramento</label>
                            <input type="datetime-local" name="finished_at"
                                   value="{{ old('finished_at', $card->finished_at?->format('Y-m-d\TH:i')) }}" class="field-input">
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Link Reclame Aqui</label>
                            <input type="text" name="ra_claim_link" value="{{ old('ra_claim_link', $card->ra_claim_link) }}"
                                   placeholder="https://www.reclameaqui.com.br/..." class="field-input">
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Detalhes do Caso</label>
                            <textarea name="reason_details" rows="4" placeholder="Descreva os fatores do caso..." class="field-input resize-none">{{ old('reason_details', $card->reason_details) }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Solução Aplicada</label>
                            <textarea name="applied_solution" rows="4" placeholder="Ações tomadas para reter o cliente..." class="field-input resize-none">{{ old('applied_solution', $card->applied_solution) }}</textarea>
                        </div>
                    </div>
                    @if($errors->any())
                    <p class="text-xs text-rose-600 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 rounded-xl px-4 py-3 font-semibold">{{ $errors->first() }}</p>
                    @endif
                    <div class="border-t border-slate-100 dark:border-slate-700 pt-5">
                        <button type="submit" class="btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>

            {{-- Chats --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-700 p-6 shadow-premium" x-data="{ addingChat: false }">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">💬 Chats Vinculados</h3>
                    </div>
                    <button @click="addingChat = !addingChat" class="btn-ghost text-xs" x-text="addingChat ? 'Cancelar' : '+ Vincular Chat'"></button>
                </div>
                <div x-show="addingChat" x-cloak class="mb-5 p-4 bg-slate-50/80 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 rounded-xl animate-fadeIn">
                    <form method="POST" action="{{ route('cards.chats.store', $card) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        <div>
                            <label class="field-label">ID do Chat <span class="text-rose-500">*</span></label>
                            <input type="text" name="id" required placeholder="Ex: chat_98a72c" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Iniciado em <span class="text-rose-500">*</span></label>
                            <input type="datetime-local" name="started_at" value="{{ now()->format('Y-m-d\TH:i') }}" class="field-input">
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="btn-primary w-full">Vincular Chat</button>
                        </div>
                    </form>
                </div>
                <div class="space-y-2.5">
                    @forelse($card->chats as $chat)
                    <div class="flex items-center justify-between border border-slate-100 dark:border-slate-700 bg-slate-50/30 dark:bg-slate-800/30 rounded-xl px-4 py-3.5">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($chat->closed_at)
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shrink-0"></span>
                            @else
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shrink-0 animate-pulse"></span>
                            @endif
                            <div class="min-w-0">
                                <p class="text-xs font-mono font-bold text-slate-700 dark:text-slate-300 truncate">{{ $chat->id }}</p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold mt-0.5">
                                    Início: {{ $chat->started_at?->format('d/m/Y H:i') }}
                                    @if($chat->closed_at) · Encerrado: {{ $chat->closed_at->format('d/m/Y H:i') }} @endif
                                </p>
                            </div>
                        </div>
                        @if(!$chat->closed_at)
                        <form method="POST" action="{{ route('cards.chats.close', [$card, $chat]) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-ghost text-[10px] ml-2">Encerrar</button>
                        </form>
                        @else
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30 px-2.5 py-1 rounded-full ml-2">Encerrado</span>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-6 text-slate-400 dark:text-slate-600 bg-slate-50/30 dark:bg-slate-800/20 border border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                        <p class="text-xs italic font-medium">Nenhum chat vinculado.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Coluna direita --}}
        <div class="space-y-6">

            {{-- Cliente --}}
            <div class="bg-slate-900 dark:bg-slate-950 text-white rounded-2xl border border-slate-800 shadow-premium overflow-hidden relative p-6">
                <div class="absolute top-[-50px] right-[-50px] w-[150px] h-[150px] rounded-full bg-brand-500/10 blur-[50px] pointer-events-none"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">🎯 Cliente</h3>
                <a href="{{ route('customers.show', $card->customer) }}"
                   class="text-base font-extrabold text-white hover:text-brand-400 transition-colors block">
                    {{ $card->customer->company_name }}
                </a>
                <p class="text-xs text-slate-400 mt-1">{{ $card->customer->client_name }}</p>
                @if($card->customer->email)
                    <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $card->customer->email }}</p>
                @endif
                <div class="flex flex-wrap gap-2 mt-4 border-t border-slate-800 pt-4">
                    @if($card->customer->tier)
                        <span class="text-[9px] uppercase tracking-wider px-2 py-0.5 rounded-full bg-gradient-to-r font-bold {{ $tGrad }}">
                            {{ $card->customer->tier }}
                        </span>
                    @endif
                    @if($card->customer->plan_name)
                        <span class="bg-slate-800 text-slate-300 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-slate-700">
                            {{ $card->customer->plan_name }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- MRR em risco --}}
            @if($card->customer->monthly_fee)
            <div class="bg-gradient-to-br from-emerald-950 to-slate-900 border border-emerald-900/60 text-white rounded-2xl p-6 shadow-premium">
                <h4 class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider mb-1">Faturamento em Risco</h4>
                <div class="text-2xl font-extrabold text-emerald-300 font-mono flex items-baseline gap-1">
                    <span class="text-xs font-semibold text-emerald-400">R$</span>
                    {{ number_format($card->customer->monthly_fee, 2, ',', '.') }}
                    <span class="text-[10px] text-emerald-500 font-sans">/mês</span>
                </div>
            </div>
            @endif

            {{-- Comentários --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-premium flex flex-col overflow-hidden" style="max-height: 620px">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider flex items-center justify-between">
                        💬 Notas do Caso
                        <span class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold px-2 py-0.5 rounded-full text-[10px]">{{ $comments->count() }}</span>
                    </h3>
                </div>
                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4" style="min-height: 180px">
                    @forelse($comments as $comment)
                    <div class="group border-b border-slate-50 dark:border-slate-800 last:border-0 pb-3 last:pb-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-brand-500 to-accent-indigo text-white text-[10px] font-bold flex items-center justify-center shrink-0 uppercase">
                                    {{ strtoupper(substr($comment->author ?: 'A', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate block">{{ $comment->author ?: 'Anônimo' }}</span>
                                    <span class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold block uppercase">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('cards.comments.destroy', [$card, $comment]) }}"
                                  data-confirm-title="Excluir nota"
                                  data-confirm-msg="Deseja remover esta nota permanentemente?"
                                  @submit.prevent="$dispatch('open-confirm', { title: $el.dataset.confirmTitle, message: $el.dataset.confirmMsg, form: $el })">
                                @csrf @method('DELETE')
                                <button type="submit" class="opacity-0 group-hover:opacity-100 text-slate-300 dark:text-slate-600 hover:text-rose-600 transition-opacity p-0.5 rounded">✕</button>
                            </form>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2 bg-slate-50 dark:bg-slate-800 p-2.5 rounded-xl border-l-2 border-brand-400 leading-relaxed whitespace-pre-wrap">{{ $comment->content }}</p>
                    </div>
                    @empty
                    <div class="text-center py-10 text-slate-400 dark:text-slate-600 italic">
                        <p class="text-xs font-medium">Nenhuma nota ainda.</p>
                    </div>
                    @endforelse
                </div>
                <div class="border-t border-slate-100 dark:border-slate-700 p-4 bg-slate-50/30 dark:bg-slate-800/30">
                    <form method="POST" action="{{ route('cards.comments.store', $card) }}" class="space-y-2">
                        @csrf
                        <input type="text" name="author" placeholder="Seu nome (opcional)" class="field-input text-xs">
                        <textarea name="content" rows="3" placeholder="Escreva uma nota..." required class="field-input text-xs resize-none"></textarea>
                        <button type="submit" class="btn-primary w-full text-[11px]">Adicionar Nota</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
