@extends('layouts.app')

@section('title', 'BLOCK STORY | KIDS OWN THE BLOCK')

@section('content')
<main class="bg-white pt-10 pb-16">
    <div class="max-w-[1600px] mx-auto px-3 md:px-6">
        <div class="flex justify-between items-baseline flex-wrap gap-3 mb-7">
            <h1 class="font-display text-[28px]">BLOCK STORY</h1>
            <div class="flex gap-4">
                <a href="{{ route('lookbooks') }}" data-nav class="font-mono-meta text-[11px] underline">LOOKBOOKS ↗</a>
                <a href="{{ route('run-club') }}" data-nav class="font-mono-meta text-[11px] underline">RUN CLUB ↗</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1.4fr_0.6fr] gap-9 items-start">
            {{-- Left Column: Campaign & BTS Video Feed --}}
            <div>
                @php $featured = $videos[0]; @endphp
                <div class="border border-black mb-5 reveal-on-scroll">
                    <div class="aspect-video bg-[#0a0a0a] relative flex items-center justify-center text-white overflow-hidden">
                        <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(110,14,14,0.55) 0%, rgba(0,0,0,0.92) 75%);"></div>
                        <button type="button" onclick="this.nextElementSibling.classList.remove('hidden'); this.classList.add('hidden')" class="relative z-10 w-16 h-16 rounded-full border border-white bg-black/65 flex items-center justify-center hover:scale-105 transition-transform cursor-pointer" aria-label="Play featured video">
                            ▶
                        </button>
                        <div class="hidden relative z-10 text-center p-6">
                            <div class="font-display text-lg mb-2">NOW PLAYING: {{ $featured['title'] }}</div>
                            <p class="font-mono-meta text-xs text-[#aaa]">{{ $featured['subtitle'] }}</p>
                        </div>
                        <span class="font-mono-meta absolute bottom-3.5 right-4 text-[11px] text-[#ccc]">{{ $featured['duration'] }}</span>
                    </div>
                    <div class="p-4">
                        <div class="font-mono-meta text-[11px] text-[#666] mb-1.5">{{ $featured['tag'] }} · {{ $featured['date'] }}</div>
                        <h2 class="font-display text-lg mb-2">{{ $featured['title'] }}</h2>
                        <p class="text-[13px] text-[#333] leading-relaxed mb-3">{{ $featured['subtitle'] }}</p>
                        <span class="font-mono-meta text-[11px] font-semibold">WATCH ON YOUTUBE ↗</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach(array_slice($videos, 1) as $idx => $item)
                        <div class="border border-black/15 reveal-on-scroll" data-delay="{{ $idx * 80 }}">
                            <div class="aspect-video bg-[#141414] flex items-center justify-center text-white relative">
                                <button type="button" onclick="this.textContent='PLAYING · {{ $item['duration'] }}'; this.classList.remove('rounded-full','w-11','h-11','border')" class="w-11 h-11 rounded-full border border-white flex items-center justify-center text-xs hover:scale-105 transition-transform cursor-pointer">▶</button>
                            </div>
                            <div class="p-3.5">
                                <div class="font-mono-meta text-[10px] text-[#666] mb-1">{{ $item['tag'] }} · {{ $item['date'] }}</div>
                                <h3 class="font-display text-[13px] mb-1.5 leading-snug">{{ $item['title'] }}</h3>
                                <p class="text-xs text-[#444] leading-relaxed mb-2.5">{{ $item['subtitle'] }}</p>
                                <span class="font-mono-meta text-[10px] font-semibold">WATCH ON YOUTUBE ↗</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Right Column: LATEST POSTS Sidebar --}}
            <aside class="lg:border-l lg:border-black/10 lg:pl-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="font-display text-sm">LATEST POSTS</h2>
                    <a href="{{ route('lookbooks') }}" data-nav class="font-mono-meta text-[10px]">VIEW ALL POSTS ↗</a>
                </div>
                <div class="flex flex-col gap-4">
                    @foreach([
                        ['handle' => '@kidsowntheblock', 'caption' => 'Drop 04 Heavyweight 240 GSM Tees live now online & flagship.', 'bg' => '#161616', 'tag' => 'DROP 04'],
                        ['handle' => '@kotb.runclub', 'caption' => 'Thursday 7:30 PM Downtown 5.2K loop. All paces welcome.', 'bg' => '#5a0c0c', 'tag' => 'RUN CLUB'],
                        ['handle' => '@kidsowntheblock', 'caption' => 'Midnight Block Lookbook shot in studio. 5 new silhouettes.', 'bg' => '#162742', 'tag' => 'LOOKBOOK'],
                    ] as $idx => $post)
                        <div class="border border-black/15 p-3 reveal-on-scroll" data-delay="{{ $idx * 90 }}">
                            <div class="aspect-square text-white flex flex-col justify-between p-3.5 mb-2.5" style="background:{{ $post['bg'] }}">
                                <span class="font-mono-meta text-[10px] opacity-75">{{ $post['handle'] }}</span>
                                <span class="font-display text-lg">{{ $post['tag'] }}</span>
                            </div>
                            <p class="text-xs leading-relaxed">{{ $post['caption'] }}</p>
                        </div>
                    @endforeach
                </div>
            </aside>
        </div>
    </div>
</main>
@endsection
