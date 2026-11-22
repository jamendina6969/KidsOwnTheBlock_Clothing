@extends('layouts.app', ['hideChrome' => true])

@section('title', 'VIP EARLY ACCESS | KOTB')

@section('content')
<main class="min-h-screen bg-black text-white flex flex-col items-center justify-center px-4 text-center">
    <div class="anim-float mb-8">
        @include('components.brand-mark', ['color' => 'white', 'size' => 110, 'showWordmark' => false])
    </div>

    <div class="font-mono-meta text-[11px] text-[#d9ff00] tracking-[0.2em] mb-2">DROP 05 · VIP EARLY ACCESS</div>
    <h1 class="font-display text-2xl md:text-3xl mb-6">ENTER STOREFRONT PASSCODE</h1>

    @if(session('error'))
        <div class="font-mono-meta text-xs text-red-400 border border-red-500 px-4 py-2 mb-4">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('vip.unlock') }}" class="w-full max-w-[340px] flex flex-col gap-3">
        @csrf
        <input type="text" name="passcode" required placeholder="ENTER PASSCODE" class="h-12 rounded-[8px] border border-white/40 bg-black text-white text-center font-mono-meta tracking-[0.25em] uppercase text-sm outline-none focus:border-white" />
        <button type="submit" class="btn-sheen font-display bg-white text-black rounded-[30px] h-11 text-xs cursor-pointer">
            UNLOCK DROP
        </button>
    </form>

    <a href="{{ route('splash') }}" class="font-mono-meta text-[11px] text-white/50 hover:text-white mt-8 underline">← BACK TO LANDING</a>
</main>
@endsection
