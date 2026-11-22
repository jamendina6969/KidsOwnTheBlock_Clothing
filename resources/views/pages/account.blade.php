@extends('layouts.app')

@section('title', 'MY ACCOUNT | KIDS OWN THE BLOCK')

@section('content')
<main class="bg-white pt-10 pb-16">
    <div class="max-w-[1100px] mx-auto px-3 md:px-6">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-6 mb-8 border-b border-black/10">
            <div>
                <div class="font-mono-meta text-xs text-[#666] mb-1">KOTB MEMBER</div>
                <h1 class="font-display text-2xl">{{ strtoupper($user->name) }}</h1>
                <div class="font-mono-meta text-xs text-[#666] mt-1">{{ $user->email }}</div>
            </div>

            <div class="flex items-center gap-3">
                @if($user->is_admin)
                    <a href="{{ route('admin.dashboard') }}" data-nav class="font-display border border-black px-5 py-2.5 rounded-[30px] text-xs hover:bg-black hover:text-white transition-colors">
                        ADMIN DASHBOARD
                    </a>
                @endif
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="font-display bg-black text-white rounded-[30px] px-5 py-2.5 text-xs cursor-pointer">
                        LOG OUT
                    </button>
                </form>
            </div>
        </div>

        {{-- Block Points & Rewards Banner --}}
        <div class="border border-black bg-[#ededed] p-5 mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="font-mono-meta text-[10px] text-[#666] mb-0.5">KOTB LOYALTY REWARDS</div>
                <div class="font-display text-xl">{{ $user->points ?? 0 }} BLOCK POINTS</div>
                <div class="font-mono-meta text-[11px] text-[#555]">Earn 1 pt per ₱1 spent on verified orders. Redeem 100 pts for 15% off.</div>
            </div>
            <form method="POST" action="{{ route('account.redeem') }}">
                @csrf
                <button type="submit" {{ ($user->points ?? 0) < 100 ? 'disabled' : '' }} class="font-display rounded-[30px] px-5 py-2.5 text-xs {{ ($user->points ?? 0) >= 100 ? 'bg-black text-white cursor-pointer' : 'bg-black/20 text-black/40 cursor-not-allowed' }}">
                    REDEEM 100 PTS (15% OFF)
                </button>
            </form>
        </div>

        @if(session('redeemed_code'))
            <div class="border border-emerald-700 bg-emerald-50 text-emerald-800 p-4 mb-6 font-mono-meta text-xs">
                ✓ Reward Unlocked! Use promo code <strong class="underline">{{ session('redeemed_code') }}</strong> at checkout for 15% off.
            </div>
        @endif

        <h2 class="font-display text-base mb-4">ORDER HISTORY</h2>

        @if($orders->isEmpty())
            <div class="border border-black/15 p-8 text-center">
                <p class="font-mono-meta text-xs text-[#666] mb-4">You haven't placed any orders yet.</p>
                <a href="{{ route('shop') }}" data-nav class="font-display inline-block bg-black text-white rounded-[30px] px-6 py-2.5 text-xs">SHOP DROP 04</a>
            </div>
        @else
            <div class="flex flex-col gap-3 mb-12">
                @foreach($orders as $order)
                    <div class="border border-black p-4 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="font-display text-sm">{{ $order->order_number }}</div>
                            <div class="font-mono-meta text-xs text-[#666]">
                                {{ $order->created_at?->format('M d, Y') }} · {{ strtoupper($order->payment_method) }} · {{ strtoupper($order->status) }}
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="font-mono-meta text-sm font-semibold">₱{{ number_format((float) ($order->total ?: $order->subtotal), 2) }} PHP</span>
                            <a href="{{ route('order.receipt', ['order_number' => $order->order_number]) }}" data-nav class="font-mono-meta text-xs underline">VIEW RECEIPT ↗</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <h2 class="font-display text-base mb-4">SAVED WISHLIST</h2>
        @if(($wishlistItems ?? collect())->isEmpty())
            <div class="border border-black/15 p-6 text-center font-mono-meta text-xs text-[#666]">
                No saved garments yet. Tap "♡ SAVE TO WISHLIST" on any product page.
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($wishlistItems as $wl)
                    @include('components.product-card', ['product' => $wl->product, 'surface' => 'white'])
                @endforeach
            </div>
        @endif
    </div>
</main>
@endsection
