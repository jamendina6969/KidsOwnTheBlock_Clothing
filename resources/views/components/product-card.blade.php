@php
    $surface = $surface ?? 'white';
    $delay = $delay ?? 0;
@endphp

<a
    href="{{ route('product.show', ['slug' => $product->slug]) }}"
    data-nav
    data-delay="{{ $delay }}"
    aria-label="{{ $product->title }} — ₱{{ number_format((float) $product->price, 2) }} PHP{{ $product->sold_out ? ' (Sold out)' : '' }}"
    class="product-card reveal-on-scroll relative block w-full aspect-[4/5] overflow-hidden {{ $surface === 'grey' ? 'bg-[#ededed]' : 'bg-white' }}"
>
    @if($product->sold_out)
        <span class="anim-badge-glow absolute top-2.5 left-2.5 z-10 bg-white text-black border border-black rounded-[4rem] px-3 py-1 text-[11px] font-medium leading-tight">
            Sold out
        </span>
    @endif

    <div class="product-card-media">
        <div class="w-full h-full">
            @include('components.garment-svg', ['product' => $product, 'view' => 'front'])
        </div>
        <div class="view-back w-full h-full {{ $surface === 'grey' ? 'bg-[#ededed]' : 'bg-white' }}">
            @include('components.garment-svg', ['product' => $product, 'view' => 'back'])
        </div>
    </div>
</a>
