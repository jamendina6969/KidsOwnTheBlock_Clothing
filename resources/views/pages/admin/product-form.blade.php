@extends('layouts.admin')

@section('title', ($product ? 'EDIT ' . $product->title : 'ADD GARMENT') . ' | ADMIN')

@section('content')
<div class="max-w-[820px]">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl">{{ $product ? 'EDIT GARMENT' : 'ADD NEW GARMENT' }}</h1>
        <a href="{{ route('admin.dashboard', ['tab' => 'inventory']) }}" class="font-mono-meta text-xs underline">← BACK TO INVENTORY</a>
    </div>

    <form
        method="POST"
        enctype="multipart/form-data"
        action="{{ $product ? route('admin.products.update', $product->id) : route('admin.products.store') }}"
        class="bg-white border border-black p-6 md:p-8 flex flex-col gap-5"
    >
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="font-mono-meta text-[11px] text-[#666] block mb-1">TITLE</label>
                <input type="text" name="title" required value="{{ old('title', $product?->title) }}" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px]" />
            </div>
            <div>
                <label class="font-mono-meta text-[11px] text-[#666] block mb-1">PRICE (PHP)</label>
                <input type="number" step="0.01" name="price" required value="{{ old('price', $product?->price ?? 48) }}" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px]" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="font-mono-meta text-[11px] text-[#666] block mb-1">STOCK QTY</label>
                <input type="number" name="stock_qty" min="0" value="{{ old('stock_qty', $product?->stock_qty ?? 24) }}" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] font-mono-meta" />
            </div>
            <div>
                <label class="font-mono-meta text-[11px] text-[#666] block mb-1">UNIT COST (₱)</label>
                <input type="number" step="0.01" name="unit_cost" min="0.5" value="{{ old('unit_cost', $product?->unit_cost ?? 18.00) }}" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] font-mono-meta" />
            </div>
            <div>
                <label class="font-mono-meta text-[11px] text-[#666] block mb-1">LEAD TIME (DAYS)</label>
                <input type="number" name="lead_time_days" min="1" value="{{ old('lead_time_days', $product?->lead_time_days ?? 7) }}" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] font-mono-meta" />
            </div>
        </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="font-mono-meta text-[11px] text-[#666] block mb-1">CATEGORY</label>
                    <select name="category" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] bg-white">
                        @foreach(['TEES', 'HOODIES', 'BOTTOMS', 'OUTERWEAR', 'HEADWEAR', 'WOMENS', 'RUNNING', 'HERITAGE PIECES'] as $cat)
                            <option value="{{ $cat }}" {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-mono-meta text-[11px] text-[#666] block mb-1">SVG FALLBACK SILHOUETTE</label>
                    <select name="silhouette" class="w-full h-11 rounded-[8px] border border-black px-3.5 text-[13px] bg-white">
                        @foreach(['tee', 'hoodie', 'jacket', 'shorts', 'pants', 'cap', 'baby-tee', 'jersey'] as $sil)
                            <option value="{{ $sil }}" {{ old('silhouette', $product?->silhouette) === $sil ? 'selected' : '' }}>{{ strtoupper($sil) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="font-mono-meta text-[11px] text-[#666] block mb-1">BASE COLOR</label>
                    <input type="text" name="base_color" required value="{{ old('base_color', $product?->base_color ?? '#111111') }}" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs font-mono-meta" />
                </div>
                <div>
                    <label class="font-mono-meta text-[11px] text-[#666] block mb-1">ACCENT COLOR</label>
                    <input type="text" name="accent_color" required value="{{ old('accent_color', $product?->accent_color ?? '#ffffff') }}" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs font-mono-meta" />
                </div>
                <div>
                    <label class="font-mono-meta text-[11px] text-[#666] block mb-1">INK COLOR</label>
                    <input type="text" name="ink_color" required value="{{ old('ink_color', $product?->ink_color ?? '#e5e5e5') }}" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs font-mono-meta" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="font-mono-meta text-[11px] text-[#666] block mb-1">MOTIF TEXT</label>
                    <input type="text" name="motif" required value="{{ old('motif', $product?->motif ?? 'KOTB') }}" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs" />
                </div>
                <div>
                    <label class="font-mono-meta text-[11px] text-[#666] block mb-1">SUB MOTIF</label>
                    <input type="text" name="sub_motif" required value="{{ old('sub_motif', $product?->sub_motif ?? 'BLOCK 2026') }}" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs" />
                </div>
            </div>

            <div>
                <label class="font-mono-meta text-[11px] text-[#666] block mb-1">SHOPIFY PRODUCT ID / VARIANT ID (FOR AUTO TAPSTITCH SYNC)</label>
                <input type="text" name="printful_variant_id" value="{{ old('printful_variant_id', $product?->printful_variant_id) }}" placeholder="e.g. 9876543210987 (from Shopify Admin product URL)" class="w-full h-10 rounded-[8px] border border-black px-3 text-xs font-mono-meta" />
            </div>

            {{-- Real Photography Uploads --}}
            <div class="border border-black/15 p-4 bg-[#fafafa]">
                <div class="font-display text-xs mb-3">REAL PRODUCT PHOTOGRAPHY (OPTIONAL — OVERRIDES SVG)</div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-mono-meta">
                    <div>
                        <label class="block text-[#666] mb-1">FRONT PNG/JPG</label>
                        <input type="file" name="front_image" accept="image/*" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-[#666] mb-1">BACK PNG/JPG</label>
                        <input type="file" name="back_image" accept="image/*" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-[#666] mb-1">SIZE CHART</label>
                        <input type="file" name="size_chart_image" accept="image/*" class="w-full" />
                    </div>
                </div>
            </div>

            {{-- Per-size Stock & Collection Bands --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="font-mono-meta text-[11px] text-[#666] mb-2">PER-SIZE INVENTORY (S – XXL)</div>
                    @php
                        $sizeMap = collect($product?->sizes ?? [])->keyBy('size');
                    @endphp
                    <div class="grid grid-cols-5 gap-2 text-xs font-mono-meta">
                        @foreach(['S', 'M', 'L', 'XL', 'XXL'] as $sz)
                            @php
                                $szObj = $sizeMap->get($sz);
                                $szQty = $szObj['qty'] ?? (($szObj['inStock'] ?? true) ? 10 : 0);
                            @endphp
                            <div class="border border-black/20 p-1.5 text-center bg-[#fafafa]">
                                <label class="block text-[10px] font-bold mb-1">{{ $sz }}</label>
                                <input type="number" name="size_qty[{{ $sz }}]" min="0" value="{{ $szQty }}" class="w-full h-8 border border-black px-1 text-center bg-white" />
                            </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <div class="font-mono-meta text-[11px] text-[#666] mb-2">COLLECTION BANDS</div>
                    @php $bands = $product?->bands ?? ['new']; @endphp
                    <div class="flex flex-wrap gap-3 text-xs font-mono-meta">
                        @foreach(['new', 'restocked', 'heritage'] as $b)
                            <label class="inline-flex items-center gap-1.5">
                                <input type="checkbox" name="bands[]" value="{{ $b }}" {{ in_array($b, $bands) ? 'checked' : '' }} class="accent-black" />
                                <span>{{ strtoupper($b) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <label class="inline-flex items-center gap-2 text-xs font-mono-meta">
                <input type="checkbox" name="sold_out" value="1" {{ $product?->sold_out ? 'checked' : '' }} class="accent-black" />
                <span>MARK ENTIRE GARMENT AS SOLD OUT</span>
            </label>

            <div>
                <label class="font-mono-meta text-[11px] text-[#666] block mb-1">DESCRIPTION</label>
                <textarea name="description" rows="3" required class="w-full rounded-[8px] border border-black p-3 text-[13px]">{{ old('description', $product?->description) }}</textarea>
            </div>

            <div>
                <label class="font-mono-meta text-[11px] text-[#666] block mb-1">SPECS (ONE PER LINE)</label>
                <textarea name="specs_text" rows="3" class="w-full rounded-[8px] border border-black p-3 text-[13px] font-mono-meta">{{ old('specs_text', implode("\n", $product?->specs ?? ['240 GSM Combed Cotton', 'Boxy Street Fit'])) }}</textarea>
            </div>

            <button type="submit" class="font-display bg-black text-white rounded-[30px] py-3.5 text-xs cursor-pointer">
                {{ $product ? 'SAVE CHANGES' : 'CREATE GARMENT' }}
            </button>
        </form>
</div>
@endsection
