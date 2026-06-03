{{--
  Partial: campo de tags com autocomplete, filtro e criação inline
  Variáveis:
    $currentTags  (array)           — tags já selecionadas
    $allTags      (array/Collection) — todas as tags disponíveis
    $fieldName    (string, default 'tags')
--}}
@php
    $fieldName   = $fieldName   ?? 'tags';
    $currentTags = $currentTags ?? [];
    $allTags     = ($allTags instanceof \Illuminate\Support\Collection) ? $allTags->toArray() : (array)($allTags ?? []);
    if ($currentTags instanceof \Illuminate\Support\Collection) $currentTags = $currentTags->toArray();
@endphp

<div x-data="tagInput(JSON.parse($el.getAttribute('data-current')), JSON.parse($el.getAttribute('data-available')))"
     data-current='@json($currentTags, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)'
     data-available='@json($allTags, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)'
     class="relative"
     @click.outside="open = false">

    {{-- Chips + input --}}
    <div @click="$refs.input.focus(); open = true"
         class="flex flex-wrap gap-1.5 p-2.5 border border-slate-200 dark:border-slate-700 rounded-xl
                min-h-[42px] bg-slate-50/50 dark:bg-slate-800/50 cursor-text
                focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10 transition-all">

        <template x-for="(tag, i) in tags" :key="i">
            <span class="flex items-center gap-1 text-xs font-semibold bg-brand-50 dark:bg-brand-900/30
                         text-brand-700 dark:text-brand-300 px-2.5 py-0.5 rounded-lg select-none">
                <span x-text="tag"></span>
                <button type="button" @click.stop="remove(i)"
                        class="hover:text-rose-500 transition-colors leading-none ml-0.5 text-brand-400">×</button>
            </span>
        </template>

        <input x-ref="input"
               type="text"
               x-model="input"
               @focus="open = true"
               @keydown="key($event)"
               @input="open = true"
               placeholder="Adicionar etiqueta…"
               class="flex-1 min-w-[140px] bg-transparent text-sm outline-none
                      text-slate-700 dark:text-slate-300 placeholder-slate-400
                      dark:placeholder-slate-600 px-1 py-0.5">
    </div>

    {{-- Dropdown --}}
    <div x-show="open" x-cloak
         class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border
                border-slate-200 dark:border-slate-700 shadow-lg overflow-auto max-h-48">

        {{-- Sugestões existentes --}}
        <template x-for="tag in filtered" :key="tag">
            <div @mousedown.prevent @click="add(tag)"
                 class="flex items-center justify-between px-4 py-2.5 text-sm cursor-pointer
                        text-slate-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-brand-900/30
                        hover:text-brand-700 dark:hover:text-brand-300 transition-colors">
                <span x-text="tag"></span>
                <span class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold uppercase tracking-wider">etiqueta</span>
            </div>
        </template>

        {{-- Criar nova tag --}}
        <div x-show="showCreate"
             @mousedown.prevent @click="add()"
             class="flex items-center gap-2 px-4 py-2.5 text-sm cursor-pointer
                    text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/20
                    transition-colors border-t border-slate-100 dark:border-slate-700 font-semibold">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Criar "<span x-text="input.trim()" class="italic"></span>"
        </div>

        {{-- Estado vazio --}}
        <div x-show="filtered.length === 0 && !showCreate"
             class="px-4 py-3 text-xs text-slate-400 dark:text-slate-500 italic text-center">
            Nenhuma etiqueta criada ainda. Digite para criar.
        </div>
    </div>

    {{-- Hidden inputs para submit --}}
    <template x-for="(tag, i) in tags" :key="i">
        <input type="hidden" :name="`{{ $fieldName }}[${i}]`" :value="tag">
    </template>
    <input type="hidden" name="{{ $fieldName }}[]" value="" x-show="tags.length === 0" x-cloak>
</div>
