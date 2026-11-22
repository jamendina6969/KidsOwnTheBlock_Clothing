<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ChatMessage;
use App\Models\Lookbook;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\RestockAlert;
use App\Models\RunClubRsvp;
use App\Models\Story;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\ForecastingService;
use App\Services\PodFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard(Request $request, ForecastingService $forecasting)
    {
        $tab = $request->query('tab', 'overview');
        $smaWindow = in_array((int) $request->query('sma', 3), [2, 3, 4, 6]) ? (int) $request->query('sma', 3) : 3;
        $alpha = (float) $request->query('alpha', 0.35);
        $alpha = max(0.1, min(0.8, $alpha));
        $serviceLevel = in_array((int) $request->query('service_level', 95), [90, 95, 99]) ? (int) $request->query('service_level', 95) : 95;
        $orderingCost = max(50.0, min(5000.0, (float) $request->query('ordering_cost', 500)));

        // Filter parameters
        $q = trim((string) $request->query('q', ''));
        $catFilter = (string) $request->query('category', 'ALL');
        $stockFilter = (string) $request->query('stock_status', 'all');
        $payFilter = (string) $request->query('payment_status', 'all');
        $fulfillFilter = (string) $request->query('fulfillment_status', 'all');
        $forecastStatusFilter = (string) $request->query('forecast_status', 'all');
        $abcFilter = (string) $request->query('abc', 'all');
        $ticketStatusFilter = (string) $request->query('ticket_status', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $allProducts = Product::orderBy('best_seller_rank')->get();
        $allOrders = Order::latest()->get();

        // Date range filter for Orders & Overview
        $filteredOrders = $allOrders
            ->when($dateFrom, fn ($c) => $c->filter(fn ($o) => $o->created_at && $o->created_at->toDateString() >= $dateFrom))
            ->when($dateTo, fn ($c) => $c->filter(fn ($o) => $o->created_at && $o->created_at->toDateString() <= $dateTo));

        // Analytics computed on date-filtered orders & all products
        $analytics = $forecasting->buildDashboardAnalytics($allProducts, $filteredOrders, $smaWindow, $alpha, $serviceLevel, $orderingCost);

        // Apply Inventory filters
        $products = $allProducts
            ->when($q !== '', fn ($c) => $c->filter(fn ($p) => str_contains(strtolower($p->title . ' ' . $p->motif), strtolower($q))))
            ->when($catFilter !== 'ALL', fn ($c) => $c->where('category', $catFilter))
            ->when($stockFilter === 'in_stock', fn ($c) => $c->where('sold_out', false))
            ->when($stockFilter === 'sold_out', fn ($c) => $c->where('sold_out', true))
            ->values();

        // Apply Orders filters
        $orders = $filteredOrders
            ->when($q !== '', fn ($c) => $c->filter(fn ($o) => str_contains(strtolower($o->order_number . ' ' . $o->customer_name . ' ' . $o->customer_email), strtolower($q))))
            ->when($payFilter !== 'all', fn ($c) => $c->where('payment_status', $payFilter))
            ->when($fulfillFilter !== 'all', fn ($c) => $c->where('status', $fulfillFilter))
            ->values();

        // Apply Forecasting table filters
        $analytics['filtered_forecasts'] = $analytics['product_forecasts']
            ->when($q !== '', fn ($c) => $c->filter(fn ($r) => str_contains(strtolower($r['product']->title), strtolower($q))))
            ->when($forecastStatusFilter !== 'all', fn ($c) => $c->where('status', $forecastStatusFilter))
            ->when($abcFilter !== 'all', fn ($c) => $c->where('abc', $abcFilter))
            ->values();

        $customers = User::withCount('orders')->latest()->get()
            ->when($q !== '', fn ($c) => $c->filter(fn ($u) => str_contains(strtolower($u->name . ' ' . $u->email), strtolower($q))))
            ->values();

        $promos = PromoCode::latest()->get();
        $subscribers = NewsletterSubscriber::latest()->get();
        $rsvps = RunClubRsvp::latest()->get();
        $tickets = SupportTicket::latest()->get()
            ->when($ticketStatusFilter !== 'all', fn ($c) => $c->where('status', $ticketStatusFilter))
            ->values();
        $lookbooks = Lookbook::orderBy('look_number')->get();
        $stories = Story::latest()->get();
        $restockAlerts = RestockAlert::with('product')->latest()->get();
        $activityLogs = ActivityLog::latest()->take(50)->get();
        $chatThreads = ChatMessage::orderBy('id')->get()->groupBy('session_id');
        $unreadChatCount = ChatMessage::where('sender', 'visitor')->where('is_read', false)->count();

        $totalRevenue = $filteredOrders->sum(fn ($o) => (float) ($o->total ?: $o->subtotal));
        $avgOrderValue = $filteredOrders->count() > 0 ? $totalRevenue / $filteredOrders->count() : 0;

        $stats = [
            'products' => $allProducts->count(),
            'orders' => $filteredOrders->count(),
            'revenue' => $totalRevenue,
            'aov' => $avgOrderValue,
            'reorder_alerts' => $analytics['reorder_count'],
            'unread_chats' => $unreadChatCount,
            'customers' => User::count(),
            'subscribers' => $subscribers->count(),
        ];

        $filters = compact('q', 'catFilter', 'stockFilter', 'payFilter', 'fulfillFilter', 'forecastStatusFilter', 'abcFilter', 'ticketStatusFilter', 'dateFrom', 'dateTo');

        return view('pages.admin.dashboard', compact(
            'tab',
            'smaWindow',
            'products',
            'orders',
            'customers',
            'promos',
            'subscribers',
            'rsvps',
            'tickets',
            'lookbooks',
            'stories',
            'restockAlerts',
            'activityLogs',
            'chatThreads',
            'analytics',
            'stats',
            'filters'
        ));
    }

    public function createProduct()
    {
        return view('pages.admin.product-form', ['product' => null]);
    }

    public function storeProduct(Request $request)
    {
        $data = $this->validateProduct($request);
        $data['slug'] = Str::slug($data['title']) . '-' . random_int(10, 99);
        $data = $this->handleUploads($request, $data);

        Product::create($data);

        return redirect()->route('admin.dashboard', ['tab' => 'inventory'])->with('success', 'Product created.');
    }

    public function editProduct(int $id)
    {
        $product = Product::findOrFail($id);
        return view('pages.admin.product-form', compact('product'));
    }

    public function updateProduct(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $data = $this->validateProduct($request);
        $data = $this->handleUploads($request, $data, $product);

        $product->update($data);

        return redirect()->route('admin.dashboard', ['tab' => 'inventory'])->with('success', 'Product updated.');
    }

    public function toggleSoldOut(int $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['sold_out' => !$product->sold_out]);

        return back()->with('success', 'Stock status updated.');
    }

    public function restockEoq(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $qty = max(1, (int) $request->input('eoq_qty', 24));
        $product->update([
            'stock_qty' => ($product->stock_qty ?? 0) + $qty,
            'sold_out' => false,
        ]);

        ActivityLog::record('EOQ_RESTOCK', "Restocked {$product->title} (+{$qty} units)");

        return back()->with('success', "Restocked {$product->title} (+{$qty} EOQ units).");
    }

    public function poSlip(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $eoqQty = max(1, (int) $request->query('eoq', 24));
        return view('pages.admin.po-slip', compact('product', 'eoqQty'));
    }

    public function exportOrders()
    {
        $orders = Order::latest()->get();
        $csv = "order_number,customer_name,customer_email,city,payment_method,payment_status,status,total,created_at\n";
        foreach ($orders as $o) {
            $tot = $o->total ?: $o->subtotal;
            $csv .= "\"{$o->order_number}\",\"{$o->customer_name}\",\"{$o->customer_email}\",\"{$o->city}\",\"{$o->payment_method}\",\"{$o->payment_status}\",\"{$o->status}\",\"{$tot}\",\"{$o->created_at}\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="kotb-orders.csv"',
        ]);
    }

    public function bulkOrderStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'bulk_status' => 'required|in:confirmed,packed,shipped,delivered,cancelled',
        ]);

        $ids = $request->input('order_ids', []);
        $status = $request->input('bulk_status');
        Order::whereIn('id', $ids)->update(['status' => $status]);
        ActivityLog::record('BULK_ORDER_STATUS', "Updated " . count($ids) . " orders to {$status}");

        return back()->with('success', 'Bulk order status updated (' . count($ids) . ' orders).');
    }

    public function deleteProduct(int $id)
    {
        Product::findOrFail($id)->delete();
        return redirect()->route('admin.dashboard', ['tab' => 'inventory'])->with('success', 'Product deleted.');
    }

    public function updateOrderStatus(Request $request, int $id)
    {
        $request->validate(['status' => 'required|in:pending_verification,confirmed,packed,shipped,delivered,cancelled']);
        $order = Order::findOrFail($id);
        $newStatus = $request->input('status');

        if ($newStatus === 'cancelled' && $order->status !== 'cancelled') {
            $this->restoreOrderStock($order);
        }

        $order->update(['status' => $newStatus]);
        ActivityLog::record('ORDER_STATUS', "{$order->order_number} → {$newStatus}");

        return back()->with('success', "Order {$order->order_number} status updated.");
    }

    private function restoreOrderStock(Order $order): void
    {
        foreach ($order->items ?? [] as $item) {
            $product = Product::where('slug', $item['slug'] ?? '')->first();
            if ($product) {
                $qty = (int) ($item['quantity'] ?? 1);
                $updatedSizes = collect($product->sizes ?? [])->map(function ($s) use ($item, $qty) {
                    if (($s['size'] ?? '') === ($item['size'] ?? '')) {
                        $s['qty'] = (int) ($s['qty'] ?? 0) + $qty;
                        $s['inStock'] = true;
                    }
                    return $s;
                })->toArray();

                $product->update([
                    'stock_qty' => (int) $product->stock_qty + $qty,
                    'sizes' => $updatedSizes,
                    'sold_out' => false,
                ]);
            }
        }
    }

    public function verifyPayment(int $id)
    {
        $order = Order::findOrFail($id);
        $order->update([
            'payment_status' => 'paid',
            'status' => $order->status === 'pending_verification' ? 'confirmed' : $order->status,
        ]);

        ActivityLog::record('PAYMENT_VERIFIED', "Verified payment for {$order->order_number}");

        if ((config('services.shopify.token') || (config('services.shopify.client_id') && config('services.shopify.client_secret'))) && empty($order->external_pod_id)) {
            try {
                $res = app(PodFulfillmentService::class)->sendToShopify($order);
                if ($res['ok']) {
                    ActivityLog::record('SHOPIFY_AUTO_SENT', "Auto-pushed {$order->order_number} to Shopify (#{$res['pod_id']}) for Tapstitch");
                }
            } catch (\Throwable $e) {
                // Non-blocking
            }
        }

        if ($order->user_id) {
            $u = User::find($order->user_id);
            $pts = (int) floor((float) ($order->total ?: $order->subtotal));
            if ($u && $pts > 0) {
                $u->increment('points', $pts);
            }
        }

        try {
            if ($order->customer_email && config('mail.default') === 'smtp' && config('mail.mailers.smtp.username')) {
                Mail::raw(
                    "Good news! Your payment for Order #{$order->order_number} has been verified.\n\nWe are now preparing your garments for dispatch.\nReceipt: " . route('order.receipt', ['order_number' => $order->order_number]),
                    fn ($m) => $m->to($order->customer_email)->subject("KOTB Payment Verified: {$order->order_number}")
                );
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }

        return back()->with('success', "Payment verified for {$order->order_number}.");
    }

    public function rejectPayment(int $id)
    {
        $order = Order::findOrFail($id);
        if ($order->status !== 'cancelled') {
            $this->restoreOrderStock($order);
        }
        $order->update([
            'payment_status' => 'rejected',
            'status' => 'cancelled',
        ]);
        ActivityLog::record('PAYMENT_REJECTED', "Rejected payment & restored stock for {$order->order_number}");

        return back()->with('success', "Payment rejected, stock restored, and order {$order->order_number} cancelled.");
    }

    public function replyChat(Request $request, string $sessionId)
    {
        $data = $request->validate(['message' => 'required|string|max:500']);

        ChatMessage::where('session_id', $sessionId)
            ->where('sender', 'visitor')
            ->update(['is_read' => true]);

        ChatMessage::create([
            'session_id' => $sessionId,
            'sender' => 'admin',
            'visitor_name' => 'KOTB Staff',
            'message' => trim($data['message']),
            'is_read' => true,
        ]);

        ActivityLog::record('CHAT_REPLY', "Replied to visitor chat #{$sessionId}");

        return redirect()->route('admin.dashboard', ['tab' => 'community'])->with('success', 'Reply sent to visitor.');
    }

    public function sendToPrintful(int $id, PodFulfillmentService $pod)
    {
        $order = Order::findOrFail($id);
        $res = $pod->sendToPrintful($order);

        if (!$res['ok']) {
            return back()->with('error', $res['message']);
        }

        ActivityLog::record('PRINTFUL_SENT', "Sent {$order->order_number} to Printful (ID: {$res['pod_id']})");

        return back()->with('success', "Order {$order->order_number} sent to Printful (POD ID: {$res['pod_id']}).");
    }

    public function sendToShopify(int $id, PodFulfillmentService $pod)
    {
        $order = Order::findOrFail($id);
        $res = $pod->sendToShopify($order);

        if (!$res['ok']) {
            return back()->with('error', $res['message']);
        }

        ActivityLog::record('SHOPIFY_SENT', "Pushed {$order->order_number} to Shopify (#{$res['pod_id']}) for Tapstitch");

        return back()->with('success', "Order {$order->order_number} pushed to Shopify (#{$res['pod_id']}) for automatic Tapstitch fulfillment.");
    }

    public function markTapstitch(Request $request, int $id)
    {
        $request->validate(['tapstitch_order_id' => 'required|string|max:80']);
        $order = Order::findOrFail($id);

        $tapstitchId = trim((string) $request->input('tapstitch_order_id'));
        $order->update([
            'fulfillment_provider' => 'tapstitch',
            'external_pod_id' => $tapstitchId,
            'pod_status' => 'submitted',
        ]);

        ActivityLog::record('TAPSTITCH_SENT', "Recorded Tapstitch Order #{$tapstitchId} for {$order->order_number}");

        return back()->with('success', "Order {$order->order_number} marked as sent to Tapstitch (#{$tapstitchId}).");
    }

    public function podSheet(int $id)
    {
        $order = Order::findOrFail($id);
        $csv = "order_number,recipient_name,email,phone,address,city,country,tapstitch_sku,item,size,qty,payment_method,total_php\n";
        foreach ($order->items ?? [] as $item) {
            $tot = $order->total ?: $order->subtotal;
            $product = Product::where('slug', $item['slug'] ?? '')->first();
            $sku = $product?->printful_variant_id ?? '';
            $csv .= "\"{$order->order_number}\",\"{$order->customer_name}\",\"{$order->customer_email}\",\"{$order->phone}\",\"{$order->shipping_address}\",\"{$order->city}\",\"{$order->country}\",\"{$sku}\",\"{$item['title']}\",\"{$item['size']}\",\"{$item['quantity']}\",\"{$order->payment_method}\",\"{$tot}\"\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$order->order_number}-tapstitch-dispatch.csv\"",
        ]);
    }

    public function toggleTicket(int $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->update(['status' => $ticket->status === 'resolved' ? 'open' : 'resolved']);

        return back()->with('success', 'Support ticket updated.');
    }

    public function exportSubscribers()
    {
        $subs = NewsletterSubscriber::latest()->get();
        $csv = "email,subscribed_at\n";
        foreach ($subs as $s) {
            $csv .= "{$s->email},{$s->created_at}\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="kotb-subscribers.csv"',
        ]);
    }

    public function uploadSplashVideo(Request $request)
    {
        $request->validate(['splash_video' => 'required|mimes:mp4,webm,mov|max:51200']);
        $dir = public_path('videos');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        // Remove previous splash videos so the newly uploaded one plays
        foreach (glob($dir . '/*.{mp4,webm,mov}', GLOB_BRACE) ?: [] as $old) {
            @unlink($old);
        }
        $file = $request->file('splash_video');
        $file->move($dir, 'splash-bg.' . $file->getClientOriginalExtension());

        return back()->with('success', 'Landing page video updated!');
    }

    public function storeLookbook(Request $request)
    {
        $data = $request->validate([
            'collection_title' => 'required|string|max:120',
            'season' => 'required|string|max:60',
            'look_number' => 'required|integer|min:1',
            'title' => 'required|string|max:160',
            'garments' => 'required|string|max:200',
            'image' => 'nullable|image|max:6144',
        ]);

        $imgPath = null;
        if ($request->hasFile('image')) {
            $dir = public_path('uploads/lookbooks');
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $file = $request->file('image');
            $name = time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $name);
            $imgPath = 'uploads/lookbooks/' . $name;
        }

        Lookbook::create([
            'collection_title' => strtoupper($data['collection_title']),
            'season' => strtoupper($data['season']),
            'look_number' => $data['look_number'],
            'title' => $data['title'],
            'garments' => $data['garments'],
            'image_path' => $imgPath,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'cms'])->with('success', 'Lookbook slide added.');
    }

    public function deleteLookbook(int $id)
    {
        Lookbook::findOrFail($id)->delete();
        return back()->with('success', 'Lookbook slide deleted.');
    }

    public function storeStory(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:160',
            'subtitle' => 'required|string|max:500',
            'duration' => 'required|string|max:20',
            'date_label' => 'required|string|max:30',
            'tag' => 'required|string|max:30',
            'video_url' => 'nullable|string|max:255',
        ]);

        Story::create([
            'title' => strtoupper($data['title']),
            'subtitle' => $data['subtitle'],
            'duration' => $data['duration'],
            'date_label' => strtoupper($data['date_label']),
            'tag' => strtoupper($data['tag']),
            'video_url' => $data['video_url'] ?? null,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'cms'])->with('success', 'Block Story added.');
    }

    public function deleteStory(int $id)
    {
        Story::findOrFail($id)->delete();
        return back()->with('success', 'Block Story deleted.');
    }

    public function toggleVipLock(Request $request)
    {
        $vipFile = storage_path('app/vip_lock.json');
        $curr = file_exists($vipFile) ? json_decode(file_get_contents($vipFile), true) : ['enabled' => false, 'passcode' => 'BLOCK2026'];

        $enabled = $request->boolean('enabled');
        $passcode = strtoupper(trim((string) $request->input('passcode', $curr['passcode'] ?? 'BLOCK2026')));

        file_put_contents($vipFile, json_encode(['enabled' => $enabled, 'passcode' => $passcode ?: 'BLOCK2026']));

        return back()->with('success', $enabled ? "VIP Drop Gate ENABLED (Passcode: {$passcode})." : 'VIP Drop Gate DISABLED — Storefront is public.');
    }

    public function updateBanner(Request $request)
    {
        $data = $request->validate([
            'marquee_text' => 'required|string|max:240',
            'drop_target_iso' => 'required|string|max:40',
        ]);

        file_put_contents(storage_path('app/store_banner.json'), json_encode([
            'marquee_text' => strtoupper(trim($data['marquee_text'])),
            'drop_target_iso' => trim($data['drop_target_iso']),
        ]));

        ActivityLog::record('BANNER_UPDATED', 'Updated marquee & Drop 05 countdown');

        return back()->with('success', 'Storefront marquee & Drop countdown updated.');
    }

    public function storePromo(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:30|unique:promo_codes,code',
            'discount_percent' => 'required|integer|min:1|max:90',
        ]);

        PromoCode::create([
            'code' => strtoupper(trim($data['code'])),
            'discount_percent' => $data['discount_percent'],
            'active' => true,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'promos'])->with('success', 'Promo code created.');
    }

    public function togglePromo(int $id)
    {
        $promo = PromoCode::findOrFail($id);
        $promo->update(['active' => !$promo->active]);
        return back()->with('success', 'Promo code updated.');
    }

    public function deletePromo(int $id)
    {
        PromoCode::findOrFail($id)->delete();
        return back()->with('success', 'Promo code deleted.');
    }

    private function validateProduct(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:160',
            'price' => 'required|numeric|min:1',
            'unit_cost' => 'nullable|numeric|min:0.5',
            'stock_qty' => 'nullable|integer|min:0',
            'lead_time_days' => 'nullable|integer|min:1',
            'category' => 'required|string',
            'silhouette' => 'required|string',
            'base_color' => 'required|string|max:20',
            'accent_color' => 'required|string|max:20',
            'ink_color' => 'required|string|max:20',
            'motif' => 'required|string|max:40',
            'sub_motif' => 'required|string|max:40',
            'printful_variant_id' => 'nullable|string|max:60',
            'description' => 'required|string',
        ]);

        $inStockSizes = $request->input('sizes_in_stock', []);
        $sizeQtys = $request->input('size_qty', []);
        $sizes = collect(['S', 'M', 'L', 'XL', 'XXL'])->map(function ($s) use ($inStockSizes, $sizeQtys) {
            $qty = isset($sizeQtys[$s]) ? max(0, (int) $sizeQtys[$s]) : (in_array($s, $inStockSizes) ? 10 : 0);
            return [
                'size' => $s,
                'inStock' => $qty > 0 && (empty($sizeQtys) ? in_array($s, $inStockSizes) : true),
                'qty' => $qty,
            ];
        })->toArray();

        $specsRaw = (string) $request->input('specs_text', '');
        $specs = collect(explode("\n", $specsRaw))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->toArray();

        $validated['bands'] = $request->input('bands', ['new']);
        $validated['sold_out'] = $request->boolean('sold_out');
        $validated['stock_qty'] = !empty($sizeQtys) ? array_sum(array_map('intval', $sizeQtys)) : ($validated['stock_qty'] ?? 24);
        $validated['unit_cost'] = $validated['unit_cost'] ?? round($validated['price'] * 0.4, 2);
        $validated['lead_time_days'] = $validated['lead_time_days'] ?? 7;
        $validated['sizes'] = $sizes;
        $validated['specs'] = $specs ?: ['240 GSM Combed Cotton', 'Boxy Street Fit'];

        return $validated;
    }

    private function handleUploads(Request $request, array $data, ?Product $existing = null): array
    {
        $uploadDir = public_path('uploads/products');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach (['front_image', 'back_image', 'size_chart_image'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $field . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $data[$field] = 'uploads/products/' . $filename;
            } elseif ($existing) {
                $data[$field] = $existing->$field;
            }
        }

        return $data;
    }
}
