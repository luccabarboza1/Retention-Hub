@extends('layouts.app')
@section('title', 'Board de Ouvidoria')
@section('header', 'Board de Ouvidoria')

@php
$colorMap = [
    'blue'   => [
        'bg' => 'bg-sky-100/80 border-sky-300 dark:bg-sky-900/20 dark:border-sky-700/60',
        'dot' => 'bg-sky-500 shadow-[0_0_8px_rgba(56,189,248,0.6)]',
        'cardBorder' => 'border-l-sky-400'
    ],
    'yellow' => [
        'bg' => 'bg-amber-100/80 border-amber-300 dark:bg-amber-900/20 dark:border-amber-700/60',
        'dot' => 'bg-amber-500 shadow-[0_0_8px_rgba(251,191,36,0.6)]',
        'cardBorder' => 'border-l-amber-400'
    ],
    'green'  => [
        'bg' => 'bg-emerald-100/80 border-emerald-300 dark:bg-emerald-900/20 dark:border-emerald-700/60',
        'dot' => 'bg-emerald-500 shadow-[0_0_8px_rgba(52,211,153,0.6)]',
        'cardBorder' => 'border-l-emerald-400'
    ],
    'red'    => [
        'bg' => 'bg-rose-100/80 border-rose-300 dark:bg-rose-900/20 dark:border-rose-700/60',
        'dot' => 'bg-rose-500 shadow-[0_0_8px_rgba(251,113,133,0.6)]',
        'cardBorder' => 'border-l-rose-400'
    ],
    'purple' => [
        'bg' => 'bg-purple-100/80 border-purple-300 dark:bg-purple-900/20 dark:border-purple-700/60',
        'dot' => 'bg-purple-500 shadow-[0_0_8px_rgba(192,132,252,0.6)]',
        'cardBorder' => 'border-l-purple-400'
    ],
    'pink'   => [
        'bg' => 'bg-pink-100/80 border-pink-300 dark:bg-pink-900/20 dark:border-pink-700/60',
        'dot' => 'bg-pink-500 shadow-[0_0_8px_rgba(244,114,182,0.6)]',
        'cardBorder' => 'border-l-pink-400'
    ],
    'indigo' => [
        'bg' => 'bg-indigo-100/80 border-indigo-300 dark:bg-indigo-900/20 dark:border-indigo-700/60',
        'dot' => 'bg-indigo-500 shadow-[0_0_8px_rgba(129,140,248,0.6)]',
        'cardBorder' => 'border-l-indigo-400'
    ],
    'gray'   => [
        'bg' => 'bg-slate-100/80 border-slate-300 dark:bg-slate-800/40 dark:border-slate-600/60',
        'dot' => 'bg-slate-500 shadow-[0_0_8px_rgba(156,163,175,0.6)]',
        'cardBorder' => 'border-l-slate-400'
    ],
];

// Mapeamento de Tiers para gradientes estéticos
$tierColors = [
    'gold' => 'from-amber-400 to-amber-600 text-white font-bold',
    'silver' => 'from-slate-300 to-slate-400 text-slate-800 font-bold',
    'bronze' => 'from-orange-300 to-orange-500 text-white font-bold',
    'premium' => 'from-brand-500 to-accent-indigo text-white font-bold shadow-glow-brand',
    'vip' => 'from-rose-500 to-pink-600 text-white font-bold shadow-sm',
    'standard' => 'from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 text-slate-600 dark:text-slate-300 font-bold',
];
@endphp

