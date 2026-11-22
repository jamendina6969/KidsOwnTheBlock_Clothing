<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayMongoController extends Controller
{
    public static function createCheckoutSession(Order $order): ?string
    {
        $secretKey = config('services.paymongo.secret_key');
        if (!$secretKey) {
            return null;
        }

        $subtotal = max(1.0, (float) ($order->subtotal ?: 1));
        $discount = (float) ($order->discount_amount ?? 0);
        $ratio = max(0.0, ($subtotal - $discount) / $subtotal);

        $lineItems = collect($order->items ?? [])->map(fn ($item) => [
            'currency' => 'PHP',
            'amount' => max(100, (int) round(((float) $item['price']) * $ratio * 100)),
            'name' => $item['title'] . ' (' . $item['size'] . ')' . ($discount > 0 ? ' [Promo Applied]' : ''),
            'quantity' => (int) $item['quantity'],
        ])->values()->toArray();

        if (($order->shipping_fee ?? 0) > 0) {
            $lineItems[] = [
                'currency' => 'PHP',
                'amount' => (int) round(((float) $order->shipping_fee) * 100),
                'name' => 'Shipping Fee',
                'quantity' => 1,
            ];
        }

        $response = Http::withBasicAuth($secretKey, '')
            ->post('https://api.paymongo.com/v1/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'send_email_receipt' => true,
                        'show_description' => true,
                        'show_line_items' => true,
                        'reference_number' => $order->order_number,
                        'description' => 'KIDS OWN THE BLOCK — Order ' . $order->order_number,
                        'payment_method_types' => ['gcash', 'paymaya', 'card', 'grab_pay'],
                        'line_items' => $lineItems,
                        'success_url' => route('order.receipt', ['order_number' => $order->order_number]),
                        'cancel_url' => route('checkout.page'),
                    ],
                ],
            ]);

        if ($response->successful()) {
            return $response->json('data.attributes.checkout_url');
        }

        return null;
    }

    public function webhook(Request $request)
    {
        $webhookSecret = config('services.paymongo.webhook_secret');
        if ($webhookSecret) {
            $sigHeader = (string) $request->header('Paymongo-Signature', '');
            parse_str(str_replace(',', '&', $sigHeader), $parts);
            $timestamp = $parts['t'] ?? '';
            $sig = $parts['te'] ?? ($parts['li'] ?? '');
            $expected = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $webhookSecret);
            if (!hash_equals($expected, $sig)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        $type = $request->input('data.attributes.type');
        $ref = $request->input('data.attributes.data.attributes.reference_number');

        if ($type === 'checkout_session.payment.paid' && $ref) {
            $order = Order::where('order_number', $ref)->first();
            if ($order) {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                ]);
                ActivityLog::record('PAYMONGO_PAID', "Auto-verified via PayMongo webhook for {$ref}");

                if ((config('services.shopify.token') || (config('services.shopify.client_id') && config('services.shopify.client_secret'))) && empty($order->external_pod_id)) {
                    try {
                        $res = app(\App\Services\PodFulfillmentService::class)->sendToShopify($order);
                        if ($res['ok']) {
                            ActivityLog::record('SHOPIFY_AUTO_SENT', "Auto-pushed {$ref} to Shopify (#{$res['pod_id']}) for Tapstitch");
                        }
                    } catch (\Throwable $e) {
                        // Non-blocking
                    }
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
