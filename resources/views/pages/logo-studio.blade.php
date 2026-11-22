@extends('layouts.app')

@section('title', '100 DOSMIL-STYLE LOGO MOCKUPS | KIDS OWN THE BLOCK')

@section('content')
<main class="bg-white pt-10 pb-20">
    <div class="max-w-[1600px] mx-auto px-3 md:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4 pb-6 mb-7 border-b border-black">
            <div>
                <div class="font-mono-meta text-xs text-[#666] mb-1">DOSMIL-INSPIRED STENCIL LAB · 100 VARIATIONS</div>
                <h1 class="font-display text-2xl md:text-3xl">KIDS OWN THE BLOCK — 100 STENCIL LOGO MOCKUPS</h1>
                <p class="font-mono-meta text-xs text-[#666] mt-1.5">
                    Symmetrical ink-cut dual-profile &amp; street-stencil emblems inspired by Dosmil2000. Click any mockup to download SVG or apply to your Landing Page.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="toggle-theme-btn" class="font-display border border-black rounded-[30px] px-4 py-2 text-xs cursor-pointer hover:bg-black hover:text-white transition-colors">
                    INVERT PREVIEW (BLACK / WHITE)
                </button>
                <a href="{{ route('splash') }}" data-nav class="font-display bg-black text-white rounded-[30px] px-5 py-2 text-xs">
                    VIEW LANDING PAGE ↗
                </a>
            </div>
        </div>

        {{-- Filter Chips --}}
        <div class="flex flex-wrap items-center gap-2 mb-6">
            @foreach(['ALL 100', 'TWOFACE STENCIL', 'HOODED PROFILES', 'RORSCHACH INK', 'WORDMARK + MARK'] as $idx => $cat)
                <button type="button" data-logo-filter="{{ $cat }}" class="logo-filter-btn rounded-[30px] border border-black px-4 py-1.5 text-xs font-semibold cursor-pointer {{ $idx === 0 ? 'bg-black text-white' : 'bg-white text-black' }}">
                    {{ $cat }}
                </button>
            @endforeach
            <span id="logo-count-label" class="font-mono-meta text-xs text-[#666] ml-auto">100 mockups</span>
        </div>

        <div id="logo-toast" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-black text-[#d9ff00] font-mono-meta text-xs px-5 py-3 border border-white/20">
            ✓ Logo applied to Landing Page!
        </div>

        <div id="logo-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3"></div>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const grid = document.getElementById('logo-grid');
    let darkCard = false; // Light grey/white header preview first, toggleable to black splash
    let activeFilter = 'ALL 100';

    // 10 Organic Dosmil2000-Inspired Symmetrical Street-Stencil Marks (centered in 200x110)
    const marks = [
        {
            name: 'Laugh vs Depress Youngstunna',
            group: 'TWOFACE STENCIL',
            draw: (fg, bg) => `
                <g transform="translate(30, 0) scale(0.92)">
                    <path d="M44 10 C24 12 12 28 8 46 L19 48 C19 42 20 36 22 34 C30 35 37 32 42 32 C39 36 37 39 40 42 C46 44 52 42 58 45 C64 49 70 50 70 50 C70 50 76 49 82 45 C88 42 94 44 100 42 C103 39 101 36 98 32 C103 32 110 35 118 34 C120 36 121 42 121 48 L132 46 C128 28 116 12 96 10 C92 10 89 12 87 16 C92 26 84 40 70 40 C56 40 48 26 53 16 C51 12 48 10 44 10 Z" fill="${fg}" />
                    <path d="M27 46 C23 46 21 51 18 53 C12 53 9 58 11 63 C14 67 17 70 16 78 C15 84 20 88 26 85 C33 88 42 88 48 84 C50 79 44 74 44 68 C45 63 54 61 58 58 C61 55 60 49 54 49 C47 49 41 53 36 52 C32 51 31 46 27 46 Z" fill="${fg}" />
                    <path d="M21 64 Q29 56 37 64 Q29 61 21 64 Z" fill="${bg}" />
                    <path d="M18 90 Q32 99 45 89 Q32 93 18 90 Z" fill="${fg}" />
                    <path d="M22 103 Q32 106 41 99 Q33 98 22 103 Z" fill="${fg}" />
                    <path d="M113 46 C117 46 119 51 122 53 C128 53 131 58 129 63 C126 67 123 70 124 78 C125 84 120 88 114 85 C107 88 98 88 92 84 C90 79 96 74 96 68 C95 63 86 61 82 58 C79 55 80 49 86 49 C93 49 99 53 104 52 C108 51 109 46 113 46 Z" fill="${fg}" />
                    <path d="M102 60 Q111 63 119 60 Q114 70 105 68 Z" fill="${bg}" />
                    <circle cx="114" cy="74" r="1.8" fill="${bg}" />
                    <path d="M95 95 Q108 86 121 95 Q108 91 95 95 Z" fill="${fg}" />
                    <path d="M98 102 Q108 100 117 105 Q107 107 98 102 Z" fill="${fg}" />
                </g>`
        },
        {
            name: 'Hooded Twins',
            group: 'HOODED PROFILES',
            draw: (fg, bg) => `
                <g transform="translate(40, 2)">
                    <path d="M60 10 C38 10 22 28 20 52 C18 68 26 82 38 88 L46 80 C38 74 36 62 42 54 C48 48 54 44 60 44 C66 44 72 48 78 54 C84 62 82 74 74 80 L82 88 C94 82 102 68 100 52 C98 28 82 10 60 10 Z" fill="${fg}"/>
                    <path d="M42 24 C50 18 70 18 78 24 C70 30 50 30 42 24 Z" fill="${bg}"/>
                    <path d="M27 44 L44 37 L41 47 L28 50 Z M93 44 L76 37 L79 47 L92 50 Z" fill="${bg}"/>
                    <path d="M28 60 L40 58 L37 66 L29 65 Z M92 60 L80 58 L83 66 L91 65 Z" fill="${bg}"/>
                </g>`
        },
        {
            name: 'Rorschach Crown',
            group: 'RORSCHACH INK',
            draw: (fg, bg) => `
                <g transform="translate(40, 2)">
                    <path d="M60 14 L48 26 L36 16 L28 36 L18 42 L26 56 L20 68 L34 76 L38 88 L52 80 L60 88 L68 80 L82 88 L86 76 L100 68 L94 56 L102 42 L92 36 L84 16 L72 26 Z" fill="${fg}"/>
                    <path d="M34 44 L48 39 L46 49 L34 52 Z M86 44 L72 39 L74 49 L86 52 Z" fill="${bg}"/>
                    <path d="M54 34 L60 46 L66 34 L60 28 Z" fill="${bg}"/>
                    <path d="M38 64 L48 61 L44 70 Z M82 64 L72 61 L76 70 Z" fill="${bg}"/>
                </g>`
        },
        {
            name: 'Rebel Dual Mask',
            group: 'TWOFACE STENCIL',
            draw: (fg, bg) => `
                <g transform="translate(40, 2)">
                    <path d="M60 16 C44 12 28 22 24 40 C20 56 26 76 42 88 L50 76 C44 68 44 56 50 48 C54 43 58 42 60 46 C62 42 66 43 70 48 C76 56 76 68 70 76 L78 88 C94 76 100 56 96 40 C92 22 76 12 60 16 Z" fill="${fg}"/>
                    <polygon points="30,38 46,34 44,44 31,46" fill="${bg}"/>
                    <polygon points="90,38 74,34 76,44 89,46" fill="${bg}"/>
                    <path d="M29 56 Q38 62 45 56 L41 64 Q34 66 29 56 Z M91 56 Q82 62 75 56 L79 64 Q86 66 91 56 Z" fill="${bg}"/>
                </g>`
        },
        {
            name: 'Sharp Visor Twins',
            group: 'TWOFACE STENCIL',
            draw: (fg, bg) => `
                <g transform="translate(40, 2)">
                    <path d="M60 14 C46 16 32 24 26 38 L18 52 L28 62 L24 76 L42 88 L52 78 C46 68 48 56 54 48 L60 40 L66 48 C72 56 74 68 68 78 L78 88 L96 76 L92 62 L102 52 L94 38 C88 24 74 16 60 14 Z" fill="${fg}"/>
                    <polygon points="28,40 48,32 46,42 26,48" fill="${bg}"/>
                    <polygon points="92,40 72,32 74,42 94,48" fill="${bg}"/>
                    <polygon points="32,62 45,58 42,67 30,68" fill="${bg}"/>
                    <polygon points="88,62 75,58 78,67 90,68" fill="${bg}"/>
                </g>`
        },
        {
            name: 'Inked Arch Profiles',
            group: 'HOODED PROFILES',
            draw: (fg, bg) => `
                <g transform="translate(40, 2)">
                    <path d="M60 10 C40 12 28 24 26 42 C20 48 20 58 26 64 C28 74 34 84 44 90 L50 80 C44 74 44 64 48 56 C52 48 56 44 60 44 C64 44 68 48 72 56 C76 64 76 74 70 80 L76 90 C86 84 92 74 94 64 C100 58 100 48 94 42 C92 24 80 12 60 10 Z" fill="${fg}"/>
                    <path d="M36 28 Q60 14 84 28 Q60 24 36 28 Z" fill="${bg}"/>
                    <circle cx="38" cy="44" r="5" fill="${bg}"/>
                    <circle cx="82" cy="44" r="5" fill="${bg}"/>
                    <path d="M34 62 L44 60 L41 68 Z M86 62 L76 60 L79 68 Z" fill="${bg}"/>
                </g>`
        },
        {
            name: 'Woodcut Street Mask',
            group: 'RORSCHACH INK',
            draw: (fg, bg) => `
                <g transform="translate(40, 2)">
                    <path d="M60 14 L42 18 L28 32 L22 50 L28 68 L38 86 L50 88 L54 74 L48 60 L54 44 L60 36 L66 44 L72 60 L66 74 L70 88 L82 86 L92 68 L98 50 L92 32 L78 18 Z" fill="${fg}"/>
                    <path d="M30 40 L46 34 L48 44 L32 48 Z M90 40 L74 34 L72 44 L88 48 Z" fill="${bg}"/>
                    <path d="M32 58 L46 56 L42 64 L34 64 Z M88 58 L74 56 L78 64 L86 64 Z" fill="${bg}"/>
                    <path d="M38 72 L46 70 L44 78 Z M82 72 L74 70 L76 78 Z" fill="${bg}"/>
                </g>`
        },
        {
            name: 'Split Grimace Emblem',
            group: 'TWOFACE STENCIL',
            draw: (fg, bg) => `
                <g transform="translate(40, 2)">
                    <path d="M60 12 C42 14 26 26 24 46 C22 64 30 82 46 90 L52 78 C46 70 46 56 52 46 C55 41 58 38 60 38 C62 38 65 41 68 46 C74 56 74 70 68 78 L74 90 C90 82 98 64 96 46 C94 26 78 14 60 12 Z" fill="${fg}"/>
                    <path d="M30 42 C36 36 44 36 48 42 C44 46 36 46 30 42 Z M90 42 C84 36 76 36 72 42 C76 46 84 46 90 42 Z" fill="${bg}"/>
                    <path d="M32 62 L46 58 L44 68 L34 68 Z M88 62 L74 58 L76 68 L86 68 Z" fill="${bg}"/>
                </g>`
        },
        {
            name: 'Drip Ink Blot',
            group: 'RORSCHACH INK',
            draw: (fg, bg) => `
                <g transform="translate(40, 2)">
                    <path d="M60 14 C48 14 40 22 36 30 C26 26 20 34 24 44 C18 48 18 58 26 62 C24 72 30 84 40 88 C44 90 48 84 48 76 C50 68 54 60 60 56 C66 60 70 68 72 76 C72 84 76 90 80 88 C90 84 96 72 94 62 C102 58 102 48 96 44 C100 34 94 26 84 30 C80 22 72 14 60 14 Z" fill="${fg}"/>
                    <path d="M30 42 L44 38 L42 46 L31 48 Z M90 42 L76 38 L78 46 L89 48 Z" fill="${bg}"/>
                    <circle cx="38" cy="58" r="4" fill="${bg}"/>
                    <circle cx="82" cy="58" r="4" fill="${bg}"/>
                </g>`
        },
        {
            name: 'Street Kid Silhouette',
            group: 'HOODED PROFILES',
            draw: (fg, bg) => `
                <g transform="translate(40, 2)">
                    <path d="M60 16 C44 16 32 24 28 36 L20 38 L24 48 C20 54 22 64 30 68 L34 86 L48 90 L52 76 C48 68 48 56 54 48 C57 44 63 44 66 48 C72 56 72 68 68 76 L72 90 L86 86 L90 68 C98 64 100 54 96 48 L100 38 L92 36 C88 24 76 16 60 16 Z" fill="${fg}"/>
                    <path d="M32 46 L46 42 L45 49 L33 51 Z M88 46 L74 42 L75 49 L87 51 Z" fill="${bg}"/>
                    <path d="M35 64 L45 62 L43 68 Z M85 64 L75 62 L77 68 Z" fill="${bg}"/>
                </g>`
        },
    ];

    // 10 Clean Dosmil-Style Typography Treatments (10 x 10 = 100)
    const treatments = [
        { name: 'Pure Mark (No Text)', group: null, draw: () => `` },
        { name: 'Wide Extended', group: 'WORDMARK + MARK', draw: (fg) => `<text x="100" y="116" text-anchor="middle" fill="${fg}" font-size="10.5" font-weight="900" font-family="Archivo,sans-serif" letter-spacing="2.4">KIDS OWN THE BLOCK</text>` },
        { name: 'KOTB Wide', group: 'WORDMARK + MARK', draw: (fg) => `<text x="100" y="118" text-anchor="middle" fill="${fg}" font-size="17" font-weight="900" font-family="Archivo,sans-serif" letter-spacing="6">KOTB</text>` },
        { name: 'KOTB + Subtext', group: 'WORDMARK + MARK', draw: (fg) => `<text x="100" y="112" text-anchor="middle" fill="${fg}" font-size="14" font-weight="900" font-family="Archivo,sans-serif" letter-spacing="4">KOTB</text><text x="100" y="125" text-anchor="middle" fill="${fg}" font-size="6.5" font-family="JetBrains Mono,monospace" letter-spacing="2.2">KIDS OWN THE BLOCK</text>` },
        { name: 'Block 2026', group: 'WORDMARK + MARK', draw: (fg) => `<text x="100" y="112" text-anchor="middle" fill="${fg}" font-size="10" font-weight="900" font-family="Archivo,sans-serif" letter-spacing="2">KIDS OWN THE BLOCK</text><text x="100" y="124" text-anchor="middle" fill="${fg}" font-size="7" font-family="JetBrains Mono,monospace" letter-spacing="3">MMXXVI</text>` },
        { name: 'Micro Mono', group: 'WORDMARK + MARK', draw: (fg) => `<text x="100" y="116" text-anchor="middle" fill="${fg}" font-size="7.5" font-weight="600" font-family="JetBrains Mono,monospace" letter-spacing="3.5">KIDS OWN THE BLOCK</text>` },
        { name: 'Stacked Manifesto', group: 'WORDMARK + MARK', draw: (fg) => `<text x="100" y="111" text-anchor="middle" fill="${fg}" font-size="10" font-weight="900" font-family="Archivo,sans-serif" letter-spacing="2.8">KIDS OWN</text><text x="100" y="123" text-anchor="middle" fill="${fg}" font-size="10" font-weight="900" font-family="Archivo,sans-serif" letter-spacing="2.8">THE BLOCK</text>` },
        { name: 'Heavy Underline', group: 'WORDMARK + MARK', draw: (fg) => `<text x="100" y="113" text-anchor="middle" fill="${fg}" font-size="10" font-weight="900" font-family="Archivo,sans-serif" letter-spacing="2">KIDS OWN THE BLOCK</text><line x1="32" y1="120" x2="168" y2="120" stroke="${fg}" stroke-width="1.8"/>` },
        { name: 'Pill Tag', group: 'WORDMARK + MARK', draw: (fg, bg) => `<rect x="30" y="104" width="140" height="20" rx="10" fill="${fg}"/><text x="100" y="117.5" text-anchor="middle" fill="${bg}" font-size="8.5" font-weight="900" font-family="Archivo,sans-serif" letter-spacing="1.6">KIDS OWN THE BLOCK</text>` },
        { name: 'Street Division', group: 'WORDMARK + MARK', draw: (fg) => `<text x="100" y="113" text-anchor="middle" fill="${fg}" font-size="13" font-weight="900" font-family="Archivo,sans-serif" letter-spacing="4">KOTB // 01</text><text x="100" y="124" text-anchor="middle" fill="${fg}" font-size="6.5" font-family="JetBrains Mono,monospace" letter-spacing="2">OWN THE BLOCK</text>` },
    ];

    const mockups = [];
    let num = 1;
    marks.forEach((mark) => {
        treatments.forEach((tr) => {
            mockups.push({
                id: num++,
                label: `${mark.name} · ${tr.name}`,
                group: tr.group || mark.group,
                renderSvg: (fg, bg, transparent = false) => `
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 135" class="w-full h-full block">
                        ${transparent ? '' : `<rect width="200" height="135" fill="${bg}"/>`}
                        ${mark.draw(fg, bg)}
                        ${tr.draw(fg, bg)}
                    </svg>`
            });
        });
    });

    const renderGrid = () => {
        const fg = darkCard ? '#FFFFFF' : '#000000';
        const bg = darkCard ? '#000000' : '#EDEDED';
        const filtered = activeFilter === 'ALL 100' ? mockups : mockups.filter(m => m.group === activeFilter);
        document.getElementById('logo-count-label').textContent = `${filtered.length} mockups`;

        grid.innerHTML = filtered.map((m) => `
            <div class="border border-black/15 hover:border-black transition-colors flex flex-col bg-white">
                <div class="aspect-[200/135] w-full relative overflow-hidden" style="background:${bg}">
                    ${m.renderSvg(fg, bg, false)}
                    <span class="font-mono-meta text-[9px] absolute top-2 left-2 px-1.5 py-0.5 ${darkCard ? 'bg-white/15 text-white' : 'bg-black/10 text-black'}">#${String(m.id).padStart(3, '0')}</span>
                </div>
                <div class="p-2.5 border-t border-black/10 flex flex-col gap-2">
                    <div class="font-mono-meta text-[10px] text-[#555] truncate" title="${m.label}">${m.label}</div>
                    <div class="flex gap-1.5">
                        <button type="button" data-dl-id="${m.id}" class="flex-1 font-mono-meta text-[10px] border border-black py-1 hover:bg-black hover:text-white transition-colors cursor-pointer">SVG ↓</button>
                        <button type="button" data-apply-id="${m.id}" class="flex-1 font-mono-meta text-[10px] bg-black text-white py-1 hover:opacity-80 transition-opacity cursor-pointer">USE ON SPLASH</button>
                    </div>
                </div>
            </div>
        `).join('');
    };

    renderGrid();

    document.getElementById('toggle-theme-btn')?.addEventListener('click', () => {
        darkCard = !darkCard;
        renderGrid();
    });

    document.querySelectorAll('.logo-filter-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            activeFilter = btn.getAttribute('data-logo-filter');
            document.querySelectorAll('.logo-filter-btn').forEach((b) => {
                b.classList.toggle('bg-black', b === btn);
                b.classList.toggle('text-white', b === btn);
                b.classList.toggle('bg-white', b !== btn);
                b.classList.toggle('text-black', b !== btn);
            });
            renderGrid();
        });
    });

    grid.addEventListener('click', async (e) => {
        const dlBtn = e.target.closest('[data-dl-id]');
        const applyBtn = e.target.closest('[data-apply-id]');

        if (dlBtn) {
            const m = mockups.find(x => x.id === parseInt(dlBtn.getAttribute('data-dl-id'), 10));
            const svgStr = m.renderSvg('#FFFFFF', '#000000', true);
            const blob = new Blob([svgStr], { type: 'image/svg+xml' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `kotb-dosmil-logo-${String(m.id).padStart(3, '0')}.svg`;
            a.click();
            URL.revokeObjectURL(url);
        }

        if (applyBtn) {
            const m = mockups.find(x => x.id === parseInt(applyBtn.getAttribute('data-apply-id'), 10));
            const svgStr = m.renderSvg('#FFFFFF', '#000000', true);
            await fetch('/logo-studio/apply', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ svg: svgStr }),
            });
            const toast = document.getElementById('logo-toast');
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 2600);
        }
    });
});
</script>
@endpush
