{{--
  Partial: dropdown de filtro com estilo padrão do sistema
  Variáveis: $name (string), $placeholder (string), $options (array/Collection), $current (string), $width (string, default 'w-48')
--}}
@php
    $width   = $width       ?? 'w-48';
    $current = $current     ?? '';
    $opts    = ($options instanceof \Illuminate\Support\Collection) ? $options->toArray() : (array)($options ?? []);
@endphp

<div class="{{ $width }} relative shrink-0"
     x-data="{ open: false, value: '{{ $current }}', options: [] }"
     x-init="options = JSON.parse($el.getAttribute('data-options'))"
     data-options='@json($opts)'
     @click.outside="open = false">

    {{-- Trigger --}}
    <div class="select-wrap">
        <button type="button" @click="open = !open"
                class="field-input text-xs font-semibold text-left w-full truncate"
                :class="value ? 'text-slate-800 dark:text-slate-100' : 'text-slate-400 dark:text-slate-500'">
            <span x-text="value || '{{ $placeholder }}'"></span>
        </button>
    </div>

    {{-- Hidden input para o form --}}
    <input type="hidden" name="{{ $name }}" :value="value">

    {{-- Dropdown --}}
    <div x-show="open" x-cloak
         class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-auto max-h-56">

        {{-- Opção vazia --}}
        <div @click="value = ''; open = false; $el.closest('form').submit()"
             :class="value === '' ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 font-bold' : 'text-slate-400 dark:text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
             class="px-4 py-2.5 text-xs cursor-pointer transition-colors italic">
            {{ $placeholder }}
        </div>

        <template x-for="opt in options" :key="opt">
            <div @click="value = opt; open = false; $el.closest('form').submit()"
                 :class="value === opt
                     ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 font-bold'
                     : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                 class="px-4 py-2.5 text-xs cursor-pointer transition-colors flex items-center justify-between">
                <span x-text="opt"></span>
                <svg x-show="value === opt" class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400 shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </template>
    </div>
</div>