@section('content')
<div x-data="board()" class="h-full flex flex-col">

    {{-- Board Toolbar / Stats --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4 shrink-0 bg-slate-50/60 dark:bg-slate-900/60 p-4 rounded-xl border border-slate-100/80 dark:border-slate-800/80 backdrop-blur-sm">
        <div>
            <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-300 uppercase tracking-wider">Painel Executivo</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                Monitorando <span class="font-bold text-brand-600 dark:text-brand-400 text-sm px-1.5 py-0.5 rounded-md bg-brand-50 dark:bg-brand-900/30">{{ $cards->flatten()->count() }}</span> card(s) ativo(s)
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            {{-- Filter by Tag Dropdown --}}
            <form method="GET" action="{{ route('board') }}" class="flex items-center gap-2">
                <div class="w-48 shrink-0 select-wrap">
                    <select name="tag" onchange="this.form.submit()" class="field-input text-xs font-semibold">
                        <option value="">Filtrar por Etiqueta…</option>
                        @foreach($allTags as $t)
                        <option value="{{ $t }}" {{ $tagFilter === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                @if($tagFilter)
                <a href="{{ route('board') }}" class="px-3.5 py-2 border border-slate-200 dark:border-slate-700 text-slate-605 dark:text-slate-400 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex items-center justify-center shrink-0">
                    Limpar
                </a>
                @endif
            </form>

            <button @click="showColModal = true"
                class="flex items-center gap-1.5 text-xs px-3 py-2 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium rounded-lg transition-all duration-200 border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Nova Etapa
            </button>
            <a href="{{ route('cards.create') }}"
                class="group flex items-center gap-2 text-xs px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow-glow-brand transition-all duration-200 hover:-translate-y-0.5">
                <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Adicionar Card
            </a>
        </div>
    </div>

    {{-- Kanban Track (Horizontal Scroll) --}}
    <div class="flex-1 flex gap-5 overflow-x-auto pb-6 items-start">
        @foreach($columns as $col)
        @php 
            $c = $colorMap[$col->color] ?? $colorMap['gray'];
            $colCards = $cards->get($col->name, collect());
        @endphp
        <div class="flex flex-col w-80 shrink-0 bg-slate-100/60 dark:bg-slate-800/40 rounded-2xl border border-slate-200 dark:border-slate-700/60 p-3 h-full max-h-[calc(100vh-260px)]" data-column="{{ $col->name }}">

            {{-- Column Header --}}
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full {{ $c['dot'] }}"></span>
                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200 truncate" title="{{ $col->name }}">{{ $col->name }}</span>
                    <span class="text-[10px] font-bold bg-slate-200/60 dark:bg-slate-700 text-slate-600 dark:text-slate-400 px-2 py-0.5 rounded-full">{{ $colCards->count() }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="editCol({{ $col->id }}, '{{ addslashes($col->name) }}', '{{ $col->color }}')"
                        class="text-slate-400 hover:text-brand-600 hover:bg-white dark:hover:bg-slate-800 p-1 rounded-lg transition-all" title="Editar etapa">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.172-8.172z"/></svg>
                    </button>
                    <form method="POST" action="{{ route('columns.destroy', $col) }}"
                          data-confirm-title="Excluir etapa"
                          data-confirm-msg="Excluir &quot;{{ $col->name }}&quot;? Os cards permanecerão no sistema."
                          @submit.prevent="$dispatch('open-confirm', { title: $el.dataset.confirmTitle, message: $el.dataset.confirmMsg, form: $el })">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-slate-400 hover:text-rose-600 hover:bg-white dark:hover:bg-slate-800 p-1 rounded-lg transition-all" title="Excluir etapa">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Cards Container (Scrollable) --}}
            <div class="kanban-col flex-1 overflow-y-auto flex flex-col gap-3 rounded-xl border border-dashed {{ $c['bg'] }} p-2.5 transition-all duration-200"
                 data-status="{{ $col->name }}"
                 id="col-{{ $loop->index }}">
                
                {{-- Empty Placeholder --}}
                <div class="empty-placeholder flex flex-col items-center justify-center py-10 px-4 text-center {{ $colCards->count() ? 'hidden' : '' }}">
                    <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500 mb-2">
                        📥
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-medium italic">Sem cards nesta etapa</p>
                </div>

                {{-- Card loop --}}
                @foreach($colCards as $card)
                @php
                    $tColorKey = strtolower($card->customer->tier ?? '');
                    $tGrad = $tierColors[$tColorKey] ?? $tierColors['standard'];
                @endphp
                <a href="{{ route('cards.show', $card) }}"
                   class="block bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 hover:shadow-premium-hover hover:border-brand-300 hover:-translate-y-0.5 transition-all duration-300 group cursor-grab active:cursor-grabbing border-l-4 {{ $c['cardBorder'] }}"
                   data-card-id="{{ $card->id }}"
                   draggable="false">
                    
                    {{-- Card Header --}}
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="text-[10px] font-bold font-mono text-slate-400 dark:text-slate-500 uppercase">#{{ $card->id }}</span>
                        @if($card->customer->tier)
                        <span class="text-[9px] uppercase tracking-wider px-2 py-0.5 rounded-full bg-gradient-to-r {{ $tGrad }}">
                            {{ $card->customer->tier }}
                        </span>
                        @endif
                    </div>

                    {{-- Customer Company --}}
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 leading-snug group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors mb-1.5">
                        {{ $card->customer->company_name ?? '—' }}
                    </h3>

                    {{-- Contact Reason --}}
                    @if($card->contact_reason)
                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed mb-3">
                        {{ $card->contact_reason }}
                    </p>
                    @endif

                    {{-- Card Tags --}}
                    @if($card->tags && is_array($card->tags) && count($card->tags))
                    <div class="flex flex-wrap gap-1 mb-2.5">
                        @foreach($card->tags as $t)
                        <span class="inline-block text-[9px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-2 py-0.5 rounded">
                            {{ $t }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    <div class="border-t border-slate-100 dark:border-slate-800/60 my-2"></div>

                    {{-- Card Footer --}}
                    <div class="flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                        <span class="flex items-center gap-1">
                            📅 {{ $card->started_at->format('d/m/Y') }}
                        </span>
                        
                        @if($card->ombudsman_agent)
                        <div class="flex items-center gap-1.5" title="Agente: {{ $card->ombudsman_agent }}">
                            <span class="w-5 h-5 rounded-full bg-gradient-to-tr from-brand-500 to-accent-indigo text-white text-[9px] font-bold flex items-center justify-center shadow-sm uppercase">
                                {{ strtoupper(substr($card->ombudsman_agent, 0, 1)) }}
                            </span>
                            <span class="max-w-[70px] truncate font-medium text-slate-500 dark:text-slate-400">{{ $card->ombudsman_agent }}</span>
                        </div>
                        @endif
                    </div>
                </a>
                @endforeach

            </div>

        </div>
        @endforeach
    </div>

    {{-- Modal: Create/Edit Column --}}
    <div x-show="showColModal || editColId" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm animate-fadeIn">
        <div @click.outside="closeColModal()" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-2xl w-96 p-6 overflow-hidden relative">
            <div class="absolute top-[-50px] right-[-50px] w-[150px] h-[150px] rounded-full bg-brand-500/5 blur-[50px] pointer-events-none"></div>
            
            <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100 mb-5 tracking-tight flex items-center gap-2" x-text="editColId ? 'Editar Etapa' : 'Criar Nova Etapa'"></h2>

            {{-- Creation Form --}}
            <template x-if="!editColId">
                <form method="POST" action="{{ route('columns.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Nome da Etapa</label>
                        <input name="name" required class="field-input" placeholder="Ex: Em Análise">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Cor Temática</label>
                        <select name="color" class="field-input">
                            <option value="blue">Azul</option>
                            <option value="yellow">Amarelo</option>
                            <option value="green">Verde</option>
                            <option value="red">Vermelho</option>
                            <option value="purple">Roxo</option>
                            <option value="indigo">Índigo</option>
                            <option value="pink">Rosa</option>
                            <option value="gray">Cinza</option>
                        </select>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-xl py-3 shadow-glow-brand transition-all">Criar Etapa</button>
                        <button type="button" @click="closeColModal()" class="flex-1 border border-slate-200 dark:border-slate-700 text-slate-650 dark:text-slate-400 font-semibold text-sm rounded-xl py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">Cancelar</button>
                    </div>
                </form>
            </template>

            {{-- Editing Form --}}
            <template x-if="editColId">
                <form method="POST" :action="'/columns/' + editColId" class="space-y-4">
                    @csrf @method('PATCH')
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Nome da Etapa</label>
                        <input name="name" :value="editColName" required class="field-input">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Cor Temática</label>
                        <select name="color" class="field-input">
                            <option value="blue" :selected="editColColor === 'blue'">Azul</option>
                            <option value="yellow" :selected="editColColor === 'yellow'">Amarelo</option>
                            <option value="green" :selected="editColColor === 'green'">Verde</option>
                            <option value="red" :selected="editColColor === 'red'">Vermelho</option>
                            <option value="purple" :selected="editColColor === 'purple'">Roxo</option>
                            <option value="indigo" :selected="editColColor === 'indigo'">Índigo</option>
                            <option value="pink" :selected="editColColor === 'pink'">Rosa</option>
                            <option value="gray" :selected="editColColor === 'gray'">Cinza</option>
                        </select>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-xl py-3 shadow-glow-brand transition-all">Salvar Etapa</button>
                        <button type="button" @click="closeColModal()" class="flex-1 border border-slate-200 dark:border-slate-700 text-slate-650 dark:text-slate-400 font-semibold text-sm rounded-xl py-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">Cancelar</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>

{{-- Global Error Flasher --}}
@if($errors->any())
<div class="fixed bottom-6 right-6 bg-rose-50 border border-rose-100 text-rose-800 text-xs font-semibold px-4 py-3.5 rounded-xl shadow-lg animate-fadeIn z-50 flex items-center gap-2">
    <span class="w-4 h-4 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">✕</span>
    <span>{{ $errors->first() }}</span>
</div>
@endif

<!-- Sortable.js for Drag & Drop support -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
function board() {
    return {
        showColModal: false,
        editColId: null,
        editColName: '',
        editColColor: 'gray',
        editCol(id, name, color) { 
            this.editColId = id; 
            this.editColName = name; 
            this.editColColor = color; 
        },
        closeColModal() { 
            this.showColModal = false; 
            this.editColId = null; 
        },
    }
}

function syncPlaceholders() {
    document.querySelectorAll('.kanban-col').forEach(function (col) {
        const placeholder = col.querySelector('.empty-placeholder');
        if (!placeholder) return;
        const hasCards = col.querySelectorAll('[data-card-id]').length > 0;
        placeholder.classList.toggle('hidden', hasCards);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    syncPlaceholders();

    document.querySelectorAll('.kanban-col').forEach(function (col) {
        Sortable.create(col, {
            group: 'cards',
            animation: 180,
            ghostClass: 'opacity-20',
            dragClass: 'shadow-2xl',
            draggable: '[data-card-id]',
            onEnd: function (evt) {
                syncPlaceholders();

                const cardId = evt.item.dataset.cardId;
                const status = evt.to.dataset.status;

                if (!cardId || !status) return;

                const fd = new FormData();
                fd.append('status', status);
                
                // Add csrf token header for Laravel security
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                fetch('/cards/' + cardId + '/move', {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-CSRF-TOKEN': token || ''
                    }
                })
                .then(r => r.json())
                .then(data => { if (!data.ok) location.reload(); })
                .catch(() => location.reload());
            }
        });
    });
});
</script>
@endsection
