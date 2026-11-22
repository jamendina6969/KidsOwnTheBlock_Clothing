{{-- Left Slide-Out Menu Drawer --}}
<div id="kotb-menu-drawer" class="fixed inset-0 z-50 hidden">
    <div id="menu-backdrop" class="absolute inset-0 bg-black/35"></div>
    <aside id="kotb-menu-panel" role="dialog" aria-label="Site navigation" class="anim-slide-left relative w-full max-w-[400px] h-full bg-white border-r border-black flex flex-col justify-between overflow-y-auto">
        <div>
            <div class="flex items-center justify-between px-6 py-5 border-b border-black/10 bg-white">
                <span id="menu-header-label" class="font-mono-meta text-[11px] text-[#666]">MENU</span>
                <button id="menu-back-btn" type="button" class="hidden font-display inline-flex items-center gap-2 text-[13px] cursor-pointer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    <span>BACK</span>
                </button>
                <button id="close-menu-btn" type="button" aria-label="Close menu" class="p-1 cursor-pointer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            {{-- Root Level 1 --}}
            <nav id="menu-view-root" class="py-6">
                <button type="button" data-submenu="story" class="font-display w-full flex items-center justify-between px-6 py-4 text-lg text-left hover:bg-black/5 transition-colors cursor-pointer">
                    <span>BLOCK STORY</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
                <button type="button" data-submenu="shop" class="font-display w-full flex items-center justify-between px-6 py-4 text-lg text-left hover:bg-black/5 transition-colors cursor-pointer">
                    <span>SHOP</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
                <a href="{{ route('help') }}" data-nav class="font-display w-full flex items-center justify-between px-6 py-4 text-lg text-left hover:bg-black/5 transition-colors">
                    <span>HELP</span>
                </a>
                <a href="{{ route('order.track') }}" data-nav class="font-display w-full flex items-center justify-between px-6 py-4 text-lg text-left hover:bg-black/5 transition-colors">
                    <span>TRACK ORDER</span>
                </a>
            </nav>

            {{-- Block Story Submenu --}}
            <nav id="menu-view-story" class="hidden py-4">
                <a href="{{ route('story') }}" data-nav class="font-display block px-6 py-3.5 text-base hover:bg-black/5">BLOCK STORY</a>
                <a href="{{ route('lookbooks') }}" data-nav class="font-display block px-6 py-3.5 text-base hover:bg-black/5">LOOKBOOKS</a>
                <a href="{{ route('archive') }}" data-nav class="font-display block px-6 py-3.5 text-base hover:bg-black/5">DROP ARCHIVE</a>
                <a href="{{ route('run-club') }}" data-nav class="font-display block px-6 py-3.5 text-base hover:bg-black/5">RUN CLUB</a>
            </nav>

            {{-- Shop Submenu --}}
            <nav id="menu-view-shop" class="hidden py-4">
                <a href="{{ route('collections') }}" data-nav class="font-display block px-6 py-3 text-[15px] hover:bg-black/5 border-b border-black/10">ALL COLLECTIONS</a>
                @foreach(['ALL PRODUCTS', 'TEES', 'HOODIES', 'BOTTOMS', 'OUTERWEAR', 'HEADWEAR', 'WOMENS', 'RUNNING', 'HERITAGE PIECES'] as $cat)
                    <a href="{{ route('shop', ['category' => $cat]) }}" data-nav class="font-display block px-6 py-3 text-[15px] hover:bg-black/5">{{ $cat }}</a>
                @endforeach
            </nav>
        </div>

        <div class="px-6 py-5 border-t border-black/10 bg-[#ededed] flex flex-col gap-4">
            <button id="drawer-login-btn" type="button" class="inline-flex items-center gap-2.5 text-sm font-medium cursor-pointer">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Log in</span>
            </button>
            <div class="flex items-center gap-3.5">
                @foreach(['FB', 'PIN', 'IG', 'TT', 'YT'] as $social)
                    <a href="#{{ strtolower($social) }}" onclick="return false" class="font-mono-meta text-[11px] font-semibold text-black border border-black px-2 py-1">{{ $social }}</a>
                @endforeach
            </div>
        </div>
    </aside>
