@extends('layouts.app')

@section('title', $product->title . ' | KIDS OWN THE BLOCK')
@section('meta_description', $product->description)

@section('content')
@php
    $firstAvailable = collect($product->sizes)->firstWhere('inStock', true)['size'] ?? 'L';
@endphp

<main class="bg-white pt-8 pb-16">
    <div class="max-w-[1600px] mx-auto px-3 md:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-[1.05fr_0.95fr] gap-8 lg:gap-12 items-start">
            {{-- Left Column: Primary 4:5 View with Zoom Loupe + 3 Square Thumbnails --}}
            <div class="anim-fade-up">
                <div id="pdp-stage-front" class="pdp-stage pdp-zoom-container aspect-[4/5] w-full bg-[#f9f9f9]">
                    @include('components.garment-svg', ['product' => $product, 'view' => 'front'])
                </div>
                <div id="pdp-stage-back" class="pdp-stage pdp-zoom-container hidden aspect-[4/5] w-full bg-[#f9f9f9]">
                    @include('components.garment-svg', ['product' => $product, 'view' => 'back'])
                </div>
                <div id="pdp-stage-size-chart" class="pdp-stage hidden aspect-[4/5] w-full bg-[#0c0c0c]">
                    @include('components.garment-svg', ['product' => $product, 'view' => 'size-chart'])
                </div>

                <div class="grid grid-cols-3 gap-2 mt-2">
                    @foreach(['front', 'back', 'size-chart'] as $idx => $v)
                        <button type="button" data-pdp-thumb="{{ $v }}" class="pdp-thumb aspect-square overflow-hidden border {{ $idx === 0 ? 'border-black' : 'border-transparent' }} {{ $v === 'size-chart' ? 'bg-[#0c0c0c]' : 'bg-[#f5f5f5]' }} cursor-pointer">
                            @include('components.garment-svg', ['product' => $product, 'view' => $v])
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Right Column: Details, Size Pills, Stepper, CTAs --}}
            <div class="flex flex-col gap-5 anim-fade-up" style="animation-delay: 100ms;">
                <div>
                    <div class="font-mono-meta text-[11px] text-[#666] mb-2">{{ $product->category }}</div>
                    <h1 class="font-display text-[28px] leading-tight mb-3">{{ $product->title }}</h1>
                    <div class="font-mono-meta text-[17px] font-medium">₱{{ number_format((float) $product->price, 2) }} PHP</div>
                </div>

                <form id="pdp-add-to-cart-form" class="flex flex-col gap-5">
                    <input type="hidden" name="slug" value="{{ $product->slug }}" />
                    <input type="hidden" id="pdp-size-input" name="size" value="{{ $firstAvailable }}" />
                    <input type="hidden" id="pdp-qty-input" name="quantity" value="1" />

                    {{-- Size Pills + Fit Finder --}}
                    <div>
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-xs text-[#666]">Size</span>
                            <button type="button" id="open-fit-finder" class="font-mono-meta text-[11px] underline cursor-pointer">What's my size?</button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->sizes as $s)
                                @php
                                    $inStock = $s['inStock'];
                                    $isActive = $s['size'] === $firstAvailable && $inStock;
                                @endphp
                                <button
                                    type="button"
                                    {{ $inStock ? '' : 'disabled' }}
                                    data-size-pill="{{ $s['size'] }}"
                                    class="size-pill min-w-[52px] h-[38px] rounded-[30px] border border-black px-3.5 text-xs font-semibold transition-colors {{ $isActive ? 'bg-black text-white' : ($inStock ? 'bg-white text-black cursor-pointer' : 'bg-white text-black/35 line-through cursor-not-allowed') }}"
                                >
                                    {{ $s['size'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Quantity Stepper --}}
                    <div>
                        <div class="text-xs text-[#666] mb-2.5">Quantity</div>
                        <div class="inline-flex items-center border border-black rounded-[8px] h-11">
                            <button type="button" id="pdp-qty-minus" class="w-10 h-full flex items-center justify-center cursor-pointer">-</button>
                            <span id="pdp-qty-display" class="font-mono-meta w-9 text-center text-sm">1</span>
                            <button type="button" id="pdp-qty-plus" class="w-10 h-full flex items-center justify-center cursor-pointer">+</button>
                        </div>
                    </div>

                    {{-- Primary & COD CTAs --}}
                    <div id="pdp-stock-error" class="hidden font-mono-meta text-[11px] text-red-700 border border-red-700 bg-red-50 px-3 py-2"></div>
                    <div class="flex flex-col gap-2.5 mt-1">
                        <button
                            type="submit"
                            {{ $product->sold_out ? 'disabled' : '' }}
                            class="btn-sheen font-display w-full h-12 rounded-[30px] text-[13px] transition-all active:scale-[0.98] {{ $product->sold_out ? 'bg-[#d4d4d4] text-[#666] cursor-not-allowed' : 'bg-black text-white cursor-pointer hover:opacity-90' }}"
                        >
                            {{ $product->sold_out ? 'SOLD OUT' : 'ADD TO CART' }}
                        </button>

                        @unless($product->sold_out)
                            <button type="button" id="pdp-cod-btn" class="font-display w-full h-12 rounded-[30px] bg-white text-black border border-black text-xs inline-flex items-center justify-center gap-2 cursor-pointer hover:bg-black hover:text-white transition-colors">
                                <span>BUY WITH CASH ON DELIVERY</span>
                            </button>
                            <div id="pdp-cod-msg" class="hidden font-mono-meta text-[11px] px-3.5 py-2.5 border border-black bg-[#f5f5f5]">
                                ✓ COD reservation ready. Click ADD TO CART to finalize delivery.
                            </div>
                        @endunless

                        {{-- Save to Wishlist --}}
                        <button type="button" id="pdp-wishlist-btn" data-product-id="{{ $product->id }}" class="font-mono-meta w-full h-10 rounded-[30px] border border-black/25 hover:border-black text-xs cursor-pointer transition-colors">
                            {{ ($inWishlist ?? false) ? '♥ SAVED IN WISHLIST' : '♡ SAVE TO WISHLIST' }}
                        </button>
                    </div>
                </form>

                {{-- Notify Me on Restock (for Sold Out items) --}}
                @if($product->sold_out)
                    <form id="pdp-notify-form" class="border border-black p-4 bg-[#fafafa] flex flex-col gap-2.5">
                        <div class="font-display text-xs">NOTIFY ME WHEN RESTOCKED</div>
                        <div class="flex gap-2">
                            <input type="email" name="email" required placeholder="yourname@gmail.com" class="flex-1 h-10 rounded-[8px] border border-black px-3 text-xs bg-white outline-none" />
                            <button type="submit" class="font-display bg-black text-white px-4 h-10 rounded-[8px] text-[11px] cursor-pointer">NOTIFY ME</button>
                        </div>
                        <div id="pdp-notify-done" class="hidden font-mono-meta text-[11px] text-emerald-700">✓ You're on the restock alert list!</div>
                    </form>
                @endif

                {{-- Previous / Next Navigation --}}
                <div class="font-mono-meta flex items-center gap-3.5 text-xs pt-1.5">
                    <a href="{{ route('product.show', ['slug' => $prevProduct->slug]) }}" data-nav class="link-sweep">‹ Previous</a>
                    <span>·</span>
                    <a href="{{ route('product.show', ['slug' => $nextProduct->slug]) }}" data-nav class="link-sweep">Next ›</a>
                </div>

                {{-- Garment Specs --}}
                <div class="border-t border-black/10 pt-5">
                    <p class="text-sm leading-relaxed text-[#222] mb-3.5">{{ $product->description }}</p>
                    <ul class="list-disc pl-5 flex flex-col gap-1.5 text-[13px]">
                        @foreach($product->specs as $spec)
                            <li>{{ $spec }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Complete the Fit Upsell Row --}}
        @if(($completeTheFit ?? collect())->isNotEmpty())
            <section class="mt-16 pt-10 border-t border-black/10">
                <h2 class="font-display text-lg mb-5">COMPLETE THE FIT</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-1 md:gap-2">
                    @foreach($completeTheFit as $idx => $fit)
                        @include('components.product-card', ['product' => $fit, 'surface' => 'white', 'delay' => $idx * 60])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Recently Viewed Strip (localStorage) --}}
        <section id="recently-viewed-section" class="hidden mt-14 pt-10 border-t border-black/10">
            <h2 class="font-display text-lg mb-5">RECENTLY VIEWED</h2>
            <div id="recently-viewed-grid" class="grid grid-cols-2 md:grid-cols-4 gap-2"></div>
        </section>

        {{-- Fit & Buyer Reviews --}}
        <section class="mt-14 pt-10 border-t border-black/10">
            <div class="grid grid-cols-1 lg:grid-cols-[0.85fr_1.15fr] gap-8 items-start">
                <div>
                    <h2 class="font-display text-lg mb-2">FIT &amp; BLOCK REVIEWS ({{ ($reviews ?? collect())->count() }})</h2>
                    <p class="font-mono-meta text-xs text-[#666] mb-5">Share how this piece fits on you.</p>

                    @if(session('review_success'))
                        <div class="font-mono-meta text-xs border border-emerald-700 bg-emerald-50 text-emerald-800 p-3 mb-4">
                            ✓ {{ session('review_success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('product.review', ['slug' => $product->slug]) }}" class="border border-black p-5 flex flex-col gap-3">
                        @csrf
                        <input type="text" name="reviewer_name" required value="{{ auth()->user()?->name }}" placeholder="Your name" class="h-10 rounded-[8px] border border-black px-3 text-xs outline-none" />
                        <div class="grid grid-cols-2 gap-2.5 font-mono-meta text-xs">
                            <select name="rating" class="h-10 rounded-[8px] border border-black px-2.5 bg-white">
                                <option value="5">★★★★★ (5/5)</option>
                                <option value="4">★★★★☆ (4/5)</option>
                                <option value="3">★★★☆☆ (3/5)</option>
                                <option value="2">★★☆☆☆ (2/5)</option>
                                <option value="1">★☆☆☆☆ (1/5)</option>
                            </select>
                            <select name="fit_feedback" class="h-10 rounded-[8px] border border-black px-2.5 bg-white">
                                <option value="True to Size">Fit: True to Size</option>
                                <option value="Boxy Oversized">Fit: Boxy Oversized</option>
                                <option value="Runs Small">Fit: Runs Small</option>
                            </select>
                        </div>
                        <textarea name="comment" required rows="3" placeholder="Fabric weight, print quality, sizing notes..." class="rounded-[8px] border border-black p-3 text-xs outline-none"></textarea>
                        <button type="submit" class="btn-sheen font-display self-start bg-black text-white rounded-[30px] px-6 py-2.5 text-xs cursor-pointer">
                            POST REVIEW
                        </button>
                    </form>
                </div>

                <div class="flex flex-col gap-3">
                    @forelse($reviews ?? [] as $rev)
                        <div class="border border-black/15 p-4 bg-[#fafafa]">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-display text-xs">{{ $rev->reviewer_name }}</span>
                                    @if($rev->verified_buyer)
                                        <span class="font-mono-meta text-[9px] px-1.5 py-0.5 bg-black text-white">VERIFIED BUYER</span>
                                    @endif
                                </div>
                                <span class="font-mono-meta text-[11px] text-[#666]">{{ str_repeat('★', $rev->rating) }} · {{ strtoupper($rev->fit_feedback) }}</span>
                            </div>
                            <p class="text-xs text-[#333] leading-relaxed">{{ $rev->comment }}</p>
                        </div>
                    @empty
                        <div class="border border-black/15 p-8 text-center font-mono-meta text-xs text-[#666]">
                            No reviews yet for this garment. Be the first to review the fit!
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</main>

{{-- Size & Fit Finder Modal --}}
<div id="fit-finder-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 p-4">
    <div class="anim-scale-in w-full max-w-[380px] bg-white border border-black p-6 relative">
        <button id="close-fit-finder" type="button" class="absolute top-4 right-4 cursor-pointer">✕</button>
        <h3 class="font-display text-base mb-1">SIZE &amp; FIT FINDER</h3>
        <p class="font-mono-meta text-[11px] text-[#666] mb-4">Boxy street cut — enter your stats for a fit recommendation.</p>
        <div class="grid grid-cols-2 gap-3 mb-4 font-mono-meta text-xs">
            <div>
                <label class="block text-[10px] text-[#666] mb-1">HEIGHT (CM)</label>
                <input id="fit-height" type="number" value="175" min="140" max="210" class="w-full h-10 border border-black rounded-[8px] px-3" />
            </div>
            <div>
                <label class="block text-[10px] text-[#666] mb-1">WEIGHT (KG)</label>
                <input id="fit-weight" type="number" value="70" min="40" max="140" class="w-full h-10 border border-black rounded-[8px] px-3" />
            </div>
        </div>
        <button type="button" id="calc-fit-btn" class="font-display w-full bg-black text-white rounded-[30px] py-2.5 text-xs cursor-pointer mb-3">RECOMMEND SIZE</button>
        <div id="fit-result" class="hidden font-mono-meta text-xs border border-black bg-[#ededed] p-3 text-center"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Thumbnail view switcher
    document.querySelectorAll('.pdp-thumb').forEach((btn) => {
        btn.addEventListener('click', () => {
            const view = btn.getAttribute('data-pdp-thumb');
            document.querySelectorAll('.pdp-stage').forEach((s) => s.classList.add('hidden'));
            document.getElementById(`pdp-stage-${view}`)?.classList.remove('hidden');
            document.querySelectorAll('.pdp-thumb').forEach((t) => {
                t.classList.remove('border-black');
                t.classList.add('border-transparent');
            });
            btn.classList.remove('border-transparent');
            btn.classList.add('border-black');
        });
    });

    // Size pill selector
    const sizeInput = document.getElementById('pdp-size-input');
    document.querySelectorAll('.size-pill:not([disabled])').forEach((pill) => {
        pill.addEventListener('click', () => {
            sizeInput.value = pill.getAttribute('data-size-pill');
            document.querySelectorAll('.size-pill:not([disabled])').forEach((p) => {
                p.classList.remove('bg-black', 'text-white');
                p.classList.add('bg-white', 'text-black');
            });
            pill.classList.remove('bg-white', 'text-black');
            pill.classList.add('bg-black', 'text-white');
        });
    });

    // Quantity stepper
    let qty = 1;
    const qtyInput = document.getElementById('pdp-qty-input');
    const qtyDisplay = document.getElementById('pdp-qty-display');
    document.getElementById('pdp-qty-minus')?.addEventListener('click', () => {
        qty = Math.max(1, qty - 1);
        qtyInput.value = qty;
        qtyDisplay.textContent = qty;
    });
    document.getElementById('pdp-qty-plus')?.addEventListener('click', () => {
        qty += 1;
        qtyInput.value = qty;
        qtyDisplay.textContent = qty;
    });

    document.getElementById('pdp-cod-btn')?.addEventListener('click', () => {
        document.getElementById('pdp-cod-msg')?.classList.remove('hidden');
    });

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const wlBtn = document.getElementById('pdp-wishlist-btn');
    wlBtn?.addEventListener('click', async () => {
        const res = await fetch('/wishlist/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ product_id: wlBtn.getAttribute('data-product-id') }),
        });
        if (res.status === 401) {
            document.getElementById('open-account-btn')?.click();
            return;
        }
        const data = await res.json();
        wlBtn.textContent = data.saved ? '♥ SAVED IN WISHLIST' : '♡ SAVE TO WISHLIST';
    });

    document.getElementById('pdp-notify-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = e.target.querySelector('input[name="email"]')?.value;
        await fetch(`/products/{{ $product->slug }}/notify`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ email }),
        });
        document.getElementById('pdp-notify-done')?.classList.remove('hidden');
        e.target.reset();
    });

    // PDP Zoom Loupe Mouse Tracking
    document.querySelectorAll('.pdp-zoom-container').forEach((box) => {
        box.addEventListener('mousemove', (e) => {
            const rect = box.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            box.style.setProperty('--zoom-x', `${x}%`);
            box.style.setProperty('--zoom-y', `${y}%`);
        });
    });

    // Fit Finder Modal
    const fitModal = document.getElementById('fit-finder-modal');
    document.getElementById('open-fit-finder')?.addEventListener('click', () => {
        fitModal?.classList.remove('hidden');
        fitModal?.classList.add('flex');
    });
    document.getElementById('close-fit-finder')?.addEventListener('click', () => {
        fitModal?.classList.add('hidden');
        fitModal?.classList.remove('flex');
    });
    document.getElementById('calc-fit-btn')?.addEventListener('click', () => {
        const h = parseInt(document.getElementById('fit-height')?.value || '175', 10);
        const w = parseInt(document.getElementById('fit-weight')?.value || '70', 10);
        let rec = 'L';
        if (h < 166 && w < 60) rec = 'S';
        else if (h < 174 && w < 68) rec = 'M';
        else if (h < 182 && w < 82) rec = 'L';
        else if (h < 190 && w < 95) rec = 'XL';
        else rec = 'XXL';
        const resEl = document.getElementById('fit-result');
        resEl.textContent = `RECOMMENDED: SIZE ${rec} (Relaxed Boxy Fit)`;
        resEl.classList.remove('hidden');
    });

    // Recently Viewed localStorage tracker
    try {
        const current = {
            slug: @json($product->slug),
            title: @json($product->title),
            price: @json(number_format((float) $product->price, 2)),
            url: @json(route('product.show', ['slug' => $product->slug])),
        };
        let list = JSON.parse(localStorage.getItem('kotb_recent') || '[]');
        list = [current, ...list.filter(x => x.slug !== current.slug)].slice(0, 5);
        localStorage.setItem('kotb_recent', JSON.stringify(list));
        const others = list.filter(x => x.slug !== current.slug).slice(0, 4);
        if (others.length > 0) {
            const sec = document.getElementById('recently-viewed-section');
            const grid = document.getElementById('recently-viewed-grid');
            sec?.classList.remove('hidden');
            grid.innerHTML = others.map(item => `
                <a href="${item.url}" data-nav class="border border-black/15 hover:border-black p-3.5 flex flex-col justify-between gap-2 bg-[#fafafa] transition-colors">
                    <div class="font-display text-xs leading-snug">${item.title}</div>
                    <div class="font-mono-meta text-[11px] text-[#666]">₱${item.price} PHP →</div>
                </a>
            `).join('');
        }
    } catch (e) {}
});
</script>
@endpush
