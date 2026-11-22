<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ForecastingService
{
    public function buildDashboardAnalytics(
        Collection $products,
        Collection $orders,
        int $smaWindow = 3,
        float $alpha = 0.35,
        int $serviceLevel = 95,
        float $orderingCostOverride = 500.0
    ): array {
        $zScore = match ($serviceLevel) {
            99 => 2.33,
            90 => 1.28,
            default => 1.65, // 95% service level
        };

        $weeks = [];
        for ($i = 7; $i >= 0; $i--) {
            $start = Carbon::now()->subWeeks($i)->startOfWeek();
            $end = (clone $start)->endOfWeek();
            $label = $start->format('M d');
            $weeks[$label] = [
                'label' => $label,
                'start' => $start,
                'end' => $end,
                'revenue' => 0.0,
                'orders' => 0,
                'units' => 0,
            ];
        }

        $categorySales = [];
        $productWeeklyUnits = [];
        $productRevenue = [];
        $productUnits = [];

        foreach ($products as $p) {
            $productWeeklyUnits[$p->slug] = array_fill(0, 8, 0);
            $productRevenue[$p->slug] = 0.0;
            $productUnits[$p->slug] = 0;
        }

        $weekKeys = array_keys($weeks);

        foreach ($orders as $order) {
            $created = $order->created_at ? Carbon::parse($order->created_at) : Carbon::now();
            $orderTotal = (float) ($order->total ?: $order->subtotal);

            foreach ($weeks as $idx => $w) {
                if ($created->betweenIncluded($w['start'], $w['end'])) {
                    $weeks[$idx]['revenue'] += $orderTotal;
                    $weeks[$idx]['orders'] += 1;

                    $weekIndex = array_search($idx, $weekKeys, true);
                    foreach ($order->items ?? [] as $item) {
                        $qty = (int) ($item['quantity'] ?? 1);
                        $lineRev = $qty * (float) ($item['price'] ?? 0);
                        $weeks[$idx]['units'] += $qty;
                        $slug = $item['slug'] ?? '';
                        if (isset($productWeeklyUnits[$slug]) && $weekIndex !== false) {
                            $productWeeklyUnits[$slug][$weekIndex] += $qty;
                            $productRevenue[$slug] += $lineRev;
                            $productUnits[$slug] += $qty;
                        }
                        $cat = $this->resolveCategory($products, $slug);
                        $categorySales[$cat] = ($categorySales[$cat] ?? 0) + $lineRev;
                    }
                    break;
                }
            }
        }

        $weekValues = array_values($weeks);
        $weeklyRevenue = array_map(fn ($w) => round($w['revenue'], 2), $weekValues);
        $weeklyOrders = array_map(fn ($w) => $w['orders'], $weekValues);
        $totalUnitsSeries = array_map(fn ($w) => $w['units'], $weekValues);

        // Week-over-Week Growth Deltas (Week 8 vs Week 7)
        $revDelta = $this->percentDelta($weeklyRevenue[6] ?? 0, $weeklyRevenue[7] ?? 0);
        $ordDelta = $this->percentDelta($weeklyOrders[6] ?? 0, $weeklyOrders[7] ?? 0);
        $unitDelta = $this->percentDelta($totalUnitsSeries[6] ?? 0, $totalUnitsSeries[7] ?? 0);

        $smaSeries = $this->computeSmaSeries($totalUnitsSeries, $smaWindow);
        $ewmaSeries = $this->computeEwmaSeries($totalUnitsSeries, $alpha);

        // ABC Revenue Ranking
        $totalCatalogRev = max(1.0, array_sum($productRevenue));
        $sortedRevs = $productRevenue;
        arsort($sortedRevs);
        $abcMap = [];
        $cumShare = 0.0;
        foreach ($sortedRevs as $slug => $rev) {
            $cumShare += ($rev / $totalCatalogRev) * 100.0;
            $abcMap[$slug] = $cumShare <= 80.0 ? 'A' : ($cumShare <= 95.0 ? 'B' : 'C');
        }

        // Per-product EWMA + SMA + Z-Score Safety Stock + EOQ + ABC-XYZ
        $productForecasts = $products->map(function (Product $p) use (
            $productWeeklyUnits,
            $productRevenue,
            $productUnits,
            $smaWindow,
            $alpha,
            $zScore,
            $orderingCostOverride,
            $abcMap
        ) {
            $history = $productWeeklyUnits[$p->slug] ?? array_fill(0, 8, 0);
            $recentSlice = array_slice($history, -$smaWindow);
            $weeklySma = count($recentSlice) > 0 ? array_sum($recentSlice) / count($recentSlice) : 0.0;

            $ewmaSeriesProd = $this->computeEwmaSeries($history, $alpha);
            $weeklyEwma = end($ewmaSeriesProd) ?: 0.0;

            // Standard deviation & Coefficient of Variation (XYZ)
            $mean = array_sum($history) / max(1, count($history));
            $variance = array_sum(array_map(fn ($x) => pow($x - $mean, 2), $history)) / max(1, count($history));
            $stdDevWeekly = sqrt($variance);
            $cv = $mean > 0 ? $stdDevWeekly / $mean : 1.0;
            $xyzClass = $cv < 0.35 ? 'X' : ($cv < 0.70 ? 'Y' : 'Z');
            $abcClass = $abcMap[$p->slug] ?? 'C';

            // Forecasted demand uses EWMA (with baseline floor for newly added SKUs)
            $effectiveWeeklyDemand = max($weeklyEwma, $weeklySma, $p->sold_out ? 1.5 : 2.0);
            $annualDemand = $effectiveWeeklyDemand * 52.0;

            $orderingCost = max(1.0, $orderingCostOverride);
            $unitCost = max(1.0, (float) ($p->unit_cost ?? ($p->price * 0.42)));
            $holdingRate = max(0.05, (float) ($p->holding_cost_rate ?? 0.20));
            $holdingCostPerUnit = $unitCost * $holdingRate;

            // Wilson EOQ = sqrt((2 * D * S) / H)
            $eoq = (int) ceil(sqrt((2.0 * $annualDemand * $orderingCost) / $holdingCostPerUnit));

            // Probabilistic Safety Stock = Z * sigma_daily * sqrt(LeadTime)
            $dailyDemand = $effectiveWeeklyDemand / 7.0;
            $dailyStdDev = max(0.3, $stdDevWeekly / sqrt(7.0));
            $leadTime = max(1, (int) ($p->lead_time_days ?? 7));
            $safetyStock = (int) ceil($zScore * $dailyStdDev * sqrt($leadTime));
            $rop = (int) ceil(($dailyDemand * $leadTime) + $safetyStock);

            $currentStock = $p->sold_out ? 0 : (int) ($p->stock_qty ?? 24);
            $status = $currentStock <= $rop ? 'REORDER NOW' : ($currentStock <= (int) ceil($rop * 1.35) ? 'WATCH' : 'HEALTHY');

            return [
                'product' => $p,
                'history' => $history,
                'weekly_sma' => round($weeklySma, 2),
                'weekly_ewma' => round($weeklyEwma, 2),
                'annual_demand' => (int) round($annualDemand),
                'safety_stock' => $safetyStock,
                'eoq' => $eoq,
                'rop' => $rop,
                'current_stock' => $currentStock,
                'revenue' => round($productRevenue[$p->slug] ?? 0, 2),
                'units_sold' => $productUnits[$p->slug] ?? 0,
                'abc_xyz' => $abcClass . $xyzClass,
                'abc' => $abcClass,
                'xyz' => $xyzClass,
                'status' => $status,
            ];
        })->sortBy(fn ($row) => match ($row['status']) {
            'REORDER NOW' => 0,
            'WATCH' => 1,
            default => 2,
        })->values();

        $topMovers = $productForecasts->sortByDesc('revenue')->take(5)->values();

        return [
            'week_labels' => $weekKeys,
            'weekly_revenue' => $weeklyRevenue,
            'weekly_orders' => $weeklyOrders,
            'weekly_units' => $totalUnitsSeries,
            'weekly_sma' => $smaSeries,
            'weekly_ewma' => $ewmaSeries,
            'rev_delta' => $revDelta,
            'ord_delta' => $ordDelta,
            'unit_delta' => $unitDelta,
            'category_labels' => array_keys($categorySales),
            'category_values' => array_map(fn ($v) => round($v, 2), array_values($categorySales)),
            'product_forecasts' => $productForecasts,
            'top_movers' => $topMovers,
            'reorder_count' => $productForecasts->where('status', 'REORDER NOW')->count(),
            'watch_count' => $productForecasts->where('status', 'WATCH')->count(),
            'healthy_count' => $productForecasts->where('status', 'HEALTHY')->count(),
            'sma_window' => $smaWindow,
            'alpha' => $alpha,
            'service_level' => $serviceLevel,
            'z_score' => $zScore,
            'ordering_cost' => $orderingCostOverride,
        ];
    }

    private function computeSmaSeries(array $series, int $window): array
    {
        $result = [];
        $count = count($series);
        for ($i = 0; $i < $count; $i++) {
            $start = max(0, $i - $window + 1);
            $slice = array_slice($series, $start, $i - $start + 1);
            $result[] = round(array_sum($slice) / max(1, count($slice)), 2);
        }
        return $result;
    }

    private function computeEwmaSeries(array $series, float $alpha): array
    {
        if (empty($series)) {
            return [];
        }
        $result = [(float) $series[0]];
        for ($i = 1; $i < count($series); $i++) {
            $prev = $result[$i - 1];
            $result[] = round(($alpha * $series[$i]) + ((1.0 - $alpha) * $prev), 2);
        }
        return $result;
    }

    private function percentDelta(float $prev, float $curr): float
    {
        if ($prev <= 0) {
            return $curr > 0 ? 100.0 : 0.0;
        }
        return round((($curr - $prev) / $prev) * 100.0, 1);
    }

    private function resolveCategory(Collection $products, string $slug): string
    {
        $match = $products->firstWhere('slug', $slug);
        return $match ? $match->category : 'OTHER';
    }
}
