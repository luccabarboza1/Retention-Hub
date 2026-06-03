{{--
  Partial: campo de etiquetas (tags)
  Variáveis: $currentTags (array), $allTags (Collection/array), $fieldName (string, default 'tags')
--}}
@php
    $fieldName   = $fieldName   ?? 'tags';
    $currentTags = $currentTags ?? [];
    $allTags     = $allTags     ?? [];
    if ($allTags instanceof \Illuminate\Support\Collection) $allTags = $allTags->toArray();
    if ($currentTags instanceof \Illuminate\Support\Collection) $currentTags = $currentTags->toArray();
@endphp

<div x-data="tagInput(@json($currentTags), @json($allTags))"
     data-current='@json($currentTags)'
     data-suggestions='@json($allTags)'
     @click.outside="open = false">

    {{-- Tags selecionadas + input --}}
    <div class="flex flex-wrap gap-1.5 p-2.5 border border-slate-200 dark:border-slate-700 rounded-xl min-h-[42px]
                bg-slate-50/50 dark:bg-slate-800/50 focus-within:border-brand-500
                focus-within:ring-4 focus-within:ring-brand-500/10 transition-all">

        <template x-for="(tag, i) in tags" :key="i">
            <span class="flex items-center gap-1 text-xs font-semibold bg-brand-50 dark:bg-brand-900/30
                         text-brand-700 dark:text-brand-300 px-2.5 py-0.5 rounded-lg">
                <span x-text="tag"></span>
                <button type="button" @click="remove(i)"
                        class="hover:text-rose-500 transition-colors leading-none ml-0.5">×</button>
            </span>
        </template>

        <input type="text" x-model="input"
               @keydown="key($event)"
               @focus="open = true"
               @input="open = true"
               placeholder="Adicionar etiqueta…"
               class="flex-1 min-w-[140px] bg-transparent text-sm outline-none
                      text-slate-700 dark:text-slate-300 placeholder-slate-400
                      dark:placeholder-slate-600 px-1 py-0.5">
    </div>

    {{-- Sugestões --}}
    <div x-show="open && filteredSuggestions.length > 0" x-cloak
         class="absolute z-50 mt-1 w-full bg-white dark:bg-slate-800 rounded-xl border
                border-slate-200 dark:border-slate-700 shadow-lg overflow-auto max-h-44">
        <template x-for="s in filteredSuggestions" :key="s">
            <div @click="add(s)"
                 class="px-4 py-2.5 text-sm cursor-pointer text-slate-700 dark:text-slate-200
                        hover:bg-brand-50 dark:hover:bg-brand-900/30 hover:text-brand-700
                        dark:hover:text-brand-300 transition-colors"
                 x-text="s"></div>
        </template>
        <div x-show="input.trim() && !filteredSuggestions.some(s => s.toLowerCase() === input.trim().toLowerCase())"
             @click="add()"
             class="px-4 py-2.5 text-sm cursor-pointer text-brand-600 dark:text-brand-400
                    hover:bg-brand-50 dark:hover:bg-brand-900/30 transition-colors border-t
                    border-slate-100 dark:border-slate-700 font-semibold">
            Criar "<span x-text="input.trim()"></span>"
        </div>
    </div>

    {{-- Hidden inputs para submit --}}
    <template x-for="(tag, i) in tags" :key="i">
        <input type="hidden" :name="`{{ $fieldName }}[${i}]`" :value="tag">
    </template>
    {{-- Garante array vazio quando sem tags --}}
    <input type="hidden" name="{{ $fieldName }}[]" value="" x-show="tags.length === 0" x-cloak>
</div>
