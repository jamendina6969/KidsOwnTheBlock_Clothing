@extends('layouts.app')

@section('title', 'CHECKOUT | KIDS OWN THE BLOCK')

@section('content')
<main class="bg-white pt-10 pb-16">
    <div class="max-w-[1200px] mx-auto px-3 md:px-6">
        <h1 class="font-display text-2xl mb-7 anim-fade-up">CHECKOUT</h1>

        <div class="grid grid-cols-1 lg:grid-cols-[1.15fr_0.85fr] gap-10 items-start">
            {{-- Left: Shipping & Payment Form --}}
            <form method="POST" action="{{ route('cart.checkout') }}" enctype="multipart/form-data" class="flex flex-col gap-5 border border-black p-6 md:p-8 anim-fade-up">
                @csrf

                <div class="font-display text-sm border-b border-black/10 pb-2.5">1. CONTACT &amp; SHIPPING</div>

                @if($errors->any())
                    <div class="font-mono-meta text-xs text-red-700 border border-red-700 bg-red-50 p-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="font-mono-meta text-[11px] text-[#666] block mb-1">FULL NAME</label>
                        <input type="text" name="customer_name" required value="{{ old('customer_name', auth()->user()?->name) }}" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
                    </div>
                    <div>
                        <label class="font-mono-meta text-[11px] text-[#666] block mb-1">EMAIL</label>
                        <input type="email" name="customer_email" required value="{{ old('customer_email', auth()->user()?->email) }}" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="font-mono-meta text-[11px] text-[#666] block mb-1">PHONE</label>
                        <input type="text" name="phone" required value="{{ old('phone') }}" placeholder="+63 917 000 0000" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
                    </div>
                    <div>
                        <label class="font-mono-meta text-[11px] text-[#666] block mb-1">CITY</label>
                        <input type="text" name="city" required value="{{ old('city') }}" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="font-mono-meta text-[11px] text-[#666] block mb-1">COUNTRY / DESTINATION</label>
                        <select id="checkout-country" name="country" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] bg-white outline-none">
                            <option value="PH" selected>Philippines (Local POD — ₱150 / Free ₱3,500+)</option>
                            <option value="US">United States (Printful Intl — ₱950)</option>
                            <option value="SG">Singapore (Printful Intl — ₱950)</option>
                            <option value="AU">Australia (Printful Intl — ₱950)</option>
                            <option value="JP">Japan (Printful Intl — ₱950)</option>
                            <option value="GB">United Kingdom (Printful Intl — ₱950)</option>
                            <option value="CA">Canada (Printful Intl — ₱950)</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-mono-meta text-[11px] text-[#666] block mb-1">SHIPPING ADDRESS</label>
                        <input type="text" name="shipping_address" required value="{{ old('shipping_address') }}" placeholder="Street, Apt / Suite, Postal Code" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
                    </div>
                </div>

                <div>
                    <label class="font-mono-meta text-[11px] text-[#666] block mb-1">DELIVERY NOTES (OPTIONAL)</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
                </div>

                <div class="font-display text-sm border-b border-black/10 pb-2.5 mt-2">2. PAYMENT METHOD &amp; VERIFICATION</div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 border border-black p-3.5 cursor-pointer">
                        <input type="radio" name="payment_method" value="gcash" checked class="accent-black pay-method-radio" />
                        <div>
                            <div class="font-display text-xs">GCASH / MAYA</div>
                            <div class="font-mono-meta text-[10px] text-[#666]">Upload receipt</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 border border-black p-3.5 cursor-pointer">
                        <input type="radio" name="payment_method" value="bank" class="accent-black pay-method-radio" />
                        <div>
                            <div class="font-display text-xs">BANK TRANSFER</div>
                            <div class="font-mono-meta text-[10px] text-[#666]">Upload receipt</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 border border-black p-3.5 cursor-pointer">
                        <input type="radio" name="payment_method" value="paymongo" class="accent-black pay-method-radio" />
                        <div>
                            <div class="font-display text-xs">PAYMONGO ONLINE</div>
                            <div class="font-mono-meta text-[10px] text-[#666]">Instant Card / E-Wallet</div>
                        </div>
                    </label>
                    <label id="cod-radio-card" class="flex items-center gap-2.5 border border-black p-3.5 cursor-pointer">
                        <input id="cod-radio-input" type="radio" name="payment_method" value="cod" class="accent-black pay-method-radio" />
                        <div>
                            <div class="font-display text-xs">CASH ON DELIVERY</div>
                            <div class="font-mono-meta text-[10px] text-[#666]">PH Local only</div>
                        </div>
                    </label>
                </div>

                {{-- Proof of Payment Box (shown for GCash / Bank) --}}
                <div id="proof-of-payment-box" class="border border-black bg-[#fafafa] p-4 flex flex-col gap-3">
                    <div id="payment-instructions" class="font-mono-meta text-xs text-[#222] border-b border-black/10 pb-3">
                        Send total to <strong>GCash: 0917-555-2026 (KOTB STUDIO)</strong> or <strong>Maya: 0917-555-2026</strong>, then enter your Reference No. and upload your screenshot below.
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="font-mono-meta text-[11px] text-[#666] block mb-1">REFERENCE NUMBER *</label>
                            <input id="pay-ref-input" type="text" name="payment_reference" value="{{ old('payment_reference') }}" placeholder="e.g. 9012 4481 2290" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs font-mono-meta bg-white" />
                        </div>
                        <div>
                            <label class="font-mono-meta text-[11px] text-[#666] block mb-1">RECEIPT SCREENSHOT *</label>
                            <input id="pay-proof-input" type="file" name="payment_proof" accept="image/*" class="w-full text-xs font-mono-meta pt-1.5" />
                        </div>
                    </div>
                </div>

                <button type="submit" class="font-display mt-2 w-full bg-black text-white rounded-[30px] py-4 text-[13px] cursor-pointer hover:opacity-85 transition-opacity">
                    PLACE ORDER · ₱{{ number_format($total, 2) }} PHP
                </button>
            </form>

            {{-- Right: Order Summary + Promo Code --}}
            <aside class="bg-[#ededed] border border-black/15 p-6 anim-fade-up" style="animation-delay: 100ms;">
                <h2 class="font-display text-sm mb-4">ORDER SUMMARY</h2>
                <div class="flex flex-col gap-4 pb-4 border-b border-black/10">
                    @foreach($cart as $item)
                        <div class="grid grid-cols-[64px_1fr_auto] gap-3 items-center">
                            <div class="aspect-[4/5] bg-white overflow-hidden">
                                {!! $item['thumb_html'] ?? '' !!}
                            </div>
                            <div>
                                <div class="font-display text-xs leading-snug">{{ $item['title'] }}</div>
                                <div class="font-mono-meta text-[11px] text-[#666]">SIZE {{ $item['size'] }} × {{ $item['quantity'] }}</div>
                            </div>
                            <div class="font-mono-meta text-xs font-semibold">₱{{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Promo Code Input --}}
                <div class="py-4 border-b border-black/10">
                    <div class="flex gap-2">
                        <input id="promo-code-input" type="text" value="{{ $promoCode }}" placeholder="Promo code (e.g. BLOCK10)" class="flex-1 h-10 rounded-[8px] border border-black px-3 text-xs font-mono-meta uppercase bg-white outline-none" />
                        <button id="apply-promo-btn" type="button" class="font-display bg-black text-white px-4 h-10 rounded-[8px] text-[11px] cursor-pointer">APPLY</button>
                    </div>
                    <div id="promo-feedback" class="font-mono-meta text-[11px] mt-1.5 {{ $promoCode ? 'text-emerald-700' : 'hidden' }}">
                        @if($promoCode) ✓ Code {{ $promoCode }} applied ({{ $discountPercent }}% off) @endif
                    </div>
                </div>

                <div class="font-mono-meta flex flex-col gap-2 py-4 text-xs border-b border-black/10">
                    <div class="flex justify-between">
                        <span class="text-[#666]">Subtotal</span>
                        <span>₱{{ number_format($subtotal, 2) }} PHP</span>
                    </div>
                    @if($discountAmount > 0)
                        <div class="flex justify-between text-emerald-700 font-semibold">
                            <span>Discount ({{ $promoCode }})</span>
                            <span>-₱{{ number_format($discountAmount, 2) }} PHP</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-[#666]">Shipping {{ $shippingFee == 0 ? '(Free over ₱3,500)' : '' }}</span>
                        <span>{{ $shippingFee == 0 ? 'FREE' : '₱' . number_format($shippingFee, 2) . ' PHP' }}</span>
                    </div>
                </div>

                <div class="flex justify-between items-baseline pt-4">
                    <span class="font-display text-sm">TOTAL</span>
                    <span class="font-mono-meta text-lg font-bold">₱{{ number_format($total, 2) }} PHP</span>
                </div>
            </aside>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const proofBox = document.getElementById('proof-of-payment-box');
    const instructions = document.getElementById('payment-instructions');

    document.querySelectorAll('.pay-method-radio').forEach((radio) => {
        radio.addEventListener('change', () => {
            if (radio.value === 'cod' || radio.value === 'paymongo') {
                proofBox.classList.add('hidden');
            } else {
                proofBox.classList.remove('hidden');
                instructions.innerHTML = radio.value === 'gcash'
                    ? 'Send total to <strong>GCash: 0917-555-2026 (KOTB STUDIO)</strong> or <strong>Maya: 0917-555-2026</strong>, then enter your Reference No. and upload your screenshot below.'
                    : 'Transfer total to <strong>BDO / UnionBank · Acct: 0041-8820-9102 (KIDS OWN THE BLOCK)</strong>, then enter your Reference No. and upload your receipt below.';
            }
        });
    });

    const countrySelect = document.getElementById('checkout-country');
    const codCard = document.getElementById('cod-radio-card');
    const codInput = document.getElementById('cod-radio-input');
    countrySelect?.addEventListener('change', () => {
        const isIntl = countrySelect.value !== 'PH';
        if (codCard) codCard.classList.toggle('hidden', isIntl);
        if (isIntl && codInput?.checked) {
            document.querySelector('input[name="payment_method"][value="paymongo"]')?.click();
        }
    });

    document.getElementById('apply-promo-btn')?.addEventListener('click', async () => {
        const code = document.getElementById('promo-code-input')?.value || '';
        const fb = document.getElementById('promo-feedback');
        const res = await fetch('/checkout/promo', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ code }),
        });
        const data = await res.json();
        if (!res.ok) {
            fb.textContent = data.error || 'Invalid code.';
            fb.className = 'font-mono-meta text-[11px] mt-1.5 text-red-700';
            return;
        }
        window.location.reload();
    });
});
</script>
@endpush
