@extends('layouts.app')

@section('title', 'LOOKBOOKS | KIDS OWN THE BLOCK')

@section('content')
<main class="bg-white pt-10 pb-16">
    <div class="max-w-[1600px] mx-auto px-3 md:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-10 lg:gap-12 items-start">
            {{-- Left: Sharp 0px-radius Editorial Photo with Ken Burns Motion --}}
            <div class="anim-fade-up">
                <div class="aspect-[4/5.2] w-full max-w-[560px] overflow-hidden bg-black">
                    <svg id="lb-main-svg" viewBox="0 0 400 520" class="anim-ken-burns w-full h-full block">
                        <rect id="lb-bg" width="400" height="520" fill="{{ $collections[0]['slides'][0]['bg'] }}" />
                        <path id="lb-d1" d="M0 0 Q55 260 15 520 L0 520 Z" fill="{{ $collections[0]['slides'][0]['drape'] }}" opacity="0.7" />
                        <path id="lb-d2" d="M120 0 Q150 260 100 520 L65 520 Q95 260 75 0 Z" fill="{{ $collections[0]['slides'][0]['drape'] }}" opacity="0.45" />
                        <path id="lb-d3" d="M295 0 Q330 260 285 520 L250 520 Q285 260 260 0 Z" fill="{{ $collections[0]['slides'][0]['drape'] }}" opacity="0.5" />
                        <path id="lb-d4" d="M400 0 L400 520 L355 520 Q330 260 365 0 Z" fill="{{ $collections[0]['slides'][0]['drape'] }}" opacity="0.75" />
                        <ellipse cx="200" cy="482" rx="110" ry="16" fill="rgba(0,0,0,0.45)" />
                        <circle id="lb-head" cx="200" cy="112" r="26" fill="{{ $collections[0]['slides'][0]['sil'] }}" />
                        <path id="lb-body" d="M148 164 Q200 144 252 164 L276 312 L242 318 L234 475 L204 475 L200 340 L196 475 L166 475 L158 318 L124 312 Z" fill="{{ $collections[0]['slides'][0]['sil'] }}" />
                        <rect id="lb-box" x="172" y="204" width="56" height="20" fill="none" stroke="{{ $collections[0]['slides'][0]['acc'] }}" stroke-width="2" />
                        <text id="lb-text" x="200" y="218" text-anchor="middle" fill="{{ $collections[0]['slides'][0]['acc'] }}" font-size="10" font-weight="800" font-family="Archivo, sans-serif" letter-spacing="2">KOTB</text>
                        <text id="lb-look-tag" x="24" y="494" fill="rgba(255,255,255,0.65)" font-size="10" font-family="JetBrains Mono, monospace">LOOK 01</text>
                    </svg>
                </div>
                <h1 id="lb-col-title" class="font-display text-[22px] mt-5 mb-1.5">{{ $collections[0]['title'] }}</h1>
                <div id="lb-look-label" class="text-[13px] font-semibold">Look 1</div>
                <div id="lb-look-caption" class="font-mono-meta text-xs text-[#666] mt-1">
                    {{ $collections[0]['slides'][0]['title'] }} — {{ $collections[0]['slides'][0]['garments'] }}
                </div>
            </div>

            {{-- Right: 5-item Thumbnail Strip + Counter + Collections Archive --}}
            <div class="flex flex-col gap-6 anim-fade-up" style="animation-delay: 120ms;">
                <div id="lb-thumbs" class="grid grid-cols-5 gap-1.5">
                    @foreach($collections[0]['slides'] as $idx => $s)
                        <button type="button" data-lb-idx="{{ $idx }}" class="lb-thumb aspect-[4/5] overflow-hidden border {{ $idx === 0 ? 'border-black p-0.5' : 'border-transparent' }} cursor-pointer">
                            <svg viewBox="0 0 400 520" class="w-full h-full block" style="background:{{ $s['bg'] }}">
                                <rect width="400" height="520" fill="{{ $s['bg'] }}" />
                                <circle cx="200" cy="112" r="26" fill="{{ $s['sil'] }}" />
                                <path d="M148 164 Q200 144 252 164 L276 312 L242 318 L234 475 L204 475 L200 340 L196 475 L166 475 L158 318 L124 312 Z" fill="{{ $s['sil'] }}" />
                            </svg>
                        </button>
                    @endforeach
                </div>

                <div class="font-mono-meta flex items-center justify-between gap-4 text-[13px]">
                    <div class="flex items-center gap-4">
                        <button id="lb-prev" type="button" aria-label="Previous look" class="cursor-pointer">←</button>
                        <span id="lb-counter">1 of {{ count($collections[0]['slides']) }}</span>
                        <button id="lb-next" type="button" aria-label="Next look" class="cursor-pointer">→</button>
                    </div>
                    <a href="{{ route('shop') }}" data-nav class="btn-sheen font-display bg-black text-white rounded-[30px] px-4 py-2 text-[11px]">
                        SHOP THIS LOOK ↗
                    </a>
                </div>

                <div class="border-t border-black/10 pt-5">
                    <button id="lb-archive-toggle" type="button" class="font-display inline-flex items-center gap-2 text-[13px] cursor-pointer">
                        <span>COLLECTIONS</span>
                        <span>▾</span>
                    </button>
                    <div id="lb-archive-list" class="hidden flex-col gap-2.5 mt-3.5">
                        @foreach($collections as $cIdx => $col)
                            <button type="button" data-col-idx="{{ $cIdx }}" class="lb-col-btn text-left px-3.5 py-2.5 border {{ $cIdx === 0 ? 'border-black bg-[#f5f5f5]' : 'border-black/10 bg-white' }} cursor-pointer">
                                <div class="font-display text-xs">{{ $col['title'] }}</div>
                                <div class="font-mono-meta text-[11px] text-[#666] mt-0.5">{{ $col['season'] }} · {{ count($col['slides']) }} LOOKS</div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Shop the Look Tagged Garments --}}
                @if(($lookProducts ?? collect())->isNotEmpty())
                    <div class="border-t border-black/10 pt-5">
                        <div class="font-mono-meta text-[10px] text-[#666] mb-3">TAGGED GARMENTS IN THIS COLLECTION</div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($lookProducts as $lp)
                                <a href="{{ route('product.show', ['slug' => $lp->slug]) }}" data-nav class="border border-black/15 hover:border-black p-2.5 flex items-center gap-2.5 transition-colors bg-[#fafafa]">
                                    <div class="w-10 h-12 shrink-0 bg-white overflow-hidden">
                                        @include('components.garment-svg', ['product' => $lp, 'view' => 'front'])
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-display text-[10px] truncate">{{ $lp->title }}</div>
                                        <div class="font-mono-meta text-[10px] text-[#666]">₱{{ number_format((float) $lp->price, 2) }} →</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const collections = @json($collections);
    let colIdx = 0;
    let slideIdx = 0;

    const renderSlide = () => {
        const col = collections[colIdx];
        const slide = col.slides[slideIdx] || col.slides[0];
        const mainSvg = document.getElementById('lb-main-svg');
        mainSvg.classList.remove('anim-shutter');
        void mainSvg.offsetWidth;
        mainSvg.classList.add('anim-shutter');
        document.getElementById('lb-bg').setAttribute('fill', slide.bg);
        ['lb-d1','lb-d2','lb-d3','lb-d4'].forEach(id => document.getElementById(id).setAttribute('fill', slide.drape));
        document.getElementById('lb-head').setAttribute('fill', slide.sil);
        document.getElementById('lb-body').setAttribute('fill', slide.sil);
        document.getElementById('lb-box').setAttribute('stroke', slide.acc);
        document.getElementById('lb-text').setAttribute('fill', slide.acc);
        document.getElementById('lb-look-tag').textContent = `LOOK 0${slide.look}`;
        document.getElementById('lb-col-title').textContent = col.title;
        document.getElementById('lb-look-label').textContent = `Look ${slide.look}`;
        document.getElementById('lb-look-caption').textContent = `${slide.title} — ${slide.garments}`;
        document.getElementById('lb-counter').textContent = `${slideIdx + 1} of ${col.slides.length}`;

        document.querySelectorAll('.lb-thumb').forEach((t, i) => {
            t.classList.toggle('border-black', i === slideIdx);
            t.classList.toggle('p-0.5', i === slideIdx);
            t.classList.toggle('border-transparent', i !== slideIdx);
        });
    };

    document.getElementById('lb-thumbs')?.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-lb-idx]');
        if (!btn) return;
        slideIdx = parseInt(btn.getAttribute('data-lb-idx'), 10);
        renderSlide();
    });

    document.getElementById('lb-prev')?.addEventListener('click', () => {
        const len = collections[colIdx].slides.length;
        slideIdx = (slideIdx - 1 + len) % len;
        renderSlide();
    });
    document.getElementById('lb-next')?.addEventListener('click', () => {
        const len = collections[colIdx].slides.length;
        slideIdx = (slideIdx + 1) % len;
        renderSlide();
    });

    document.getElementById('lb-archive-toggle')?.addEventListener('click', () => {
        const list = document.getElementById('lb-archive-list');
        list.classList.toggle('hidden');
        list.classList.toggle('flex');
    });

    document.querySelectorAll('.lb-col-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            colIdx = parseInt(btn.getAttribute('data-col-idx'), 10);
            slideIdx = 0;
            const thumbs = document.getElementById('lb-thumbs');
            thumbs.innerHTML = collections[colIdx].slides.map((s, idx) => `
                <button type="button" data-lb-idx="${idx}" class="lb-thumb aspect-[4/5] overflow-hidden border ${idx === 0 ? 'border-black p-0.5' : 'border-transparent'} cursor-pointer">
                    <svg viewBox="0 0 400 520" class="w-full h-full block" style="background:${s.bg}">
                        <rect width="400" height="520" fill="${s.bg}" />
                        <circle cx="200" cy="112" r="26" fill="${s.sil}" />
                        <path d="M148 164 Q200 144 252 164 L276 312 L242 318 L234 475 L204 475 L200 340 L196 475 L166 475 L158 318 L124 312 Z" fill="${s.sil}" />
                    </svg>
                </button>
            `).join('');
            renderSlide();
        });
    });
});
</script>
@endpush
