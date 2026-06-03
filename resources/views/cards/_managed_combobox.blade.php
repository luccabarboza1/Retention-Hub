{{--
  Partial: select com gestão inline de opções
  Variáveis: $type, $name, $label, $placeholder, $options (Collection), $old (string), $col
             $saveUrl — URL do endpoint de persistência
--}}
@php
    $saveUrl ??= route('settings.card-options', $type);
    $normalizedOptions = ($options instanceof \Illuminate\Support\Collection)
        ? $options->values()->toArray()
        : array_values((array)($options ?? []));
    $currentValue = old($name, $old ?? '');
@endphp

<div x-data="managedCombobox('{{ $saveUrl }}', JSON.parse($el.dataset.opts), '{{ $currentValue }}')"
     data-opts='@json($normalizedOptions)'
     class="{{ $col ?? '' }}">

    {{-- Label + botão Gerenciar --}}
    <div class="flex items-center justify-between mb-1.5">
        <label class="field-label mb-0">{{ $label }}</label>
        <button type="button" @click="managing = !managing"
                class="flex items-center gap-1 text-[10px] font-bold transition-colors"
                :class="managing
                    ? 'text-brand-600 dark:text-brand-400'
                    : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300'">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-text="managing ? 'Fechar' : 'Gerenciar'"></span>
        </button>
    </div>

    {{-- Select nativo — igual aos outros campos do formulário --}}
    <div class="select-wrap">
        <select name="{{ $name }}" x-model="value"
                class="field-input font-semibold text-slate-800 dark:text-slate-100">
            <option value="">{{ $placeholder }}</option>
            <template x-for="opt in options" :key="opt">
                <option :value="opt" :selected="opt === value" x-text="opt"></option>
            </template>
        </select>
    </div>

    {{-- Painel de gestão de opções --}}
    <div x-show="managing" style="display:none"
         class="mt-2 border border-brand-200 dark:border-brand-900/50 rounded-xl bg-brand-50/20 dark:bg-brand-900/10 p-3 space-y-2">

        <div class="space-y-1 max-h-36 overflow-y-auto">
            <template x-for="opt in options" :key="opt">
                <div class="flex items-center justify-between px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-xs">
                    <span class="font-semibold text-slate-700 dark:text-slate-300 truncate" x-text="opt"></span>
                    <button type="button" @click="removeOption(opt)"
                            class="text-slate-300 dark:text-slate-600 hover:text-rose-500 transition-colors ml-2 shrink-0 w-5 h-5 rounded hover:bg-rose-50 dark:hover:bg-rose-950/20 flex items-center justify-center">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
            <p x-show="options.length === 0"
               class="text-xs text-slate-400 dark:text-slate-500 italic px-1 py-1">
                Nenhuma opção criada ainda.
            </p>
        </div>

        <div class="flex gap-2">
            <input type="text" x-model="newOption"
                   @keydown.enter.prevent="addOption()"
                   placeholder="Nova opção…"
                   class="flex-1 text-xs border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-600 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/10 transition-all">
            <button type="button" @click="addOption()"
                    :disabled="!newOption.trim() || saving"
                    class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white text-xs font-bold rounded-lg transition-all shrink-0">
                <span x-show="!saving">+ Adicionar</span>
                <span x-show="saving" x-cloak
                      class="inline-block w-3 h-3 rounded-full border-2 border-white/30 border-t-white animate-spin"></span>
            </button>
        </div>
    </div>
</div>
