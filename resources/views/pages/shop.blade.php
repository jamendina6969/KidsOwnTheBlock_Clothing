@extends('layouts.app')

@section('title', $category . ' | KIDS OWN THE BLOCK')

@section('content')
{{-- Brutalist Animated Marquee Ticker --}}
<div class="marquee-wrapper bg-black text-white border-b border-white/15 overflow-hidden py-2">
    <div class="marquee-track font-mono-meta text-[11px] tracking-[0.18em] uppercase">
        @for($i = 0; $i < 6; $i++)
            <span class="mx-6">{{ $storeBanner['marquee_text'] ?? 'DROP 04 LIVE · 240 GSM HEAVYWEIGHT · KIDS OWN THE BLOCK · WORLDWIDE DISPATCH ·' }}</span>
        @endfor
    </div>
</div>

{{-- Drop 05 Live Countdown Bar --}}
<div class="bg-[#111] text-white border-b border-black py-2 px-4 text-center font-mono-meta text-[11px] tracking-[0.12em]">
    DROP 05 ARCHIVE CAPSULE UNLOCKS IN: <span id="kotb-drop-countdown" data-target="{{ $storeBanner['drop_target_iso'] ?? '' }}" class="text-[#d9ff00] font-bold ml-1">02D : 14H : 38M : 12S</span>
</div>

