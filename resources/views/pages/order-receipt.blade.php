@extends('layouts.app')

@section('title', 'ORDER ' . $order->order_number . ' | KIDS OWN THE BLOCK')

@section('content')
<main class="bg-white pt-12 pb-16">
    <div class="max-w-[780px] mx-auto px-3 md:px-6">
        <div class="border border-black p-7 md:p-10 anim-fade-up">
            @if(session('paymongo_notice'))
                <div class="font-mono-meta text-xs border border-amber-400 bg-amber-50 text-amber-800 p-3 mb-5">
                    ℹ {{ session('paymongo_notice') }}
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <span class="font-mono-meta text-xs text-[#666]">ORDER RECEIPT</span>
                <span class="font-mono-meta text-[10px] px-2.5 py-1 border {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : ($order->payment_status === 'pending_verification' ? 'bg-amber-50 text-amber-700 border-amber-300' : 'bg-[#ededed] border-black/25') }}">
                    PAYMENT: {{ strtoupper(str_replace('_', ' ', $order->payment_status ?? 'confirmed')) }}
                </span>
            </div>

            <h1 class="font-display text-2xl md:text-3xl mb-2">THANK YOU, {{ strtoupper($order->customer_name ?? 'BLOCK MEMBER') }}</h1>
            <p class="font-mono-meta text-xs text-[#666] mb-7">
                Order #{{ $order->order_number }} · Method: {{ strtoupper($order->payment_method) }} · Fulfillment: {{ strtoupper($order->status) }}
                @if($order->payment_reference) · Ref: {{ $order->payment_reference }} @endif
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-4 border-y border-black/10 font-mono-meta text-xs mb-6">
                <div>
                    <div class="text-[#666] text-[10px] mb-1">SHIPPING TO</div>
                    <div>{{ $order->customer_name }}</div>
                    <div>{{ ($canViewPrivate ?? false) ? $order->shipping_address : '•••••••• (Log in to view full address)' }}</div>
                    <div>{{ $order->city }}</div>
                </div>
                <div>
                    <div class="text-[#666] text-[10px] mb-1">CONTACT</div>
                    <div>{{ ($canViewPrivate ?? false) ? $order->customer_email : substr($order->customer_email ?? '', 0, 3) . '***@***' }}</div>
                    <div>{{ ($canViewPrivate ?? false) ? $order->phone : '••••••••' }}</div>
                </div>
            </div>

            <div class="flex flex-col gap-3 pb-5 border-b border-black/10">
                @foreach($order->items ?? [] as $item)
                    <div class="flex justify-between items-center text-xs">
                        <div>
                            <span class="font-display">{{ $item['title'] }}</span>
                            <span class="font-mono-meta text-[#666] ml-2">({{ $item['size'] }} × {{ $item['quantity'] }})</span>
                        </div>
                        <span class="font-mono-meta">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="font-mono-meta flex flex-col gap-1.5 pt-4 text-xs">
                @if(($order->discount_amount ?? 0) > 0)
                    <div class="flex justify-between text-emerald-700">
                        <span>PROMO ({{ $order->promo_code }})</span>
                        <span>-₱{{ number_format($order->discount_amount, 2) }} PHP</span>
                    </div>
                @endif
                <div class="flex justify-between text-[#666]">
                    <span>SHIPPING</span>
                    <span>{{ ($order->shipping_fee ?? 0) == 0 ? 'FREE' : '₱' . number_format($order->shipping_fee, 2) . ' PHP' }}</span>
                </div>
            </div>

            <div class="flex justify-between items-baseline pt-3 mb-6 border-t border-black/10 mt-3">
                <span class="font-display text-sm">TOTAL</span>
                <span class="font-mono-meta text-lg font-bold">₱{{ number_format((float) ($order->total ?: $order->subtotal), 2) }} PHP</span>
            </div>

            @if($order->payment_proof)
                <div class="mb-6 border border-black/15 p-3 bg-[#fafafa]">
                    <div class="font-mono-meta text-[10px] text-[#666] mb-2">UPLOADED PAYMENT RECEIPT</div>
                    <img src="{{ asset($order->payment_proof) }}" alt="Payment proof" class="max-h-48 object-contain border border-black/10" />
                </div>
            @endif

            <a href="{{ route('shop') }}" data-nav class="font-display inline-block bg-black text-white rounded-[30px] px-7 py-3 text-xs">
                CONTINUE SHOPPING
            </a>
        </div>
    </div>
</main>
@endsection
