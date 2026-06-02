@extends('layouts.app')
@section('title', 'Configurações')
@section('header', 'Configurações')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-2 text-xs mb-1 px-1">
        <span class="font-bold text-slate-600 dark:text-slate-300">Configurações</span>
        <span class="text-slate-300 dark:text-slate-700">/</span>
        <span class="text-slate-400 dark:text-slate-500 font-medium">Geral</span>
    </div>

    {{-- Nav entre páginas de settings --}}
    <div class="flex gap-2">
        <a href="{{ route('settings.general') }}"
           class="text-xs font-bold px-4 py-2 rounded-xl {{ request()->routeIs('settings.general') ? 'bg-brand-600 text-white shadow-glow-brand' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }} transition-all">
            Geral
        </a>
        <a href="{{ route('settings.products') }}"
           class="text-xs font-bold px-4 py-2 rounded-xl {{ request()->routeIs('settings.products') ? 'bg-brand-600 text-white shadow-glow-brand' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }} transition-all">
            Planos de Produto
        </a>
    </div>

    <form method="POST" action="{{ route('settings.general.update') }}" class="space-y-4">
        @csrf

        {{-- Integração n8n --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Integração n8n</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Configure os webhooks de integração com o n8n.</p>
            </div>

            <div class="space-y-1.5">
                <label class="field-label">URL do Lookup de Cliente
                    <span class="ml-1 text-[9px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-1.5 py-0.5 rounded uppercase tracking-wider">Preenchimento automático</span>
                </label>
                <input type="url" name="customer_lookup_url"
                       value="{{ old('customer_lookup_url', $settings['customer_lookup_url']) }}"
                       placeholder="https://n8n-workflows.umbler.com/webhook/v1/retention-hub/customer-lookup"
                       class="field-input font-mono text-sm">
                <p class="text-[10px] text-slate-400 dark:text-slate-500">
                    Endpoint POST que recebe <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">{"email": "..."}</code>
                    e retorna os dados do cliente em JSON.
                    Deixe em branco para desativar o preenchimento automático.
                </p>
            </div>

            @if($errors->any())
            <div class="text-xs text-rose-600 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 rounded-xl px-4 py-3 font-semibold">
                {{ $errors->first() }}
            </div>
            @endif
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary">Salvar Configurações</button>
        </div>
    </form>

</div>
@endsection