</div>

{{-- Predictive Search Overlay --}}
<div id="kotb-search-overlay" class="fixed inset-0 z-50 hidden">
    <div id="search-backdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="anim-drop-down relative bg-white border-b border-black px-6 pt-5 pb-7">
        <div class="max-w-[820px] mx-auto flex items-center gap-3.5">
            <div class="flex-1 relative flex items-center">
                <input id="kotb-search-input" type="text" placeholder="Search tees, hoodies, cargo, run club..." class="w-full h-[46px] rounded-[8px] border border-black pl-4 pr-11 text-sm outline-none bg-white" />
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6" class="absolute right-3.5 pointer-events-none"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
            <button id="close-search-btn" type="button" aria-label="Close search" class="p-1.5 cursor-pointer">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="max-w-[820px] mx-auto mt-5">
            <div id="kotb-search-count" class="font-mono-meta text-[11px] text-[#666] mb-3">POPULAR SEARCHES</div>
            <div id="kotb-search-results" class="grid grid-cols-2 sm:grid-cols-4 gap-2"></div>
        </div>
    </div>
</div>

{{-- Right Slide-Over Cart Drawer --}}
<div id="kotb-cart-drawer" class="fixed inset-0 z-50 hidden justify-end">
    <div id="cart-backdrop" class="absolute inset-0 bg-black/35"></div>
    <aside role="dialog" aria-label="Shopping cart" class="anim-slide-right relative w-full max-w-[420px] h-full bg-white border-l border-black flex flex-col">
        <div class="flex items-center justify-between px-6 py-5 border-b border-black/10">
            <span class="font-display text-[15px]">YOUR CART</span>
            <button id="close-cart-btn" type="button" aria-label="Close cart" class="p-1 cursor-pointer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        {{-- Free Shipping Progress Bar (₱3,500 Threshold) --}}
        @php
            $shipPct = min(100, round(($cartSubtotal / 3500) * 100));
            $shipRem = max(0, 3500 - $cartSubtotal);
        @endphp
        <div id="kotb-shipping-bar" class="px-6 py-3 bg-[#f7f7f7] border-b border-black/10 {{ count($cart) === 0 ? 'hidden' : '' }}">
            <div id="kotb-shipping-text" class="font-mono-meta text-[11px] mb-1.5">
                @if($shipRem <= 0)
                    ✓ FREE EXPRESS SHIPPING UNLOCKED
                @else
                    ADD ₱{{ number_format($shipRem, 2) }} MORE FOR FREE SHIPPING
                @endif
            </div>
            <div class="w-full h-1.5 bg-black/10 overflow-hidden">
                <div id="kotb-shipping-fill" class="h-full bg-black transition-all duration-300" style="width: {{ $shipPct }}%"></div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            {{-- Empty State --}}
            <div id="kotb-cart-empty" class="h-full flex-col items-center justify-center text-center gap-4 {{ count($cart) === 0 ? 'flex' : 'hidden' }}">
                <div class="font-display text-xl">Your cart is empty</div>
                <button id="continue-shopping-btn" type="button" class="font-display bg-black text-white rounded-[30px] px-7 py-3 text-xs cursor-pointer">
                    CONTINUE SHOPPING
                </button>
            </div>

            {{-- Confirmed State --}}
            <div id="kotb-cart-confirmed" class="hidden h-full flex-col items-center justify-center text-center gap-4">
                <div class="font-display text-lg">ORDER CONFIRMED</div>
                <p class="font-mono-meta text-xs text-[#666]">
                    Order #<span id="confirmed-order-number">KOTB-2026</span> saved to MySQL. Dispatch details sent to your email.
                </p>
                <a href="{{ route('shop') }}" data-nav class="font-display bg-black text-white rounded-[30px] px-7 py-3 text-xs">
                    CONTINUE SHOPPING
                </a>
            </div>

            {{-- Items List --}}
            <div id="kotb-cart-items" class="flex flex-col gap-5 {{ count($cart) === 0 ? 'hidden' : '' }}">
                @foreach($cart as $item)
                    <div class="grid grid-cols-[76px_1fr] gap-3.5 pb-4 border-b border-black/10">
                        <div class="aspect-[4/5] bg-[#f7f7f7] overflow-hidden">
                            {!! $item['thumb_html'] ?? '' !!}
                        </div>
                        <div class="flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start gap-2">
                                    <a href="{{ route('product.show', ['slug' => $item['slug']]) }}" class="font-display text-xs leading-snug text-left">{{ $item['title'] }}</a>
                                    <button type="button" data-cart-remove="{{ $item['key'] }}" class="text-[11px] text-[#666] hover:text-black">Remove</button>
                                </div>
                                <div class="font-mono-meta text-[11px] text-[#666] mt-1">SIZE: {{ $item['size'] }}</div>
                            </div>
                            <div class="flex items-center justify-between mt-2.5">
                                <div class="inline-flex items-center border border-black rounded-[8px] h-8">
                                    <button type="button" data-cart-delta="-1" data-cart-key="{{ $item['key'] }}" class="w-7 h-full flex items-center justify-center text-xs">-</button>
                                    <span class="font-mono-meta w-6 text-center text-xs">{{ $item['quantity'] }}</span>
                                    <button type="button" data-cart-delta="1" data-cart-key="{{ $item['key'] }}" class="w-7 h-full flex items-center justify-center text-xs">+</button>
                                </div>
                                <div class="font-mono-meta text-xs">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div id="kotb-cart-footer" class="px-6 pt-5 pb-7 border-t border-black/10 bg-[#fafafa] {{ count($cart) === 0 ? 'hidden' : '' }}">
            <div class="flex justify-between mb-1.5">
                <span class="font-display text-[13px]">SUBTOTAL</span>
                <span id="kotb-cart-subtotal" class="font-mono-meta text-[15px] font-semibold">₱{{ number_format($cartSubtotal, 2) }} PHP</span>
            </div>
            <p class="text-[11px] text-[#666] mb-4">Taxes and shipping calculated at checkout.</p>
            <a href="{{ route('checkout.page') }}" data-nav class="font-display block text-center w-full bg-black text-white rounded-[30px] py-3.5 text-[13px] cursor-pointer hover:opacity-85 transition-opacity">
                CHECK OUT
            </a>
        </div>
    </aside>
