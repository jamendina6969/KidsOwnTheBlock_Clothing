@extends('layouts.app', ['hideChrome' => true])

@section('title', 'KOTB | Philippine Streetwear & Block Community')

@section('content')
@php
    $videoFiles = glob(public_path('videos/*.{mp4,webm,mov}'), GLOB_BRACE) ?: [];
    $splashVideo = !empty($videoFiles) ? 'videos/' . rawurlencode(basename($videoFiles[0])) : null;
@endphp

<main class="relative w-full h-screen overflow-hidden bg-black flex flex-col items-center justify-center">
    @if($splashVideo)
        {{-- Uploaded Ambient Monochrome Streetwear Video --}}
        <video
            autoplay
            loop
            muted
            playsinline
            class="anim-pure-video absolute inset-0 w-full h-full object-cover grayscale contrast-115 pointer-events-none"
        >
            <source src="{{ asset($splashVideo) }}" type="video/mp4" />
        </video>
    @else
        {{-- Fallback Ambient Monochrome Architectural Tiled Wall + Silhouette Backdrop --}}
        <svg viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" aria-hidden="true" class="anim-ambient absolute inset-0 w-full h-full">
            <defs>
                <pattern id="tileGrid" width="72" height="72" patternUnits="userSpaceOnUse">
                    <rect width="72" height="72" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="1" />
                </pattern>
                <radialGradient id="spot" cx="50%" cy="46%" r="52%">
                    <stop offset="0%" stop-color="rgba(255,255,255,0.22)" />
                    <stop offset="65%" stop-color="rgba(255,255,255,0.03)" />
                    <stop offset="100%" stop-color="rgba(0,0,0,1)" />
                </radialGradient>
            </defs>
            <rect width="1440" height="900" fill="url(#tileGrid)" />
            <rect width="1440" height="900" fill="url(#spot)" />
            <path d="M610 900 L635 560 Q645 480 720 475 Q795 480 805 560 L830 900 Z" fill="rgba(255,255,255,0.08)" />
            <ellipse cx="720" cy="365" rx="68" ry="86" fill="rgba(255,255,255,0.09)" />
        </svg>
    @endif

    <div class="film-grain-overlay absolute inset-0 z-[1]"></div>
    <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(circle at center, rgba(0,0,0,0.12) 0%, rgba(0,0,0,0.55) 100%);"></div>

    {{-- Exact Dosmil2000 Reference Alignment: Pure Slow Fade (#5) --}}
    <div class="absolute inset-x-0 top-[18vh] z-10 flex justify-center anim-pure-emblem">
        @if(file_exists(public_path('images/active-logo.svg')))
            <img src="{{ asset('images/active-logo.svg') }}?v={{ filemtime(public_path('images/active-logo.svg')) }}" alt="KOTB" class="w-[132px] md:w-[152px] h-auto object-contain" />
        @else
            @include('components.brand-mark', ['color' => 'white', 'size' => 142, 'showWordmark' => false])
        @endif
    </div>

    <nav aria-label="Splash navigation" class="absolute inset-x-0 top-[50vh] z-10 flex flex-col items-center gap-2.5">
        <a href="{{ route('story') }}" data-nav class="anim-link-smooth link-sweep font-display text-white text-[11px] tracking-[0.14em] py-0.5 px-2 hover:opacity-70 transition-opacity" style="animation-delay: 480ms;">
            BLOCK STORY
        </a>
        <a href="{{ route('shop') }}" data-nav class="anim-link-smooth link-sweep font-display text-white text-[11px] tracking-[0.14em] py-0.5 px-2 hover:opacity-70 transition-opacity" style="animation-delay: 600ms;">
            SHOP
        </a>
        <a href="{{ route('help') }}" data-nav class="anim-link-smooth link-sweep font-display text-white text-[11px] tracking-[0.14em] py-0.5 px-2 hover:opacity-70 transition-opacity" style="animation-delay: 720ms;">
            HELP
        </a>
    </nav>
</main>
@endsection
