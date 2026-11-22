@extends('layouts.app')

@section('title', 'HELP | KIDS OWN THE BLOCK')

@section('content')
<main class="bg-white pt-12 pb-16">
    <div class="max-w-[1080px] mx-auto px-3 md:px-6">
        <h1 class="font-display text-[30px] mb-5 anim-fade-up">HOW CAN WE HELP?</h1>

        <div class="relative mb-5">
            <input id="help-search" type="text" placeholder="Search shipping, returns, sizing, COD..." class="w-full h-12 rounded-[8px] border border-black pl-4 pr-11 text-sm outline-none" />
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.6" class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </div>

        <div class="flex flex-wrap gap-2 mb-9">
            @foreach(['ALL', 'Orders', 'Shipping', 'Returns', 'Sizing'] as $idx => $t)
                <button type="button" data-help-topic="{{ $t }}" class="help-topic-btn rounded-[30px] border border-black px-4 py-1.5 text-xs font-semibold cursor-pointer {{ $idx === 0 ? 'bg-black text-white' : 'bg-white text-black' }}">
                    {{ $t }}
                </button>
            @endforeach
        </div>

        <div id="help-faq-grid" class="grid grid-cols-1 md:grid-cols-2 gap-x-9 mb-14">
            @foreach($faqs as $idx => $faq)
                <div class="help-faq-item border-y border-black/10 py-4 reveal-on-scroll" data-topic="{{ $faq['topic'] }}" data-delay="{{ $idx * 50 }}">
                    <button type="button" class="help-faq-toggle w-full flex justify-between items-center gap-3 text-left text-sm font-semibold cursor-pointer">
                        <span>{{ $faq['question'] }}</span>
                        <span class="transition-transform">▾</span>
                    </button>
                    <p class="help-faq-answer {{ $idx === 0 ? '' : 'hidden' }} text-[13px] leading-relaxed text-[#444] mt-3">{{ $faq['answer'] }}</p>
                </div>
            @endforeach
        </div>

        <section class="border border-black p-7 reveal-on-scroll">
            <div class="flex gap-2.5 mb-5">
                <button type="button" data-ticket-tab="order" class="ticket-tab font-display rounded-[30px] border border-black bg-black text-white px-4 py-2 text-[11px] cursor-pointer">ORDER SUPPORT</button>
                <button type="button" data-ticket-tab="sizing" class="ticket-tab font-display rounded-[30px] border border-black bg-white text-black px-4 py-2 text-[11px] cursor-pointer">SIZING &amp; RESTOCKS</button>
            </div>

            <form id="help-ticket-form" class="grid gap-3.5 max-w-[540px]">
                <input type="hidden" id="ticket-topic-input" name="topic" value="order" />
                <input type="email" name="email" required placeholder="Your email" class="h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
                <input id="ticket-order-input" type="text" name="order_number" placeholder="Order number (e.g. #KOTB-1042)" class="h-11 rounded-[8px] border border-black px-3.5 text-[13px] outline-none" />
                <textarea name="message" required rows="4" placeholder="Tell us how we can help..." class="rounded-[8px] border border-black p-3.5 text-[13px] outline-none"></textarea>
                <button type="submit" class="font-display justify-self-start bg-black text-white rounded-[30px] px-7 py-3 text-xs cursor-pointer">SUBMIT REQUEST</button>
            </form>
            <div id="help-ticket-done" class="hidden font-mono-meta text-[13px] py-4">
                ✓ Ticket submitted &amp; logged to MySQL. Our customer care team will reply within 24 hours.
            </div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let activeTopic = 'ALL';

    const filterFaqs = () => {
        const q = (document.getElementById('help-search')?.value || '').toLowerCase();
        document.querySelectorAll('.help-faq-item').forEach((item) => {
            const topic = item.getAttribute('data-topic');
            const text = item.textContent.toLowerCase();
            const match = (activeTopic === 'ALL' || topic === activeTopic) && (!q || text.includes(q));
            item.classList.toggle('hidden', !match);
        });
    };

    document.getElementById('help-search')?.addEventListener('input', filterFaqs);
    document.querySelectorAll('.help-topic-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            activeTopic = btn.getAttribute('data-help-topic');
            document.querySelectorAll('.help-topic-btn').forEach((b) => {
                b.classList.toggle('bg-black', b === btn);
                b.classList.toggle('text-white', b === btn);
                b.classList.toggle('bg-white', b !== btn);
                b.classList.toggle('text-black', b !== btn);
            });
            filterFaqs();
        });
    });

    document.querySelectorAll('.help-faq-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.nextElementSibling.classList.toggle('hidden');
        });
    });

    document.querySelectorAll('.ticket-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            const t = tab.getAttribute('data-ticket-tab');
            document.getElementById('ticket-topic-input').value = t;
            document.getElementById('ticket-order-input').classList.toggle('hidden', t !== 'order');
            document.querySelectorAll('.ticket-tab').forEach((b) => {
                b.classList.toggle('bg-black', b === tab);
                b.classList.toggle('text-white', b === tab);
                b.classList.toggle('bg-white', b !== tab);
                b.classList.toggle('text-black', b !== tab);
            });
        });
    });

    document.getElementById('help-ticket-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        await fetch('/help/ticket', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(Object.fromEntries(fd.entries())),
        });
        e.target.classList.add('hidden');
        document.getElementById('help-ticket-done').classList.remove('hidden');
    });
});
</script>
@endpush
