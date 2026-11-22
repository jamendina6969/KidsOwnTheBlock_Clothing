<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        // Seed sample promo codes
        foreach ([
            ['code' => 'BLOCK10', 'discount_percent' => 10, 'active' => true, 'uses_count' => 14],
            ['code' => 'VIP20', 'discount_percent' => 20, 'active' => true, 'uses_count' => 6],
            ['code' => 'RUNCLUB15', 'discount_percent' => 15, 'active' => false, 'uses_count' => 23],
        ] as $promo) {
            PromoCode::updateOrCreate(['code' => $promo['code']], $promo);
        }

        // Assign realistic PHP stock quantities & unit costs across catalog
        $stockOverrides = [
            'kotb-block-heavyweight-tee' => ['stock_qty' => 8, 'unit_cost' => 380.00, 'lead_time_days' => 7],
            'concrete-youth-zip-hoodie' => ['stock_qty' => 11, 'unit_cost' => 980.00, 'lead_time_days' => 10],
            'midnight-block-utility-shorts' => ['stock_qty' => 28, 'unit_cost' => 580.00, 'lead_time_days' => 7],
            'two-block-cropped-baby-tee' => ['stock_qty' => 34, 'unit_cost' => 340.00, 'lead_time_days' => 5],
            'night-shift-work-jacket' => ['stock_qty' => 0, 'unit_cost' => 1450.00, 'lead_time_days' => 14],
            'block-run-club-mesh-jersey' => ['stock_qty' => 42, 'unit_cost' => 490.00, 'lead_time_days' => 6],
            'stone-wash-baggy-denim' => ['stock_qty' => 14, 'unit_cost' => 1150.00, 'lead_time_days' => 12],
            'archive-6-panel-block-cap' => ['stock_qty' => 9, 'unit_cost' => 260.00, 'lead_time_days' => 5],
            'crimson-curtain-graphic-tee' => ['stock_qty' => 22, 'unit_cost' => 390.00, 'lead_time_days' => 7],
            'heritage-001-box-hoodie' => ['stock_qty' => 19, 'unit_cost' => 950.00, 'lead_time_days' => 10],
            'subterranean-track-pants' => ['stock_qty' => 0, 'unit_cost' => 760.00, 'lead_time_days' => 9],
            'cobalt-studio-coach-jacket' => ['stock_qty' => 16, 'unit_cost' => 1250.00, 'lead_time_days' => 10],
        ];

        foreach ($stockOverrides as $slug => $vals) {
            Product::where('slug', $slug)->update($vals);
        }

        // Seed 8 weeks of historical orders if none exist yet
        if (Order::count() > 0) {
            return;
        }

        $products = Product::all();
        if ($products->isEmpty()) {
            return;
        }

        $weeklyOrderCounts = [3, 4, 5, 4, 6, 7, 8, 9];
        $statuses = ['delivered', 'delivered', 'delivered', 'shipped', 'packed', 'confirmed'];
        $seq = 1001;

        foreach ($weeklyOrderCounts as $idx => $count) {
            $weeksAgo = 7 - $idx;
            for ($j = 0; $j < $count; $j++) {
                $date = Carbon::now()->subWeeks($weeksAgo)->startOfWeek()->addDays($j % 6)->setHour(14 + ($j % 6));
                $picked = $products->random(min(2, $products->count()));
                $items = [];
                $subtotal = 0;

                foreach ($picked as $prod) {
                    $qty = ($prod->best_seller_rank <= 4) ? random_int(1, 3) : 1;
                    $items[] = [
                        'slug' => $prod->slug,
                        'title' => $prod->title,
                        'price' => (float) $prod->price,
                        'size' => ['M', 'L', 'XL'][random_int(0, 2)],
                        'quantity' => $qty,
                    ];
                    $subtotal += $prod->price * $qty;
                }

                $shipping = $subtotal >= 3500 ? 0.0 : 150.0;
                $order = Order::create([
                    'order_number' => 'KOTB-2026-' . $seq++,
                    'customer_name' => 'Block Member #' . ($seq - 1000),
                    'customer_email' => 'member' . ($seq - 1000) . '@kotb.club',
                    'phone' => '+1 555 01' . random_int(10, 99),
                    'shipping_address' => random_int(100, 999) . ' Mercer St',
                    'city' => ['New York', 'Los Angeles', 'Chicago', 'Manila'][random_int(0, 3)],
                    'payment_method' => $j % 3 === 0 ? 'cod' : 'card',
                    'subtotal' => $subtotal,
                    'shipping_fee' => $shipping,
                    'total' => $subtotal + $shipping,
                    'items' => $items,
                    'status' => $weeksAgo > 1 ? 'delivered' : $statuses[random_int(3, 5)],
                ]);
                $order->timestamps = false;
                $order->created_at = $date;
                $order->updated_at = $date;
                $order->save();
            }
        }
    }
}
