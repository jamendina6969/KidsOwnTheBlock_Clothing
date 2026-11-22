@extends('layouts.app')

@section('title', 'DROP ARCHIVE | KOTB')

@section('content')
<main class="bg-white pt-10 pb-20">
    <div class="max-w-[1600px] mx-auto px-3 md:px-6">
        <div class="pb-6 mb-10 border-b border-black">
            <div class="font-mono-meta text-xs text-[#666] mb-1">CHRONOLOGICAL CAPSULE TIMELINE</div>
            <h1 class="font-display text-2xl md:text-3xl">DROP ARCHIVE</h1>
        </div>

        <div class="flex flex-col gap-14">
            @foreach($drops as $dIdx => $drop)
                <section class="reveal-on-scroll border-l-2 border-black pl-5 md:pl-8" data-delay="{{ $dIdx * 80 }}">
                    <div class="flex flex-wrap items-baseline gap-3 mb-2">
                        <span class="font-mono-meta text-xs bg-black text-white px-2.5 py-0.5">{{ $drop['drop'] }}</span>
                        <span class="font-mono-meta text-xs text-[#666]">{{ $drop['date'] }}</span>
                    </div>
                    <h2 class="font-display text-xl mb-1.5">{{ $drop['title'] }}</h2>
                    <p class="font-mono-meta text-xs text-[#555] mb-5 max-w-2xl">{{ $drop['note'] }}</p>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-1 md:gap-2">
                        @foreach($drop['items'] as $idx => $item)
                            @include('components.product-card', ['product' => $item, 'surface' => 'white', 'delay' => $idx * 50])
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</main>
@endsection
