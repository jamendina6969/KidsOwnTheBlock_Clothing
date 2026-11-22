<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use Database\Seeders\ProductSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CartController extends Controller
{
    private function findProduct(string $slug): ?Product
    {
        try {
            $p = Product::where('slug', $slug)->first();
            if ($p) {
                return $p;
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        foreach (ProductSeeder::defaultProducts() as $idx => $item) {
            if ($item['slug'] === $slug) {
                $obj = new Product($item);
                $obj->id = $idx + 1;
                return $obj;
            }
        }

        return null;
    }

    public function add(Request $request)
    {
        $slug = (string) $request->input('slug');
        $size = (string) $request->input('size', 'L');
        $qty = max(1, (int) $request->input('quantity', 1));

        $product = $this->findProduct($slug);
        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        if ($product->sold_out) {
            return response()->json(['error' => 'This garment is sold out.'], 422);
        }

        $sizeEntry = collect($product->sizes)->firstWhere('size', $size);
        if (!$sizeEntry || empty($sizeEntry['inStock'])) {
            return response()->json(['error' => "Size {$size} is currently sold out."], 422);
        }

        $cart = session('cart', []);
        $key = $slug . '__' . $size;
        $existingQty = isset($cart[$key]) ? (int) $cart[$key]['quantity'] : 0;
        $maxSize = (int) ($sizeEntry['qty'] ?? ($product->stock_qty ?? 99));
        $maxTotal = (int) ($product->stock_qty ?? 99);
        $cap = min($maxSize, $maxTotal);

        if (($existingQty + $qty) > $cap) {
            return response()->json([
                'error' => "Only {$cap} left in stock for Size {$size}.",
            ], 422);
        }

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $qty;
        } else {
            $cart[$key] = [
                'key' => $key,
                'slug' => $slug,
                'title' => $product->title,
                'price' => (float) $product->price,
                'size' => $size,
                'quantity' => $qty,
                'silhouette' => $product->silhouette,
                'base_color' => $product->base_color,
                'accent_color' => $product->accent_color,
                'ink_color' => $product->ink_color,
                'motif' => $product->motif,
                'sub_motif' => $product->sub_motif,
                'front_image' => $product->front_image,
                'thumb_html' => view('components.garment-svg', ['product' => $product, 'view' => 'front'])->render(),
            ];
        }

        session(['cart' => $cart]);

        return $this->cartResponse($cart);
    }

    public function update(Request $request)
    {
        $key = (string) $request->input('key');
        $delta = (int) $request->input('delta', 0);
        $cart = session('cart', []);

        if (isset($cart[$key])) {
            $newQty = $cart[$key]['quantity'] + $delta;
            if ($delta > 0) {
                $product = $this->findProduct($cart[$key]['slug']);
                if ($product) {
                    $sizeObj = collect($product->sizes ?? [])->firstWhere('size', $cart[$key]['size']);
                    $maxSize = (int) ($sizeObj['qty'] ?? ($product->stock_qty ?? 99));
                    $maxTotal = (int) ($product->stock_qty ?? 99);
                    $cap = min($maxSize, $maxTotal);
                    if ($newQty > $cap) {
                        $newQty = max(1, $cap);
                    }
                }
            }
            $cart[$key]['quantity'] = $newQty;
            if ($cart[$key]['quantity'] <= 0) {
                unset($cart[$key]);
            }
            session(['cart' => $cart]);
        }

        return $this->cartResponse($cart);
    }

    public function remove(Request $request)
    {
        $key = (string) $request->input('key');
        $cart = session('cart', []);
        unset($cart[$key]);
        session(['cart' => $cart]);

        return $this->cartResponse($cart);
    }

    public function applyPromo(Request $request)
    {
        $code = strtoupper(trim((string) $request->input('code', '')));
        if ($code === '') {
            session()->forget('promo_code');
            return response()->json(['ok' => true, 'discount_percent' => 0]);
        }

        $promo = PromoCode::where('code', $code)->where('active', true)->first();
        if (!$promo) {
            return response()->json(['error' => 'Invalid or expired promo code.'], 422);
        }

        session(['promo_code' => $promo->code]);

        return response()->json([
            'ok' => true,
            'code' => $promo->code,
            'discount_percent' => $promo->discount_percent,
        ]);
    }

    public function checkoutPage()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop');
        }

        $subtotal = collect($cart)->sum(fn ($i) => $i['price'] * $i['quantity']);
        $promoCode = session('promo_code');
        $discountPercent = 0;
        if ($promoCode) {
            $promo = PromoCode::where('code', $promoCode)->where('active', true)->first();
            if ($promo) {
                $discountPercent = $promo->discount_percent;
            } else {
                session()->forget('promo_code');
                $promoCode = null;
            }
        }

        $discountAmount = round($subtotal * ($discountPercent / 100), 2);
        $country = strtoupper(trim((string) ($data['country'] ?? 'PH')));
        $isIntl = $country !== 'PH';
        $fulfillmentProvider = $isIntl ? 'tapstitch' : 'local_ph';

        $afterDiscount = max(0, $subtotal - $discountAmount);
        $shippingFee = $isIntl ? 950.00 : ($afterDiscount >= 3500 ? 0.00 : 150.00);
        $total = $afterDiscount + $shippingFee;

        return view('pages.checkout', compact('cart', 'subtotal', 'promoCode', 'discountPercent', 'discountAmount', 'shippingFee', 'total'));
    }

    public function checkout(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop');
        }

        $data = $request->validate([
            'customer_name' => 'required|string|max:120',
            'customer_email' => 'required|email',
            'phone' => 'required|string|max:40',
            'shipping_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'nullable|string|max:10',
            'payment_method' => 'required|in:gcash,bank,cod,paymongo',
            'payment_reference' => 'nullable|string|max:100',
            'payment_proof' => 'nullable|image|max:4096',
            'notes' => 'nullable|string|max:500',
        ]);

        if (in_array($data['payment_method'], ['gcash', 'bank'])) {
            $request->validate([
                'payment_reference' => 'required|string|min:4|max:100',
                'payment_proof' => 'required|image|max:4096',
            ], [
                'payment_reference.required' => 'Please enter your GCash / Bank transaction reference number.',
                'payment_proof.required' => 'Please upload your payment receipt screenshot.',
            ]);
        }

        $subtotal = collect($cart)->sum(fn ($i) => $i['price'] * $i['quantity']);
        $promoCode = session('promo_code');
        $discountAmount = 0.0;
        $promoModel = null;

        if ($promoCode) {
            $promoModel = PromoCode::where('code', $promoCode)->where('active', true)->first();
            if ($promoModel) {
                $discountAmount = round($subtotal * ($promoModel->discount_percent / 100), 2);
            }
        }

        $country = strtoupper(trim((string) ($data['country'] ?? 'PH')));
        $isIntl = $country !== 'PH';
        $fulfillmentProvider = $isIntl ? 'tapstitch' : 'local_ph';

        $afterDiscount = max(0, $subtotal - $discountAmount);
        $shippingFee = $isIntl ? 950.00 : ($afterDiscount >= 3500 ? 0.00 : 150.00);
        $total = $afterDiscount + $shippingFee;
        $orderNumber = 'KOTB-' . date('Y') . '-' . random_int(1000, 9999);

        // Save proof-of-payment screenshot if uploaded
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $dir = public_path('uploads/receipts');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $file = $request->file('payment_proof');
            $filename = $orderNumber . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $proofPath = 'uploads/receipts/' . $filename;
        }

        $isManualTransfer = in_array($data['payment_method'], ['gcash', 'bank']);
        $isPayMongo = $data['payment_method'] === 'paymongo';
        $paymentStatus = $isManualTransfer ? 'pending_verification' : ($isPayMongo ? 'pending_payment' : 'unpaid_cod');
        $fulfillmentStatus = ($isManualTransfer || $isPayMongo) ? 'pending_verification' : 'confirmed';

        $createdOrder = null;
        try {
            DB::transaction(function () use (
                &$createdOrder,
                $data,
                $orderNumber,
                $paymentStatus,
                $proofPath,
                $promoModel,
                $subtotal,
                $discountAmount,
                $shippingFee,
                $total,
                $cart,
                $fulfillmentStatus,
                $country,
                $fulfillmentProvider
            ) {
                // Re-verify & decrement product stock_qty and per-size qty atomically
                foreach ($cart as $item) {
                    $product = Product::where('slug', $item['slug'])->lockForUpdate()->first();
                    if (!$product) {
                        continue;
                    }

                    $buyQty = (int) $item['quantity'];
                    $sizeObj = collect($product->sizes ?? [])->firstWhere('size', $item['size']);
                    $currSizeQty = (int) ($sizeObj['qty'] ?? (!empty($sizeObj['inStock']) ? ($product->stock_qty ?? 10) : 0));

                    if ($product->sold_out || (int) $product->stock_qty < $buyQty || empty($sizeObj['inStock']) || $currSizeQty < $buyQty) {
                        throw new \RuntimeException("Sorry, {$product->title} (Size {$item['size']}) just sold out or has insufficient stock.");
                    }

                    $newQty = max(0, (int) $product->stock_qty - $buyQty);

                    $updatedSizes = collect($product->sizes ?? [])->map(function ($s) use ($item, $buyQty) {
                        if (($s['size'] ?? '') === $item['size']) {
                            $rem = max(0, (int) ($s['qty'] ?? ($s['inStock'] ? 10 : 0)) - $buyQty);
                            $s['qty'] = $rem;
                            $s['inStock'] = $rem > 0;
                        }
                        return $s;
                    })->toArray();

                    $anySizeLeft = collect($updatedSizes)->contains(fn ($s) => !empty($s['inStock']));

                    $product->update([
                        'stock_qty' => $newQty,
                        'sizes' => $updatedSizes,
                        'sold_out' => ($newQty <= 0 || !$anySizeLeft),
                    ]);
                }

                $createdOrder = Order::create([
                    'user_id' => auth()->id(),
                    'order_number' => $orderNumber,
                    'customer_name' => $data['customer_name'],
                    'customer_email' => $data['customer_email'],
                    'phone' => $data['phone'],
                    'shipping_address' => $data['shipping_address'],
                    'city' => $data['city'],
                    'country' => $country,
                    'fulfillment_provider' => $fulfillmentProvider,
                    'notes' => $data['notes'] ?? null,
                    'payment_method' => $data['payment_method'],
                    'payment_status' => $paymentStatus,
                    'payment_reference' => $data['payment_reference'] ?? null,
                    'payment_proof' => $proofPath,
                    'promo_code' => $promoModel?->code,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'shipping_fee' => $shippingFee,
                    'total' => $total,
                    'items' => array_values($cart),
                    'status' => $fulfillmentStatus,
                ]);

                if ($promoModel) {
                    $promoModel->increment('uses_count');
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('checkout.page')->withErrors(['stock' => $e->getMessage()])->withInput();
        }

        // Send Order Confirmation Email if SMTP is configured
        try {
            if (config('mail.default') === 'smtp' && config('mail.mailers.smtp.username')) {
                Mail::raw(
                    "Thank you for your KIDS OWN THE BLOCK order!\n\nOrder #: {$orderNumber}\nTotal: ₱" . number_format($total, 2) . " PHP\nPayment: " . strtoupper($data['payment_method']) . " (" . strtoupper($paymentStatus) . ")\n\nView your receipt anytime at: " . route('order.receipt', ['order_number' => $orderNumber]),
                    fn ($m) => $m->to($data['customer_email'])->subject("KOTB Order Confirmed: {$orderNumber}")
                );
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }

        session()->forget(['cart', 'promo_code']);
        session()->push('recent_orders', $orderNumber);

        if ($isPayMongo && $createdOrder) {
            $checkoutUrl = PayMongoController::createCheckoutSession($createdOrder);
            if ($checkoutUrl) {
                return redirect()->away($checkoutUrl);
            }
            return redirect()->route('order.receipt', ['order_number' => $orderNumber])
                ->with('paymongo_notice', 'PayMongo API keys are not configured in .env yet — your order is saved as Pending Payment.');
        }

        return redirect()->route('order.receipt', ['order_number' => $orderNumber]);
    }

    public function receipt(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            return redirect()->route('shop');
        }

        $isOwner = auth()->check() && (auth()->user()->is_admin || auth()->id() === $order->user_id);
        $inSession = in_array($orderNumber, session('recent_orders', []));
        $canViewPrivate = $isOwner || $inSession;

        return view('pages.order-receipt', compact('order', 'canViewPrivate'));
    }

    private function cartResponse(array $cart)
    {
        $items = array_values($cart);
        $count = collect($items)->sum('quantity');
        $subtotal = collect($items)->sum(fn ($i) => $i['price'] * $i['quantity']);

        return response()->json([
            'items' => $items,
            'count' => $count,
            'subtotal' => number_format($subtotal, 2),
        ]);
    }
}
