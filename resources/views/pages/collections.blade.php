@extends('layouts.app')

@section('title', 'COLLECTIONS | KOTB')

@section('content')
<main class="bg-white pt-10 pb-20">
    <div class="max-w-[1600px] mx-auto px-3 md:px-6">
        <div class="flex items-baseline justify-between flex-wrap gap-3 pb-6 mb-8 border-b border-black/10">
            <h1 class="font-display text-2xl md:text-3xl">COLLECTIONS</h1>
            <span class="font-mono-meta text-xs text-[#666]">{{ $collections->count() }} COLLECTIONS</span>
        </div>

        {{-- Dosmil2000-Style Minimalist Collections Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 md:gap-3">
            @foreach($collections as $idx => $col)
                <a
                    href="{{ route('shop', ['category' => $col['name']]) }}"
                    data-nav
                    data-delay="{{ ($idx % 4) * 60 }}"
                    class="product-card reveal-on-scroll group block bg-[#ededed] border border-transparent hover:border-black transition-colors"
                >
                    <div class="aspect-[4/5] w-full overflow-hidden p-4">
                        <div class="product-card-media">
                            @include('components.garment-svg', ['product' => $col['preview'], 'view' => 'front'])
                        </div>
                    </div>
                    <div class="bg-white px-3 py-3.5 border-t border-black/10 flex items-center justify-between gap-2">
                        <span class="font-display text-xs md:text-sm group-hover:underline">{{ $col['name'] }}</span>
                        <span class="font-mono-meta text-[11px] text-[#666] shrink-0">{{ $col['count'] }} →</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</main>
@endsection
