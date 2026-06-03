{{--
  Partial: formulário dinâmico de produto
  Variáveis esperadas:
    $action        — URL do form
    $method        — POST (store) ou PATCH (update)
    $product       — (opcional) Product para pré-preencher
    $planConfigs   — Collection de ProductPlanConfig
    $cancelAlpine  — expressão Alpine para fechar (ex: "editing = false")
--}}
@php
$isEdit = isset($product);
$type   = old('product_type', $product->product_type ?? '');
$talk2Plans = $planConfigs->where('product_type', 'Talk2')->values();
@endphp

<form method="POST" action="{{ $action }}"
      x-data="{
          ptype: '{{ $type }}',
          planConfigs: [],
          planPrice: 0,
          attendants: {{ old('attendants_count', $product->attendants_count ?? 1) }},
          get total() { return this.planPrice * this.attendants; },
          setPlan(name) {
              const p = this.planConfigs.find(c => c.plan_name === name);
              this.planPrice = p ? parseFloat(p.price_per_unit) : 0;
          }
      }"
      x-init="planConfigs = JSON.parse($el.getAttribute('data-plans'))"
      data-plans='@json($talk2Plans)'
      class="space-y-3">
    @csrf
    @if($isEdit)
        @method('PATCH')
    @endif

    {{-- Tipo do produto (fixo no edit) --}}
    @if(!$isEdit)
    <div @change="ptype = $event.detail; planPrice = 0; attendants = 1">
        @include('cards._managed_combobox', [
            'name' => 'product_type',
            'label' => 'Tipo de Produto',
            'placeholder' => 'Selecione…',
            'options' => ['Talk2', 'Host'],
            'old' => old('product_type', $product->product_type ?? ''),
            'freeText' => false
        ])
    </div>
    @endif

    {{-- ── TALK2 ── --}}
    <div x-show="ptype === 'Talk2'" x-cloak class="space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="field-label">ID da Organização (External ID) <span class="text-rose-500">*</span></label>
                <input type="text" name="external_id" value="{{ old('external_id', $product->external_id ?? '') }}"
                       placeholder="ID da org no Talk2" class="field-input font-mono">
            </div>
            <div>
                <label class="field-label">ID do Contrato</label>
                <input type="text" name="contract_identifier" value="{{ old('contract_identifier', $product->contract_identifier ?? '') }}"
                       placeholder="Identificador do contrato" class="field-input font-mono">
            </div>
            <div @change="setPlan($event.detail)">
                @include('cards._managed_combobox', [
                    'name' => 'plan_name',
                    'label' => 'Plano',
                    'placeholder' => 'Selecione…',
                    'options' => $talk2Plans->pluck('plan_name')->toArray(),
                    'old' => old('plan_name', $product->plan_name ?? ''),
                    'freeText' => false
                ])
            </div>
            <div>
                <label class="field-label">Quantidade de Atendentes <span class="text-rose-500">*</span></label>
                <input type="number" name="attendants_count" x-model.number="attendants" min="1"
                       value="{{ old('attendants_count', $product->attendants_count ?? '') }}"
                       class="field-input font-mono">
            </div>
        </div>

        {{-- Soluções --}}
        <div>
            <label class="field-label">Soluções Habilitadas</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-1 mb-2">
                @foreach([
                    ['has_chatbot', 'Chatbot Ativo', 'brand', $isEdit ? $product->has_chatbot : false],
                    ['has_ai', 'Inteligência Artificial', 'purple', $isEdit ? $product->has_ai : false],
                    ['has_implementation', 'Implementação Assistida', 'emerald', $isEdit ? $product->has_implementation : false]
                ] as [$name, $label, $color, $checkedVal])
                @php $isChecked = old($name, $checkedVal); @endphp
                <label class="border rounded-xl p-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all flex items-center gap-2 select-none"
                       x-data="{ checked: {{ $isChecked ? 'true' : 'false' }} }"
                       :class="checked ? 'border-{{ $color }}-500 dark:border-{{ $color }}-600 bg-{{ $color }}-50/20 dark:bg-{{ $color }}-950/15' : 'bg-slate-50/30 dark:bg-slate-900/30 border-dashed border-slate-200 dark:border-slate-800'">
                    <input type="hidden" name="{{ $name }}" value="0">
                    <input type="checkbox" name="{{ $name }}" value="1" @change="checked = $el.checked" {{ $isChecked ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-{{ $color }}-600">
                    <div class="min-w-0 font-sans">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block leading-tight">{{ $label }}</span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        {{-- Total calculado --}}
        <div class="bg-brand-50 dark:bg-brand-900/20 border border-brand-100 dark:border-brand-900/40 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-xs font-bold text-brand-700 dark:text-brand-400">Valor mensal estimado</span>
            <span class="text-base font-extrabold text-brand-700 dark:text-brand-300 font-mono"
                  x-text="'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2})">
                R$ 0,00
            </span>
        </div>
    </div>

    {{-- ── HOST ── --}}
    <div x-show="ptype === 'Host'" x-cloak class="space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="field-label">ID do Ninjato (External ID) <span class="text-rose-500">*</span></label>
                <input type="text" name="external_id" value="{{ old('external_id', $product->external_id ?? '') }}"
                       placeholder="ID no Ninjato" class="field-input font-mono">
            </div>
            <div>
                <label class="field-label">ID do Contrato</label>
                <input type="text" name="contract_identifier" value="{{ old('contract_identifier', $product->contract_identifier ?? '') }}"
                       placeholder="Identificador do contrato" class="field-input font-mono">
            </div>
            <div class="md:col-span-2">
                <label class="field-label">Consumo (R$)</label>
                <input type="number" name="consumption" step="0.01" min="0"
                       value="{{ old('consumption', $product->consumption ?? '') }}"
                       class="field-input font-mono">
            </div>
        </div>
        {{-- Serviços Host --}}
        <div>
            <label class="field-label">Serviços Contratados</label>
            @php $currentServices = old('host_services', $product->host_services ?? []); @endphp
            <div class="flex flex-wrap gap-3 mt-1">
                @foreach(['email' => '📧 Email Profissional', 'dominio' => '🌐 Domínio', 'hospedagem' => '🖥️ Hospedagem'] as $val => $label)
                <label class="flex items-center gap-2 cursor-pointer select-none px-3 py-2 rounded-xl border transition-all
                              {{ in_array($val, $currentServices) ? 'bg-sky-50 dark:bg-sky-950/20 border-sky-300 dark:border-sky-700 text-sky-700 dark:text-sky-300' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}"
                       x-data="{ checked: {{ in_array($val, $currentServices) ? 'true' : 'false' }} }"
                       :class="checked ? 'bg-sky-50 dark:bg-sky-950/20 border-sky-300 dark:border-sky-700 text-sky-700 dark:text-sky-300' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400'">
                    <input type="checkbox" name="host_services[]" value="{{ $val }}"
                           @change="checked = $el.checked"
                           {{ in_array($val, $currentServices) ? 'checked' : '' }}
                           class="w-3.5 h-3.5 accent-sky-600">
                    <span class="text-xs font-semibold">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Status e data (comum) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3" x-show="ptype !== ''" x-cloak>
        <div>
            @include('cards._managed_combobox', [
                'name' => 'status',
                'label' => 'Status',
                'placeholder' => 'Selecione…',
                'options' => ['ativo', 'cancelado'],
                'old' => old('status', $product->status ?? 'ativo'),
                'freeText' => false
            ])
        </div>
        <div>
            <label class="field-label">Data de Criação (Externa)</label>
            <input type="date" name="external_created_at"
                   value="{{ old('external_created_at', isset($product) && $product->external_created_at ? $product->external_created_at->format('Y-m-d') : '') }}"
                   class="field-input dark:text-slate-200">
        </div>
    </div>

    {{-- Botões --}}
    <div class="flex gap-2 pt-1" x-show="ptype !== ''">
        <button type="submit" class="btn-primary text-xs px-4 py-2">
            {{ $isEdit ? 'Salvar' : 'Adicionar Produto' }}
        </button>
        <button type="button" @click="{{ $cancelAlpine }}"
                class="text-xs px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
            Cancelar
        </button>
    </div>
</form>
