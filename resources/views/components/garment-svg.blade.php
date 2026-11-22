@php
    $view = $view ?? 'front';
    $sil = $product->silhouette;
    $base = $product->base_color;
    $accent = $product->accent_color;
    $ink = $product->ink_color;
    $motif = $product->motif;
    $sub = $product->sub_motif;
    $isBack = $view === 'back';
    $isLight = in_array($base, ['#f4f2ed', '#eceae4', '#cfccc4']);
    $strokeOutline = $isLight ? 'rgba(0,0,0,0.22)' : 'rgba(255,255,255,0.14)';

    $uploadedImage = match ($view) {
        'front' => $product->front_image ?? null,
        'back' => $product->back_image ?? null,
        'size-chart' => $product->size_chart_image ?? null,
        default => null,
    };
@endphp

@if($uploadedImage)
    <img src="{{ asset($uploadedImage) }}" alt="{{ $product->title }} {{ $view }} view" class="w-full h-full object-contain block" loading="lazy" />
@elseif($view === 'size-chart')
<svg viewBox="0 0 400 500" class="w-full h-full block bg-[#0c0c0c]" role="img" aria-label="{{ $product->title }} size chart">
    <rect width="400" height="500" fill="#0c0c0c" />
    <line x1="28" y1="74" x2="372" y2="74" stroke="rgba(255,255,255,0.2)" stroke-width="1" />
    <text x="28" y="48" fill="#ffffff" font-size="16" font-weight="800" font-family="Archivo, sans-serif" letter-spacing="2">SIZE SPECIFICATION (IN)</text>
    <text x="28" y="64" fill="#888888" font-size="10" font-family="JetBrains Mono, monospace">{{ $product->title }}</text>
    <path d="M145 110 L175 102 Q200 118 225 102 L255 110 L295 150 L272 175 L252 156 L252 260 L148 260 L148 156 L128 175 L105 150 Z" fill="none" stroke="#ffffff" stroke-width="1.5" stroke-dasharray="4 3" />
    <line x1="148" y1="205" x2="252" y2="205" stroke="#d9ff00" stroke-width="1.2" />
    <text x="200" y="198" text-anchor="middle" fill="#d9ff00" font-size="9" font-family="JetBrains Mono, monospace">A: CHEST WIDTH</text>
    <line x1="268" y1="106" x2="268" y2="260" stroke="#ffffff" stroke-width="1.2" />
    <text x="276" y="190" fill="#ffffff" font-size="9" font-family="JetBrains Mono, monospace">B: LENGTH</text>
    @foreach(['SIZE', 'S', 'M', 'L', 'XL', 'XXL'] as $i => $col)
        <text x="{{ 42 + $i * 58 }}" y="315" fill="{{ $i === 0 ? '#888888' : '#ffffff' }}" font-size="11" font-weight="700" font-family="JetBrains Mono, monospace">{{ $col }}</text>
    @endforeach
    <line x1="28" y1="326" x2="372" y2="326" stroke="rgba(255,255,255,0.2)" />
    @foreach(['CHEST', '21.5', '22.5', '23.8', '25.0', '26.2'] as $i => $val)
        <text x="{{ 42 + $i * 58 }}" y="352" fill="{{ $i === 0 ? '#888888' : '#e5e5e5' }}" font-size="11" font-family="JetBrains Mono, monospace">{{ $val }}</text>
    @endforeach
    <line x1="28" y1="364" x2="372" y2="364" stroke="rgba(255,255,255,0.12)" />
    @foreach(['LENGTH', '27.5', '28.5', '29.5', '30.5', '31.5'] as $i => $val)
        <text x="{{ 42 + $i * 58 }}" y="390" fill="{{ $i === 0 ? '#888888' : '#e5e5e5' }}" font-size="11" font-family="JetBrains Mono, monospace">{{ $val }}</text>
    @endforeach
    <text x="28" y="468" fill="#666666" font-size="9" font-family="JetBrains Mono, monospace">TOLERANCE +/- 0.5 IN · PRE-SHRUNK GARMENT</text>
