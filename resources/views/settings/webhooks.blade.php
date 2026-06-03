@extends('layouts.app')
@section('title', 'Webhooks')
@section('header', 'Configurações')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ adding: false }">

    <div class="flex items-center gap-2 text-xs mb-1 px-1">
        <a href="{{ route('settings.index') }}" class="font-bold text-brand-600 dark:text-brand-400">← Configurações</a>
        <span class="text-slate-300 dark:text-slate-700">/</span>
        <span class="text-slate-400 dark:text-slate-500 font-medium">Webhooks</span>
    </div>

    {{-- Alerta: secret exibido apenas uma vez --}}
    @if(session('webhook_created'))
    @php $created = session('webhook_created'); @endphp
    <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl p-5 space-y-3 animate-fadeIn"
         x-data="{ copied: false }">
        <div class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-800 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0">✓</span>
            <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">
                Webhook <strong>{{ $created['name'] }}</strong> criado com sucesso!
            </p>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-800/40 rounded-xl p-3">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">
                🔑 Secret — copie agora, não será exibido novamente
            </p>
            <div class="flex items-center gap-2">
                <code class="flex-1 text-xs font-mono text-slate-700 dark:text-slate-300 break-all">{{ $created['secret'] }}</code>
                <button type="button"
                        @click="navigator.clipboard.writeText('{{ $created['secret'] }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="shrink-0 px-3 py-1.5 text-[10px] font-bold rounded-lg transition-all"
                        :class="copied ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'">
                    <span x-show="!copied">Copiar</span>
                    <span x-show="copied" x-cloak>✓ Copiado</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5 shadow-premium flex items-center justify-between">
        <div>
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Webhooks</h2>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Receba notificações em tempo real quando eventos ocorrerem.</p>
        </div>
        <button @click="adding = !adding" class="btn-primary text-xs px-4 py-2.5">+ Novo Webhook</button>
    </div>

    {{-- Form: Novo webhook --}}
    <div x-show="adding" x-cloak
         class="bg-white dark:bg-slate-900 rounded-2xl border border-brand-200 dark:border-brand-900/60 p-6 shadow-premium animate-fadeIn">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">Criar Webhook</h3>
        <form method="POST" action="{{ route('settings.webhooks.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Nome <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" placeholder="Ex: n8n eventos" required class="field-input">
                </div>
                <div class="md:col-span-2">
                    <label class="field-label">URL de destino <span class="text-rose-500">*</span></label>
                    <input type="url" name="url" placeholder="https://seu-n8n.com/webhook/..." required class="field-input font-mono text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="field-label">Eventos <span class="text-rose-500">*</span></label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach(['*' => 'Todos os eventos', 'customer.created' => 'Cliente criado', 'customer.updated' => 'Cliente atualizado', 'card.created' => 'Card criado', 'card.updated' => 'Card atualizado', 'card.finished' => 'Card encerrado'] as $val => $label)
                        <label class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border cursor-pointer select-none transition-all
                                      bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400
                                      has-[:checked]:bg-brand-50 has-[:checked]:dark:bg-brand-900/20 has-[:checked]:border-brand-400 has-[:checked]:dark:border-brand-700 has-[:checked]:text-brand-700 has-[:checked]:dark:text-brand-300">
                            <input type="checkbox" name="trigger_types[]" value="{{ $val }}" class="w-3.5 h-3.5 accent-brand-600">
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="field-label">Descrição</label>
                    <input type="text" name="description" placeholder="Opcional" class="field-input">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked id="new_active" class="w-4 h-4 rounded accent-brand-600">
                    <label for="new_active" class="text-xs font-semibold text-slate-600 dark:text-slate-400 cursor-pointer">Ativo</label>
                </div>
            </div>
            @if($errors->any())
            <p class="text-xs text-rose-600 dark:text-rose-400 font-semibold">{{ $errors->first() }}</p>
            @endif
            <div class="flex gap-2 pt-1">
                <button type="submit" class="btn-primary text-xs px-5 py-2.5">Criar Webhook</button>
                <button type="button" @click="adding = false"
                        class="text-xs px-5 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-500 font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    Cancelar
                </button>
            </div>
        </form>
    </div>

    {{-- Lista de webhooks --}}
    @if($webhooks->isEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-8 text-center shadow-premium">
        <p class="text-sm text-slate-400 dark:text-slate-500">Nenhum webhook configurado.</p>
        <p class="text-xs text-slate-300 dark:text-slate-600 mt-1">Clique em "+ Novo Webhook" para começar.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($webhooks as $wh)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-premium overflow-hidden"
             x-data="{ editing: false }">

            {{-- View mode --}}
            <div x-show="!editing" class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <span class="text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ $wh->name }}</span>
                            @if($wh->is_active)
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/40 uppercase tracking-wider">Ativo</span>
                            @else
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-500 border border-slate-200 dark:border-slate-700 uppercase tracking-wider">Inativo</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-mono truncate">{{ $wh->url }}</p>
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach($wh->trigger_types ?? [] as $t)
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 border border-brand-100 dark:border-brand-800/40">
                                {{ $t === '*' ? 'Todos os eventos' : $t }}
                            </span>
                            @endforeach
                        </div>
                        @if($wh->description)
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1.5">{{ $wh->description }}</p>
                        @endif
                    </div>
                    <div class="flex gap-1 shrink-0">
                        <button @click="editing = true"
                                class="text-[10px] font-bold text-slate-400 hover:text-brand-600 px-2.5 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                            ✏️ Editar
                        </button>
                        <form method="POST" action="{{ route('settings.webhooks.destroy', $wh) }}"
                              data-confirm-title="Remover webhook"
                              data-confirm-msg="{{ $wh->name }} — esta ação é irreversível."
                              @submit.prevent="$dispatch('open-confirm', { title: $el.dataset.confirmTitle, message: $el.dataset.confirmMsg, form: $el })">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-[10px] font-bold text-slate-400 hover:text-rose-600 px-2.5 py-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Edit mode --}}
            <div x-show="editing" x-cloak class="p-5 border-t-4 border-brand-500">
                <form method="POST" action="{{ route('settings.webhooks.update', $wh) }}" class="space-y-3">
                    @csrf @method('PATCH')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="field-label">Nome</label>
                            <input type="text" name="name" value="{{ $wh->name }}" required class="field-input">
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">URL</label>
                            <input type="url" name="url" value="{{ $wh->url }}" required class="field-input font-mono text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Eventos</label>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach(['*' => 'Todos os eventos', 'customer.created' => 'Cliente criado', 'customer.updated' => 'Cliente atualizado', 'card.created' => 'Card criado', 'card.updated' => 'Card atualizado', 'card.finished' => 'Card encerrado'] as $val => $label)
                                <label class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border cursor-pointer select-none transition-all
                                              bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400
                                              has-[:checked]:bg-brand-50 has-[:checked]:dark:bg-brand-900/20 has-[:checked]:border-brand-400 has-[:checked]:dark:border-brand-700 has-[:checked]:text-brand-700 has-[:checked]:dark:text-brand-300">
                                    <input type="checkbox" name="trigger_types[]" value="{{ $val }}"
                                           {{ in_array($val, $wh->trigger_types ?? []) ? 'checked' : '' }}
                                           class="w-3.5 h-3.5 accent-brand-600">
                                    {{ $label }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="field-label">Descrição</label>
                            <input type="text" name="description" value="{{ $wh->description }}" class="field-input">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ $wh->is_active ? 'checked' : '' }}
                                   id="active_{{ $wh->id }}" class="w-4 h-4 rounded accent-brand-600">
                            <label for="active_{{ $wh->id }}" class="text-xs font-semibold text-slate-600 dark:text-slate-400 cursor-pointer">Ativo</label>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary text-[10px] px-4 py-1.5">Salvar</button>
                        <button type="button" @click="editing = false"
                                class="text-[10px] px-4 py-1.5 border border-slate-200 dark:border-slate-700 text-slate-500 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Referência de eventos --}}
    <div class="bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-5">
        <h3 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3">Referência de Eventos</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            @foreach(['customer.created' => 'Cliente cadastrado pela primeira vez', 'customer.updated' => 'Dados do cliente atualizados', 'card.created' => 'Card criado via interface ou API', 'card.updated' => 'Card editado (campos, status, etapa)', 'card.finished' => 'Card encerrado (Retido ou Churn)'] as $evt => $desc)
            <div class="flex items-start gap-2.5">
                <code class="text-[10px] font-mono font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 px-2 py-0.5 rounded shrink-0">{{ $evt }}</code>
                <span class="text-[11px] text-slate-400 dark:text-slate-500">{{ $desc }}</span>
            </div>
            @endforeach
        </div>
        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-3">
            Use <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1 rounded">*</code> para receber todos os eventos em uma única assinatura.
        </p>
    </div>

</div>
@endsection
