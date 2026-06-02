@extends('layouts.app')
@section('title', $customer->company_name . ' — Histórico')
@section('header', 'Histórico de Atendimentos')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Actions / Back --}}
    <div class="flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-slate-850/50 px-4 py-3 rounded-xl border border-slate-100 dark:border-slate-800/80 backdrop-blur-sm">
        <div class="flex items-center gap-2 text-xs">
            <a href="{{ route('customers.show', $customer) }}" class="font-bold text-brand-600 dark:text-brand-400 flex items-center gap-1">
                ← Voltar ao Perfil
            </a>
            <span class="text-slate-300 dark:text-slate-700">/</span>
            <span class="text-slate-500 dark:text-slate-400 font-medium">Histórico de Ouvidoria</span>
            <span class="text-slate-300 dark:text-slate-700">/</span>
            <span class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">{{ $customer->company_name }}</span>
        </div>
        
        <a href="{{ route('cards.create', ['customer_id' => $customer->id]) }}"
           class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-glow-brand transition-all flex items-center gap-1 hover:-translate-y-0.5">
            + Novo Card
        </a>
    </div>

    {{-- Timeline Tracker --}}
    <div class="space-y-4 relative before:absolute before:left-6 before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-200/60 dark:before:bg-slate-800/60 z-10">
        @forelse($cards as $card)
        <div class="relative pl-12 animate-fadeIn">
            {{-- Glowing Dot Anchor --}}
            <div class="absolute left-[19px] top-4 w-[10px] h-[10px] rounded-full bg-brand-600 shadow-glow-brand ring-4 ring-white dark:ring-slate-950 shrink-0 z-20 animate-pulse"></div>
            
            <a href="{{ route('cards.show', $card) }}"
               class="block bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-sm hover:shadow-premium-hover dark:hover:border-brand-600 hover:-translate-y-0.5 transition-all duration-300 group">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-[9px] font-bold font-mono text-slate-400 dark:text-slate-500 uppercase">#{{ $card->id }}</span>
                            <span class="text-[9px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold px-2 py-0.5 rounded-full uppercase">
                                {{ $card->status }}
                            </span>
                        </div>
                        <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-snug group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                            {{ $card->contact_reason ?? 'Sem motivo geral registrado' }}
                        </h4>
                        @if($card->ombudsman_agent)
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mt-1">Ouvidor Responsável: <span class="font-bold text-slate-600 dark:text-slate-400">{{ $card->ombudsman_agent }}</span></p>
                        @endif
                    </div>
                    <div class="shrink-0 text-left md:text-right">
                        <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-550 bg-slate-50 dark:bg-slate-800/60 border border-slate-200/40 dark:border-slate-700/60 px-2.5 py-1 rounded-lg">
                            📅 {{ $card->started_at->format('d/m/Y') }}
                        </span>
                        <span class="text-xs text-brand-600 dark:text-brand-400 font-bold block mt-2 group-hover:translate-x-0.5 transition-transform">
                            Acessar Workspace →
                        </span>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-12 text-center shadow-premium">
            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-lg mx-auto mb-3">🗃️</div>
            <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Nenhum card registrado para este cliente</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 max-w-xs mx-auto">Você pode registrar uma ocorrência de ouvidoria clicando no botão "+ Novo Card" no canto superior.</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
