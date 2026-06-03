{{--
  Partial: combobox com gestão inline de opções e visual premium alinhado
  Variáveis: $type, $name, $label, $placeholder, $options (Collection), $old (string), $col (classes extra)
             $freeText (bool, default true) — false = somente selecionar, sem digitação livre
             $saveUrl (string, opcional) — URL de save; padrão usa settings.card-options
--}}
@php
    $freeText = $freeText ?? true;
    $saveUrl = $saveUrl ?? (isset($type) ? route('settings.card-options', $type) : '');
    
    // Normalizar opções para array de strings
    if (isset($options)) {
        if ($options instanceof \Illuminate\Support\Collection) {
            $normalizedOptions = $options->values()->toArray();
        } else {
            $normalizedOptions = array_values((array) $options);
        }
    } else {
        $normalizedOptions = [];
    }
@endphp

{{-- Correção de aspas no atributo HTML e inicialização robusta no x-init --}}
<div x-data="managedCombobox('{{ $saveUrl }}', [], '{{ old($name, $old ?? '') }}')"
     x-init="options = JSON.parse($el.getAttribute('data-options')); filtered = [...options]"
     data-options="{{ json_encode($normalizedOptions) }}"
     class="relative {{ $col }}"
     @click.outside="open = false; managing = false">

    {{-- Label + botão gerenciar --}}
    <div class="flex items-center justify-between mb-1.5">
        <label class="field-label mb-0">{{ $label }}</label>
        @if(isset($type) && $type)
        <button type="button" @click.stop="managing = !managing; open = false"
                class="flex items-center gap-1 text-[10px] font-bold transition-colors"
                :class="managing ? 'text-brand-600 dark:text-brand-400' : 'text-slate-400 dark:text-slate-650 hover:text-slate-600 dark:hover:text-slate-400'">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-text="managing ? 'Fechar' : 'Gerenciar'"></span>
        </button>
        @endif
    </div>

    {{-- Trigger — padrão select-wrap nativo do projeto (mantém a chevron e o tamanho idênticos) --}}
    <div class="select-wrap">
        <input type="text"
               @if($freeText) x-model="query" @input="filter()" @else :value="value" readonly @endif
               @focus="open = true"
               @click="{{ !$freeText ? 'open = !open' : '' }}"
               @keydown.arrow-down.prevent="nav(1)"
               @keydown.arrow-up.prevent="nav(-1)"
               @keydown.enter.prevent="confirm()"
               @keydown.escape="open = false"
               placeholder="{{ $placeholder }}"
               class="field-input cursor-pointer select-item text-slate-800 dark:text-slate-100 font-semibold focus:border-brand-500 transition-all duration-200">
    </div>
    <input type="hidden" name="{{ $name }}" x-model="value">

    {{-- Dropdown de Opções --}}
    <div x-show="open" x-cloak
         class="absolute z-50 w-full mt-1.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden">

        {{-- Estado vazio --}}
        <div x-show="filtered.length === 0" class="px-4 py-6 flex flex-col items-center gap-2 text-center">
            <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">Nenhuma opção encontrada.</p>
            @if(isset($type) && $type)
            <button type="button" @click.stop="open = false; managing = true"
                    class="text-[10px] font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 hover:bg-brand-100 dark:hover:bg-brand-900/50 px-3 py-1.5 rounded-lg transition-all">
                + Adicionar primeira opção
            </button>
            @endif
        </div>

        {{-- Opções --}}
        <div x-show="filtered.length > 0" class="overflow-auto max-h-44 py-1">
            <template x-for="(opt, i) in filtered" :key="opt">
                <div @click="select(opt)"
                     :class="hi === i || value === opt
                         ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 font-bold'
                         : 'text-slate-750 dark:text-slate-250 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                     class="px-4 py-2.5 text-xs cursor-pointer transition-colors flex items-center justify-between">
                    <span x-text="opt"></span>
                    <svg x-show="value === opt" class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </template>
        </div>
    </div>

    {{-- Painel de gestão --}}
    @if(isset($type) && $type)
    <div x-show="managing" x-cloak
         class="mt-2 border border-brand-200/80 dark:border-brand-900/40 rounded-xl bg-brand-50/20 dark:bg-brand-950/5 p-3.5 space-y-3 shadow-sm animate-fadeIn">

        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
            <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gerenciar Opções</span>
            <button type="button" @click.stop="managing = false" class="text-[10px] font-bold text-rose-500 hover:text-rose-650 hover:underline">
                Fechar
            </button>
        </div>

        <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
            <template x-for="opt in options" :key="opt">
                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-xs shadow-sm hover:border-slate-200 dark:hover:border-slate-700 transition-all duration-200">
                    <span class="font-semibold text-slate-750 dark:text-slate-350 truncate" x-text="opt"></span>
                    <button type="button" @click.stop="removeOption(opt)"
                            class="text-slate-300 dark:text-slate-650 hover:text-rose-500 dark:hover:text-rose-400 transition-colors ml-2 shrink-0 w-5 h-5 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/20 flex items-center justify-center">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
            <div x-show="options.length === 0" class="px-2 py-2 text-xs text-slate-400 dark:text-slate-550 italic text-center">
                Nenhuma opção criada ainda.
            </div>
        </div>

        <div class="flex gap-2">
            <input type="text" x-model="newOption" @keydown.enter.prevent="addOption()"
                   placeholder="Digite nova opção…"
                   class="flex-1 text-xs border border-slate-200 dark:border-slate-750 rounded-lg px-2.5 py-2 bg-white dark:bg-slate-850 text-slate-750 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-600 outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/5 transition-all">
            <button type="button" @click.stop="addOption()" :disabled="!newOption.trim() || saving"
                    class="px-3 py-2 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white text-xs font-bold rounded-lg transition-all shrink-0 flex items-center gap-1 shadow-sm">
                <span x-show="!saving" class="flex items-center gap-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Adicionar
                </span>
                <span x-show="saving" x-cloak class="w-3.5 h-3.5 rounded-full border-2 border-white/30 border-t-white animate-spin"></span>
            </button>
        </div>
    </div>
    @endif
</div>
