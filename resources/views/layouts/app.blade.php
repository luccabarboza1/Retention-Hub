<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Retention Hub') — Ouvidoria</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f5f3ff', 100: '#ede9fe', 200: '#ddd6fe',
                            300: '#c4b5fd', 400: '#a78bfa', 500: '#8b5cf6',
                            600: '#7c3aed', 700: '#6d28d9', 800: '#5b21b6', 900: '#4c1d95',
                        },
                        accent: {
                            emerald: '#10b981', amber: '#f59e0b', rose: '#f43f5e',
                            indigo: '#6366f1', purple: '#a855f7', cyan: '#06b6d4',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                        mono: ['Fira Code', 'Courier New', 'monospace'],
                    },
                    boxShadow: {
                        'premium':       '0 8px 30px rgb(0 0 0 / 0.04)',
                        'premium-hover': '0 20px 40px -10px rgb(124 58 237 / 0.08)',
                        'glow-brand':    '0 0 20px rgba(124, 58, 237, 0.15)',
                    }
                }
            }
        }
    </script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        html.sidebar-pre-collapsed #app-sidebar { width: 4rem; transition: none; }
        html.sidebar-pre-collapsed .sidebar-text { display: none !important; }
        html.sidebar-pre-collapsed .sidebar-toggle-icon { transform: rotate(180deg); transition: none; }
        html.sidebar-pre-collapsed .sidebar-status { opacity: 0; pointer-events: none; }
        html.sidebar-pre-collapsed .sidebar-brand-text { opacity: 0; }
        html.sidebar-pre-collapsed .sidebar-config-label { opacity: 0; }
        html.sidebar-pre-collapsed .sidebar-label { opacity: 0; }
        html.sidebar-pre-expanded #app-sidebar { width: 16rem; transition: none; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn { animation: fadeIn 0.2s ease-out both; }

        .kanban-col { min-height: 250px; }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }

        /* ─── Dark mode CSS overrides ─── */
        .dark { color-scheme: dark; }

        /* Inputs & selects */
        .field-input {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            background-color: rgba(248,250,252,0.5);
            outline: none;
            transition: all 0.2s;
            color: #1e293b;
            font-family: inherit;
            appearance: none;
            -webkit-appearance: none;
        }
        .field-input:focus {
            background-color: #fff;
            border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124,58,237,0.08);
        }
        .dark .field-input {
            border-color: #334155;
            background-color: rgba(30,41,59,0.5);
            color: #f1f5f9;
        }
        .dark .field-input:focus {
            background-color: #1e293b;
            border-color: #7c3aed;
        }
        .dark .field-input::placeholder { color: #475569; }

        /* Select wrapper with custom arrow */
        .select-wrap { position: relative; }
        .select-wrap::after {
            content: '';
            position: absolute;
            right: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 5px solid #94a3b8;
            pointer-events: none;
        }
        .select-wrap select.field-input { padding-right: 2.25rem; cursor: pointer; }
        .dark .select-wrap::after { border-top-color: #64748b; }

        /* Transição uniforme durante toggle de tema */
        .theme-transitioning *,
        .theme-transitioning *::before,
        .theme-transitioning *::after {
            transition-property: color, background-color, border-color, fill, stroke !important;
            transition-duration: 200ms !important;
            transition-timing-function: ease !important;
        }

        /* Labels */
        .field-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.375rem;
        }
        .dark .field-label { color: #64748b; }

        /* Buttons */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background-color: #7c3aed;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 0.75rem;
            box-shadow: 0 0 20px rgba(124,58,237,0.15);
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-primary:hover { background-color: #6d28d9; transform: translateY(-1px); }

        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #7c3aed;
            background-color: #f5f3ff;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-ghost:hover { background-color: #ede9fe; color: #6d28d9; }
        .dark .btn-ghost { color: #a78bfa; background-color: rgba(109,40,217,0.15); }
        .dark .btn-ghost:hover { background-color: rgba(109,40,217,0.25); }
    </style>

    {{-- Pré-aplicar dark mode e largura da sidebar antes do Alpine --}}
    <script>
        (function() {
            const saved = localStorage.getItem('rh-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
            if (localStorage.getItem('rh-sidebar') === '1') {
                document.documentElement.classList.add('sidebar-pre-collapsed');
            } else {
                document.documentElement.classList.add('sidebar-pre-expanded');
            }
        })();
    </script>
</head>
<body class="bg-slate-100 dark:bg-slate-950 font-sans antialiased text-slate-800 dark:text-slate-200 flex h-screen overflow-hidden transition-colors duration-200"
      x-data="appShell()" :class="{ 'dark': isDark }">

<div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-brand-200/10 dark:bg-brand-500/5 blur-[120px] pointer-events-none z-0"></div>
<div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-accent-cyan/10 dark:bg-accent-cyan/5 blur-[120px] pointer-events-none z-0"></div>

<div class="flex h-full w-full p-3 gap-3 z-10 relative">

    {{-- ─── Sidebar ─── --}}
    <aside id="app-sidebar" :class="collapsed ? 'w-16' : 'w-64'"
           class="bg-slate-900 dark:bg-slate-950 text-white flex flex-col shrink-0 rounded-2xl border border-slate-800 dark:border-slate-800/80 shadow-premium overflow-hidden transition-all duration-300">

        {{-- Brand --}}
        <div class="px-4 py-5 border-b border-slate-800/80 flex items-center gap-3 overflow-hidden">
            <span class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-500 to-accent-indigo flex items-center justify-center font-extrabold text-sm text-white shadow-glow-brand shrink-0">
                R
            </span>
            <div class="sidebar-brand-text overflow-hidden min-w-0 transition-opacity duration-200"
                 :class="collapsed ? 'opacity-0' : 'opacity-100'">
                <p class="font-extrabold text-sm tracking-tight text-white truncate">Retention Hub</p>
                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider truncate">Ouvidoria & Retenção</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 py-4 space-y-1 px-2">
            @php
            $nav = [
                ['route' => 'dashboard',        'pattern' => 'dashboard',    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
                ['route' => 'board',           'pattern' => 'board',        'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2', 'label' => 'Board'],
                ['route' => 'customers.index', 'pattern' => 'customers.*',  'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Clientes'],
                ['route' => 'cards.create',    'pattern' => 'cards.create', 'icon' => 'M12 4v16m8-8H4', 'label' => 'Novo Card'],
            ];
            @endphp

            @foreach($nav as $item)
            @php $active = request()->routeIs($item['pattern']); @endphp
            <a href="{{ route($item['route']) }}"
               :title="collapsed ? '{{ $item['label'] }}' : ''"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200
                      {{ $active ? 'bg-gradient-to-r from-brand-600 to-brand-700 text-white shadow-glow-brand' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                </svg>
                <span x-show="!collapsed" x-transition:enter="transition-opacity duration-200 delay-100"
                      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                      class="sidebar-text truncate">{{ $item['label'] }}</span>
            </a>
            @endforeach
        </nav>

        {{-- Configurações --}}
        <div class="px-2 pb-2 border-t border-slate-800/80 pt-3">
            <p class="sidebar-config-label text-[9px] font-bold text-slate-600 uppercase tracking-widest px-3 mb-1 transition-opacity duration-200 whitespace-nowrap"
               :class="collapsed ? 'opacity-0' : 'opacity-100'">Configurações</p>
            <a href="/docs/api" target="_blank"
               :title="collapsed ? 'API Docs' : ''"
               class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 transition-all duration-200">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="sidebar-label text-sm whitespace-nowrap transition-opacity duration-200" :class="collapsed ? 'opacity-0' : ''">API Docs</span>
            </a>
            <a href="{{ route('settings.general') }}"
               :title="collapsed ? 'Configurações' : ''"
               class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium {{ request()->routeIs('settings.*') ? 'bg-gradient-to-r from-brand-600 to-brand-700 text-white shadow-glow-brand' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }} transition-all duration-200">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="sidebar-label text-sm whitespace-nowrap transition-opacity duration-200" :class="collapsed ? 'opacity-0' : ''">Configurações</span>
            </a>
        </div>

        {{-- Footer: tema + colapso --}}
        <div class="px-2 pb-4 border-t border-slate-800/80 pt-4 space-y-1">

            {{-- Dark mode toggle --}}
            <button @click="toggleDark()"
                    :title="isDark ? 'Tema claro' : 'Tema escuro'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 transition-all">
                <svg class="dark:hidden w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg class="hidden dark:block w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span class="sidebar-label text-xs whitespace-nowrap transition-opacity duration-200" :class="collapsed ? 'opacity-0' : ''" x-text="isDark ? 'Tema claro' : 'Tema escuro'"></span>
            </button>

            {{-- Collapse toggle --}}
            <button @click="collapsed = !collapsed"
                    :title="collapsed ? 'Expandir menu' : 'Recolher menu'"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/60 transition-all">
                <svg class="sidebar-toggle-icon w-5 h-5 shrink-0 transition-transform duration-300" :class="collapsed ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <span class="sidebar-label text-xs whitespace-nowrap transition-opacity duration-200" :class="collapsed ? 'opacity-0' : ''">Recolher</span>
            </button>

            <div class="sidebar-status px-3 pt-2 text-[10px] text-slate-600 transition-opacity duration-200"
                 :class="collapsed ? 'opacity-0 pointer-events-none' : 'opacity-100'">
                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-semibold uppercase tracking-wider text-emerald-600 whitespace-nowrap">Ambiente seguro</span>
                </div>
            </div>
        </div>
    </aside>

    {{-- ─── Main ─── --}}
    <div class="flex-1 flex flex-col overflow-hidden bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-700/60 shadow-premium transition-colors duration-200">

        {{-- Header --}}
        <header class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between gap-4 shrink-0">
            <h1 class="text-lg font-bold tracking-tight text-slate-800 dark:text-slate-100 shrink-0">@yield('header', 'Dashboard')</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}"
                   class="text-xs text-slate-400 dark:text-slate-500 hover:text-brand-600 dark:hover:text-brand-400 transition-colors flex items-center gap-1.5 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                    Busca
                </a>
                <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 shrink-0">
                    {{ now()->format('d/m/Y') }}
                </div>
            </div>
        </header>

        {{-- Toasts --}}
        @if(session('success'))
        <div class="mx-6 mt-5 px-5 py-3.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm rounded-xl flex items-center gap-3 animate-fadeIn shrink-0">
            <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0 text-xs font-bold">✓</span>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="mx-6 mt-5 px-5 py-3.5 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm rounded-xl flex items-center gap-3 animate-fadeIn shrink-0">
            <span class="w-5 h-5 rounded-full bg-rose-100 dark:bg-rose-800 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0 text-xs font-bold">✕</span>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        @endif

        <main class="flex-1 overflow-auto p-6">
            @yield('content')
        </main>
    </div>

</div>

<script>
function appShell() {
    return {
        collapsed: localStorage.getItem('rh-sidebar') === '1',
        isDark: document.documentElement.classList.contains('dark'),

        toggleDark() {
            document.documentElement.classList.add('theme-transitioning');
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            localStorage.setItem('rh-theme', this.isDark ? 'dark' : 'light');
            setTimeout(() => document.documentElement.classList.remove('theme-transitioning'), 300);
        },

        init() {
            this.$nextTick(() => {
                document.documentElement.classList.remove('sidebar-pre-collapsed');
                document.documentElement.classList.remove('sidebar-pre-expanded');
            });
            this.$watch('collapsed', v => localStorage.setItem('rh-sidebar', v ? '1' : '0'));
        }
    }
}
</script>

<script>
function combobox(opts, init) {
    return {
        options: opts || [],
        filtered: [],
        value: init || '',
        query: init || '',
        open: false,
        hi: -1,
        init() { this.filtered = [...this.options]; },
        filter() {
            this.value = this.query;
            this.hi = -1;
            this.filtered = this.query
                ? this.options.filter(o => o.toLowerCase().includes(this.query.toLowerCase()))
                : [...this.options];
            this.open = true;
        },
        select(v) { this.value = v; this.query = v; this.open = false; this.hi = -1; },
        nav(d) {
            if (!this.open) { this.open = true; return; }
            this.hi = Math.max(-1, Math.min(this.filtered.length - 1, this.hi + d));
        },
        confirm() {
            if (this.hi >= 0 && this.filtered[this.hi]) this.select(this.filtered[this.hi]);
            else this.open = false;
        }
    };
}

function emailTags(initial) {
    return {
        tags: Array.isArray(initial) ? initial : [],
        input: '',
        add() {
            const v = this.input.trim().toLowerCase();
            if (v && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) && !this.tags.includes(v)) {
                this.tags.push(v);
                this.input = '';
            }
        },
        remove(i) { this.tags.splice(i, 1); },
        key(e) {
            if (['Enter','Tab',','].includes(e.key)) { e.preventDefault(); this.add(); }
            if (e.key === 'Backspace' && !this.input && this.tags.length) this.tags.pop();
        }
    };
}
</script>

</body>
</html>