{{-- 3 Horizontal Slider Bands on #EDEDED --}}
@if($category === 'ALL PRODUCTS')
    @foreach([
        ['title' => 'NEW COLLECTION', 'items' => $newCollection, 'id' => 'slider-new'],
        ['title' => 'RESTOCKED PRODUCTS', 'items' => $restocked, 'id' => 'slider-restocked'],
        ['title' => 'HERITAGE PIECES', 'items' => $heritage, 'id' => 'slider-heritage'],
    ] as $band)
        <section class="bg-[#ededed] pt-8 pb-9 border-b border-black/10">
            <div class="max-w-[1600px] mx-auto px-3 md:px-6">
                <div class="flex items-center justify-between mb-4 reveal-on-scroll">
                    <h2 class="font-display text-lg">{{ $band['title'] }}</h2>
                    <div class="flex gap-2">
                        <button type="button" data-slider-btn="-1" data-slider-target="{{ $band['id'] }}" aria-label="Scroll left" class="w-8 h-8 rounded-full border border-black/25 bg-white flex items-center justify-center hover:bg-black hover:text-white transition-colors cursor-pointer">
                            ‹
                        </button>
                        <button type="button" data-slider-btn="1" data-slider-target="{{ $band['id'] }}" aria-label="Scroll right" class="w-8 h-8 rounded-full border border-black/25 bg-white flex items-center justify-center hover:bg-black hover:text-white transition-colors cursor-pointer">
                            ›
                        </button>
                    </div>
                </div>
                <div id="{{ $band['id'] }}" class="no-scrollbar grid grid-flow-col auto-cols-[70%] sm:auto-cols-[44%] md:auto-cols-[31%] lg:auto-cols-[24%] gap-1 sm:gap-2 overflow-x-auto">
                    @foreach($band['items'] as $idx => $product)
                        @include('components.product-card', ['product' => $product, 'surface' => 'grey', 'delay' => $idx * 55])
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach
@endif

{{-- ALL PRODUCTS Grid on Pure White --}}
<section class="bg-white pt-11 pb-16">
    <div class="max-w-[1600px] mx-auto px-3 md:px-6">
        <div class="flex items-baseline justify-between flex-wrap gap-3 mb-6">
            <h1 class="font-display text-2xl">{{ $category }}</h1>
            @if($category !== 'ALL PRODUCTS')
                <a href="{{ route('shop') }}" data-nav class="font-mono-meta text-[11px] underline">VIEW ALL PRODUCTS</a>
            @endif
        </div>

        {{-- Filter & Sort Form --}}
        <form method="GET" action="{{ route('shop') }}" class="flex flex-wrap items-center justify-between gap-4 pb-5 mb-5 border-b border-black/10">
            <input type="hidden" name="category" value="{{ $category }}" />

            <div class="flex flex-wrap items-center gap-4">
                <span class="font-mono-meta text-xs text-[#666]">Filter:</span>
                <select name="availability" onchange="this.form.submit()" class="text-[13px] bg-transparent cursor-pointer outline-none">
                    <option value="all" {{ $availability === 'all' ? 'selected' : '' }}>Availability: All</option>
                    <option value="in-stock" {{ $availability === 'in-stock' ? 'selected' : '' }}>In stock</option>
                    <option value="sold-out" {{ $availability === 'sold-out' ? 'selected' : '' }}>Sold out</option>
                </select>
                <select name="price" onchange="this.form.submit()" class="text-[13px] bg-transparent cursor-pointer outline-none">
                    <option value="all" {{ $priceRange === 'all' ? 'selected' : '' }}>Price: Any</option>
                    <option value="under-1000" {{ $priceRange === 'under-1000' ? 'selected' : '' }}>Under ₱1,000</option>
                    <option value="1000-2500" {{ $priceRange === '1000-2500' ? 'selected' : '' }}>₱1,000 – ₱2,500</option>
                    <option value="2500-plus" {{ $priceRange === '2500-plus' ? 'selected' : '' }}>₱2,500+</option>
                </select>
            </div>

            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2 text-[13px]">
                    <span class="text-[#666]">Sort by:</span>
                    <select name="sort" onchange="this.form.submit()" class="bg-transparent font-medium cursor-pointer outline-none">
                        <option value="featured" {{ $sort === 'featured' ? 'selected' : '' }}>Featured</option>
                        <option value="best-selling" {{ $sort === 'best-selling' ? 'selected' : '' }}>Best selling</option>
                        <option value="az" {{ $sort === 'az' ? 'selected' : '' }}>Alphabetically, A-Z</option>
                        <option value="price-asc" {{ $sort === 'price-asc' ? 'selected' : '' }}>Price, low to high</option>
                        <option value="price-desc" {{ $sort === 'price-desc' ? 'selected' : '' }}>Price, high to low</option>
                    </select>
                </label>
                <span class="font-mono-meta text-xs text-[#666]">{{ $totalCount }} {{ $totalCount === 1 ? 'product' : 'products' }}</span>
            </div>
        </form>

        {{-- 4-Col Desktop / 2-Col Mobile Grid --}}
        @if($products->isEmpty())
            <div class="py-16 text-center">
                <p class="font-display text-base mb-3">NO MATCHING GARMENTS</p>
                <a href="{{ route('shop') }}" data-nav class="font-display inline-block border border-black rounded-[30px] px-6 py-2.5 text-xs">RESET FILTERS</a>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 gap-1 md:gap-2">
                @foreach($products as $idx => $product)
                    @include('components.product-card', ['product' => $product, 'surface' => 'white', 'delay' => ($idx % 4) * 65])
                @endforeach
            </div>
        @endif

        {{-- Pagination --}}
        @if($totalPages > 1)
            <div class="font-mono-meta flex justify-center items-center gap-5 mt-12 text-[13px]">
                @for($p = 1; $p <= $totalPages; $p++)
                    <a href="{{ route('shop', array_merge(request()->query(), ['page' => $p])) }}" data-nav class="pb-0.5 {{ $p === $currentPage ? 'border-b border-black font-semibold' : 'border-b border-transparent' }}">
                        {{ $p }}
                    </a>
                @endfor
                @if($currentPage < $totalPages)
                    <a href="{{ route('shop', array_merge(request()->query(), ['page' => $currentPage + 1])) }}" data-nav aria-label="Next page">&gt;</a>
                @endif
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('kotb-drop-countdown');
    if (!el) return;
    const targetIso = el.getAttribute('data-target');
    const targetMs = targetIso ? Date.parse(targetIso) : (Date.now() + 210000000);
    const tick = () => {
        const remaining = Math.max(0, Math.floor((targetMs - Date.now()) / 1000));
        const d = String(Math.floor(remaining / 86400)).padStart(2, '0');
        const h = String(Math.floor((remaining % 86400) / 3600)).padStart(2, '0');
        const m = String(Math.floor((remaining % 3600) / 60)).padStart(2, '0');
        const s = String(remaining % 60).padStart(2, '0');
        el.textContent = `${d}D : ${h}H : ${m}M : ${s}S`;
    };
    tick();
    setInterval(tick, 1000);
});
</script>
@endpush