</div>

{{-- Account Modal (Real Login & Register) --}}
<div id="kotb-account-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 p-4">
    <div class="anim-fade-up w-full max-w-[400px] bg-white border border-black p-7 relative">
        <button id="close-account-btn" type="button" aria-label="Close" class="absolute top-4 right-4 cursor-pointer">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
        <div class="flex justify-center mb-5">
            @include('components.brand-mark', ['color' => 'black', 'size' => 38, 'showWordmark' => true])
        </div>

        <div class="grid grid-cols-3 border border-black mb-5">
            <button type="button" data-auth-tab="otp" class="auth-tab font-display py-2 text-[10px] bg-black text-white cursor-pointer">GMAIL CODE</button>
            <button type="button" data-auth-tab="login" class="auth-tab font-display py-2 text-[10px] bg-white text-black cursor-pointer">PASSWORD</button>
            <button type="button" data-auth-tab="register" class="auth-tab font-display py-2 text-[10px] bg-white text-black cursor-pointer">REGISTER</button>
        </div>

        <div id="auth-error-msg" class="hidden font-mono-meta text-[11px] text-red-700 border border-red-700 bg-red-50 px-3 py-2 mb-3"></div>
        <div id="auth-info-msg" class="hidden font-mono-meta text-[11px] text-emerald-800 border border-emerald-700 bg-emerald-50 px-3 py-2 mb-3"></div>

        {{-- Passwordless 6-Digit Gmail OTP Form --}}
        <div id="kotb-otp-panel" class="flex flex-col gap-3.5">
            <form id="kotb-otp-send-form" class="flex flex-col gap-3">
                <p class="font-mono-meta text-[11px] text-[#666] text-center">Enter your Gmail to receive a 6-digit login code (no password needed).</p>
                <input id="otp-email-input" type="email" name="email" required placeholder="yourname@gmail.com" class="h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
                <button type="submit" class="font-display bg-black text-white rounded-[30px] py-3 text-xs cursor-pointer">SEND 6-DIGIT CODE</button>
            </form>
            <form id="kotb-otp-verify-form" class="hidden flex-col gap-3">
                <input id="otp-code-input" type="text" name="code" maxlength="6" required placeholder="Enter 6-digit code" class="h-11 rounded-[8px] border border-black px-3.5 text-center font-mono-meta tracking-[0.35em] text-base outline-none" />
                <button type="submit" class="font-display bg-black text-white rounded-[30px] py-3 text-xs cursor-pointer">VERIFY &amp; SIGN IN</button>
            </form>
        </div>

        <form id="kotb-login-form" class="hidden flex-col gap-3.5">
            <input type="email" name="email" required placeholder="Email" class="h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
            <input type="password" name="password" required placeholder="Password" class="h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
            <button type="submit" class="font-display mt-1 bg-black text-white rounded-[30px] py-3 text-xs cursor-pointer">SIGN IN</button>
        </form>

        <form id="kotb-register-form" class="hidden flex-col gap-3.5">
            <input type="text" name="name" required placeholder="Full name" class="h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
            <input type="email" name="email" required placeholder="Email" class="h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
            <input type="password" name="password" required minlength="6" placeholder="Password (min 6 chars)" class="h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
            <button type="submit" class="font-display mt-1 bg-black text-white rounded-[30px] py-3 text-xs cursor-pointer">CREATE ACCOUNT</button>
        </form>
    </div>
