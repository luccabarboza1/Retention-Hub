@extends('layouts.app')
@section('title', 'Novo Cliente')
@section('header', 'Novo Registro de Cliente')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Actions / Back --}}
    <div class="flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-slate-850/50 px-4 py-3 rounded-xl border border-slate-100 dark:border-slate-800/80 backdrop-blur-sm">
        <div class="flex items-center gap-2 text-xs">
            <a href="{{ route('customers.index') }}" class="font-bold text-brand-600 dark:text-brand-400 flex items-center gap-1">
                ← Voltar a Clientes
            </a>
            <span class="text-slate-300 dark:text-slate-700">/</span>
            <span class="text-slate-500 dark:text-slate-400 font-medium">Novo Registro</span>
        </div>
    </div>

    {{-- Centered card panel --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-premium relative overflow-hidden animate-fadeIn">
        <div class="absolute top-0 left-0 w-full h-[4px] bg-gradient-to-r from-brand-600 to-accent-indigo animate-pulse"></div>
        
        <div class="mb-6">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 tracking-tight">Formulário de Cadastro</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 font-medium">Cadastre a empresa corporativa e ative os produtos base.</p>
        </div>

        <form method="POST" action="{{ route('customers.store') }}" class="space-y-6">
            @csrf

            @include('customers._form')

            @if($errors->any())
            <div class="text-xs text-rose-600 bg-rose-50 border border-rose-100 rounded-xl px-4 py-3 font-semibold">
                {{ $errors->first() }}
            </div>
            @endif

            <div class="flex gap-3 border-t border-slate-100 dark:border-slate-850 pt-5">
                <button type="submit"
                        class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-glow-brand transition-all hover:-translate-y-0.5">
                    Cadastrar Cliente
                </button>
                <a href="{{ route('customers.index') }}"
                   class="px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-655 dark:text-slate-400 font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all flex items-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
