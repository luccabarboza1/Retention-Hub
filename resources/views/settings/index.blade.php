@extends('layouts.app')
@section('title', 'Configurações')
@section('header', 'Configurações')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <p class="text-xs text-slate-400 dark:text-slate-500 px-1">Gerencie as configurações do sistema.</p>

    <div class="flex flex-col gap-3">

        {{-- Geral --}}
        <a href="{{ route('settings.general') }}"
           class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium hover:shadow-premium-hover hover:border-brand-300 dark:hover:border-brand-800 transition-all duration-200 flex items-center gap-4 min-h-[80px]">
            <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 group-hover:bg-brand-100 dark:group-hover:bg-brand-900/50 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">URL do Lookup de Cliente</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Configure o endpoint n8n para preenchimento automático de dados do cliente.</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 group-hover:text-brand-400 transition-colors shrink-0 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        {{-- Planos de Produto --}}
        <a href="{{ route('settings.products') }}"
           class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium hover:shadow-premium-hover hover:border-brand-300 dark:hover:border-brand-800 transition-all duration-200 flex items-center gap-4 min-h-[80px]">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/40 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">Planos de Produto</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Configure os planos Talk2 (Enterprise, Professional) e seus preços por atendente.</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 group-hover:text-brand-400 transition-colors shrink-0 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        {{-- API Docs --}}
        <a href="/docs/api" target="_blank"
           class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium hover:shadow-premium-hover hover:border-brand-300 dark:hover:border-brand-800 transition-all duration-200 flex items-center gap-4 min-h-[80px]">
            <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/20 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0 group-hover:bg-sky-100 dark:group-hover:bg-sky-900/40 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors flex items-center gap-1.5">
                    API Docs
                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Documentação interativa de todos os endpoints REST da API.</p>
            </div>
            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 group-hover:text-brand-400 transition-colors shrink-0 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

    </div>
</div>
@endsection