</svg>
@else
<svg viewBox="0 0 400 500" class="w-full h-full block" role="img" aria-label="{{ $product->title }} {{ $view }} view">
    <ellipse cx="200" cy="442" rx="95" ry="10" fill="rgba(0,0,0,0.06)" />
    @if($sil === 'tee')
        <path d="M132 86 L168 78 Q200 98 232 78 L268 86 L345 148 L308 198 L278 176 L278 416 L122 416 L122 176 L92 198 L55 148 Z" fill="{{ $base }}" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
        <path d="{{ $isBack ? 'M168 78 Q200 88 232 78' : 'M168 78 Q200 108 232 78' }}" fill="none" stroke="{{ $strokeOutline }}" stroke-width="4" />
        <line x1="122" y1="402" x2="278" y2="402" stroke="{{ $strokeOutline }}" stroke-width="1" stroke-dasharray="3 2" />
        @if(!$isBack)
            <rect x="150" y="160" width="100" height="36" fill="none" stroke="{{ $accent }}" stroke-width="2.5" />
            <text x="200" y="185" text-anchor="middle" fill="{{ $accent }}" font-size="18" font-weight="900" font-family="Archivo, sans-serif" letter-spacing="2">{{ $motif }}</text>
            <text x="200" y="214" text-anchor="middle" fill="{{ $ink }}" font-size="8" font-family="JetBrains Mono, monospace" letter-spacing="3">{{ $sub }}</text>
        @else
            <text x="200" y="175" text-anchor="middle" fill="{{ $accent }}" font-size="28" font-weight="900" font-family="Archivo, sans-serif" letter-spacing="3">KOTB</text>
            <rect x="144" y="192" width="112" height="112" fill="none" stroke="{{ $ink }}" stroke-width="1.5" />
            <path d="M164 218H196V278H164V218Z" fill="{{ $accent }}" />
            <path d="M204 218H236V278H204V218Z" fill="{{ $accent }}" />
            <text x="200" y="324" text-anchor="middle" fill="{{ $ink }}" font-size="9" font-family="JetBrains Mono, monospace" letter-spacing="2">{{ $sub }}</text>
        @endif
    @elseif($sil === 'baby-tee')
        <path d="M144 106 L174 98 Q200 120 226 98 L256 106 L316 144 L294 184 L264 166 L254 342 L146 342 L136 166 L106 184 L84 144 Z" fill="{{ $base }}" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
        <path d="{{ $isBack ? 'M174 98 Q200 108 226 98' : 'M174 98 Q200 126 226 98' }}" fill="none" stroke="{{ $accent }}" stroke-width="4" />
        <text x="200" y="198" text-anchor="middle" fill="{{ $accent }}" font-size="22" font-weight="900" font-family="Archivo, sans-serif" letter-spacing="3">{{ $isBack ? 'OWN THE BLOCK' : $motif }}</text>
        <text x="200" y="218" text-anchor="middle" fill="{{ $ink }}" font-size="8" font-family="JetBrains Mono, monospace" letter-spacing="2">{{ $sub }}</text>
    @elseif($sil === 'hoodie')
        <path d="M150 96 Q200 50 250 96 L282 106 L350 218 L314 246 L282 186 L282 406 L118 406 L118 186 L86 246 L50 218 L118 106 Z" fill="{{ $base }}" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
        @if(!$isBack)
            <path d="M164 96 Q200 138 236 96 Q200 72 164 96 Z" fill="rgba(0,0,0,0.28)" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
            <path d="M148 310 L252 310 L264 368 L136 368 Z" fill="none" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
            <text x="200" y="198" text-anchor="middle" fill="{{ $accent }}" font-size="18" font-weight="900" font-family="Archivo, sans-serif" letter-spacing="2">{{ $motif }}</text>
            <text x="200" y="216" text-anchor="middle" fill="{{ $ink }}" font-size="8" font-family="JetBrains Mono, monospace" letter-spacing="3">{{ $sub }}</text>
        @else
            <rect x="148" y="160" width="104" height="104" fill="none" stroke="{{ $accent }}" stroke-width="2" />
            <text x="200" y="218" text-anchor="middle" fill="{{ $accent }}" font-size="22" font-weight="900" font-family="Archivo, sans-serif" letter-spacing="2">{{ $motif }}</text>
            <text x="200" y="242" text-anchor="middle" fill="{{ $ink }}" font-size="9" font-family="JetBrains Mono, monospace">{{ $sub }}</text>
        @endif
        <rect x="118" y="382" width="164" height="24" fill="rgba(0,0,0,0.12)" stroke="{{ $strokeOutline }}" stroke-width="1" />
    @elseif($sil === 'jacket')
        <path d="M148 92 L176 82 L200 94 L224 82 L252 92 L286 106 L348 228 L312 252 L280 184 L280 404 L120 404 L120 184 L88 252 L52 228 L114 106 Z" fill="{{ $base }}" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
        <path d="M168 88 L200 122 L232 88" fill="none" stroke="{{ $accent }}" stroke-width="2.5" />
        @if(!$isBack)
            <line x1="200" y1="122" x2="200" y2="404" stroke="{{ $strokeOutline }}" stroke-width="2" />
            <rect x="218" y="166" width="38" height="42" fill="none" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
            <text x="158" y="182" text-anchor="middle" fill="{{ $accent }}" font-size="11" font-weight="800" font-family="Archivo, sans-serif" letter-spacing="1.5">{{ $motif }}</text>
        @else
            <text x="200" y="205" text-anchor="middle" fill="{{ $accent }}" font-size="24" font-weight="900" font-family="Archivo, sans-serif" letter-spacing="3">{{ $motif }}</text>
            <text x="200" y="232" text-anchor="middle" fill="{{ $ink }}" font-size="10" font-family="JetBrains Mono, monospace" letter-spacing="4">{{ $sub }}</text>
        @endif
    @elseif($sil === 'jersey')
        <path d="M142 84 L168 84 Q200 126 232 84 L258 84 L276 172 L276 410 L124 410 L124 172 Z" fill="{{ $base }}" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
        <path d="M124 172 Q144 132 142 84" fill="none" stroke="{{ $accent }}" stroke-width="2" />
        <path d="M276 172 Q256 132 258 84" fill="none" stroke="{{ $accent }}" stroke-width="2" />
        <text x="200" y="210" text-anchor="middle" fill="{{ $accent }}" font-size="20" font-weight="900" font-family="Archivo, sans-serif" letter-spacing="2">{{ $motif }}</text>
        <text x="200" y="255" text-anchor="middle" fill="{{ $ink }}" font-size="34" font-weight="900" font-family="JetBrains Mono, monospace">{{ $isBack ? '01' : $sub }}</text>
    @elseif($sil === 'shorts')
        <path d="M124 114 L276 114 L302 356 L214 356 L200 248 L186 356 L98 356 Z" fill="{{ $base }}" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
        <rect x="124" y="114" width="152" height="24" fill="rgba(0,0,0,0.18)" stroke="{{ $strokeOutline }}" stroke-width="1" />
        <rect x="108" y="214" width="38" height="68" fill="none" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
        <rect x="254" y="214" width="38" height="68" fill="none" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
        <text x="150" y="324" text-anchor="middle" fill="{{ $accent }}" font-size="11" font-weight="800" font-family="Archivo, sans-serif" letter-spacing="1">{{ $motif }}</text>
    @elseif($sil === 'pants')
        <path d="M134 74 L266 74 L288 432 L212 432 L200 202 L188 432 L112 432 Z" fill="{{ $base }}" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
        <rect x="134" y="74" width="132" height="22" fill="rgba(0,0,0,0.2)" stroke="{{ $strokeOutline }}" stroke-width="1" />
        @if(!$isBack)
            <line x1="200" y1="96" x2="200" y2="180" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
            <text x="156" y="250" text-anchor="middle" fill="{{ $accent }}" font-size="12" font-weight="800" font-family="Archivo, sans-serif" letter-spacing="1.5">{{ $motif }}</text>
        @else
            <rect x="146" y="120" width="42" height="46" fill="none" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
            <rect x="212" y="120" width="42" height="46" fill="none" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
            <text x="233" y="148" text-anchor="middle" fill="{{ $accent }}" font-size="9" font-weight="800" font-family="JetBrains Mono, monospace">{{ $sub }}</text>
        @endif
    @elseif($sil === 'cap')
        <path d="M108 258 C108 174 150 140 200 140 C250 140 292 174 292 258 Z" fill="{{ $base }}" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
        <path d="M94 258 Q200 286 306 258 Q266 296 200 296 Q134 296 94 258 Z" fill="{{ $base }}" stroke="{{ $strokeOutline }}" stroke-width="1.5" />
        <line x1="200" y1="140" x2="200" y2="258" stroke="{{ $strokeOutline }}" stroke-width="1" />
        <circle cx="200" cy="140" r="5" fill="{{ $accent }}" />
        <text x="200" y="220" text-anchor="middle" fill="{{ $accent }}" font-size="20" font-weight="900" font-family="Archivo, sans-serif" letter-spacing="3">{{ $isBack ? $sub : $motif }}</text>
    @endif
</svg>
@endif
