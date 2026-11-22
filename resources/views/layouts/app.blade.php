<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'KOTB | Philippine Streetwear & Block Community')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}" />
    <meta name="theme-color" content="#000000" />
    <meta name="description" content="@yield('meta_description', 'KIDS OWN THE BLOCK — Stark minimalist streetwear, heavyweight 240 GSM tees, hoodies, lookbooks, and run club.')" />
    <meta property="og:title" content="@yield('title', 'KIDS OWN THE BLOCK | Streetwear & Block Culture')" />
    <meta property="og:description" content="@yield('meta_description', 'Stark minimalist streetwear, heavyweight 240 GSM tees, hoodies, lookbooks, and run club.')" />
    <meta property="og:type" content="website" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@100..125,400..900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-white text-black">
    @php
        $hideChrome = $hideChrome ?? false;
        $cart = session('cart', []);
        $cartCount = collect($cart)->sum('quantity');
        $cartSubtotal = collect($cart)->sum(fn ($i) => $i['price'] * $i['quantity']);
    @endphp

    @unless($hideChrome)
        {{-- Sticky 3-Zone Header --}}
        <header class="sticky top-0 z-40 bg-[#ededed] border-b border-black/10 transition-all duration-200">
            <div id="kotb-header-bar" class="max-w-[1600px] mx-auto px-3 md:px-6 grid grid-cols-3 items-center h-[76px] transition-all duration-200">
                <div class="flex items-center">
                    <button id="open-menu-btn" type="button" aria-label="Open navigation menu" class="inline-flex items-center py-2 px-1 cursor-pointer">
                        <svg width="22" height="16" viewBox="0 0 22 16" fill="none" aria-hidden="true">
                            <line x1="0" y1="1.5" x2="22" y2="1.5" stroke="#000" stroke-width="1.5" />
                            <line x1="0" y1="8" x2="22" y2="8" stroke="#000" stroke-width="1.5" />
                            <line x1="0" y1="14.5" x2="22" y2="14.5" stroke="#000" stroke-width="1.5" />
                        </svg>
                    </button>
                </div>

                <div class="flex justify-center">
                    <a id="kotb-header-logo" href="{{ route('shop') }}" data-nav aria-label="Kids Own The Block Home" class="inline-flex items-center justify-center transition-transform duration-200">
                        @include('components.brand-mark', ['color' => 'black', 'size' => 44, 'showWordmark' => false])
                    </a>
                </div>

                <div class="flex items-center justify-end gap-4 md:gap-5">
                    <button id="open-search-btn" type="button" aria-label="Search products" class="p-1 cursor-pointer">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" data-nav class="font-mono-meta text-[10px] border border-black px-2 py-0.5 hover:bg-black hover:text-white transition-colors">ADMIN</a>
                        @endif
                        <a href="{{ route('account') }}" data-nav aria-label="My Account" class="p-1 cursor-pointer">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </a>
                    @else
                        <button id="open-account-btn" type="button" aria-label="Account" class="p-1 cursor-pointer">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </button>
                    @endauth
                    <button id="open-cart-btn" type="button" aria-label="Shopping cart" class="p-1 relative cursor-pointer">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <span id="kotb-cart-badge" class="font-mono-meta absolute -top-0.5 -right-1.5 min-w-[16px] h-4 rounded-full bg-black text-white text-[10px] items-center justify-center px-1 {{ $cartCount > 0 ? 'flex' : 'hidden' }}">{{ $cartCount }}</span>
                    </button>
                </div>
            </div>
        </header>
    @endunless

    <div class="flex-1">
        @yield('content')
    </div>

    @unless($hideChrome)
        {{-- Light Grey Footer --}}
        <footer class="bg-[#ebebeb] border-t border-black/10 pt-12 pb-7">
            <div class="max-w-[1600px] mx-auto px-3 md:px-6">
                <div class="flex flex-wrap items-end justify-between gap-7 pb-10">
                    <div class="w-full max-w-[380px]">
                        <h3 class="font-display text-base mb-3.5">Subscribe</h3>
                        <form id="kotb-newsletter-form" class="relative">
                            <input type="email" name="email" required placeholder="Email" aria-label="Email address" class="w-full h-11 rounded-[8px] border border-black/55 bg-transparent pl-3.5 pr-11 text-[13px] outline-none focus:border-black" />
                            <button type="submit" aria-label="Subscribe" class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </button>
                        </form>
                        <div id="kotb-newsletter-status" class="hidden font-mono-meta text-[11px] mt-2 text-black">
                            ✓ Subscribed to drop alerts &amp; early access.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        @foreach(['FB', 'IG', 'YT', 'TT', 'PIN'] as $s)
                            <a href="#{{ strtolower($s) }}" onclick="return false" class="font-mono-meta text-[11px] font-semibold text-black border border-black px-2 py-1 hover:bg-black hover:text-white transition-colors">{{ $s }}</a>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-black/10 pt-5 flex flex-wrap justify-center items-center gap-2.5 text-center">
                    <span class="font-mono-meta text-[11px] text-[#666]">© 2026, KIDS OWN THE BLOCK</span>
                    <span class="font-mono-meta text-[11px] text-[#666]">·</span>
                    <a href="{{ route('help') }}" data-nav class="font-mono-meta text-[11px] text-[#666] hover:text-black">Privacy policy</a>
                </div>
            </div>
        </footer>
    @endunless

    @include('components.overlays', ['cart' => $cart, 'cartSubtotal' => $cartSubtotal])
    @unless($hideChrome)
        <button id="kotb-scroll-top" type="button" aria-label="Scroll to top" class="hidden fixed left-5 bottom-5 z-40 bg-white text-black border border-black rounded-[30px] px-3.5 py-2 font-mono-meta text-[11px] cursor-pointer hover:bg-black hover:text-white transition-colors">
            ↑ TOP
        </button>
    @endunless
    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }
    </script>
</body>
</html>
