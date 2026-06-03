{{-- Partial de formulário reestruturado com sistema de design premium --}}
@php $c = $customer ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Seção: Identificação --}}
    <div class="md:col-span-2 border-b border-slate-100 dark:border-slate-800 pb-2 mb-1">
        <h4 class="text-xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider">👤 Identificação da Conta</h4>
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Razão Social / Empresa <span class="text-rose-500">*</span></label>
        <input type="text" name="company_name" value="{{ old('company_name', $c?->company_name) }}" required
               placeholder="Ex: ACME Corporation"
               class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-slate-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-800 transition-all font-semibold text-slate-800 dark:text-slate-100 dark:placeholder-slate-500">
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Responsável Principal <span class="text-rose-500">*</span></label>
        <input type="text" name="client_name" value="{{ old('client_name', $c?->client_name) }}" required
               placeholder="Ex: João Silva"
               class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-slate-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-800 transition-all font-medium text-slate-700 dark:text-slate-300 dark:placeholder-slate-500">
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">E-mail Principal de Contato</label>
        <input type="email" name="email" value="{{ old('email', $c?->email) }}"
               placeholder="contato@empresa.com.br"
               class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-slate-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-800 transition-all text-brand-600 dark:text-brand-400 font-medium dark:placeholder-slate-500">
    </div>

    @include('customers._managed_combobox', [
        'type' => 'segments',
        'name' => 'segment',
        'label' => 'Segmento de Atuação',
        'placeholder' => 'Selecione…',
        'options' => $segments,
        'old' => old('segment', $c?->segment),
        'freeText' => false
    ])

    {{-- Seção: Contrato --}}
    <div class="md:col-span-2 border-b border-slate-100 dark:border-slate-800 pb-2 mb-1 mt-3">
        <h4 class="text-xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider">📄 Contrato e Assinatura</h4>
    </div>


    @include('customers._managed_combobox', [
        'type' => 'tiers',
        'name' => 'tier',
        'label' => 'Categoria / Tier',
        'placeholder' => 'Selecione…',
        'options' => $tiers,
        'old' => old('tier', $c?->tier),
        'freeText' => false
    ])

    <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Valor Recorrente Mensal (MRR)</label>
        <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 dark:text-slate-500">R$</span>
            <input type="number" name="monthly_fee" value="{{ old('monthly_fee', $c?->monthly_fee) }}" min="0" step="0.01" placeholder="0,00"
                   class="w-full border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-slate-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-800 transition-all font-mono font-bold text-slate-800 dark:text-slate-250 dark:placeholder-slate-550">
        </div>
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Canal de Aquisição / Venda</label>
        <input type="text" name="channel_type" value="{{ old('channel_type', $c?->channel_type) }}" placeholder="Ex: Outbound, Inbound, Parcerias"
               class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-slate-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-800 transition-all dark:text-slate-300 dark:placeholder-slate-500">
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Data de Contratação</label>
        <input type="date" name="contracted_at" value="{{ old('contracted_at', $c?->contracted_at?->format('Y-m-d')) }}"
               class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-slate-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-800 transition-all dark:text-slate-300">
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Data de Cancelamento</label>
        <input type="date" name="canceled_at" value="{{ old('canceled_at', $c?->canceled_at?->format('Y-m-d')) }}"
               class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-slate-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-800 transition-all text-rose-600 dark:text-rose-400">
    </div>

    {{-- Seção: Outros --}}
    <div class="md:col-span-2 border-b border-slate-100 dark:border-slate-800 pb-2 mb-1 mt-3">
        <h4 class="text-xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider">🏢 Dados Complementares</h4>
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Porte Corporativo</label>
        <input type="text" name="company_size" value="{{ old('company_size', $c?->company_size) }}" placeholder="Ex: PME, Enterprise, Mid-Market"
               class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-slate-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-800 transition-all dark:text-slate-300 dark:placeholder-slate-500">
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Seguidores do Instagram</label>
        <input type="number" name="instagram_followers_count" value="{{ old('instagram_followers_count', $c?->instagram_followers_count) }}" min="0" placeholder="0"
               class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-slate-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-800 transition-all font-mono dark:text-slate-300 dark:placeholder-slate-500">
    </div>

    {{-- Tags --}}
    <div class="md:col-span-2 mt-3">
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Etiquetas / Tags</label>
        <div class="relative" x-data="tagInput(JSON.parse($el.getAttribute('data-tags')), JSON.parse($el.getAttribute('data-suggestions')))" 
             data-tags="{{ json_encode(old('tags', $c?->tags ?? [])) }}"
             data-suggestions="{{ json_encode($allTags ?? []) }}"
             @click.away="open = false">
            <div class="flex flex-wrap gap-1.5 p-2.5 border border-slate-200 dark:border-slate-700 rounded-xl min-h-[42px] bg-slate-50/50 dark:bg-slate-800/50 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10 transition-all">
                <template x-for="(tag, i) in tags" :key="i">
                    <span class="flex items-center gap-1 text-xs font-medium bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 px-2 py-0.5 rounded-lg">
                        <span x-text="tag"></span>
                        <button type="button" @click="remove(i)" class="hover:text-rose-500 transition-colors leading-none">×</button>
                    </span>
                </template>
                <input type="text" x-model="input" @keydown="key($event)" 
                       @focus="open = true"
                       @blur="setTimeout(() => { add(); open = false; }, 200)"
                       placeholder="Adicionar etiqueta..." class="flex-1 min-w-[160px] bg-transparent text-sm outline-none text-slate-700 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-650 px-1 py-0.5">
            </div>

            {{-- Autocomplete Dropdown --}}
            <div x-show="open && filteredSuggestions.length > 0" 
                 class="absolute left-0 right-0 z-50 mt-1 max-h-60 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-premium py-1"
                 x-cloak>
                <template x-for="tag in filteredSuggestions" :key="tag">
                    <button type="button" 
                            @mousedown.prevent
                            @click="add(tag)" 
                            class="w-full text-left px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center justify-between">
                        <span x-text="tag"></span>
                        <span class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-wider font-bold">Etiqueta Existente</span>
                    </button>
                </template>
            </div>

            <template x-for="(tag, i) in tags" :key="i">
                <input type="hidden" :name="`tags[${i}]`" :value="tag">
            </template>
            <p class="text-[10px] text-slate-400 dark:text-slate-650 mt-1">Pressione Enter, Tab ou Vírgula após cada etiqueta</p>
        </div>
    </div>

</div>
