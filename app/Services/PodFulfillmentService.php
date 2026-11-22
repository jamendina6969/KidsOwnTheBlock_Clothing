<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

class PodFulfillmentService
{
    public function sendToPrintful(Order $order): array
    {
        $token = config('services.printful.token');
        if (!$token) {
            return [
                'ok' => false,
                'message' => 'PRINTFUL_API_TOKEN is not set in .env yet.',
            ];
        }

        $items = collect($order->items ?? [])->map(function ($item) {
            $prod = Product::where('slug', $item['slug'] ?? '')->first();
            return [
                'sync_variant_id' => $prod?->printful_variant_id ? (int) $prod->printful_variant_id : null,
                'name' => $item['title'] . ' (' . $item['size'] . ')',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'retail_price' => number_format((float) ($item['price'] ?? 0), 2, '.', ''),
            ];
        })->filter(fn ($i) => !empty($i['sync_variant_id']))->values()->toArray();

        if (empty($items)) {
            return [
                'ok' => false,
                'message' => 'Add a Printful Sync Variant ID to the ordered product(s) in Admin → Inventory → Edit first.',
            ];
        }

        $response = Http::withToken($token)
            ->post('https://api.printful.com/orders', [
                'external_id' => $order->order_number,
                'recipient' => [
                    'name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'phone' => $order->phone,
                    'address1' => $order->shipping_address,
                    'city' => $order->city,
                    'country_code' => $order->country ?: 'US',
                ],
                'items' => $items,
            ]);

        if ($response->successful()) {
            $podId = (string) $response->json('result.id');
            $podStatus = (string) $response->json('result.status', 'draft');
            $order->update([
                'fulfillment_provider' => 'printful',
                'external_pod_id' => $podId,
                'pod_status' => $podStatus,
            ]);

            return ['ok' => true, 'pod_id' => $podId, 'status' => $podStatus];
        }

        return [
            'ok' => false,
            'message' => $response->json('error.message') ?? 'Printful API rejected the order.',
        ];
    }

    /**
     * Push an order to Shopify Admin API so Tapstitch auto-fulfills it.
     */
    public function sendToShopify(Order $order): array
    {
        $domain = trim((string) config('services.shopify.domain'));
        $domain = preg_replace('#^https?://#', '', rtrim($domain, '/'));
        $token = $this->resolveShopifyAccessToken($domain);

        if (!$domain || !$token) {
            return [
                'ok' => false,
                'message' => 'SHOPIFY_STORE_DOMAIN and either SHOPIFY_ADMIN_TOKEN or SHOPIFY_CLIENT_ID/SHOPIFY_CLIENT_SECRET must be set in .env.',
            ];
        }
        $lineItems = [];

        foreach ($order->items ?? [] as $item) {
            $prod = Product::where('slug', $item['slug'] ?? '')->first();
            $ref = trim((string) ($prod?->printful_variant_id ?? ''));
            if (!$ref || !ctype_digit($ref)) {
                continue;
            }

            $variantId = $this->resolveShopifyVariantId($domain, $token, $ref, (string) ($item['size'] ?? ''));
            if ($variantId) {
                $lineItems[] = [
                    'variant_id' => $variantId,
                    'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                ];
            }
        }

        if (empty($lineItems)) {
            return [
                'ok' => false,
                'message' => 'Add the numeric Shopify Product ID (or Variant ID) to the ordered product(s) in Admin → Products first.',
            ];
        }

        $parts = preg_split('/\s+/', trim((string) $order->customer_name), 2);
        $firstName = $parts[0] ?: 'Customer';
        $lastName = $parts[1] ?? 'KOTB';

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $token,
            'Content-Type' => 'application/json',
        ])->post("https://{$domain}/admin/api/2024-10/orders.json", [
            'order' => [
                'email' => $order->customer_email,
                'financial_status' => $order->payment_status === 'paid' ? 'paid' : 'pending',
                'send_receipt' => false,
                'send_fulfillment_receipt' => false,
                'note' => "KOTB Order {$order->order_number}",
                'tags' => 'kotb-website,tapstitch-bridge',
                'shipping_address' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'address1' => $order->shipping_address,
                    'city' => $order->city,
                    'country_code' => $order->country ?: 'PH',
                    'phone' => $order->phone,
                ],
                'line_items' => $lineItems,
            ],
        ]);

        if ($response->successful()) {
            $shopifyOrderNum = (string) ($response->json('order.order_number') ?: $response->json('order.id'));
            $order->update([
                'fulfillment_provider' => 'shopify_tapstitch',
                'external_pod_id' => $shopifyOrderNum,
                'pod_status' => 'submitted',
            ]);

            return ['ok' => true, 'pod_id' => $shopifyOrderNum];
        }

        $err = $response->json('errors');
        $msg = is_array($err) ? json_encode($err) : ($err ?: 'Shopify API rejected the order.');

        return [
            'ok' => false,
            'message' => "Shopify error: {$msg}",
        ];
    }

    private function resolveShopifyVariantId(string $domain, string $token, string $ref, string $size): ?int
    {
        $prodRes = Http::withHeaders([
            'X-Shopify-Access-Token' => $token,
        ])->get("https://{$domain}/admin/api/2024-10/products/{$ref}.json");

        if ($prodRes->successful()) {
            $variants = collect($prodRes->json('product.variants') ?? []);
            if ($variants->isEmpty()) {
                return null;
            }

            $targetSize = strtoupper(trim($size));
            $match = $variants->first(function ($v) use ($targetSize) {
                if ($targetSize === '') {
                    return false;
                }
                foreach (['option1', 'option2', 'option3', 'title'] as $key) {
                    $val = strtoupper(trim((string) ($v[$key] ?? '')));
                    if ($val === $targetSize || str_contains($val, $targetSize)) {
                        return true;
                    }
                }
                return false;
            });

            $chosen = $match ?: $variants->first();
            return isset($chosen['id']) ? (int) $chosen['id'] : null;
        }

        // If ref wasn't a Product ID, treat it directly as a numeric Variant ID.
        return (int) $ref;
    }

    private function resolveShopifyAccessToken(string $domain): string
    {
        $staticToken = trim((string) config('services.shopify.token'));
        if ($staticToken !== '') {
            return $staticToken;
        }

        $clientId = trim((string) config('services.shopify.client_id'));
        $clientSecret = trim((string) config('services.shopify.client_secret'));
        if (!$domain || !$clientId || !$clientSecret) {
            return '';
        }

        $cacheKey = 'shopify_cc_token_' . md5($domain . '|' . $clientId);
        $cached = cache()->get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $res = Http::asForm()->post("https://{$domain}/admin/oauth/access_token", [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        $accessToken = trim((string) $res->json('access_token'));
        if ($res->successful() && $accessToken !== '') {
            $ttl = max(300, ((int) $res->json('expires_in', 86400)) - 300);
            cache()->put($cacheKey, $accessToken, $ttl);
            return $accessToken;
        }

        return '';
    }
}