</div>

{{-- Floating Chat Pill --}}
<div class="fixed right-5 bottom-5 z-40">
    <div id="kotb-chat-panel" class="hidden w-[305px] bg-white border border-black mb-3 flex flex-col anim-fade-up">
        <div class="bg-black text-white px-3.5 py-3 flex items-center justify-between">
            <span class="font-display text-xs">KOTB CONCIERGE</span>
            <button id="kotb-chat-close" type="button" class="text-white cursor-pointer">✕</button>
        </div>
        <div id="kotb-chat-log" class="p-3.5 h-48 overflow-y-auto flex flex-col gap-2.5 bg-[#fafafa]">
            <div class="self-start bg-[#ededed] text-black px-3 py-2 text-xs max-w-[85%]">
                Welcome to KIDS OWN THE BLOCK. Ask about sizing, drop restocks, or order status.
            </div>
        </div>
        <div class="px-2.5 py-1.5 border-t border-black/10 bg-white flex flex-wrap gap-1">
            @foreach(['Sizing', 'Shipping', 'GCash / COD', 'Promo Code', 'Track Order'] as $chip)
                <button type="button" data-chat-chip="{{ $chip }}" class="font-mono-meta text-[10px] border border-black/25 px-2 py-0.5 rounded-full hover:bg-black hover:text-white transition-colors cursor-pointer">{{ $chip }}</button>
            @endforeach
        </div>
        <form id="kotb-chat-form" class="flex border-t border-black/10 p-2 gap-2">
            <input id="kotb-chat-input" type="text" placeholder="Write a message..." class="flex-1 border border-black/15 rounded-[8px] px-2.5 py-1.5 text-xs outline-none" />
            <button type="submit" class="bg-black text-white rounded-[8px] px-3 text-xs cursor-pointer">→</button>
        </form>
    </div>
    <button id="kotb-chat-toggle" type="button" class="bg-[#1a1a1a] text-white rounded-[30px] px-4 py-2.5 inline-flex items-center gap-2 text-[13px] font-semibold border border-white/20 cursor-pointer hover:bg-black transition-colors">
        <span>Chat</span>
    </button>
</div>

{{-- Page Transition Preloader --}}
<div id="kotb-preloader" class="fixed inset-0 z-50 hidden flex-col items-center justify-center gap-4 bg-[#ededed]/90">
    <div class="anim-emblem-pulse">
        @include('components.brand-mark', ['color' => 'black', 'size' => 46, 'showWordmark' => false])
    </div>
    <div class="w-[140px] h-[2px] bg-black/15 overflow-hidden relative">
        <div class="anim-preloader-sweep absolute inset-0 bg-black"></div>
    </div>
</div>
