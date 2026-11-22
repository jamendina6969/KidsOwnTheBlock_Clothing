@php
    $color = $color ?? 'black';
    $size = $size ?? 44;
    $showWordmark = $showWordmark ?? true;
    $fill = $color === 'white' ? '#FFFFFF' : '#000000';
    $stroke = $color === 'white' ? '#000000' : '#FFFFFF';
    $height = $size * 0.85;
    $fontSize = max(9, $size * 0.21);
@endphp

<div class="inline-flex flex-col items-center select-none" style="gap: {{ $showWordmark ? '6px' : '0px' }};">
    {{-- KIDS OWN THE BLOCK: Floating-Stencil "Laughing vs. Depressed Youngstunna" Emblem --}}
    <svg width="{{ $size }}" height="{{ $height }}" viewBox="0 0 140 116" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Kids Own The Block Emblem">
        {{-- Top Sweeping Twin-Arch Crown / Brow Piece --}}
        <path d="M44 10 C24 12 12 28 8 46 L19 48 C19 42 20 36 22 34 C30 35 37 32 42 32 C39 36 37 39 40 42 C46 44 52 42 58 45 C64 49 70 50 70 50 C70 50 76 49 82 45 C88 42 94 44 100 42 C103 39 101 36 98 32 C103 32 110 35 118 34 C120 36 121 42 121 48 L132 46 C128 28 116 12 96 10 C92 10 89 12 87 16 C92 26 84 40 70 40 C56 40 48 26 53 16 C51 12 48 10 44 10 Z" fill="{{ $fill }}" />

        {{-- LEFT STENCIL ISLAND: LAUGHING YOUNGSTUNNA --}}
        <g>
            {{-- Laughing cheek & brow island with upward-curved squint eye cutout --}}
            <path d="M27 46 C23 46 21 51 18 53 C12 53 9 58 11 63 C14 67 17 70 16 78 C15 84 20 88 26 85 C33 88 42 88 48 84 C50 79 44 74 44 68 C45 63 54 61 58 58 C61 55 60 49 54 49 C47 49 41 53 36 52 C32 51 31 46 27 46 Z" fill="{{ $fill }}" />
            {{-- Upward-curved happy/laughing eye cutout --}}
            <path d="M21 64 Q29 56 37 64 Q29 61 21 64 Z" fill="{{ $stroke }}" />
            {{-- Wide upward laughing mouth + chin stencil floating pieces --}}
            <path d="M18 90 Q32 99 45 89 Q32 93 18 90 Z" fill="{{ $fill }}" />
            <path d="M22 103 Q32 106 41 99 Q33 98 22 103 Z" fill="{{ $fill }}" />
        </g>

        {{-- RIGHT STENCIL ISLAND: DEPRESSED YOUNGSTUNNA --}}
        <g>
            {{-- Slumped cheek & brow island with heavy drooping eye cutout --}}
            <path d="M113 46 C117 46 119 51 122 53 C128 53 131 58 129 63 C126 67 123 70 124 78 C125 84 120 88 114 85 C107 88 98 88 92 84 C90 79 96 74 96 68 C95 63 86 61 82 58 C79 55 80 49 86 49 C93 49 99 53 104 52 C108 51 109 46 113 46 Z" fill="{{ $fill }}" />
            {{-- Heavy drooping melancholic eye cutout --}}
            <path d="M102 60 Q111 63 119 60 Q114 70 105 68 Z" fill="{{ $stroke }}" />
            {{-- Subtle teardrop dot under right eye --}}
            <circle cx="114" cy="74" r="1.8" fill="{{ $stroke }}" />
            {{-- Downward-turned sad/depressed frown + heavy chin floating pieces --}}
            <path d="M95 95 Q108 86 121 95 Q108 91 95 95 Z" fill="{{ $fill }}" />
            <path d="M98 102 Q108 100 117 105 Q107 107 98 102 Z" fill="{{ $fill }}" />
        </g>
    </svg>

    @if($showWordmark)
        <span class="font-display leading-none" style="color: {{ $fill }}; font-size: {{ $fontSize }}px; letter-spacing: 0.16em;">
            KIDS OWN THE BLOCK
        </span>
    @endif
</div>
