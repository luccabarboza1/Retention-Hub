{{--
  Partial: combobox com gestão inline de opções
  Variáveis: $type, $name, $label, $placeholder, $options (Collection), $old (string), $col (classes extra)
             $freeText (bool, default true) — false = somente selecionar, sem digitação livre
             $saveUrl (string, opcional) — URL de save; padrão usa settings.card-options
--}}
@php $freeText = $freeText ?? true; @endphp
<div x-data="managedCombobox('{{ $saveUrl ?? route('settings.card-options', $type) }}', @json($options->values()), '{{ old($name, $old ?? '') }}')"
     class="relative {{ $col }}"
     @click.outside="open = false; managing = false">

    {{-- Label + botão gerenciar --}}
    <div class="flex items-center justify-between mb-1.5">
        <label class="field-label mb-0">{{ $label }}</label>
        <button type="button" @click="managing = !managing; open = false"
                class="flex items-center gap-1 text-[10px] font-bold transition-colors"
                :class="managing ? 'text-brand-600 dark:text-brand-400' : 'text-slate-400 dark:text-slate-600 hover:text-slate-600 dark:hover:text-slate-400'">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-text="managing ? 'Fechar' : 'Gerenciar'"></span>
        </button>
    </div>

    {{-- Input / trigger --}}
    <div class="relative" x-show="!managing">
        @if($freeText)
        <input type="text" x-model="query" @input="filter()" @focus="open = true"
               @keydown.arrow-down.prevent="nav(1)" @keydown.arrow-up.prevent="nav(-1)"
               @keydown.enter.prevent="confirm()" @keydown.escape="open = false"
               placeholder="{{ $placeholder }}" class="field-input pr-8">
        @else
        <button type="button" @click="open = !open"
                class="field-input pr-8 text-left flex items-center cursor-pointer w-full"
                :class="value ? 'text-slate-800 dark:text-slate-100 font-semibold' : 'text-slate-400 dark:text-slate-500'">
            <span x-text="value || '{{ $placeholder }}'"></span>
        </button>
        @endif
        <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 transition-transform duration-200"
              :class="open ? 'rotate-180' : ''">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </span>
    </div>
    <input type="hidden" name="{{ $name }}" x-model="value">

    {{-- Dropdown: estado vazio --}}
    <div x-show="open && options.length === 0 && !managing" x-cloak
         class="absolute z-50 w-full mt-1.5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-premium overflow-hidden">
        <div class="px-4 py-5 flex flex-col items-center gap-3 text-center">
            <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">Nenhuma opção criada.</p>
            <button type="button" @click="open = false; managing = true"
                    class="text-[10px] font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 hover:bg-brand-100 dark:hover:bg-brand-900/40 px-3 py-1.5 rounded-lg transition-all">
                + Adicionar primeira opção
            </button>
        </div>
    </div>

    {{-- Dropdown: com opções --}}
    <div x-show="open && options.length > 0 && !managing" x-cloak
         class="absolute z-50 w-full mt-1.5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-premium overflow-hidden">

        {{-- Campo de busca (apenas freeText) --}}
        @if($freeText)
        <div class="px-3 pt-3 pb-2 border-b border-slate-100 dark:border-slate-800">
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <input type="text" x-model="query" @input="filter()"
                       placeholder="Buscar…"
                       class="w-full pl-7 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all text-slate-700 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-600">
            </div>
        </div>
        @endif

        <div class="overflow-auto max-h-48 py-1">
            <template x-for="(opt, i) in filtered" :key="opt">
                <div @click="select(opt)"
                     :class="[
                         hi === i || value === opt
                             ? 'bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-300'
                             : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/60',
                         value === opt ? 'font-bold' : 'font-medium'
                     ]"
                     class="flex items-center justify-between px-4 py-2.5 text-sm cursor-pointer transition-colors">
                    <span x-text="opt"></span>
                    <svg x-show="value === opt" class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </template>
            <div x-show="filtered.length === 0" class="px-4 py-3 text-xs text-slate-400 dark:text-slate-500 italic text-center">
                Sem resultados.
            </div>
        </div>
    </div>

    {{-- Painel de gestão --}}
    <div x-show="managing" x-cloak
         class="border border-brand-200 dark:border-brand-900/50 rounded-xl bg-brand-50/30 dark:bg-brand-950/10 p-3 space-y-2">

        <div class="space-y-1 max-h-40 overflow-y-auto">
            <template x-for="opt in options" :key="opt">
                <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-xs group">
                    <span class="font-semibold text-slate-700 dark:text-slate-300 truncate" x-text="opt"></span>
                    <button type="button" @click="removeOption(opt)"
                            class="text-slate-300 dark:text-slate-700 hover:text-rose-500 dark:hover:text-rose-400 transition-colors ml-2 shrink-0 w-5 h-5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/20 flex items-center justify-center">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
            <div x-show="options.length === 0" class="px-2 py-2 text-xs text-slate-400 dark:text-slate-500 italic">
                Nenhuma opção criada ainda.
            </div>
        </div>

        <div class="flex gap-2">
            <input type="text" x-model="newOption" @keydown.enter.prevent="addOption()"
                   placeholder="Nova opção…"
                   class="flex-1 text-xs border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-600 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all">
            <button type="button" @click="addOption()" :disabled="!newOption.trim() || saving"
                    class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white text-xs font-bold rounded-lg transition-all shrink-0 flex items-center gap-1">
                <span x-show="!saving">
                    <svg class="w-3 h-3 inline -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Adicionar
                </span>
                <span x-show="saving" x-cloak class="w-3 h-3 rounded-full border-2 border-white/30 border-t-white animate-spin"></span>
            </button>
        </div>
    </div>
</div>
