@extends('layouts.admin')

@section('title', 'ADMIN CONTROL CENTER | KIDS OWN THE BLOCK')

@section('content')

{{-- =========================================================
     TAB 1: OVERVIEW & EXECUTIVE ANALYTICS
     ========================================================= --}}
@if($tab === 'overview')
    {{-- Overview Date Range Picker & Quick Filter Bar --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-white border border-black/15 p-3.5 mb-5 flex flex-wrap items-center justify-between gap-3 anim-fade-up">
        <input type="hidden" name="tab" value="overview" />
        <div class="font-display text-xs">ANALYTICS DATE RANGE</div>
        <div class="flex flex-wrap items-center gap-2 font-mono-meta text-xs">
            <label class="text-[10px] text-[#666]">FROM</label>
            <input type="date" name="date_from" value="{{ $filters['dateFrom'] }}" class="h-9 border border-black px-2.5 text-xs bg-white" />
            <label class="text-[10px] text-[#666]">TO</label>
            <input type="date" name="date_to" value="{{ $filters['dateTo'] }}" class="h-9 border border-black px-2.5 text-xs bg-white" />
            <button type="submit" class="btn-sheen font-display bg-black text-white px-4 h-9 text-[11px] cursor-pointer">APPLY</button>
            @if($filters['dateFrom'] || $filters['dateTo'])
                <a href="{{ route('admin.dashboard', ['tab' => 'overview']) }}" class="font-mono-meta text-[11px] underline px-2">Reset</a>
            @endif
        </div>
    </form>

    @php
        $revSeries = $analytics['weekly_revenue'];
        $ordSeries = $analytics['weekly_orders'];
        $unitSeries = $analytics['weekly_units'];
        $makeSpark = function(array $vals) {
            $max = max(1, max($vals));
            $min = min($vals);
            $range = max(1, $max - $min);
            $pts = [];
            $count = count($vals);
            foreach ($vals as $i => $v) {
                $x = $count > 1 ? ($i / ($count - 1)) * 100 : 50;
                $y = 26 - (($v - $min) / $range) * 22;
                $pts[] = round($x, 1) . ',' . round($y, 1);
            }
            return implode(' ', $pts);
        };
    @endphp

    {{-- 5 Clickable Executive KPI Cards with Sparklines & Growth Deltas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3.5 mb-7">
        {{-- Revenue Card --}}
        <a href="{{ route('admin.dashboard', ['tab' => 'orders']) }}" class="block bg-white border border-black/15 hover:border-black p-4 transition-all hover:-translate-y-0.5 shadow-[0_1px_2px_rgba(0,0,0,0.03)] cursor-pointer">
            <div class="flex items-center justify-between mb-1.5">
                <span class="font-mono-meta text-[10px] text-[#666]">8-WK REVENUE ↗</span>
                <span class="font-mono-meta text-[10px] px-1.5 py-0.5 {{ $analytics['rev_delta'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    {{ $analytics['rev_delta'] >= 0 ? '▲' : '▼' }} {{ abs($analytics['rev_delta']) }}%
                </span>
            </div>
            <div class="font-display text-2xl">₱{{ number_format($stats['revenue'], 2) }}</div>
            <svg viewBox="0 0 100 30" class="w-full h-7 mt-2 overflow-visible">
                <polyline points="{{ $makeSpark($revSeries) }}" fill="none" stroke="#059669" stroke-width="2" />
            </svg>
        </a>

        {{-- Orders Card --}}
        <a href="{{ route('admin.dashboard', ['tab' => 'orders']) }}" class="block bg-white border border-black/15 hover:border-black p-4 transition-all hover:-translate-y-0.5 shadow-[0_1px_2px_rgba(0,0,0,0.03)] cursor-pointer">
            <div class="flex items-center justify-between mb-1.5">
                <span class="font-mono-meta text-[10px] text-[#666]">TOTAL ORDERS ↗</span>
                <span class="font-mono-meta text-[10px] px-1.5 py-0.5 {{ $analytics['ord_delta'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                    {{ $analytics['ord_delta'] >= 0 ? '▲' : '▼' }} {{ abs($analytics['ord_delta']) }}%
                </span>
            </div>
            <div class="font-display text-2xl">{{ $stats['orders'] }}</div>
            <svg viewBox="0 0 100 30" class="w-full h-7 mt-2 overflow-visible">
                <polyline points="{{ $makeSpark($ordSeries) }}" fill="none" stroke="#2563eb" stroke-width="2" />
            </svg>
        </a>

        {{-- AOV Card --}}
        <a href="{{ route('admin.dashboard', ['tab' => 'orders']) }}" class="block bg-white border border-black/15 hover:border-black p-4 transition-all hover:-translate-y-0.5 shadow-[0_1px_2px_rgba(0,0,0,0.03)] cursor-pointer">
            <div class="flex items-center justify-between mb-1.5">
                <span class="font-mono-meta text-[10px] text-[#666]">AVG ORDER VALUE ↗</span>
                <span class="font-mono-meta text-[10px] px-1.5 py-0.5 bg-[#ededed] text-black">PHP</span>
            </div>
            <div class="font-display text-2xl">₱{{ number_format($stats['aov'], 2) }}</div>
            <svg viewBox="0 0 100 30" class="w-full h-7 mt-2 overflow-visible">
                <polyline points="{{ $makeSpark($unitSeries) }}" fill="none" stroke="#000000" stroke-width="2" />
            </svg>
        </a>

        {{-- Inventory Health Card --}}
        <a href="{{ route('admin.dashboard', ['tab' => 'forecasting']) }}" class="block bg-white border border-black/15 hover:border-black p-4 transition-all hover:-translate-y-0.5 shadow-[0_1px_2px_rgba(0,0,0,0.03)] cursor-pointer">
            <div class="flex items-center justify-between mb-1.5">
                <span class="font-mono-meta text-[10px] text-[#666]">REORDER ALERTS ↗</span>
                <span class="font-mono-meta text-[10px] px-1.5 py-0.5 {{ $stats['reorder_alerts'] > 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">
                    {{ $stats['reorder_alerts'] > 0 ? 'ACTION' : 'OK' }}
                </span>
            </div>
            <div class="font-display text-2xl">{{ $stats['reorder_alerts'] }} <span class="text-sm text-[#666]">/ {{ $stats['products'] }}</span></div>
            <div class="mt-3 flex h-2 w-full bg-[#ededed] overflow-hidden">
                @php $tot = max(1, $stats['products']); @endphp
                <div class="bg-emerald-600" style="width: {{ ($analytics['healthy_count'] / $tot) * 100 }}%"></div>
                <div class="bg-amber-500" style="width: {{ ($analytics['watch_count'] / $tot) * 100 }}%"></div>
                <div class="bg-red-600" style="width: {{ ($analytics['reorder_count'] / $tot) * 100 }}%"></div>
            </div>
        </a>

        {{-- Community Card --}}
        <a href="{{ route('admin.dashboard', ['tab' => 'community']) }}" class="block bg-black text-white p-4 transition-all hover:-translate-y-0.5 cursor-pointer">
            <div class="flex items-center justify-between mb-1.5">
                <span class="font-mono-meta text-[10px] text-white/60">COMMUNITY HUB ↗</span>
                <span class="font-mono-meta text-[10px] px-1.5 py-0.5 bg-[#d9ff00] text-black font-bold">ACTIVE</span>
            </div>
            <div class="font-display text-2xl">{{ $stats['customers'] + $stats['subscribers'] }}</div>
            <div class="font-mono-meta text-[11px] text-white/70 mt-2">
                {{ $stats['customers'] }} accounts · {{ $stats['subscribers'] }} drop subs
            </div>
        </a>
    </div>

    {{-- Main Charts Row --}}
    <div class="grid grid-cols-1 xl:grid-cols-[1.45fr_0.55fr] gap-6 mb-7">
        <div class="bg-white border border-black/15 p-5 min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <h2 class="font-display text-sm">REVENUE &amp; ORDER VELOCITY (8 WEEKS)</h2>
                    <p class="font-mono-meta text-[11px] text-[#666]">Weekly gross revenue (₱) vs. order count</p>
                </div>
                <span class="font-mono-meta text-[10px] px-2.5 py-1 bg-[#f5f5f5] border border-black/10">EWMA + ACTUALS</span>
            </div>
            <div class="h-[270px] relative w-full">
                <canvas id="chart-revenue-orders"></canvas>
            </div>
        </div>

        <div class="bg-white border border-black/15 p-5 min-w-0">
            <h2 class="font-display text-sm mb-1">REVENUE BY CATEGORY</h2>
            <p class="font-mono-meta text-[11px] text-[#666] mb-4">Share of gross sales</p>
            <div class="h-[250px] flex items-center justify-center relative w-full">
                <canvas id="chart-category-mix"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Movers Leaderboard + Priority Reorder Alerts --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white border border-black/15 p-5">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-display text-sm">TOP MOVERS (ABC-XYZ RANKED)</h2>
                <a href="{{ route('admin.dashboard', ['tab' => 'inventory']) }}" class="font-mono-meta text-[11px] underline">INVENTORY ↗</a>
            </div>
            @php $topRevMax = max(1, $analytics['top_movers']->max('revenue') ?? 1); @endphp
            <div class="flex flex-col gap-3.5">
                @foreach($analytics['top_movers'] as $row)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-12 bg-[#f5f5f5] shrink-0 overflow-hidden border border-black/10">
                            @include('components.garment-svg', ['product' => $row['product'], 'view' => 'front'])
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="font-display text-xs truncate">{{ $row['product']->title }}</span>
                                <span class="font-mono-meta text-xs font-semibold shrink-0">₱{{ number_format($row['revenue'], 2) }}</span>
                            </div>
                            <div class="w-full h-1.5 bg-[#ededed] overflow-hidden mb-1">
                                <div class="h-full bg-black" style="width: {{ ($row['revenue'] / $topRevMax) * 100 }}%"></div>
                            </div>
                            <div class="flex items-center justify-between font-mono-meta text-[10px] text-[#666]">
                                <span>{{ $row['units_sold'] }} units sold · Stock: {{ $row['current_stock'] }}</span>
                                <span class="px-1.5 py-0.5 bg-[#ededed] text-black font-semibold">CLASS {{ $row['abc_xyz'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white border border-black/15 p-5">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-display text-sm">EOQ + SAFETY STOCK ALERTS</h2>
                <a href="{{ route('admin.dashboard', ['tab' => 'forecasting']) }}" class="font-mono-meta text-[11px] underline">FORECASTING ↗</a>
            </div>
            <div class="flex flex-col gap-2.5">
                @foreach($analytics['product_forecasts']->take(5) as $row)
                    <div class="flex items-center justify-between border-b border-black/10 pb-2.5 text-xs">
                        <div>
                            <div class="font-display">{{ $row['product']->title }}</div>
                            <div class="font-mono-meta text-[11px] text-[#666]">
                                Stock: {{ $row['current_stock'] }} · ROP: {{ $row['rop'] }} (SS: {{ $row['safety_stock'] }}) · EOQ: {{ $row['eoq'] }}
                            </div>
                        </div>
                        <span class="font-mono-meta text-[10px] px-2 py-0.5 border {{ $row['status'] === 'REORDER NOW' ? 'bg-red-50 text-red-700 border-red-300 font-bold' : ($row['status'] === 'WATCH' ? 'bg-amber-50 text-amber-700 border-amber-300 font-semibold' : 'bg-emerald-50 text-emerald-700 border-emerald-200') }}">
                            {{ $row['status'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

{{-- =========================================================
     TAB 2: ADVANCED FORECASTING (EWMA + SMA + Z-SCORE EOQ + ABC-XYZ)
     ========================================================= --}}
@elseif($tab === 'forecasting')
    {{-- Interactive What-If Forecasting Simulator --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-black text-white p-5 mb-6">
        <input type="hidden" name="tab" value="forecasting" />
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="font-display text-base">HYBRID FORECASTING ENGINE (EWMA + SMA + PROBABILISTIC EOQ)</h2>
                <p class="font-mono-meta text-[11px] text-white/65 mt-1">
                    EWMA reacts fast to drop spikes · Safety Stock = Z · σ · √L · ABC-XYZ classifies revenue vs. volatility
                </p>
            </div>
            <button type="submit" class="font-display bg-[#d9ff00] text-black px-5 py-2 rounded-[30px] text-xs cursor-pointer">
                RECALCULATE MODEL
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 font-mono-meta text-xs">
            <div class="border border-white/20 p-3">
                <label class="block text-[10px] text-white/60 mb-1">EWMA SMOOTHING (α)</label>
                <select name="alpha" class="w-full bg-black text-white border border-white/30 px-2.5 py-1.5">
                    @foreach(['0.2' => '0.20 (Smooth)', '0.35' => '0.35 (Recommended)', '0.5' => '0.50 (Reactive)', '0.65' => '0.65 (Drop Spike)'] as $val => $lbl)
                        <option value="{{ $val }}" {{ (string) $analytics['alpha'] === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="border border-white/20 p-3">
                <label class="block text-[10px] text-white/60 mb-1">SERVICE LEVEL (Z-SCORE)</label>
                <select name="service_level" class="w-full bg-black text-white border border-white/30 px-2.5 py-1.5">
                    <option value="90" {{ $analytics['service_level'] === 90 ? 'selected' : '' }}>90% (Z = 1.28)</option>
                    <option value="95" {{ $analytics['service_level'] === 95 ? 'selected' : '' }}>95% (Z = 1.65 · Optimal)</option>
                    <option value="99" {{ $analytics['service_level'] === 99 ? 'selected' : '' }}>99% (Z = 2.33 · Zero Stockout)</option>
                </select>
            </div>
            <div class="border border-white/20 p-3">
                <label class="block text-[10px] text-white/60 mb-1">SMA COMPARISON WINDOW</label>
                <select name="sma" class="w-full bg-black text-white border border-white/30 px-2.5 py-1.5">
                    @foreach([2, 3, 4, 6] as $w)
                        <option value="{{ $w }}" {{ $analytics['sma_window'] === $w ? 'selected' : '' }}>{{ $w }}-Week SMA</option>
                    @endforeach
                </select>
            </div>
            <div class="border border-white/20 p-3">
                <label class="block text-[10px] text-white/60 mb-1">ORDERING COST (S)</label>
                <select name="ordering_cost" class="w-full bg-black text-white border border-white/30 px-2.5 py-1.5">
                    @foreach([250, 500, 1000, 1500] as $cost)
                        <option value="{{ $cost }}" {{ (int) $analytics['ordering_cost'] === $cost ? 'selected' : '' }}>₱{{ number_format($cost) }} per PO</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        <div class="bg-white border border-black/15 p-5 min-w-0">
            <h3 class="font-display text-sm mb-1">ACTUAL DEMAND VS. EWMA (α={{ $analytics['alpha'] }}) VS. {{ $analytics['sma_window'] }}W SMA</h3>
            <p class="font-mono-meta text-[11px] text-[#666] mb-4">Weekly unit velocity comparison</p>
            <div class="h-[260px] relative w-full">
                <canvas id="chart-sma-demand"></canvas>
            </div>
        </div>

        <div class="bg-white border border-black/15 p-5 min-w-0">
            <h3 class="font-display text-sm mb-1">CURRENT STOCK VS. ROP (WITH SAFETY STOCK) VS. EOQ</h3>
            <p class="font-mono-meta text-[11px] text-[#666] mb-4">Top 6 priority garments</p>
            <div class="h-[260px] relative w-full">
                <canvas id="chart-eoq-compare"></canvas>
            </div>
        </div>
    </div>

    {{-- Forecasting Search & Status/ABC Filter Bar --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-white border border-black/15 p-3.5 mb-4 flex flex-wrap items-center gap-2.5 font-mono-meta text-xs">
        <input type="hidden" name="tab" value="forecasting" />
        <input type="hidden" name="alpha" value="{{ $analytics['alpha'] }}" />
        <input type="hidden" name="service_level" value="{{ $analytics['service_level'] }}" />
        <input type="hidden" name="sma" value="{{ $analytics['sma_window'] }}" />
        <input type="hidden" name="ordering_cost" value="{{ $analytics['ordering_cost'] }}" />
        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search garment..." class="h-9 border border-black px-3 text-xs flex-1 min-w-[180px]" />
        <select name="forecast_status" onchange="this.form.submit()" class="h-9 border border-black px-2.5 bg-white cursor-pointer">
            <option value="all" {{ $filters['forecastStatusFilter'] === 'all' ? 'selected' : '' }}>Status: All</option>
            <option value="REORDER NOW" {{ $filters['forecastStatusFilter'] === 'REORDER NOW' ? 'selected' : '' }}>REORDER NOW</option>
            <option value="WATCH" {{ $filters['forecastStatusFilter'] === 'WATCH' ? 'selected' : '' }}>WATCH</option>
            <option value="HEALTHY" {{ $filters['forecastStatusFilter'] === 'HEALTHY' ? 'selected' : '' }}>HEALTHY</option>
        </select>
        <select name="abc" onchange="this.form.submit()" class="h-9 border border-black px-2.5 bg-white cursor-pointer">
            <option value="all" {{ $filters['abcFilter'] === 'all' ? 'selected' : '' }}>ABC Class: All</option>
            <option value="A" {{ $filters['abcFilter'] === 'A' ? 'selected' : '' }}>Class A (Top 80%)</option>
            <option value="B" {{ $filters['abcFilter'] === 'B' ? 'selected' : '' }}>Class B (15%)</option>
            <option value="C" {{ $filters['abcFilter'] === 'C' ? 'selected' : '' }}>Class C (5%)</option>
        </select>
        <button type="submit" class="btn-sheen font-display bg-black text-white px-4 h-9 text-[11px] cursor-pointer">FILTER</button>
    </form>

    <div class="bg-white border border-black/15 overflow-x-auto anim-fade-up">
        <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-[#ededed] border-b border-black font-mono-meta text-[11px]">
                <tr>
                    <th class="p-3">GARMENT</th>
                    <th class="p-3">ABC-XYZ</th>
                    <th class="p-3">STOCK</th>
                    <th class="p-3">EWMA / WK</th>
                    <th class="p-3">SMA / WK</th>
                    <th class="p-3">SAFETY STOCK</th>
                    <th class="p-3">ROP</th>
                    <th class="p-3">OPTIMAL EOQ</th>
                    <th class="p-3">STATUS</th>
                    <th class="p-3 text-right">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analytics['filtered_forecasts'] as $row)
                    <tr class="border-b border-black/10 hover:bg-[#fafafa]">
                        <td class="p-3 font-display">{{ $row['product']->title }}</td>
                        <td class="p-3 font-mono-meta">
                            <span class="px-1.5 py-0.5 text-[10px] font-bold {{ str_starts_with($row['abc_xyz'], 'A') ? 'bg-black text-white' : 'bg-[#ededed] text-black' }}">
                                {{ $row['abc_xyz'] }}
                            </span>
                        </td>
                        <td class="p-3 font-mono-meta font-semibold">{{ $row['current_stock'] }}</td>
                        <td class="p-3 font-mono-meta text-blue-700 font-semibold">{{ $row['weekly_ewma'] }}</td>
                        <td class="p-3 font-mono-meta text-[#666]">{{ $row['weekly_sma'] }}</td>
                        <td class="p-3 font-mono-meta">{{ $row['safety_stock'] }}</td>
                        <td class="p-3 font-mono-meta">{{ $row['rop'] }}</td>
                        <td class="p-3 font-mono-meta font-bold">{{ $row['eoq'] }} units</td>
                        <td class="p-3">
                            <span class="font-mono-meta text-[10px] px-2 py-0.5 border {{ $row['status'] === 'REORDER NOW' ? 'bg-red-50 text-red-700 border-red-300 font-bold' : ($row['status'] === 'WATCH' ? 'bg-amber-50 text-amber-700 border-amber-300 font-semibold' : 'bg-emerald-50 text-emerald-700 border-emerald-200') }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.products.po', ['id' => $row['product']->id, 'eoq' => $row['eoq']]) }}" class="font-mono-meta text-[10px] border border-black/30 px-2 py-1 hover:border-black">PO ↗</a>
                                <form method="POST" action="{{ route('admin.products.restock', $row['product']->id) }}">
                                    @csrf
                                    <input type="hidden" name="eoq_qty" value="{{ $row['eoq'] }}" />
                                    <button type="submit" class="font-mono-meta text-[10px] border border-black px-2.5 py-1 hover:bg-black hover:text-white transition-colors cursor-pointer">
                                        + RESTOCK {{ $row['eoq'] }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

{{-- =========================================================
     TAB 3: CATALOG & INVENTORY
     ========================================================= --}}
@elseif($tab === 'inventory')
    <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-white border border-black/15 p-3.5 mb-4 flex flex-wrap items-center gap-2.5 font-mono-meta text-xs anim-fade-up">
        <input type="hidden" name="tab" value="inventory" />
        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search garment title or motif..." class="h-9 border border-black px-3 text-xs flex-1 min-w-[200px]" />
        <select name="category" onchange="this.form.submit()" class="h-9 border border-black px-2.5 bg-white cursor-pointer">
            <option value="ALL" {{ $filters['catFilter'] === 'ALL' ? 'selected' : '' }}>Category: All</option>
            @foreach(['TEES', 'HOODIES', 'BOTTOMS', 'OUTERWEAR', 'HEADWEAR', 'WOMENS', 'RUNNING', 'HERITAGE PIECES'] as $cat)
                <option value="{{ $cat }}" {{ $filters['catFilter'] === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <select name="stock_status" onchange="this.form.submit()" class="h-9 border border-black px-2.5 bg-white cursor-pointer">
            <option value="all" {{ $filters['stockFilter'] === 'all' ? 'selected' : '' }}>Stock: All</option>
            <option value="in_stock" {{ $filters['stockFilter'] === 'in_stock' ? 'selected' : '' }}>In Stock</option>
            <option value="sold_out" {{ $filters['stockFilter'] === 'sold_out' ? 'selected' : '' }}>Sold Out</option>
        </select>
        <button type="submit" class="btn-sheen font-display bg-black text-white px-4 h-9 text-[11px] cursor-pointer">SEARCH</button>
        @if($filters['q'] || $filters['catFilter'] !== 'ALL' || $filters['stockFilter'] !== 'all')
            <a href="{{ route('admin.dashboard', ['tab' => 'inventory']) }}" class="underline px-2">Reset</a>
        @endif
    </form>

    <div class="bg-white border border-black/15 overflow-x-auto anim-fade-up">
        <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-[#ededed] border-b border-black font-mono-meta text-[11px]">
                <tr>
                    <th class="p-3">GARMENT</th>
                    <th class="p-3">CATEGORY</th>
                    <th class="p-3">PRICE</th>
                    <th class="p-3">COST</th>
                    <th class="p-3">STOCK</th>
                    <th class="p-3">STATUS</th>
                    <th class="p-3 text-right">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $p)
                    <tr class="border-b border-black/10 hover:bg-[#fafafa]">
                        <td class="p-3 flex items-center gap-3">
                            <div class="w-10 h-12 bg-[#f5f5f5] shrink-0 overflow-hidden">
                                @include('components.garment-svg', ['product' => $p, 'view' => 'front'])
                            </div>
                            <span class="font-display">{{ $p->title }}</span>
                        </td>
                        <td class="p-3 font-mono-meta">{{ $p->category }}</td>
                        <td class="p-3 font-mono-meta">₱{{ number_format($p->price, 2) }}</td>
                        <td class="p-3 font-mono-meta text-[#666]">₱{{ number_format((float) ($p->unit_cost ?? 350), 2) }}</td>
                        <td class="p-3 font-mono-meta font-semibold">{{ $p->sold_out ? 0 : ($p->stock_qty ?? 24) }}</td>
                        <td class="p-3">
                            <form method="POST" action="{{ route('admin.products.toggle', $p->id) }}">
                                @csrf
                                <button type="submit" class="font-mono-meta text-[11px] px-2.5 py-1 rounded-[30px] border cursor-pointer {{ $p->sold_out ? 'bg-red-50 text-red-700 border-red-300' : 'bg-emerald-50 text-emerald-700 border-emerald-300' }}">
                                    {{ $p->sold_out ? 'SOLD OUT' : 'IN STOCK' }}
                                </button>
                            </form>
                        </td>
                        <td class="p-3 text-right font-mono-meta">
                            <a href="{{ route('admin.products.edit', $p->id) }}" class="underline mr-3">Edit</a>
                            <form method="POST" action="{{ route('admin.products.delete', $p->id) }}" class="inline" onsubmit="return confirm('Delete {{ $p->title }}?')">
                                @csrf
                                <button type="submit" class="text-red-700 underline cursor-pointer">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

{{-- =========================================================
     TAB 4: ORDERS & PAYMENT VERIFICATION
     ========================================================= --}}
@elseif($tab === 'orders')
    <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-white border border-black/15 p-3.5 mb-4 flex flex-wrap items-center gap-2.5 font-mono-meta text-xs anim-fade-up">
        <input type="hidden" name="tab" value="orders" />
        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search Order #, Name, Email..." class="h-9 border border-black px-3 text-xs flex-1 min-w-[190px]" />
        <select name="payment_status" onchange="this.form.submit()" class="h-9 border border-black px-2.5 bg-white cursor-pointer">
            <option value="all" {{ $filters['payFilter'] === 'all' ? 'selected' : '' }}>Payment: All</option>
            <option value="pending_verification" {{ $filters['payFilter'] === 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
            <option value="paid" {{ $filters['payFilter'] === 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="unpaid_cod" {{ $filters['payFilter'] === 'unpaid_cod' ? 'selected' : '' }}>Unpaid COD</option>
            <option value="rejected" {{ $filters['payFilter'] === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <select name="fulfillment_status" onchange="this.form.submit()" class="h-9 border border-black px-2.5 bg-white cursor-pointer">
            <option value="all" {{ $filters['fulfillFilter'] === 'all' ? 'selected' : '' }}>Fulfillment: All</option>
            @foreach(['pending_verification', 'confirmed', 'packed', 'shipped', 'delivered', 'cancelled'] as $st)
                <option value="{{ $st }}" {{ $filters['fulfillFilter'] === $st ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $st)) }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ $filters['dateFrom'] }}" title="From date" class="h-9 border border-black px-2 bg-white" />
        <input type="date" name="date_to" value="{{ $filters['dateTo'] }}" title="To date" class="h-9 border border-black px-2 bg-white" />
        <button type="submit" class="btn-sheen font-display bg-black text-white px-4 h-9 text-[11px] cursor-pointer">FILTER</button>
        @if($filters['q'] || $filters['payFilter'] !== 'all' || $filters['fulfillFilter'] !== 'all' || $filters['dateFrom'] || $filters['dateTo'])
            <a href="{{ route('admin.dashboard', ['tab' => 'orders']) }}" class="underline px-2">Reset</a>
        @endif
    </form>

    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
        <form id="bulk-orders-form" method="POST" action="{{ route('admin.orders.bulk') }}" class="flex flex-wrap items-center gap-2 font-mono-meta text-xs">
            @csrf
            <select name="bulk_status" class="h-9 border border-black px-2.5 bg-white cursor-pointer">
                <option value="packed">Bulk Mark: PACKED</option>
                <option value="shipped">Bulk Mark: SHIPPED</option>
                <option value="delivered">Bulk Mark: DELIVERED</option>
                <option value="cancelled">Bulk Mark: CANCELLED</option>
            </select>
            <button type="submit" class="font-display bg-black text-white px-4 h-9 text-[11px] cursor-pointer">APPLY TO SELECTED</button>
        </form>
        <a href="{{ route('admin.orders.export') }}" class="font-mono-meta text-xs border border-black px-3.5 py-2 bg-white hover:bg-black hover:text-white transition-colors">
            EXPORT ORDERS CSV ↓
        </a>
    </div>

    <div class="bg-white border border-black/15 overflow-x-auto anim-fade-up">
        <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-[#ededed] border-b border-black font-mono-meta text-[11px]">
                <tr>
                    <th class="p-3 w-8"><input type="checkbox" onclick="document.querySelectorAll('.order-chk').forEach(c => c.checked = this.checked)" class="accent-black" /></th>
                    <th class="p-3">ORDER #</th>
                    <th class="p-3">CUSTOMER</th>
                    <th class="p-3">TOTAL</th>
                    <th class="p-3">PAYMENT &amp; PROOF</th>
                    <th class="p-3">POD ROUTING</th>
                    <th class="p-3">FULFILLMENT</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $pStatus = $order->payment_status ?? ($order->payment_method === 'cod' ? 'unpaid_cod' : 'paid');
                    @endphp
                    <tr class="border-b border-black/10 hover:bg-[#fafafa] transition-colors">
                        <td class="p-3"><input type="checkbox" name="order_ids[]" value="{{ $order->id }}" form="bulk-orders-form" class="order-chk accent-black" /></td>
                        <td class="p-3">
                            <a href="{{ route('order.receipt', $order->order_number) }}" class="font-display underline">{{ $order->order_number }}</a>
                            <div class="font-mono-meta text-[10px] text-[#666]">{{ $order->created_at?->format('M d, Y') }}</div>
                        </td>
                        <td class="p-3 font-mono-meta">
                            <div>{{ $order->customer_name ?? 'Guest' }}</div>
                            <div class="text-[10px] text-[#666]">{{ $order->customer_email }} · {{ $order->city ?? '—' }}</div>
                        </td>
                        <td class="p-3 font-mono-meta font-semibold">
                            ₱{{ number_format((float) ($order->total ?: $order->subtotal), 2) }}
                            <div class="text-[10px] text-[#666] uppercase">{{ $order->payment_method }}</div>
                        </td>
                        <td class="p-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono-meta text-[10px] px-2 py-0.5 border {{ $pStatus === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : ($pStatus === 'pending_verification' ? 'bg-amber-50 text-amber-700 border-amber-300 font-bold' : ($pStatus === 'rejected' ? 'bg-red-50 text-red-700 border-red-300' : 'bg-[#ededed] border-black/25')) }}">
                                    {{ strtoupper(str_replace('_', ' ', $pStatus)) }}
                                </span>
                                @if($order->payment_reference)
                                    <span class="font-mono-meta text-[10px] text-[#555]">Ref: {{ $order->payment_reference }}</span>
                                @endif
                                @if($order->payment_proof)
                                    <a href="{{ asset($order->payment_proof) }}" target="_blank" class="font-mono-meta text-[10px] underline text-blue-700">Receipt ↗</a>
                                @endif
                            </div>
                            @if($pStatus !== 'paid')
                                <div class="flex gap-1.5 mt-2">
                                    <form method="POST" action="{{ route('admin.orders.verify', $order->id) }}">
                                        @csrf
                                        <button type="submit" class="font-mono-meta text-[10px] bg-black text-white px-2 py-0.5 cursor-pointer">✓ VERIFY PAID</button>
                                    </form>
                                    @if($pStatus === 'pending_verification')
                                        <form method="POST" action="{{ route('admin.orders.reject', $order->id) }}">
                                            @csrf
                                            <button type="submit" class="font-mono-meta text-[10px] border border-red-700 text-red-700 px-2 py-0.5 cursor-pointer">✕ REJECT</button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="p-3">
                            <div class="font-mono-meta text-[10px] text-[#666] mb-1">
                                {{ $order->country ?? 'PH' }} · {{ ($order->country ?? 'PH') === 'PH' ? 'LOCAL / TAPSTITCH' : 'TAPSTITCH' }}
                            </div>
                            @if($order->external_pod_id)
                                <span class="font-mono-meta text-[10px] px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-300">
                                    TAPSTITCH #{{ $order->external_pod_id }} ({{ strtoupper($order->pod_status ?? 'submitted') }})
                                </span>
                            @else
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex flex-wrap gap-1">
                                        <form method="POST" action="{{ route('admin.orders.shopify', $order->id) }}">
                                            @csrf
                                            <button type="submit" class="font-mono-meta text-[10px] bg-black text-white px-2 py-0.5 cursor-pointer hover:opacity-80">
                                                PUSH TO SHOPIFY ↗
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.orders.podsheet', $order->id) }}" class="font-mono-meta text-[10px] border border-black px-2 py-0.5 hover:bg-black hover:text-white transition-colors">
                                            TAPSTITCH CSV ↓
                                        </a>
                                    </div>
                                    <form method="POST" action="{{ route('admin.orders.tapstitch', $order->id) }}" class="flex items-center gap-1">
                                        @csrf
                                        <input type="text" name="tapstitch_order_id" required placeholder="Tapstitch Order #" class="w-28 h-6 border border-black/40 px-1.5 font-mono-meta text-[10px] bg-white" />
                                        <button type="submit" class="h-6 font-mono-meta text-[10px] border border-black px-2 cursor-pointer hover:bg-black hover:text-white">
                                            SAVE
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </td>
                        <td class="p-3">
                            <form method="POST" action="{{ route('admin.orders.status', $order->id) }}">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="font-mono-meta text-[11px] border border-black px-2 py-1 bg-white cursor-pointer">
                                    @foreach(['pending_verification', 'confirmed', 'packed', 'shipped', 'delivered', 'cancelled'] as $st)
                                        <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $st)) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center font-mono-meta text-[#666]">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

{{-- =========================================================
     TAB 5: CUSTOMERS
     ========================================================= --}}
@elseif($tab === 'customers')
    <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-white border border-black/15 p-3.5 mb-4 flex flex-wrap items-center gap-2.5 font-mono-meta text-xs anim-fade-up">
        <input type="hidden" name="tab" value="customers" />
        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search customer name or email..." class="h-9 border border-black px-3 text-xs flex-1 min-w-[200px]" />
        <button type="submit" class="btn-sheen font-display bg-black text-white px-4 h-9 text-[11px] cursor-pointer">SEARCH</button>
        @if($filters['q'])
            <a href="{{ route('admin.dashboard', ['tab' => 'customers']) }}" class="underline px-2">Reset</a>
        @endif
    </form>

    <div class="bg-white border border-black/15 overflow-x-auto anim-fade-up">
        <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-[#ededed] border-b border-black font-mono-meta text-[11px]">
                <tr>
                    <th class="p-3">NAME</th>
                    <th class="p-3">EMAIL</th>
                    <th class="p-3">ROLE</th>
                    <th class="p-3">ORDERS</th>
                    <th class="p-3">JOINED</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $cust)
                    <tr class="border-b border-black/10">
                        <td class="p-3 font-display">{{ $cust->name }}</td>
                        <td class="p-3 font-mono-meta">{{ $cust->email }}</td>
                        <td class="p-3 font-mono-meta">
                            <span class="px-2 py-0.5 text-[10px] {{ $cust->is_admin ? 'bg-black text-white' : 'border border-black/25' }}">
                                {{ $cust->is_admin ? 'ADMIN' : 'CUSTOMER' }}
                            </span>
                        </td>
                        <td class="p-3 font-mono-meta">{{ $cust->orders_count }}</td>
                        <td class="p-3 font-mono-meta text-[#666]">{{ $cust->created_at?->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

{{-- =========================================================
     TAB 6: PROMOS & NEWSLETTER
     ========================================================= --}}
@elseif($tab === 'promos')
    <div class="grid grid-cols-1 lg:grid-cols-[1.2fr_0.8fr] gap-6">
        <div class="bg-white border border-black/15 p-5">
            <h2 class="font-display text-sm mb-4">PROMO CODES</h2>
            <form method="POST" action="{{ route('admin.promos.store') }}" class="flex flex-wrap gap-2.5 mb-5">
                @csrf
                <input type="text" name="code" required placeholder="CODE (e.g. DROP20)" class="h-10 border border-black px-3 text-xs font-mono-meta uppercase flex-1" />
                <input type="number" name="discount_percent" required min="1" max="90" placeholder="% OFF" class="h-10 w-24 border border-black px-3 text-xs font-mono-meta" />
                <button type="submit" class="font-display bg-black text-white px-5 h-10 text-xs cursor-pointer">CREATE</button>
            </form>

            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-[#ededed] border-b border-black font-mono-meta text-[11px]">
                    <tr>
                        <th class="p-2.5">CODE</th>
                        <th class="p-2.5">DISCOUNT</th>
                        <th class="p-2.5">USES</th>
                        <th class="p-2.5">STATUS</th>
                        <th class="p-2.5 text-right">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($promos as $promo)
                        <tr class="border-b border-black/10">
                            <td class="p-2.5 font-display">{{ $promo->code }}</td>
                            <td class="p-2.5 font-mono-meta">{{ $promo->discount_percent }}% OFF</td>
                            <td class="p-2.5 font-mono-meta">{{ $promo->uses_count }}</td>
                            <td class="p-2.5">
                                <form method="POST" action="{{ route('admin.promos.toggle', $promo->id) }}">
                                    @csrf
                                    <button type="submit" class="font-mono-meta text-[10px] px-2 py-0.5 border cursor-pointer {{ $promo->active ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-white text-black border-black/25' }}">
                                        {{ $promo->active ? 'ACTIVE' : 'PAUSED' }}
                                    </button>
                                </form>
                            </td>
                            <td class="p-2.5 text-right">
                                <form method="POST" action="{{ route('admin.promos.delete', $promo->id) }}">
                                    @csrf
                                    <button type="submit" class="font-mono-meta text-[11px] text-red-700 underline cursor-pointer">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white border border-black/15 p-5">
            <h2 class="font-display text-sm mb-4">NEWSLETTER SUBSCRIBERS ({{ $subscribers->count() }})</h2>
            <ul class="font-mono-meta text-xs flex flex-col gap-2 max-h-[360px] overflow-y-auto">
                @forelse($subscribers as $sub)
                    <li class="border-b border-black/10 pb-1.5 flex justify-between">
                        <span>{{ $sub->email }}</span>
                        <span class="text-[#666]">{{ $sub->created_at?->format('M d') }}</span>
                    </li>
                @empty
                    <li class="text-[#666]">No subscribers yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

{{-- =========================================================
     TAB 7: COMMUNITY HUB (LIVE CHAT, TICKETS, RUN CLUB, NEWSLETTER, SPLASH MEDIA)
     ========================================================= --}}
@elseif($tab === 'community')
    {{-- Live Visitor Chat Inbox (Staff Reply) --}}
    <div class="bg-white border border-black p-5 mb-6 anim-fade-up">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-display text-sm">LIVE VISITOR CHAT INBOX ({{ ($chatThreads ?? collect())->count() }})</h2>
                <p class="font-mono-meta text-[11px] text-[#666]">Messages sent from the storefront &amp; landing page Chat bubble appear here in real time.</p>
            </div>
        </div>

        @if(($chatThreads ?? collect())->isEmpty())
            <div class="border border-black/15 p-6 text-center font-mono-meta text-xs text-[#666]">
                No live chat conversations yet.
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($chatThreads as $sessId => $msgs)
                    @php
                        $firstVisitor = $msgs->firstWhere('sender', 'visitor');
                        $unreadCount = $msgs->where('sender', 'visitor')->where('is_read', false)->count();
                    @endphp
                    <div class="border border-black p-4 flex flex-col justify-between bg-[#fafafa]">
                        <div>
                            <div class="flex items-center justify-between border-b border-black/10 pb-2 mb-3">
                                <span class="font-display text-xs">{{ $firstVisitor?->visitor_name ?? 'Visitor' }}</span>
                                @if($unreadCount > 0)
                                    <span class="font-mono-meta text-[10px] px-2 py-0.5 bg-[#d9ff00] text-black font-bold">{{ $unreadCount }} NEW</span>
                                @else
                                    <span class="font-mono-meta text-[10px] text-[#666]">REPLIED</span>
                                @endif
                            </div>

                            <div class="flex flex-col gap-2 max-h-48 overflow-y-auto mb-3 pr-1">
                                @foreach($msgs as $m)
                                    <div class="text-xs px-3 py-2 max-w-[85%] {{ $m->sender === 'admin' ? 'self-end bg-black text-white' : 'self-start bg-[#ededed] text-black' }}">
                                        <div class="font-mono-meta text-[9px] opacity-60 mb-0.5">{{ $m->sender === 'admin' ? 'STAFF' : 'VISITOR' }} · {{ $m->created_at?->format('H:i') }}</div>
                                        <div>{{ $m->message }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.chat.reply', ['sessionId' => $sessId]) }}" class="flex gap-2 pt-2 border-t border-black/10">
                            @csrf
                            <input type="text" name="message" required placeholder="Type staff reply..." class="flex-1 h-9 border border-black px-3 text-xs bg-white outline-none" />
                            <button type="submit" class="font-display bg-black text-white px-4 h-9 text-[11px] cursor-pointer">REPLY</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 anim-fade-up">
        <div class="bg-white border border-black/15 p-5">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <h2 class="font-display text-sm">SUPPORT TICKETS ({{ $tickets->count() }})</h2>
                <form method="GET" action="{{ route('admin.dashboard') }}">
                    <input type="hidden" name="tab" value="community" />
                    <select name="ticket_status" onchange="this.form.submit()" class="h-8 border border-black px-2 text-[11px] font-mono-meta bg-white cursor-pointer">
                        <option value="all" {{ $filters['ticketStatusFilter'] === 'all' ? 'selected' : '' }}>All Tickets</option>
                        <option value="open" {{ $filters['ticketStatusFilter'] === 'open' ? 'selected' : '' }}>Open Only</option>
                        <option value="resolved" {{ $filters['ticketStatusFilter'] === 'resolved' ? 'selected' : '' }}>Resolved Only</option>
                    </select>
                </form>
            </div>
            <div class="flex flex-col gap-3 font-mono-meta text-xs max-h-[380px] overflow-y-auto">
                @forelse($tickets as $t)
                    @php $tStatus = $t->status ?? 'open'; @endphp
                    <div class="border border-black/15 p-3">
                        <div class="flex justify-between items-center mb-1.5 gap-2">
                            <span class="font-semibold">{{ $t->email }}</span>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 bg-[#ededed] text-[10px]">{{ strtoupper($t->topic) }}</span>
                                <form method="POST" action="{{ route('admin.tickets.toggle', $t->id) }}">
                                    @csrf
                                    <button type="submit" class="px-2 py-0.5 text-[10px] border cursor-pointer {{ $tStatus === 'resolved' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-amber-50 text-amber-700 border-amber-300' }}">
                                        {{ strtoupper($tStatus) }}
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if($t->order_number)
                            <div class="text-[11px] text-[#666] mb-1">Order: {{ $t->order_number }}</div>
                        @endif
                        <p class="text-[#333]">{{ $t->message }}</p>
                    </div>
                @empty
                    <div class="text-[#666]">No support tickets yet.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white border border-black/15 p-5">
            <h2 class="font-display text-sm mb-4">RUN CLUB RSVPS ({{ $rsvps->count() }})</h2>
            <div class="flex flex-col gap-2 font-mono-meta text-xs max-h-[380px] overflow-y-auto">
                @forelse($rsvps as $r)
                    <div class="border-b border-black/10 pb-2 flex justify-between">
                        <span class="font-semibold">{{ $r->city }}</span>
                        <span class="text-[#666]">{{ $r->created_at?->format('M d, Y H:i') }}</span>
                    </div>
                @empty
                    <div class="text-[#666]">No RSVPs yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-black/15 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-sm">NEWSLETTER DROP LIST ({{ $subscribers->count() }})</h2>
                <a href="{{ route('admin.subscribers.export') }}" class="font-mono-meta text-[11px] border border-black px-3 py-1 hover:bg-black hover:text-white transition-colors">
                    EXPORT CSV ↓
                </a>
            </div>
            <ul class="font-mono-meta text-xs flex flex-col gap-1.5 max-h-48 overflow-y-auto">
                @forelse($subscribers as $sub)
                    <li class="border-b border-black/10 pb-1 flex justify-between">
                        <span>{{ $sub->email }}</span>
                        <span class="text-[#666]">{{ $sub->created_at?->format('M d') }}</span>
                    </li>
                @empty
                    <li class="text-[#666]">No subscribers yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white border border-black/15 p-5">
            <h2 class="font-display text-sm mb-2">LANDING PAGE SPLASH VIDEO</h2>
            <p class="font-mono-meta text-[11px] text-[#666] mb-4">Upload a looping .mp4 / .webm clip to play behind your Youngstunna logo on `/`.</p>
            <form method="POST" action="{{ route('admin.splash.video') }}" enctype="multipart/form-data" class="flex flex-col gap-3">
                @csrf
                <input type="file" name="splash_video" accept="video/mp4,video/webm,video/quicktime" required class="font-mono-meta text-xs border border-black/25 p-2" />
                <button type="submit" class="font-display self-start bg-black text-white rounded-[30px] px-5 py-2.5 text-xs cursor-pointer">
                    UPLOAD &amp; ACTIVATE VIDEO
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <div class="bg-white border border-black/15 p-5">
            <h2 class="font-display text-sm mb-3">RESTOCK ALERTS ({{ ($restockAlerts ?? collect())->count() }})</h2>
            <div class="flex flex-wrap gap-2 font-mono-meta text-xs">
                @forelse($restockAlerts ?? [] as $alert)
                    <span class="border border-black/20 px-3 py-1.5 bg-[#fafafa]">
                        {{ $alert->email }} → <strong>{{ $alert->product?->title }}</strong>
                    </span>
                @empty
                    <span class="text-[#666]">No restock alert requests yet.</span>
                @endforelse
            </div>
        </div>

        @php
            $vipFile = storage_path('app/vip_lock.json');
            $vipData = file_exists($vipFile) ? json_decode(file_get_contents($vipFile), true) : ['enabled' => false, 'passcode' => 'BLOCK2026'];
        @endphp
        <div class="bg-black text-white p-5">
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-display text-sm">DROP 05 VIP EARLY ACCESS GATE</h2>
                <span class="font-mono-meta text-[10px] px-2 py-0.5 {{ !empty($vipData['enabled']) ? 'bg-[#d9ff00] text-black font-bold' : 'bg-white/15 text-white' }}">
                    {{ !empty($vipData['enabled']) ? 'LOCKED' : 'PUBLIC' }}
                </span>
            </div>
            <p class="font-mono-meta text-[11px] text-white/65 mb-4">Lock `/shop` behind a VIP passcode before a drop goes public.</p>
            <form method="POST" action="{{ route('admin.vip.lock') }}" class="flex flex-wrap items-center gap-2.5 font-mono-meta text-xs">
                @csrf
                <input type="text" name="passcode" value="{{ $vipData['passcode'] ?? 'BLOCK2026' }}" class="h-9 bg-black border border-white/40 px-3 text-white uppercase w-36" />
                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" name="enabled" value="1" {{ !empty($vipData['enabled']) ? 'checked' : '' }} class="accent-[#d9ff00]" />
                    <span>Enable Lock</span>
                </label>
                <button type="submit" class="font-display bg-white text-black px-4 h-9 rounded-[30px] text-[11px] cursor-pointer ml-auto">SAVE</button>
            </form>
        </div>
    </div>

{{-- =========================================================
     TAB 8: LOOKBOOK & STORY CMS
     ========================================================= --}}
@elseif($tab === 'cms')
    @php
        $bannerFile = storage_path('app/store_banner.json');
        $bannerData = file_exists($bannerFile) ? json_decode(file_get_contents($bannerFile), true) : [
            'marquee_text' => 'DROP 04 LIVE · 240 GSM HEAVYWEIGHT · KIDS OWN THE BLOCK · WORLDWIDE DISPATCH ·',
            'drop_target_iso' => now()->addDays(2)->format('Y-m-d\TH:i'),
        ];
    @endphp
    <div class="bg-black text-white p-5 mb-6">
        <h2 class="font-display text-sm mb-3">STOREFRONT MARQUEE &amp; DROP COUNTDOWN</h2>
        <form method="POST" action="{{ route('admin.banner.update') }}" class="grid grid-cols-1 md:grid-cols-[1fr_240px_auto] gap-3 font-mono-meta text-xs items-end">
            @csrf
            <div>
                <label class="block text-[10px] text-white/60 mb-1">MARQUEE TICKER TEXT</label>
                <input type="text" name="marquee_text" required value="{{ $bannerData['marquee_text'] }}" class="w-full h-10 bg-black border border-white/40 px-3 text-white uppercase" />
            </div>
            <div>
                <label class="block text-[10px] text-white/60 mb-1">DROP UNLOCK DATE/TIME</label>
                <input type="datetime-local" name="drop_target_iso" required value="{{ substr($bannerData['drop_target_iso'], 0, 16) }}" class="w-full h-10 bg-black border border-white/40 px-3 text-white" />
            </div>
            <button type="submit" class="font-display bg-[#d9ff00] text-black px-5 h-10 rounded-[30px] text-xs cursor-pointer">UPDATE BANNER</button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Lookbook Slides CMS --}}
        <div class="bg-white border border-black/15 p-5">
            <h2 class="font-display text-sm mb-4">ADD LOOKBOOK SLIDE</h2>
            <form method="POST" action="{{ route('admin.cms.lookbooks.store') }}" enctype="multipart/form-data" class="flex flex-col gap-3 mb-6">
                @csrf
                <div class="grid grid-cols-2 gap-2.5">
                    <input type="text" name="collection_title" required placeholder="BLOCK PARTY 2026" class="h-10 border border-black px-3 text-xs font-mono-meta" />
                    <input type="text" name="season" required placeholder="FALL / WINTER 2026" class="h-10 border border-black px-3 text-xs font-mono-meta" />
                </div>
                <div class="grid grid-cols-[80px_1fr] gap-2.5">
                    <input type="number" name="look_number" required value="1" min="1" class="h-10 border border-black px-3 text-xs font-mono-meta" />
                    <input type="text" name="title" required placeholder="Look Title (e.g. Heavyweight Tee + Cargo)" class="h-10 border border-black px-3 text-xs" />
                </div>
                <input type="text" name="garments" required placeholder="Garments breakdown (e.g. 240 GSM Onyx Tee)" class="h-10 border border-black px-3 text-xs" />
                <input type="file" name="image" accept="image/*" class="font-mono-meta text-xs border border-black/25 p-2" />
                <button type="submit" class="font-display self-start bg-black text-white rounded-[30px] px-5 py-2.5 text-xs cursor-pointer">+ ADD LOOKBOOK SLIDE</button>
            </form>

            <div class="flex flex-col gap-2 font-mono-meta text-xs">
                @foreach($lookbooks ?? [] as $lb)
                    <div class="border border-black/10 p-2.5 flex justify-between items-center gap-2">
                        <div>
                            <div class="font-display">{{ $lb->collection_title }} — LOOK {{ $lb->look_number }}</div>
                            <div class="text-[11px] text-[#666]">{{ $lb->title }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.cms.lookbooks.delete', $lb->id) }}">
                            @csrf
                            <button type="submit" class="text-red-700 underline cursor-pointer">Delete</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Block Story CMS --}}
        <div class="bg-white border border-black/15 p-5">
            <h2 class="font-display text-sm mb-4">ADD BLOCK STORY FILM</h2>
            <form method="POST" action="{{ route('admin.cms.stories.store') }}" class="flex flex-col gap-3 mb-6">
                @csrf
                <input type="text" name="title" required placeholder="Story Title (e.g. DROP 05 STUDIO BTS)" class="h-10 border border-black px-3 text-xs" />
                <textarea name="subtitle" required rows="2" placeholder="Short description..." class="border border-black p-3 text-xs"></textarea>
                <div class="grid grid-cols-3 gap-2.5">
                    <input type="text" name="tag" required value="CAMPAIGN" class="h-10 border border-black px-3 text-xs font-mono-meta" />
                    <input type="text" name="date_label" required value="NOV 2026" class="h-10 border border-black px-3 text-xs font-mono-meta" />
                    <input type="text" name="duration" required value="02:45" class="h-10 border border-black px-3 text-xs font-mono-meta" />
                </div>
                <button type="submit" class="font-display self-start bg-black text-white rounded-[30px] px-5 py-2.5 text-xs cursor-pointer">+ PUBLISH STORY</button>
            </form>

            <div class="flex flex-col gap-2 font-mono-meta text-xs">
                @foreach($stories ?? [] as $st)
                    <div class="border border-black/10 p-2.5 flex justify-between items-center gap-2">
                        <div>
                            <div class="font-display">{{ $st->title }}</div>
                            <div class="text-[11px] text-[#666]">{{ $st->tag }} · {{ $st->date_label }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.cms.stories.delete', $st->id) }}">
                            @csrf
                            <button type="submit" class="text-red-700 underline cursor-pointer">Delete</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

{{-- =========================================================
     TAB 9: ACTIVITY AUDIT LOG
     ========================================================= --}}
@elseif($tab === 'audit')
    <div class="bg-white border border-black/15 overflow-x-auto anim-fade-up">
        <table class="w-full text-left border-collapse text-xs font-mono-meta">
            <thead class="bg-[#ededed] border-b border-black text-[11px]">
                <tr>
                    <th class="p-3">TIME</th>
                    <th class="p-3">ACTOR</th>
                    <th class="p-3">ACTION</th>
                    <th class="p-3">DETAILS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activityLogs ?? [] as $log)
                    <tr class="border-b border-black/10">
                        <td class="p-3 text-[#666]">{{ $log->created_at?->format('M d, Y H:i') }}</td>
                        <td class="p-3">{{ $log->actor_email }}</td>
                        <td class="p-3"><span class="px-2 py-0.5 bg-black text-white text-[10px]">{{ $log->action }}</span></td>
                        <td class="p-3">{{ $log->details }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-6 text-center text-[#666]">No activity logged yet. Verify a payment, restock an item, or run a bulk update to see audit entries here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;
    const analytics = @json($analytics);

    Chart.defaults.font.family = "'JetBrains Mono', monospace";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#333';

    const revCanvas = document.getElementById('chart-revenue-orders');
    if (revCanvas) {
        new Chart(revCanvas, {
            data: {
                labels: analytics.week_labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Revenue (₱)',
                        data: analytics.weekly_revenue,
                        backgroundColor: '#111111',
                        borderRadius: 2,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Orders',
                        data: analytics.weekly_orders,
                        borderColor: '#059669',
                        backgroundColor: '#059669',
                        borderWidth: 2.5,
                        pointRadius: 3,
                        tension: 0.3,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
                }
            }
        });
    }

    const catCanvas = document.getElementById('chart-category-mix');
    if (catCanvas) {
        new Chart(catCanvas, {
            type: 'doughnut',
            data: {
                labels: analytics.category_labels,
                datasets: [{
                    data: analytics.category_values,
                    backgroundColor: ['#111111', '#2563eb', '#059669', '#d97706', '#dc2626', '#6b7280', '#a3a3a3'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const smaCanvas = document.getElementById('chart-sma-demand');
    if (smaCanvas) {
        new Chart(smaCanvas, {
            type: 'line',
            data: {
                labels: analytics.week_labels,
                datasets: [
                    {
                        label: 'Actual Units Sold',
                        data: analytics.weekly_units,
                        borderColor: '#111111',
                        backgroundColor: 'rgba(0,0,0,0.06)',
                        fill: true,
                        borderWidth: 2,
                        tension: 0.25,
                    },
                    {
                        label: `EWMA Forecast (α=${analytics.alpha})`,
                        data: analytics.weekly_ewma,
                        borderColor: '#2563eb',
                        borderWidth: 2.5,
                        tension: 0.25,
                    },
                    {
                        label: `${analytics.sma_window}-Week SMA`,
                        data: analytics.weekly_sma,
                        borderColor: '#d97706',
                        borderDash: [5, 4],
                        borderWidth: 2,
                        tension: 0.25,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' } } }
            }
        });
    }

    const eoqCanvas = document.getElementById('chart-eoq-compare');
    if (eoqCanvas) {
        const top6 = analytics.product_forecasts.slice(0, 6);
        new Chart(eoqCanvas, {
            type: 'bar',
            data: {
                labels: top6.map(r => r.product.title.split(' ').slice(0, 2).join(' ')),
                datasets: [
                    {
                        label: 'Current Stock',
                        data: top6.map(r => r.current_stock),
                        backgroundColor: '#059669',
                    },
                    {
                        label: 'Reorder Point (ROP)',
                        data: top6.map(r => r.rop),
                        backgroundColor: '#dc2626',
                    },
                    {
                        label: 'Optimal EOQ',
                        data: top6.map(r => r.eoq),
                        backgroundColor: '#111111',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' } } }
            }
        });
    }
});
</script>
@endpush
