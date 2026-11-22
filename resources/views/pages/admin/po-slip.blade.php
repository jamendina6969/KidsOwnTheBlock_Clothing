<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PO SLIP · {{ $product->title }} | KOTB</title>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@100..125,400..900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }
    </style>
</head>
<body class="bg-[#f5f5f5] text-black p-6 md:p-12">
    <div class="max-w-[760px] mx-auto bg-white border border-black p-8 md:p-10">
        <div class="flex items-start justify-between border-b border-black pb-6 mb-6">
            <div>
                <div class="font-mono-meta text-xs text-[#666] mb-1">PURCHASE ORDER SLIP</div>
                <h1 class="font-display text-2xl">KIDS OWN THE BLOCK</h1>
                <div class="font-mono-meta text-xs text-[#666] mt-1">Generated {{ now()->format('M d, Y H:i') }}</div>
            </div>
            <div class="text-right font-mono-meta text-xs">
                <div class="font-bold">PO-{{ date('Ymd') }}-{{ $product->id }}</div>
                <div>Lead Time: {{ $product->lead_time_days ?? 7 }} days</div>
            </div>
        </div>

        <table class="w-full text-left border-collapse font-mono-meta text-xs mb-8">
            <thead class="bg-[#ededed] border-y border-black">
                <tr>
                    <th class="p-3">SKU / GARMENT</th>
                    <th class="p-3">CURRENT STOCK</th>
                    <th class="p-3">EOQ ORDER QTY</th>
                    <th class="p-3">UNIT COST</th>
                    <th class="p-3 text-right">LINE TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $unitCost = (float) ($product->unit_cost ?? ($product->price * 0.42));
                    $lineTotal = $unitCost * $eoqQty;
                @endphp
                <tr class="border-b border-black/15">
                    <td class="p-3 font-display">{{ $product->title }}</td>
                    <td class="p-3">{{ $product->stock_qty ?? 0 }}</td>
                    <td class="p-3 font-bold">{{ $eoqQty }} units</td>
                    <td class="p-3">₱{{ number_format($unitCost, 2) }}</td>
                    <td class="p-3 text-right font-bold">₱{{ number_format($lineTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-between items-center pt-4 border-t border-black no-print">
            <a href="{{ route('admin.dashboard', ['tab' => 'forecasting']) }}" class="font-mono-meta text-xs underline">← BACK TO FORECASTING</a>
            <button onclick="window.print()" class="font-display bg-black text-white rounded-[30px] px-6 py-2.5 text-xs cursor-pointer">
                PRINT / SAVE PDF
            </button>
        </div>
    </div>
</body>
</html>
