@extends('layouts.app')
@section('title', 'Novo Cliente')
@section('header', 'Novo Registro de Cliente')

@section('content')
<div class="max-w-2xl mx-auto" x-data="wizard()">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs mb-5 px-1">
        <a href="{{ route('customers.index') }}" class="font-bold text-brand-600 dark:text-brand-400">← Clientes</a>
        <span class="text-slate-300 dark:text-slate-700">/</span>
        <span class="text-slate-500 dark:text-slate-400 font-medium">Novo Cadastro</span>
    </div>

    {{-- Progress --}}
    <div class="flex items-center gap-2 mb-6">
        @php $steps = ['Identificação','Contrato','Empresa','Soluções','Produtos']; @endphp
        @foreach($steps as $i => $label)
        <div class="flex items-center gap-2 flex-1 {{ !$loop->last ? '' : '' }}">
            <div class="flex items-center gap-2">
                <div :class="step > {{ $i }} ? 'bg-brand-600 text-white' : (step === {{ $i }} ? 'bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 border-2 border-brand-500' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600')"
                     class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-extrabold transition-all duration-200 shrink-0">
                    <span x-show="step <= {{ $i }}">{{ $i + 1 }}</span>
                    <svg x-show="step > {{ $i }}" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span :class="step === {{ $i }} ? 'text-slate-700 dark:text-slate-200 font-bold' : 'text-slate-400 dark:text-slate-600'"
                      class="text-xs hidden sm:block transition-colors">{{ $label }}</span>
            </div>
            @unless($loop->last)
            <div class="flex-1 h-px mx-2" :class="step > {{ $i }} ? 'bg-brand-400' : 'bg-slate-200 dark:bg-slate-800'"></div>
            @endunless
        </div>
        @endforeach
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('customers.store') }}">
        @csrf

        {{-- Step 0: Identificação --}}
        <div x-show="step === 0" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium animate-fadeIn space-y-5">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Identificação</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Dados principais de contato e acesso.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Razão Social / Empresa <span class="text-rose-500">*</span></label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required placeholder="ACME Corporation" class="field-input font-semibold text-slate-800 dark:text-slate-100">
                </div>
                <div>
                    <label class="field-label">Responsável Principal <span class="text-rose-500">*</span></label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" required placeholder="João Silva" class="field-input text-slate-700 dark:text-slate-200">
                </div>
                <div>
                    <label class="field-label">E-mail Principal</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="contato@empresa.com.br" class="field-input text-brand-600 dark:text-brand-400">
                </div>
                <div>
                    <label class="field-label">E-mails Relacionados</label>
                    <div x-data="emailTags(@json(old('related_emails', [])))">
                        <div class="flex flex-wrap gap-1.5 p-2.5 border border-slate-200 dark:border-slate-700 rounded-xl min-h-[42px] bg-slate-50/50 dark:bg-slate-800/50 focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/10 transition-all">
                            <template x-for="(tag, i) in tags" :key="i">
                                <span class="flex items-center gap-1 text-xs font-medium bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 px-2 py-0.5 rounded-lg">
                                    <span x-text="tag"></span>
                                    <button type="button" @click="remove(i)" class="hover:text-rose-500 transition-colors leading-none">×</button>
                                </span>
                            </template>
                            <input type="text" x-model="input" @keydown="key($event)" @blur="add()"
                                   placeholder="email + Enter" class="flex-1 min-w-[160px] bg-transparent text-sm outline-none text-slate-700 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-600 px-1 py-0.5">
                        </div>
                        <template x-for="(tag, i) in tags" :key="i">
                            <input type="hidden" :name="`related_emails[${i}]`" :value="tag">
                        </template>
                        <p class="text-[10px] text-slate-400 dark:text-slate-600 mt-1">Pressione Enter ou Tab após cada e-mail</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 1: Contrato --}}
        <div x-show="step === 1" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium animate-fadeIn space-y-5">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Contrato & Financeiro</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Informações de plano, valor e datas do contrato.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Tier combobox --}}
                <div x-data="combobox(@json($tiers->values()), '{{ old('tier') }}')" class="relative" @click.outside="open = false">
                    <label class="field-label">Tier</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input="filter()" @focus="open = true"
                               @keydown.arrow-down.prevent="nav(1)" @keydown.arrow-up.prevent="nav(-1)"
                               @keydown.enter.prevent="confirm()" @keydown.escape="open = false"
                               placeholder="Ex: Gold, VIP…" class="field-input pr-8">
                        <button type="button" @click="open = !open" tabindex="-1" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <input type="hidden" name="tier" x-model="value">
                    <div x-show="open && filtered.length" x-cloak class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-auto max-h-44">
                        <template x-for="(opt, i) in filtered" :key="opt">
                            <div @click="select(opt)" :class="hi === i ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50'" class="px-4 py-2.5 text-sm cursor-pointer transition-colors" x-text="opt"></div>
                        </template>
                    </div>
                </div>

                {{-- Plano combobox --}}
                <div x-data="combobox(@json($plans->values()), '{{ old('plan_name') }}')" class="relative" @click.outside="open = false">
                    <label class="field-label">Plano Contratado</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input="filter()" @focus="open = true"
                               @keydown.arrow-down.prevent="nav(1)" @keydown.arrow-up.prevent="nav(-1)"
                               @keydown.enter.prevent="confirm()" @keydown.escape="open = false"
                               placeholder="Ex: Host Pro, Talk2 Basic…" class="field-input pr-8">
                        <button type="button" @click="open = !open" tabindex="-1" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <input type="hidden" name="plan_name" x-model="value">
                    <div x-show="open && filtered.length" x-cloak class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-auto max-h-44">
                        <template x-for="(opt, i) in filtered" :key="opt">
                            <div @click="select(opt)" :class="hi === i ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50'" class="px-4 py-2.5 text-sm cursor-pointer transition-colors" x-text="opt"></div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="field-label">MRR (R$)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 dark:text-slate-500 pointer-events-none">R$</span>
                        <input type="number" name="monthly_fee" value="{{ old('monthly_fee') }}" min="0" step="0.01" placeholder="0,00"
                               class="field-input pl-10 font-mono font-bold text-slate-800 dark:text-slate-100">
                    </div>
                </div>

                {{-- Canal combobox --}}
                <div x-data="combobox(@json($channels->values()), '{{ old('channel_type') }}')" class="relative" @click.outside="open = false">
                    <label class="field-label">Canal de Aquisição</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input="filter()" @focus="open = true"
                               @keydown.arrow-down.prevent="nav(1)" @keydown.arrow-up.prevent="nav(-1)"
                               @keydown.enter.prevent="confirm()" @keydown.escape="open = false"
                               placeholder="Ex: Inbound, Outbound…" class="field-input pr-8">
                        <button type="button" @click="open = !open" tabindex="-1" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <input type="hidden" name="channel_type" x-model="value">
                    <div x-show="open && filtered.length" x-cloak class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-auto max-h-44">
                        <template x-for="(opt, i) in filtered" :key="opt">
                            <div @click="select(opt)" :class="hi === i ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50'" class="px-4 py-2.5 text-sm cursor-pointer transition-colors" x-text="opt"></div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="field-label">Data de Contratação</label>
                    <input type="date" name="contracted_at" value="{{ old('contracted_at') }}" class="field-input dark:text-slate-200">
                </div>
                <div>
                    <label class="field-label">Data de Cancelamento</label>
                    <input type="date" name="canceled_at" value="{{ old('canceled_at') }}" class="field-input text-rose-600 dark:text-rose-400">
                </div>
            </div>
        </div>

        {{-- Step 2: Empresa --}}
        <div x-show="step === 2" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium animate-fadeIn space-y-5">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Dados da Empresa</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Porte corporativo, segmento e presença digital.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Segmento combobox --}}
                <div x-data="combobox(@json($segments->values()), '{{ old('segment') }}')" class="relative" @click.outside="open = false">
                    <label class="field-label">Segmento de Atuação</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input="filter()" @focus="open = true"
                               @keydown.arrow-down.prevent="nav(1)" @keydown.arrow-up.prevent="nav(-1)"
                               @keydown.enter.prevent="confirm()" @keydown.escape="open = false"
                               placeholder="Ex: E-commerce, SaaS…" class="field-input pr-8">
                        <button type="button" @click="open = !open" tabindex="-1" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <input type="hidden" name="segment" x-model="value">
                    <div x-show="open && filtered.length" x-cloak class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-auto max-h-44">
                        <template x-for="(opt, i) in filtered" :key="opt">
                            <div @click="select(opt)" :class="hi === i ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50'" class="px-4 py-2.5 text-sm cursor-pointer transition-colors" x-text="opt"></div>
                        </template>
                    </div>
                </div>

                {{-- Porte combobox --}}
                <div x-data="combobox(@json($sizes->values()), '{{ old('company_size') }}')" class="relative" @click.outside="open = false">
                    <label class="field-label">Porte Corporativo</label>
                    <div class="relative">
                        <input type="text" x-model="query" @input="filter()" @focus="open = true"
                               @keydown.arrow-down.prevent="nav(1)" @keydown.arrow-up.prevent="nav(-1)"
                               @keydown.enter.prevent="confirm()" @keydown.escape="open = false"
                               placeholder="Ex: PME, Enterprise…" class="field-input pr-8">
                        <button type="button" @click="open = !open" tabindex="-1" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <input type="hidden" name="company_size" x-model="value">
                    <div x-show="open && filtered.length" x-cloak class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-auto max-h-44">
                        <template x-for="(opt, i) in filtered" :key="opt">
                            <div @click="select(opt)" :class="hi === i ? 'bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50'" class="px-4 py-2.5 text-sm cursor-pointer transition-colors" x-text="opt"></div>
                        </template>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="field-label">Seguidores no Instagram</label>
                    <input type="number" name="instagram_followers_count" value="{{ old('instagram_followers_count') }}" min="0" placeholder="0"
                           class="field-input font-mono text-slate-700 dark:text-slate-200">
                </div>
            </div>
        </div>

        {{-- Step 3: Soluções --}}
        <div x-show="step === 3" x-cloak class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium animate-fadeIn space-y-5">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Soluções Ativas</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Produtos e funcionalidades habilitadas para este cliente.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach([['has_chatbot','Chatbot Ativo','brand'],['has_ai','Inteligência Artificial','purple'],['has_implementation','Implementação Assistida','emerald']] as [$name,$label,$color])
                <label class="border rounded-xl p-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all flex items-center gap-3 select-none"
                       x-data="{ checked: {{ old($name) ? 'true' : 'false' }} }"
                       :class="checked ? 'border-{{ $color }}-500 dark:border-{{ $color }}-600 bg-{{ $color }}-50/20 dark:bg-{{ $color }}-950/15' : 'bg-slate-50/30 dark:bg-slate-900/30 border-dashed border-slate-200 dark:border-slate-800'">
                    <input type="hidden" name="{{ $name }}" value="0">
                    <input type="checkbox" name="{{ $name }}" value="1" @change="checked = $el.checked" {{ old($name) ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-{{ $color }}-600">
                    <div class="min-w-0">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block leading-tight">{{ $label }}</span>
                        <span class="text-[9px] font-bold uppercase tracking-wider mt-0.5 block"
                              :class="checked ? 'text-{{ $color }}-600 dark:text-{{ $color }}-400' : 'text-slate-400'"
                              x-text="checked ? 'Habilitado' : 'Desabilitado'"></span>
                    </div>
                </label>
                @endforeach
            </div>

            @if($errors->any())
            <div class="text-xs text-rose-600 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 rounded-xl px-4 py-3 font-semibold">
                {{ $errors->first() }}
            </div>
            @endif
        </div>

        {{-- Step 4: Produtos --}}
        <div x-show="step === 4" x-cloak
             class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium animate-fadeIn space-y-4"
             x-data="{ rows: [], nextId: 0, addRow() { this.rows.push({ id: this.nextId++, ptype: '', planConfigs: @json($planConfigs->values()), planPrice: 0, attendants: 1, get total() { return this.planPrice * this.attendants; }, setPlan(name) { const p = this.planConfigs.find(c => c.plan_name === name); this.planPrice = p ? parseFloat(p.price_per_unit) : 0; } }); }, removeRow(id) { this.rows = this.rows.filter(r => r.id !== id); } }">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Produtos (opcional)</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Adicione produtos Host ou Talk2 agora ou depois pelo perfil do cliente.</p>
            </div>

            <template x-for="(row, idx) in rows" :key="row.id">
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 space-y-3 relative">
                    <button type="button" @click="removeRow(row.id)"
                            class="absolute top-3 right-3 text-slate-400 hover:text-rose-500 text-lg leading-none transition-colors">×</button>

                    {{-- Tipo --}}
                    <div>
                        <label class="field-label">Tipo</label>
                        <div class="select-wrap">
                            <select :name="`products[${idx}][product_type]`" x-model="row.ptype"
                                    @change="row.planPrice = 0; row.attendants = 1"
                                    class="field-input font-semibold" required>
                                <option value="">Selecione…</option>
                                <option value="Talk2">Talk2</option>
                                <option value="Host">Host</option>
                            </select>
                        </div>
                    </div>

                    {{-- Talk2 fields --}}
                    <div x-show="row.ptype === 'Talk2'" class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">ID da Organização</label>
                            <input type="text" :name="`products[${idx}][external_id]`" class="field-input font-mono" placeholder="ID org">
                        </div>
                        <div>
                            <label class="field-label">ID do Contrato</label>
                            <input type="text" :name="`products[${idx}][contract_identifier]`" class="field-input font-mono">
                        </div>
                        <div>
                            <label class="field-label">Plano</label>
                            <div class="select-wrap">
                                <select :name="`products[${idx}][plan_name]`" @change="row.setPlan($event.target.value)" class="field-input">
                                    <option value="">Selecione…</option>
                                    @foreach($planConfigs->where('product_type','Talk2') as $plan)
                                    <option value="{{ $plan->plan_name }}">{{ $plan->plan_name }} (R$ {{ number_format($plan->price_per_unit,2,',','.') }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Atendentes</label>
                            <input type="number" :name="`products[${idx}][attendants_count]`" x-model.number="row.attendants" min="1" class="field-input font-mono">
                        </div>
                        <div class="col-span-2 bg-brand-50 dark:bg-brand-950/20 border border-brand-100 dark:border-brand-900/40 rounded-xl px-3 py-2 flex justify-between items-center">
                            <span class="text-[10px] font-bold text-brand-700 dark:text-brand-400">Valor estimado</span>
                            <span class="text-sm font-extrabold text-brand-700 dark:text-brand-300 font-mono"
                                  x-text="'R$ ' + row.total.toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2})"></span>
                        </div>
                    </div>

                    {{-- Host fields --}}
                    <div x-show="row.ptype === 'Host'" class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">ID do Ninjato</label>
                            <input type="text" :name="`products[${idx}][external_id]`" class="field-input font-mono" placeholder="ID Ninjato">
                        </div>
                        <div>
                            <label class="field-label">ID do Contrato</label>
                            <input type="text" :name="`products[${idx}][contract_identifier]`" class="field-input font-mono">
                        </div>
                        <div>
                            <label class="field-label">Consumo (R$)</label>
                            <input type="number" :name="`products[${idx}][consumption]`" step="0.01" min="0" class="field-input font-mono">
                        </div>
                        <div>
                            <label class="field-label">Status</label>
                            <div class="select-wrap">
                                <select :name="`products[${idx}][status]`" class="field-input">
                                    <option value="ativo">Ativo</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-span-2">
                            <label class="field-label">Serviços</label>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach(['email' => '📧 Email', 'dominio' => '🌐 Domínio', 'hospedagem' => '🖥️ Hospedagem'] as $val => $label)
                                <label class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border cursor-pointer select-none
                                              bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400
                                              has-[:checked]:bg-sky-50 has-[:checked]:dark:bg-sky-950/20 has-[:checked]:border-sky-300 has-[:checked]:dark:border-sky-700 has-[:checked]:text-sky-700 has-[:checked]:dark:text-sky-300">
                                    <input type="checkbox" :name="`products[${idx}][host_services][]`" value="{{ $val }}" class="w-3.5 h-3.5 accent-sky-600">
                                    {{ $label }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <button type="button" @click="addRow()"
                    class="w-full py-3 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-400 hover:text-brand-600 hover:border-brand-300 dark:hover:border-brand-700 transition-all">
                + Adicionar Produto
            </button>
        </div>

        {{-- Navigation --}}
        <div class="flex justify-between mt-5">
            <button type="button" @click="prev()" x-show="step > 0"
                    class="px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                ← Voltar
            </button>
            <div x-show="step === 0"></div>

            <div class="flex gap-3">
                <a href="{{ route('customers.index') }}"
                   class="px-5 py-2.5 text-slate-400 dark:text-slate-600 text-xs font-semibold rounded-xl hover:text-slate-600 dark:hover:text-slate-400 transition-all flex items-center">
                    Cancelar
                </a>
                <button type="button" @click="next()" x-show="step < 4"
                        class="btn-primary">
                    Próximo →
                </button>
                <button type="submit" x-show="step === 4"
                        class="btn-primary">
                    Cadastrar Cliente
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function wizard() {
    return {
        step: 0,
        next() { if (this.step < 4) this.step++; },
        prev() { if (this.step > 0) this.step--; }
    };
}
</script>
@endsection
