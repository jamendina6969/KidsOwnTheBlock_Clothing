@extends('layouts.admin')

@section('title', 'AI PRODUCT STUDIO | KOTB ADMIN')

@section('content')
<div class="max-w-[1280px] flex flex-col gap-6">
    {{-- Header Banner --}}
    <div class="bg-black text-white border border-black p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-2">
                <span class="font-mono-meta text-[10px] px-2 py-0.5 bg-[#d9ff00] text-black font-bold">GEMINI POD ENGINE</span>
                @if($aiConfigured)
                    <span class="font-mono-meta text-[10px] px-2 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-400/40">
                        ● LIVE · {{ strtoupper($aiModel) }}
                    </span>
                @else
                    <span class="font-mono-meta text-[10px] px-2 py-0.5 bg-amber-500/20 text-amber-200 border border-amber-400/40">
                        ● DEMO MODE (ADD GEMINI_API_KEY IN .ENV FOR LIVE AI)
                    </span>
                @endif
            </div>
            <h1 class="font-display text-2xl sm:text-3xl tracking-tight">AI DROPSHIPPING PRODUCT STUDIO</h1>
            <p class="font-mono-meta text-xs text-white/70 mt-1">
                Turn a Tapstitch or Printful blank idea into a conversion-ready KOTB garment listing, SEO pack, ad angles, and margin-verified retail price in seconds.
            </p>
        </div>
        <a href="{{ route('admin.dashboard', ['tab' => 'inventory']) }}" class="font-mono-meta text-xs border border-white/30 px-4 py-2.5 hover:bg-white hover:text-black transition-colors self-start md:self-center whitespace-nowrap">
            ← INVENTORY CATALOG
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
        {{-- Left Column: Prompt & POD Economics Builder --}}
        <div class="xl:col-span-5 bg-white border border-black p-6 flex flex-col gap-5">
            <div class="flex items-center justify-between border-b border-black/15 pb-3">
                <h2 class="font-display text-sm tracking-wider">1. CONCEPT & POD ECONOMICS</h2>
                <span class="font-mono-meta text-[10px] text-[#666]">PRINTFUL / TAPSTITCH</span>
            </div>

            {{-- Quick-Start Presets --}}
            <div>
                <div class="font-mono-meta text-[10px] text-[#666] mb-2">QUICK DROP IDEAS (CLICK TO FILL)</div>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" class="ai-preset font-mono-meta text-[10px] border border-black/30 px-2.5 py-1 hover:bg-black hover:text-white transition-colors cursor-pointer"
                        data-idea="Tapstitch 280GSM vintage acid-wash boxy tee inspired by late-night Manila street basketball"
                        data-cat="TEES" data-supplier="tapstitch_shopify" data-cost="520" data-ship="180">
                        Acid-Wash Boxy Tee
                    </button>
                    <button type="button" class="ai-preset font-mono-meta text-[10px] border border-black/30 px-2.5 py-1 hover:bg-black hover:text-white transition-colors cursor-pointer"
                        data-idea="Heavyweight 420GSM oversized drop-shoulder hoodie with minimalist block coordinates on back"
                        data-cat="HOODIES" data-supplier="tapstitch_shopify" data-cost="950" data-ship="220">
                        420GSM Block Hoodie
                    </button>
                    <button type="button" class="ai-preset font-mono-meta text-[10px] border border-black/30 px-2.5 py-1 hover:bg-black hover:text-white transition-colors cursor-pointer"
                        data-idea="Breathable Printful run club mesh jersey with reflective neon-lime typography for dawn 5K sessions"
                        data-cat="RUNNING" data-supplier="printful" data-cost="580" data-ship="160">
                        Run Club Mesh Jersey
                    </button>
                </div>
            </div>

            <form id="ai-studio-form" class="flex flex-col gap-4">
                <div>
                    <label class="font-mono-meta text-[11px] text-[#666] block mb-1">PRODUCT IDEA OR SUPPLIER BLANK *</label>
                    <textarea id="ai-idea" rows="3" required placeholder="e.g., Heavyweight 280GSM boxy washed tee with gritty concrete typography and retro Manila run-club graphic..." class="w-full rounded-[8px] border border-black p-3 text-[13px]"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="font-mono-meta text-[11px] text-[#666] block mb-1">POD SUPPLIER</label>
                        <select id="ai-supplier" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs bg-white font-mono-meta">
                            @foreach($suppliers as $key => $sup)
                                <option value="{{ $key }}" data-cost="{{ $sup['default_cost'] }}" data-ship="{{ $sup['default_shipping'] }}" data-lead="{{ $sup['lead_time_days'] }}">
                                    {{ $sup['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="font-mono-meta text-[11px] text-[#666] block mb-1">CATEGORY</label>
                        <select id="ai-category" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs bg-white font-mono-meta">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="font-mono-meta text-[11px] text-[#666] block mb-1">BRAND TONE</label>
                        <select id="ai-tone" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs bg-white font-mono-meta">
                            <option value="Streetwear Editorial">Streetwear Editorial</option>
                            <option value="Hype Drop / Limited Archive">Hype Drop / Limited Archive</option>
                            <option value="Minimalist Luxury">Minimalist Luxury</option>
                            <option value="Athletic / Run Club">Athletic / Run Club</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-mono-meta text-[11px] text-[#666] block mb-1">SHOPIFY / PRINTFUL ID (OPT.)</label>
                        <input id="ai-variant-id" type="text" placeholder="e.g. 9876543210987" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs font-mono-meta" />
                    </div>
                </div>

                {{-- Live POD Margin Calculator --}}
                <div class="border border-black bg-[#fafafa] p-4 flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="font-display text-xs">POD MARGIN & ROAS CALCULATOR</span>
                        <span class="font-mono-meta text-[10px] text-[#666]">PAYMONGO 3.5% INCLUDED</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="font-mono-meta text-[10px] text-[#666] block mb-1">BLANK+PRINT (₱)</label>
                            <input id="ai-supplier-cost" type="number" min="50" step="10" value="520" class="w-full h-9 rounded-[6px] border border-black px-2.5 text-xs font-mono-meta bg-white" />
                        </div>
                        <div>
                            <label class="font-mono-meta text-[10px] text-[#666] block mb-1">SHIPPING (₱)</label>
                            <input id="ai-shipping-cost" type="number" min="0" step="10" value="180" class="w-full h-9 rounded-[6px] border border-black px-2.5 text-xs font-mono-meta bg-white" />
                        </div>
                        <div>
                            <label class="font-mono-meta text-[10px] text-[#666] block mb-1">TARGET MARGIN %</label>
                            <input id="ai-target-margin" type="number" min="20" max="85" step="1" value="55" class="w-full h-9 rounded-[6px] border border-black px-2.5 text-xs font-mono-meta bg-white" />
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-2 pt-2 border-t border-black/15 text-center">
                        <div class="bg-white border border-black/15 p-2">
                            <div class="font-mono-meta text-[9px] text-[#666]">LANDED</div>
                            <div id="calc-landed" class="font-display text-xs mt-0.5">₱700</div>
                        </div>
                        <div class="bg-black text-[#d9ff00] p-2">
                            <div class="font-mono-meta text-[9px] text-white/70">RETAIL</div>
                            <div id="calc-retail" class="font-display text-xs mt-0.5">₱1,690</div>
                        </div>
                        <div class="bg-white border border-black/15 p-2">
                            <div class="font-mono-meta text-[9px] text-[#666]">NET PROFIT</div>
                            <div id="calc-profit" class="font-display text-xs text-emerald-700 mt-0.5">₱931</div>
                        </div>
                        <div class="bg-white border border-black/15 p-2">
                            <div class="font-mono-meta text-[9px] text-[#666]">B/E ROAS</div>
                            <div id="calc-roas" class="font-display text-xs mt-0.5">1.82x</div>
                        </div>
                    </div>
                </div>

                <button id="ai-generate-btn" type="submit" class="font-display bg-black text-[#d9ff00] hover:bg-neutral-900 rounded-[30px] py-3.5 text-xs tracking-wider cursor-pointer transition-opacity">
                    ✦ GENERATE POD LISTING WITH GEMINI
                </button>
            </form>
        </div>

        {{-- Right Column: AI Generated Listing Output & Publish --}}
        <div class="xl:col-span-7 bg-white border border-black p-6 flex flex-col gap-5">
            <div class="flex items-center justify-between border-b border-black/15 pb-3">
                <h2 class="font-display text-sm tracking-wider">2. GENERATED STOREFRONT LISTING</h2>
                <span id="ai-mode-badge" class="font-mono-meta text-[10px] px-2 py-0.5 bg-[#f5f5f5] border border-black/20 text-[#666]">
                    READY
                </span>
            </div>

            <div id="ai-notice-box" class="hidden font-mono-meta text-xs border border-black/30 bg-[#fafafa] px-3.5 py-2.5 text-[#333]"></div>

            {{-- Empty State --}}
            <div id="ai-empty-state" class="py-16 text-center flex flex-col items-center justify-center gap-3 border border-dashed border-black/25 bg-[#fafafa]">
                <div class="font-display text-lg">AWAITING DROP CONCEPT</div>
                <p class="font-mono-meta text-xs text-[#666] max-w-md">
                    Describe a Printful or Tapstitch garment on the left and click <strong>Generate</strong>. Gemini will craft the title, editorial description, specs, colorway, SEO tags, social hooks, and profit-optimized pricing.
                </p>
            </div>

            {{-- Result Workspace --}}
            <div id="ai-result-panel" class="hidden flex-col gap-5">
                {{-- Editable Core Listing Fields --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="font-mono-meta text-[10px] text-[#666] block mb-1">GARMENT TITLE</label>
                        <input id="res-title" type="text" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs font-display" />
                    </div>
                    <div>
                        <label class="font-mono-meta text-[10px] text-[#666] block mb-1">RETAIL PRICE (₱)</label>
                        <input id="res-price" type="number" step="1" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs font-mono-meta font-bold" />
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="font-mono-meta text-[10px] text-[#666] block mb-1">CATEGORY</label>
                        <input id="res-category" type="text" class="w-full h-9 rounded-[6px] border border-black px-2.5 text-xs font-mono-meta bg-[#fafafa]" />
                    </div>
                    <div>
                        <label class="font-mono-meta text-[10px] text-[#666] block mb-1">SILHOUETTE</label>
                        <input id="res-silhouette" type="text" class="w-full h-9 rounded-[6px] border border-black px-2.5 text-xs font-mono-meta bg-[#fafafa]" />
                    </div>
                    <div>
                        <label class="font-mono-meta text-[10px] text-[#666] block mb-1">MOTIF</label>
                        <input id="res-motif" type="text" class="w-full h-9 rounded-[6px] border border-black px-2.5 text-xs font-mono-meta" />
                    </div>
                    <div>
                        <label class="font-mono-meta text-[10px] text-[#666] block mb-1">SUB MOTIF</label>
                        <input id="res-sub-motif" type="text" class="w-full h-9 rounded-[6px] border border-black px-2.5 text-xs font-mono-meta" />
                    </div>
                </div>

                {{-- Colorway Swatches --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="flex items-center gap-2 border border-black/20 p-2 rounded-[6px]">
                        <span id="swatch-base" class="w-6 h-6 rounded border border-black shrink-0"></span>
                        <div class="min-w-0 flex-1">
                            <div class="font-mono-meta text-[9px] text-[#666]">BASE</div>
                            <input id="res-base-color" type="text" class="w-full font-mono-meta text-xs bg-transparent outline-none" />
                        </div>
                    </div>
                    <div class="flex items-center gap-2 border border-black/20 p-2 rounded-[6px]">
                        <span id="swatch-accent" class="w-6 h-6 rounded border border-black shrink-0"></span>
                        <div class="min-w-0 flex-1">
                            <div class="font-mono-meta text-[9px] text-[#666]">ACCENT</div>
                            <input id="res-accent-color" type="text" class="w-full font-mono-meta text-xs bg-transparent outline-none" />
                        </div>
                    </div>
                    <div class="flex items-center gap-2 border border-black/20 p-2 rounded-[6px]">
                        <span id="swatch-ink" class="w-6 h-6 rounded border border-black shrink-0"></span>
                        <div class="min-w-0 flex-1">
                            <div class="font-mono-meta text-[9px] text-[#666]">INK</div>
                            <input id="res-ink-color" type="text" class="w-full font-mono-meta text-xs bg-transparent outline-none" />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="font-mono-meta text-[10px] text-[#666] block mb-1">EDITORIAL DESCRIPTION</label>
                    <textarea id="res-description" rows="3" class="w-full rounded-[8px] border border-black p-3 text-[13px]"></textarea>
                </div>

                <div>
                    <label class="font-mono-meta text-[10px] text-[#666] block mb-1">GARMENT SPECS (ONE PER LINE)</label>
                    <textarea id="res-specs" rows="4" class="w-full rounded-[8px] border border-black p-3 text-xs font-mono-meta"></textarea>
                </div>

                {{-- SEO & Marketing Pack --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-black/20 bg-[#fafafa] p-4 flex flex-col gap-2">
                        <div class="font-display text-xs">SEO & SHOPIFY TAGS</div>
                        <div class="font-mono-meta text-[11px]"><strong>Meta Title:</strong> <span id="res-seo-title"></span></div>
                        <div class="font-mono-meta text-[11px] text-[#444]"><strong>Meta Desc:</strong> <span id="res-seo-desc"></span></div>
                        <div id="res-seo-tags" class="flex flex-wrap gap-1 pt-1"></div>
                    </div>
                    <div class="border border-black/20 bg-[#fafafa] p-4 flex flex-col gap-2">
                        <div class="font-display text-xs">LAUNCH MARKETING ANGLES</div>
                        <div class="font-mono-meta text-[11px]"><strong>Ad Headline:</strong> <span id="res-ad-headline"></span></div>
                        <div class="font-mono-meta text-[11px]"><strong>TikTok Hook:</strong> <span id="res-tiktok-hook"></span></div>
                        <div class="font-mono-meta text-[11px] text-[#444]"><strong>IG Caption:</strong> <span id="res-ig-caption"></span></div>
                    </div>
                </div>

                {{-- POD Production Notes --}}
                <div class="border-l-4 border-black bg-[#f5f5f5] px-4 py-3">
                    <div class="font-display text-[11px] mb-1">POD SUPPLIER PRODUCTION NOTES</div>
                    <p id="res-pod-notes" class="font-mono-meta text-xs text-[#333]"></p>
                </div>

                {{-- Publish Bar --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2 border-t border-black/15">
                    <div id="publish-status" class="font-mono-meta text-xs text-emerald-700"></div>
                    <button id="ai-publish-btn" type="button" class="font-display bg-black text-white hover:bg-neutral-800 rounded-[30px] px-6 py-3 text-xs tracking-wider cursor-pointer">
                        PUBLISH DIRECTLY TO STORE →
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const ideaEl = document.getElementById('ai-idea');
    const supplierEl = document.getElementById('ai-supplier');
    const categoryEl = document.getElementById('ai-category');
    const toneEl = document.getElementById('ai-tone');
    const variantEl = document.getElementById('ai-variant-id');
    const costEl = document.getElementById('ai-supplier-cost');
    const shipEl = document.getElementById('ai-shipping-cost');
    const marginEl = document.getElementById('ai-target-margin');

    let lastPayload = null;

    function updateCalculator() {
        const supplierCost = Math.max(50, parseFloat(costEl.value) || 0);
        const shipCost = Math.max(0, parseFloat(shipEl.value) || 0);
        const marginPct = Math.min(85, Math.max(20, parseFloat(marginEl.value) || 55));
        const landed = supplierCost + shipCost;
        const divisor = Math.max(0.10, 1 - 0.035 - (marginPct / 100));
        const rawRetail = landed / divisor;
        let retail = Math.max(landed + 100, Math.ceil(rawRetail / 100) * 100 - 10);
        if (retail < rawRetail) retail += 100;
        const fee = retail * 0.035;
        const profit = retail - landed - fee;
        const roas = profit > 0 ? (retail / profit) : 0;

        document.getElementById('calc-landed').textContent = '₱' + Math.round(landed).toLocaleString();
        document.getElementById('calc-retail').textContent = '₱' + Math.round(retail).toLocaleString();
        document.getElementById('calc-profit').textContent = '₱' + Math.round(profit).toLocaleString();
        document.getElementById('calc-roas').textContent = roas.toFixed(2) + 'x';
    }

    supplierEl.addEventListener('change', () => {
        const opt = supplierEl.options[supplierEl.selectedIndex];
        if (opt?.dataset.cost) costEl.value = opt.dataset.cost;
        if (opt?.dataset.ship) shipEl.value = opt.dataset.ship;
        updateCalculator();
    });

    [costEl, shipEl, marginEl].forEach(el => el.addEventListener('input', updateCalculator));
    updateCalculator();

    document.querySelectorAll('.ai-preset').forEach(btn => {
        btn.addEventListener('click', () => {
            ideaEl.value = btn.dataset.idea || '';
            categoryEl.value = btn.dataset.cat || 'TEES';
            supplierEl.value = btn.dataset.supplier || 'tapstitch_shopify';
            costEl.value = btn.dataset.cost || 520;
            shipEl.value = btn.dataset.ship || 180;
            updateCalculator();
        });
    });

    function syncSwatches() {
        document.getElementById('swatch-base').style.backgroundColor = document.getElementById('res-base-color').value;
        document.getElementById('swatch-accent').style.backgroundColor = document.getElementById('res-accent-color').value;
        document.getElementById('swatch-ink').style.backgroundColor = document.getElementById('res-ink-color').value;
    }
    ['res-base-color', 'res-accent-color', 'res-ink-color'].forEach(id => {
        document.getElementById(id).addEventListener('input', syncSwatches);
    });

    document.getElementById('ai-studio-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('ai-generate-btn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'GENERATING WITH GEMINI...';
        document.getElementById('publish-status').innerHTML = '';

        try {
            const res = await fetch('{{ route('admin.ai.generate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    idea: ideaEl.value,
                    category: categoryEl.value,
                    supplier: supplierEl.value,
                    tone: toneEl.value,
                    supplier_cost: parseFloat(costEl.value) || 520,
                    shipping_cost: parseFloat(shipEl.value) || 180,
                    target_margin: parseFloat(marginEl.value) || 55,
                }),
            });

            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'Generation failed.');
            }

            lastPayload = data;
            const p = data.product;

            document.getElementById('ai-empty-state').classList.add('hidden');
            const panel = document.getElementById('ai-result-panel');
            panel.classList.remove('hidden');
            panel.classList.add('flex');

            const badge = document.getElementById('ai-mode-badge');
            badge.textContent = data.mode === 'gemini_live' ? 'LIVE GEMINI' : 'DEMO FALLBACK';
            badge.className = data.mode === 'gemini_live'
                ? 'font-mono-meta text-[10px] px-2 py-0.5 bg-emerald-100 border border-emerald-500 text-emerald-800'
                : 'font-mono-meta text-[10px] px-2 py-0.5 bg-amber-100 border border-amber-500 text-amber-800';

            const notice = document.getElementById('ai-notice-box');
            notice.textContent = data.notice || '';
            notice.classList.toggle('hidden', !data.notice);

            document.getElementById('res-title').value = p.title || '';
            document.getElementById('res-price').value = p.price || 1490;
            document.getElementById('res-category').value = p.category || 'TEES';
            document.getElementById('res-silhouette').value = p.silhouette || 'tee';
            document.getElementById('res-motif').value = p.motif || 'KOTB';
            document.getElementById('res-sub-motif').value = p.sub_motif || 'DROP 2026';
            document.getElementById('res-base-color').value = p.base_color || '#111111';
            document.getElementById('res-accent-color').value = p.accent_color || '#d9ff00';
            document.getElementById('res-ink-color').value = p.ink_color || '#f5f5f5';
            document.getElementById('res-description').value = p.description || '';
            document.getElementById('res-specs').value = (p.specs || []).join('\n');
            syncSwatches();

            document.getElementById('res-seo-title').textContent = p.seo?.meta_title || '';
            document.getElementById('res-seo-desc').textContent = p.seo?.meta_description || '';
            const tagsBox = document.getElementById('res-seo-tags');
            tagsBox.innerHTML = '';
            (p.seo?.tags || []).forEach(tag => {
                const span = document.createElement('span');
                span.className = 'font-mono-meta text-[10px] px-2 py-0.5 bg-white border border-black/20';
                span.textContent = '#' + tag;
                tagsBox.appendChild(span);
            });

            document.getElementById('res-ad-headline').textContent = p.marketing?.ad_headline || '';
            document.getElementById('res-tiktok-hook').textContent = p.marketing?.tiktok_hook || '';
            document.getElementById('res-ig-caption').textContent = p.marketing?.ig_caption || '';
            document.getElementById('res-pod-notes').textContent = p.pod_print_notes || '';
        } catch (err) {
            alert(err.message || 'Could not generate product brief.');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    document.getElementById('ai-publish-btn').addEventListener('click', async () => {
        if (!lastPayload) return;
        const btn = document.getElementById('ai-publish-btn');
        const statusEl = document.getElementById('publish-status');
        btn.disabled = true;
        btn.textContent = 'PUBLISHING...';

        const specs = document.getElementById('res-specs').value
            .split('\n')
            .map(s => s.trim())
            .filter(Boolean);

        try {
            const res = await fetch('{{ route('admin.ai.publish') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    title: document.getElementById('res-title').value,
                    price: parseFloat(document.getElementById('res-price').value) || 1490,
                    unit_cost: lastPayload.economics?.landed_cost || 700,
                    lead_time_days: lastPayload.economics?.lead_time_days || 7,
                    category: document.getElementById('res-category').value,
                    silhouette: document.getElementById('res-silhouette').value,
                    base_color: document.getElementById('res-base-color').value,
                    accent_color: document.getElementById('res-accent-color').value,
                    ink_color: document.getElementById('res-ink-color').value,
                    motif: document.getElementById('res-motif').value,
                    sub_motif: document.getElementById('res-sub-motif').value,
                    printful_variant_id: variantEl.value || null,
                    description: document.getElementById('res-description').value,
                    specs,
                    bands: lastPayload.product?.bands || ['new'],
                }),
            });

            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'Publish failed.');
            }

            statusEl.innerHTML = `✓ ${data.message} <a href="${data.edit_url}" class="underline ml-2">EDIT PHOTOS/SIZES</a> · <a href="${data.store_url}" target="_blank" class="underline ml-1">VIEW LIVE ↗</a>`;
        } catch (err) {
            statusEl.innerHTML = `<span class="text-red-700">✕ ${err.message}</span>`;
        } finally {
            btn.disabled = false;
            btn.textContent = 'PUBLISH DIRECTLY TO STORE →';
        }
    });
});
</script>
@endpush
