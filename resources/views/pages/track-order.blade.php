@extends('layouts.app')

@section('title', 'TRACK ORDER | KOTB')

@section('content')
<main class="bg-white pt-12 pb-20">
    <div class="max-w-[720px] mx-auto px-3 md:px-6">
        <h1 class="font-display text-2xl md:text-3xl mb-2 anim-fade-up">TRACK YOUR ORDER</h1>
        <p class="font-mono-meta text-xs text-[#666] mb-7">
            Enter your Order Number and Email address to check payment verification and dispatch progress.
        </p>

        <form method="GET" action="{{ route('order.track') }}" class="border border-black p-6 flex flex-col gap-3.5 mb-8 anim-fade-up">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input type="text" name="order_number" required value="{{ $orderNumber }}" placeholder="Order # (e.g. KOTB-2026-1001)" class="h-11 rounded-[8px] border border-black px-3.5 text-xs font-mono-meta uppercase outline-none" />
                <input type="email" name="email" required value="{{ $email }}" placeholder="Email used at checkout" class="h-11 rounded-[8px] border border-black px-3.5 text-xs outline-none" />
            </div>
            <button type="submit" class="btn-sheen font-display self-start bg-black text-white rounded-[30px] px-7 py-3 text-xs cursor-pointer">
                CHECK STATUS
            </button>
        </form>

        @if($searched)
            @if($order)
                <div class="border border-black p-6 anim-scale-in">
                    <div class="flex flex-wrap justify-between items-center gap-2 pb-4 border-b border-black/10 mb-5">
                        <div>
                            <div class="font-display text-lg">{{ $order->order_number }}</div>
                            <div class="font-mono-meta text-xs text-[#666]">Placed {{ $order->created_at?->format('M d, Y') }} · {{ strtoupper($order->payment_method) }}</div>
                        </div>
                        <span class="font-mono-meta text-xs px-3 py-1 bg-black text-white">{{ strtoupper(str_replace('_', ' ', $order->status)) }}</span>
                    </div>

                    @php
                        $steps = ['confirmed', 'packed', 'shipped', 'delivered'];
                        $currIdx = array_search($order->status, $steps, true);
                    @endphp
                    <div class="grid grid-cols-4 gap-2 mb-6">
                        @foreach($steps as $sIdx => $step)
                            @php $done = $currIdx !== false && $sIdx <= $currIdx; @endphp
                            <div class="border-t-2 pt-2 font-mono-meta text-[10px] uppercase {{ $done ? 'border-black text-black font-bold' : 'border-black/15 text-[#999]' }}">
                                {{ $step }}
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ route('order.receipt', ['order_number' => $order->order_number]) }}" class="font-mono-meta text-xs underline">
                        VIEW FULL RECEIPT ↗
                    </a>
                </div>
            @else
                <div class="border border-red-700 bg-red-50 text-red-700 p-4 font-mono-meta text-xs">
                    No matching order found for {{ $orderNumber }} and {{ $email }}.
                </div>
            @endif
        @endif
    </div>
</main>
@endsection
