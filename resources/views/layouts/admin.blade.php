<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'STUDIO CONTROL | KIDS OWN THE BLOCK')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@100..125,400..900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
</head>
<body class="min-h-screen bg-[#f5f5f5] text-black flex overflow-x-hidden">
    @php
        $activeTab = $tab ?? 'overview';
        $navItems = [
            ['id' => 'overview', 'label' => 'OVERVIEW & ANALYTICS', 'badge' => null],
            ['id' => 'forecasting', 'label' => 'FORECASTING (EOQ & SMA)', 'badge' => ($stats['reorder_alerts'] ?? 0) > 0 ? $stats['reorder_alerts'] : null],
            ['id' => 'inventory', 'label' => 'CATALOG & INVENTORY', 'badge' => null],
            ['id' => 'orders', 'label' => 'ORDERS & FULFILLMENT', 'badge' => null],
            ['id' => 'customers', 'label' => 'CUSTOMERS', 'badge' => null],
            ['id' => 'promos', 'label' => 'PROMOS & DISCOUNTS', 'badge' => null],
            ['id' => 'cms', 'label' => 'LOOKBOOK & STORY CMS', 'badge' => null],
            ['id' => 'community', 'label' => 'COMMUNITY HUB', 'badge' => ($stats['unread_chats'] ?? 0) > 0 ? $stats['unread_chats'] : null],
            ['id' => 'audit', 'label' => 'ACTIVITY AUDIT LOG', 'badge' => null],
        ];
    @endphp

    {{-- Desktop / Laptop Fixed Monochrome Sidebar --}}
    <aside class="w-[250px] bg-black text-white shrink-0 hidden lg:flex flex-col justify-between h-screen sticky top-0 border-r border-black">
        <div class="overflow-y-auto">
            <div class="px-5 py-5 border-b border-white/15 flex items-center gap-3">
                @include('components.brand-mark', ['color' => 'white', 'size' => 34, 'showWordmark' => false])
                <div>
                    <div class="font-display text-xs tracking-wider">KOTB STUDIO</div>
                    <div class="font-mono-meta text-[10px] text-white/50">ADMIN CONTROL</div>
                </div>
            </div>

            <nav aria-label="Admin Modules" class="py-4 px-3 flex flex-col gap-1">
                @foreach($navItems as $item)
                    <a
                        href="{{ route('admin.dashboard', ['tab' => $item['id']]) }}"
                        class="flex items-center justify-between px-3 py-2.5 text-[11px] font-display tracking-wider transition-colors {{ $activeTab === $item['id'] ? 'bg-white text-black' : 'text-white/75 hover:text-white hover:bg-white/10' }}"
                    >
                        <span>{{ $item['label'] }}</span>
                        @if($item['badge'])
                            <span class="font-mono-meta text-[10px] px-1.5 py-0.5 {{ $activeTab === $item['id'] ? 'bg-black text-white' : 'bg-[#d9ff00] text-black font-bold' }}">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
                <a
                    href="{{ route('admin.ai.studio') }}"
                    class="flex items-center justify-between px-3 py-2.5 text-[11px] font-display tracking-wider transition-colors {{ $activeTab === 'ai_studio' ? 'bg-white text-black' : 'text-white/75 hover:text-white hover:bg-white/10' }}"
                >
                    <span>AI PRODUCT STUDIO</span>
                    <span class="font-mono-meta text-[9px] px-1.5 py-0.5 {{ $activeTab === 'ai_studio' ? 'bg-black text-[#d9ff00]' : 'bg-[#d9ff00] text-black font-bold' }}">AI</span>
                </a>
                <a
                    href="{{ route('logo.studio') }}"
                    class="flex items-center justify-between px-3 py-2.5 text-[11px] font-display tracking-wider text-white/75 hover:text-white hover:bg-white/10 transition-colors"
                >
                    <span>LOGO STUDIO (100)</span>
                    <span class="font-mono-meta text-[10px]">↗</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-white/15 flex flex-col gap-2 bg-black">
            <a href="{{ route('shop') }}" class="font-mono-meta text-[11px] text-white/80 hover:text-white flex items-center justify-between px-2.5 py-2 border border-white/20">
                <span>VIEW STOREFRONT</span>
                <span>↗</span>
            </a>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="w-full font-mono-meta text-[11px] text-white/60 hover:text-white text-left px-2 py-1 cursor-pointer truncate">
                    LOG OUT ({{ auth()->user()?->email }})
                </button>
            </form>
        </div>
    </aside>

    {{-- Mobile / Tablet Slide-Over Sidebar Drawer --}}
    <div id="admin-mobile-drawer" class="fixed inset-0 z-50 hidden lg:hidden">
        <div id="admin-mobile-backdrop" class="absolute inset-0 bg-black/50"></div>
        <aside class="anim-slide-left relative w-[270px] max-w-[85vw] bg-black text-white h-full flex flex-col justify-between border-r border-white/20">
            <div class="overflow-y-auto">
                <div class="px-5 py-4 border-b border-white/15 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        @include('components.brand-mark', ['color' => 'white', 'size' => 28, 'showWordmark' => false])
                        <span class="font-display text-xs">KOTB STUDIO</span>
                    </div>
                    <button id="close-admin-sidebar" type="button" aria-label="Close sidebar" class="text-white text-lg px-1 cursor-pointer">✕</button>
                </div>
                <nav class="py-4 px-3 flex flex-col gap-1">
                    @foreach($navItems as $item)
                        <a
                            href="{{ route('admin.dashboard', ['tab' => $item['id']]) }}"
                            class="flex items-center justify-between px-3 py-2.5 text-[11px] font-display tracking-wider {{ $activeTab === $item['id'] ? 'bg-white text-black' : 'text-white/80 hover:bg-white/10' }}"
                        >
                            <span>{{ $item['label'] }}</span>
                            @if($item['badge'])
                                <span class="font-mono-meta text-[10px] px-1.5 py-0.5 bg-[#d9ff00] text-black font-bold">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                    <a href="{{ route('admin.ai.studio') }}" class="flex items-center justify-between px-3 py-2.5 text-[11px] font-display tracking-wider {{ $activeTab === 'ai_studio' ? 'bg-white text-black' : 'text-white/80 hover:bg-white/10' }}">
                        <span>AI PRODUCT STUDIO</span><span class="font-mono-meta text-[9px] px-1.5 py-0.5 bg-[#d9ff00] text-black font-bold">AI</span>
                    </a>
                    <a href="{{ route('logo.studio') }}" class="flex items-center justify-between px-3 py-2.5 text-[11px] font-display tracking-wider text-white/80 hover:bg-white/10">
                        <span>LOGO STUDIO (100)</span><span>↗</span>
                    </a>
                </nav>
            </div>
            <div class="p-4 border-t border-white/15 flex flex-col gap-2">
                <a href="{{ route('shop') }}" class="font-mono-meta text-[11px] text-white/80 px-2.5 py-2 border border-white/20 flex justify-between">
                    <span>VIEW STOREFRONT</span><span>↗</span>
                </a>
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="w-full font-mono-meta text-[11px] text-white/60 text-left px-2 py-1 cursor-pointer">LOG OUT</button>
                </form>
            </div>
        </aside>
    </div>

    {{-- Main Workspace --}}
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="bg-white border-b border-black/15 px-4 md:px-6 lg:px-8 h-16 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3 min-w-0">
                <button id="open-admin-sidebar" type="button" aria-label="Open admin menu" class="lg:hidden p-1.5 border border-black cursor-pointer">
                    <svg width="18" height="14" viewBox="0 0 22 16" fill="none"><line x1="0" y1="1.5" x2="22" y2="1.5" stroke="#000" stroke-width="1.8"/><line x1="0" y1="8" x2="22" y2="8" stroke="#000" stroke-width="1.8"/><line x1="0" y1="14.5" x2="22" y2="14.5" stroke="#000" stroke-width="1.8"/></svg>
                </button>
                <span class="font-display text-xs sm:text-sm md:text-base uppercase truncate">{{ str_replace('_', ' ', $activeTab) }}</span>
                <span class="hidden sm:inline-flex items-center gap-1.5 font-mono-meta text-[10px] px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    LIVE · MYSQL
                </span>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('admin.ai.studio') }}" class="hidden sm:inline-flex items-center gap-1.5 font-display bg-[#d9ff00] text-black border border-black rounded-[30px] px-3.5 py-2 text-[10px] sm:text-[11px] whitespace-nowrap hover:opacity-90">
                    ✦ AI STUDIO
                </a>
                <a href="{{ route('admin.products.create') }}" class="font-display bg-black text-white rounded-[30px] px-3.5 sm:px-4 py-2 text-[10px] sm:text-[11px] whitespace-nowrap">
                    + ADD GARMENT
                </a>
                <a href="{{ route('shop') }}" class="hidden sm:inline-block lg:hidden font-mono-meta text-xs border border-black px-3 py-1.5 rounded-[30px]">
                    STORE ↗
                </a>
            </div>
        </header>

        <main class="p-4 sm:p-6 lg:p-8 flex-1 min-w-0">
            @if(session('success'))
                <div class="font-mono-meta text-xs border border-black bg-white px-4 py-3 mb-6">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="font-mono-meta text-xs border border-red-700 bg-red-50 text-red-700 px-4 py-3 mb-6">
                    ✕ {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const drawer = document.getElementById('admin-mobile-drawer');
        document.getElementById('open-admin-sidebar')?.addEventListener('click', () => drawer?.classList.remove('hidden'));
        document.getElementById('close-admin-sidebar')?.addEventListener('click', () => drawer?.classList.add('hidden'));
        document.getElementById('admin-mobile-backdrop')?.addEventListener('click', () => drawer?.classList.add('hidden'));
    });
    </script>
    @stack('scripts')
</body>
</html>
