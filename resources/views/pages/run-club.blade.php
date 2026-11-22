@extends('layouts.app')

@section('title', 'KOTB RUN CLUB | KIDS OWN THE BLOCK')

@section('content')
<main class="bg-black text-white min-h-[calc(100vh-76px)] pt-12 pb-16">
    <div class="max-w-[1600px] mx-auto px-3 md:px-6">
        <div class="mb-8 anim-fade-up">
            <div class="font-mono-meta text-[11px] text-[#999] tracking-[0.14em] mb-2.5">KOTB CREW / STREET DIVISION</div>
            <div class="font-display inline-block border border-white px-3 py-1 text-xs tracking-[0.2em] mb-4">KOTB RUN CLUB</div>
            <h1 class="font-display text-3xl md:text-4xl leading-tight">ONE BLOCK. MORE GROUND.</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[1.15fr_0.85fr] gap-7 items-start">
            {{-- Left: Interactive Dark Route Map with Radar Ping --}}
            <div class="border border-white/25 bg-[#070707] p-5 min-h-[360px]">
                <div class="font-mono-meta text-[10px] text-[#888] mb-3">CITY ROUTE RADAR — SELECT PIN</div>
                <svg viewBox="0 0 100 75" class="w-full h-[290px] block">
                    @foreach([15, 30, 45, 60] as $y)
                        <line x1="0" y1="{{ $y }}" x2="100" y2="{{ $y }}" stroke="rgba(255,255,255,0.07)" stroke-width="0.3" />
                    @endforeach
                    @foreach([20, 40, 60, 80] as $x)
                        <line x1="{{ $x }}" y1="0" x2="{{ $x }}" y2="75" stroke="rgba(255,255,255,0.07)" stroke-width="0.3" />
                    @endforeach
                    <polyline points="{{ collect($locations)->map(fn($l) => $l['x'].','.$l['y'])->implode(' ') }}" fill="none" stroke="rgba(255,255,255,0.25)" stroke-width="0.5" stroke-dasharray="1.5 1.5" />

                    @foreach($locations as $idx => $loc)
                        <g class="rc-pin cursor-pointer" data-rc-idx="{{ $idx }}">
                            <circle class="rc-ring {{ $idx === 0 ? '' : 'hidden' }}" cx="{{ $loc['x'] }}" cy="{{ $loc['y'] }}" r="4.2" fill="none" stroke="#d9ff00" stroke-width="0.4" style="transform-origin: {{ $loc['x'] }}px {{ $loc['y'] }}px; animation: radarPing 2s ease-out infinite;" />
                            <circle class="rc-dot" cx="{{ $loc['x'] }}" cy="{{ $loc['y'] }}" r="{{ $idx === 0 ? '2.2' : '1.5' }}" fill="{{ $idx === 0 ? '#d9ff00' : '#ffffff' }}" />
                            <text class="rc-label" x="{{ $loc['x'] + 3.2 }}" y="{{ $loc['y'] + 1 }}" fill="{{ $idx === 0 ? '#d9ff00' : '#aaaaaa' }}" font-size="2.7" font-family="JetBrains Mono, monospace">{{ $loc['city'] }}</text>
                        </g>
                    @endforeach
                </svg>
            </div>

            {{-- Right: Sharp 0px-radius NEXT RUN Card + Chapter Pills --}}
            <div class="flex flex-col gap-5">
                <div class="border border-white/35 p-6 bg-[#050505]">
                    <div id="rc-district" class="font-mono-meta text-[11px] text-[#d9ff00] mb-2">NEXT RUN · {{ $locations[0]['district'] }}</div>
                    <h2 id="rc-city" class="font-display text-[22px] mb-3.5">{{ $locations[0]['city'] }}</h2>

                    <div class="font-mono-meta grid grid-cols-2 gap-3 py-3.5 border-y border-white/15 text-xs mb-4">
                        <div>
                            <div class="text-[#777] text-[10px]">DATE &amp; TIME</div>
                            <div id="rc-date">{{ $locations[0]['nextDate'] }}</div>
                        </div>
                        <div>
                            <div class="text-[#777] text-[10px]">DISTANCE / PACE</div>
                            <div id="rc-pace">{{ $locations[0]['distance'] }} · {{ $locations[0]['pace'] }}</div>
                        </div>
                    </div>

                    <div id="rc-spot" class="text-[13px] text-[#ccc] mb-5">📍 {{ $locations[0]['meetupSpot'] }}</div>

                    <button id="rc-rsvp-btn" type="button" class="font-display border border-white px-5 py-3 text-white text-xs inline-flex items-center gap-2 hover:bg-white hover:text-black transition-colors cursor-pointer">
                        <span>REGISTER NOW</span>
                        <span>↗</span>
                    </button>
                    <div id="rc-rsvp-done" class="hidden font-mono-meta border border-[#d9ff00] text-[#d9ff00] px-4 py-3 text-xs">
                        ✓ REGISTERED FOR SESSION. SEE YOU ON THE BLOCK.
                    </div>
                </div>

                <div>
                    <div class="font-mono-meta text-[10px] text-[#888] mb-2.5">ACTIVE CHAPTERS</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($locations as $idx => $loc)
                            <button type="button" data-rc-idx="{{ $idx }}" class="rc-pill font-mono-meta rounded-[30px] border px-3.5 py-1.5 text-[11px] cursor-pointer {{ $idx === 0 ? 'border-white bg-white text-black' : 'border-white/30 text-white' }}">
                                {{ $loc['city'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const locations = @json($locations);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let activeIdx = 0;

    const selectChapter = (idx) => {
        activeIdx = idx;
        const loc = locations[idx];
        document.getElementById('rc-district').textContent = `NEXT RUN · ${loc.district}`;
        document.getElementById('rc-city').textContent = loc.city;
        document.getElementById('rc-date').textContent = loc.nextDate;
        document.getElementById('rc-pace').textContent = `${loc.distance} · ${loc.pace}`;
        document.getElementById('rc-spot').textContent = `📍 ${loc.meetupSpot}`;
        document.getElementById('rc-rsvp-btn').classList.remove('hidden');
        document.getElementById('rc-rsvp-done').classList.add('hidden');

        document.querySelectorAll('.rc-pin').forEach((pin, i) => {
            pin.querySelector('.rc-ring').classList.toggle('hidden', i !== idx);
            pin.querySelector('.rc-dot').setAttribute('r', i === idx ? '2.2' : '1.5');
            pin.querySelector('.rc-dot').setAttribute('fill', i === idx ? '#d9ff00' : '#ffffff');
            pin.querySelector('.rc-label').setAttribute('fill', i === idx ? '#d9ff00' : '#aaaaaa');
        });
        document.querySelectorAll('.rc-pill').forEach((pill, i) => {
            pill.classList.toggle('bg-white', i === idx);
            pill.classList.toggle('text-black', i === idx);
            pill.classList.toggle('border-white', i === idx);
            pill.classList.toggle('text-white', i !== idx);
        });
    };

    document.querySelectorAll('[data-rc-idx]').forEach((el) => {
        el.addEventListener('click', () => selectChapter(parseInt(el.getAttribute('data-rc-idx'), 10)));
    });

    document.getElementById('rc-rsvp-btn')?.addEventListener('click', async () => {
        const loc = locations[activeIdx];
        await fetch('/run-club/rsvp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ location_id: loc.id, city: loc.city }),
        });
        document.getElementById('rc-rsvp-btn').classList.add('hidden');
        document.getElementById('rc-rsvp-done').classList.remove('hidden');
    });
});
</script>
@endpush
