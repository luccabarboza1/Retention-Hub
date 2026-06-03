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

    <div>
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Plano Contratado</label>
        <input type="text" name="plan_name" value="{{ old('plan_name', $c?->plan_name) }}" placeholder="Ex: Enterprise, Pro, Growth"
               class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 outline-none bg-slate-50/50 dark:bg-slate-800/50 focus:bg-white dark:focus:bg-slate-800 transition-all font-semibold text-slate-750 dark:text-slate-350 dark:placeholder-slate-500">
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
</div>
