<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiDropshippingService
{
    public const CATEGORIES = [
        'TEES',
        'HOODIES',
        'BOTTOMS',
        'OUTERWEAR',
        'HEADWEAR',
        'WOMENS',
        'RUNNING',
        'HERITAGE PIECES',
    ];

    public const SILHOUETTES = [
        'tee',
        'hoodie',
        'jacket',
        'shorts',
        'pants',
        'cap',
        'baby-tee',
        'jersey',
    ];

    public const SUPPLIERS = [
        'tapstitch_shopify' => [
            'label' => 'Tapstitch (via Shopify Bridge)',
            'lead_time_days' => 9,
            'default_shipping' => 180.0,
            'default_cost' => 520.0,
            'notes' => 'Best for heavyweight boxy blanks, vintage acid-wash, and custom streetwear cuts synced through Shopify.',
        ],
        'printful' => [
            'label' => 'Printful POD',
            'lead_time_days' => 7,
            'default_shipping' => 160.0,
            'default_cost' => 480.0,
            'notes' => 'Direct API fulfillment with DTG printing, embroidery, and global fulfillment centers.',
        ],
        'custom_pod' => [
            'label' => 'Custom / Local POD Partner',
            'lead_time_days' => 5,
            'default_shipping' => 110.0,
            'default_cost' => 350.0,
            'notes' => 'Local silkscreen, DTF, or cut-and-sew partner using KOTB POD production sheets.',
        ],
    ];

    public function isConfigured(): bool
    {
        return trim((string) config('services.gemini.api_key')) !== '';
    }

    public function getModel(): string
    {
        return (string) config('services.gemini.model', 'gemini-2.5-flash');
    }

    /**
     * Calculate deterministic POD dropshipping pricing & unit economics in PHP (₱).
     */
    public function calculatePodEconomics(
        float $supplierCost,
        float $shippingCost,
        float $targetMarginPct = 55.0,
        float $gatewayFeePct = 3.5,
        string $supplier = 'tapstitch_shopify'
    ): array {
        $supplierCost = max(50.0, $supplierCost);
        $shippingCost = max(0.0, $shippingCost);
        $targetMarginPct = max(20.0, min(85.0, $targetMarginPct));

        $landedCost = round($supplierCost + $shippingCost, 2);
        $feeRate = $gatewayFeePct / 100.0;
        $marginRate = $targetMarginPct / 100.0;

        // Solve: retail * (1 - feeRate - marginRate) = landedCost
        $divisor = max(0.10, 1.0 - $feeRate - $marginRate);
        $rawRetail = $landedCost / $divisor;

        // Round to clean streetwear price ending in 90 (e.g. 1290, 1490, 1890)
        $roundedHundreds = ceil($rawRetail / 100) * 100;
        $recommendedRetail = max($landedCost + 100, $roundedHundreds - 10);
        if ($recommendedRetail < $rawRetail) {
            $recommendedRetail += 100;
        }

        $gatewayFee = round($recommendedRetail * $feeRate, 2);
        $netProfit = round($recommendedRetail - $landedCost - $gatewayFee, 2);
        $actualMarginPct = $recommendedRetail > 0
            ? round(($netProfit / $recommendedRetail) * 100, 1)
            : 0.0;
        $breakEvenRoas = $netProfit > 0
            ? round($recommendedRetail / $netProfit, 2)
            : 0.0;

        $supplierMeta = self::SUPPLIERS[$supplier] ?? self::SUPPLIERS['tapstitch_shopify'];

        return [
            'supplier' => $supplier,
            'supplier_label' => $supplierMeta['label'],
            'supplier_cost' => round($supplierCost, 2),
            'shipping_cost' => round($shippingCost, 2),
            'landed_cost' => $landedCost,
            'gateway_fee_pct' => $gatewayFeePct,
            'gateway_fee' => $gatewayFee,
            'recommended_price' => round($recommendedRetail, 2),
            'net_profit' => $netProfit,
            'actual_margin_pct' => $actualMarginPct,
            'break_even_roas' => $breakEvenRoas,
            'lead_time_days' => $supplierMeta['lead_time_days'],
        ];
    }

    /**
     * Generate a complete POD streetwear product listing using Google Gemini API,
     * with automatic fallback if GEMINI_API_KEY is not yet configured.
     */
    public function generateProductBrief(array $input): array
    {
        $idea = trim((string) ($input['idea'] ?? ''));
        $category = in_array(strtoupper((string) ($input['category'] ?? '')), self::CATEGORIES, true)
            ? strtoupper((string) $input['category'])
            : 'TEES';
        $supplier = array_key_exists((string) ($input['supplier'] ?? ''), self::SUPPLIERS)
            ? (string) $input['supplier']
            : 'tapstitch_shopify';
        $tone = trim((string) ($input['tone'] ?? 'Streetwear Editorial'));
        $supplierMeta = self::SUPPLIERS[$supplier];

        $supplierCost = (float) ($input['supplier_cost'] ?? $supplierMeta['default_cost']);
        $shippingCost = (float) ($input['shipping_cost'] ?? $supplierMeta['default_shipping']);
        $targetMargin = (float) ($input['target_margin'] ?? 55);

        $economics = $this->calculatePodEconomics($supplierCost, $shippingCost, $targetMargin, 3.5, $supplier);

        if (!$this->isConfigured()) {
            return [
                'ok' => true,
                'mode' => 'demo_fallback',
                'notice' => 'GEMINI_API_KEY is not set in .env yet. Showing instant demo generation with live POD margin math — add your Gemini API key in .env to enable live AI generation.',
                'product' => $this->buildFallbackListing($idea, $category, $supplier, $tone, $economics),
                'economics' => $economics,
            ];
        }

        $apiKey = trim((string) config('services.gemini.api_key'));
        $model = $this->getModel();
        $baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $endpoint = "{$baseUrl}/models/{$model}:generateContent?key={$apiKey}";

        $prompt = $this->buildPrompt($idea, $category, $supplierMeta['label'], $tone, $economics);

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.75,
                        'responseMimeType' => 'application/json',
                    ],
                ]);

            if (!$response->successful()) {
                $apiError = $response->json('error.message') ?? ('HTTP ' . $response->status());
                Log::warning('Gemini API error in AiDropshippingService', [
                    'status' => $response->status(),
                    'error' => $apiError,
                ]);

                return [
                    'ok' => true,
                    'mode' => 'demo_fallback',
                    'notice' => "Gemini API returned an error ({$apiError}). Showing smart fallback output instead.",
                    'product' => $this->buildFallbackListing($idea, $category, $supplier, $tone, $economics),
                    'economics' => $economics,
                ];
            }

            $rawText = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
            $parsed = $this->decodeJsonPayload($rawText);

            if (!is_array($parsed) || empty($parsed['title'])) {
                return [
                    'ok' => true,
                    'mode' => 'demo_fallback',
                    'notice' => 'Gemini returned an unexpected format. Showing smart fallback output instead.',
                    'product' => $this->buildFallbackListing($idea, $category, $supplier, $tone, $economics),
                    'economics' => $economics,
                ];
            }

            return [
                'ok' => true,
                'mode' => 'gemini_live',
                'model' => $model,
                'notice' => "Generated live with Google Gemini ({$model}).",
                'product' => $this->normalizeProductPayload($parsed, $category, $economics),
                'economics' => $economics,
            ];
        } catch (\Throwable $e) {
            Log::error('Gemini API exception in AiDropshippingService', ['message' => $e->getMessage()]);

            return [
                'ok' => true,
                'mode' => 'demo_fallback',
                'notice' => 'Could not reach Gemini API (' . $e->getMessage() . '). Showing smart fallback output instead.',
                'product' => $this->buildFallbackListing($idea, $category, $supplier, $tone, $economics),
                'economics' => $economics,
            ];
        }
    }

    private function buildPrompt(string $idea, string $category, string $supplierLabel, string $tone, array $economics): string
    {
        $categoriesList = implode(', ', self::CATEGORIES);
        $silhouettesList = implode(', ', self::SILHOUETTES);

        return <<<PROMPT
You are the Creative Director and Dropshipping Strategist for "KIDS OWN THE BLOCK" (KOTB), an independent streetwear & run-club clothing label based in the Philippines shipping via Print-on-Demand ({$supplierLabel}).

Create a high-converting POD streetwear product listing based on this concept:
- Concept / Supplier Blank Idea: {$idea}
- Preferred Category: {$category}
- Brand Voice / Tone: {$tone}
- Fulfillment Partner: {$supplierLabel}
- Landed POD Cost: PHP {$economics['landed_cost']}
- Recommended Retail Price: PHP {$economics['recommended_price']} (Target Net Profit: PHP {$economics['net_profit']}, Margin: {$economics['actual_margin_pct']}%)

Return ONLY a valid JSON object matching this exact schema (no markdown, no extra text):
{
  "title": "UPPERCASE streetwear garment name (3-6 words, e.g. 'CONCRETE RUNNER HEAVYWEIGHT TEE')",
  "category": "one of: {$categoriesList}",
  "silhouette": "one of: {$silhouettesList}",
  "base_color": "#hex 6-char garment fabric color",
  "accent_color": "#hex 6-char accent color",
  "ink_color": "#hex 6-char print ink color with high contrast against base_color",
  "motif": "short 1-2 word bold front graphic text (max 18 chars, e.g. 'BLOCK RUNNER')",
  "sub_motif": "short secondary graphic tag (max 24 chars, e.g. 'MANILA / EST. 2026')",
  "bands": ["array containing 1-2 of: new, restocked, heritage"],
  "description": "2-3 punchy, editorial streetwear sentences highlighting fabric weight, silhouette, graphic story, and daily wearability.",
  "specs": [
    "5 to 6 concise bullet specs covering fabric GSM, fit, print method (DTG/Puff/Embroidery), construction, and care"
  ],
  "seo": {
    "meta_title": "SEO title under 60 chars including KOTB",
    "meta_description": "Compelling meta description under 155 chars",
    "tags": ["6-8 lowercase shopify/seo tags"]
  },
  "marketing": {
    "ig_caption": "Short streetwear Instagram drop caption with 3-4 relevant hashtags",
    "tiktok_hook": "1-sentence scroll-stopping TikTok/Reels hook for this garment",
    "ad_headline": "Short paid ad headline under 45 chars"
  },
  "pod_print_notes": "2 concise sentences of practical POD production advice for {$supplierLabel} (print placement, blank recommendation, ink contrast)."
}
PROMPT;
    }

    private function decodeJsonPayload(string $rawText): ?array
    {
        $clean = trim($rawText);
        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
            $clean = preg_replace('/\s*```$/', '', $clean);
        }

        $decoded = json_decode(trim($clean), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function normalizeProductPayload(array $data, string $fallbackCategory, array $economics): array
    {
        $category = strtoupper(trim((string) ($data['category'] ?? $fallbackCategory)));
        if (!in_array($category, self::CATEGORIES, true)) {
            $category = $fallbackCategory;
        }

        $silhouette = strtolower(trim((string) ($data['silhouette'] ?? $this->defaultSilhouetteForCategory($category))));
        if (!in_array($silhouette, self::SILHOUETTES, true)) {
            $silhouette = $this->defaultSilhouetteForCategory($category);
        }

        $bands = collect($data['bands'] ?? ['new'])
            ->map(fn ($b) => strtolower(trim((string) $b)))
            ->filter(fn ($b) => in_array($b, ['new', 'restocked', 'heritage'], true))
            ->values()
            ->all();

        $specs = collect($data['specs'] ?? [])
            ->map(fn ($s) => trim((string) $s))
            ->filter()
            ->take(8)
            ->values()
            ->all();

        return [
            'title' => strtoupper(Str::limit(trim((string) ($data['title'] ?? 'KOTB DROP GARMENT')), 120, '')),
            'category' => $category,
            'silhouette' => $silhouette,
            'price' => $economics['recommended_price'],
            'unit_cost' => $economics['landed_cost'],
            'lead_time_days' => $economics['lead_time_days'],
            'base_color' => $this->sanitizeHex((string) ($data['base_color'] ?? '#111111'), '#111111'),
            'accent_color' => $this->sanitizeHex((string) ($data['accent_color'] ?? '#d9ff00'), '#d9ff00'),
            'ink_color' => $this->sanitizeHex((string) ($data['ink_color'] ?? '#f5f5f5'), '#f5f5f5'),
            'motif' => strtoupper(Str::limit(trim((string) ($data['motif'] ?? 'KOTB')), 36, '')),
            'sub_motif' => strtoupper(Str::limit(trim((string) ($data['sub_motif'] ?? 'BLOCK DROP 2026')), 36, '')),
            'bands' => $bands ?: ['new'],
            'description' => trim((string) ($data['description'] ?? 'Heavyweight streetwear silhouette engineered for everyday movement.')),
            'specs' => $specs ?: ['280 GSM Combed Heavyweight Cotton', 'Boxy Drop-Shoulder Street Fit', 'High-Density DTG Graphic Print'],
            'seo' => [
                'meta_title' => trim((string) data_get($data, 'seo.meta_title', ($data['title'] ?? 'KOTB Drop') . ' | Kids Own The Block')),
                'meta_description' => trim((string) data_get($data, 'seo.meta_description', Str::limit((string) ($data['description'] ?? ''), 150))),
                'tags' => collect(data_get($data, 'seo.tags', ['streetwear', 'kotb', strtolower($category), 'pod']))
                    ->map(fn ($t) => strtolower(trim((string) $t)))
                    ->filter()
                    ->values()
                    ->all(),
            ],
            'marketing' => [
                'ig_caption' => trim((string) data_get($data, 'marketing.ig_caption', '')),
                'tiktok_hook' => trim((string) data_get($data, 'marketing.tiktok_hook', '')),
                'ad_headline' => trim((string) data_get($data, 'marketing.ad_headline', '')),
            ],
            'pod_print_notes' => trim((string) ($data['pod_print_notes'] ?? '')),
        ];
    }

    private function buildFallbackListing(string $idea, string $category, string $supplier, string $tone, array $economics): array
    {
        $cleanIdea = $idea !== '' ? $idea : 'Heavyweight Concrete Streetwear Drop';
        $words = collect(preg_split('/[^a-zA-Z0-9]+/', $cleanIdea))
            ->filter(fn ($w) => strlen($w) >= 3)
            ->take(3)
            ->map(fn ($w) => strtoupper($w))
            ->values();

        $silhouette = $this->defaultSilhouetteForCategory($category);
        $garmentNoun = match ($silhouette) {
            'hoodie' => 'HEAVYWEIGHT HOODIE',
            'jacket' => 'UTILITY JACKET',
            'shorts' => 'MESH SHORTS',
            'pants' => 'WIDE TRACK PANTS',
            'cap' => 'STRUCTURED CAP',
            'baby-tee' => 'CROPPED BABY TEE',
            'jersey' => 'RUN CLUB JERSEY',
            default => 'BOXY GRAPHIC TEE',
        };

        $prefix = $words->isNotEmpty() ? $words->implode(' ') : 'BLOCK ARCHIVE';
        $title = Str::limit(trim("{$prefix} {$garmentNoun}"), 60, '');
        $motif = $words->take(2)->implode(' ') ?: 'KOTB';
        $supplierLabel = self::SUPPLIERS[$supplier]['label'] ?? 'POD Partner';

        $palettes = [
            'TEES' => ['#111111', '#d9ff00', '#f5f5f5'],
            'HOODIES' => ['#1c1c1e', '#e5e5e5', '#ffffff'],
            'OUTERWEAR' => ['#222520', '#d9ff00', '#f0f0ea'],
            'BOTTOMS' => ['#18181b', '#a1a1aa', '#ffffff'],
            'HEADWEAR' => ['#0f172a', '#d9ff00', '#f8fafc'],
            'WOMENS' => ['#f4f1ea', '#111111', '#111111'],
            'RUNNING' => ['#101820', '#d9ff00', '#ffffff'],
            'HERITAGE PIECES' => ['#272320', '#e2d9c8', '#f5f0e6'],
        ];
        [$base, $accent, $ink] = $palettes[$category] ?? ['#111111', '#d9ff00', '#f5f5f5'];

        $gsm = in_array($category, ['HOODIES', 'OUTERWEAR'], true) ? '420 GSM French Terry' : '260 GSM Combed Heavyweight Cotton';

        return [
            'title' => $title,
            'category' => $category,
            'silhouette' => $silhouette,
            'price' => $economics['recommended_price'],
            'unit_cost' => $economics['landed_cost'],
            'lead_time_days' => $economics['lead_time_days'],
            'base_color' => $base,
            'accent_color' => $accent,
            'ink_color' => $ink,
            'motif' => Str::limit($motif, 24, ''),
            'sub_motif' => 'KOTB // DROP 2026',
            'bands' => ['new'],
            'description' => "Built for the concrete. The {$title} takes inspiration from \"{$cleanIdea}\" with a {$tone} edge, cut in a relaxed boxy block silhouette and finished with high-density archival graphics. Made to order via {$supplierLabel} for zero deadstock waste.",
            'specs' => [
                $gsm,
                'Relaxed Boxy Drop-Shoulder Street Fit',
                'High-Definition DTG / Puff-Ready Graphic Print',
                'Double-Needle Ribbed Collar & Reinforced Seams',
                'Pre-Shrunk & Garment-Washed Handfeel',
                'Made-to-Order POD Fulfillment (Cold Wash Inside Out)',
            ],
            'seo' => [
                'meta_title' => Str::limit("{$title} | KIDS OWN THE BLOCK", 60, ''),
                'meta_description' => Str::limit("Shop the {$title} from Kids Own The Block. {$gsm}, boxy streetwear fit, made to order.", 155, ''),
                'tags' => ['kotb', 'kids-own-the-block', 'streetwear', strtolower($category), 'boxy-fit', 'pod-drop'],
            ],
            'marketing' => [
                'ig_caption' => "{$title} just landed on the block. Heavyweight fabric, zero compromises, printed to order. Tap the link in bio before the drop locks. #KidsOwnTheBlock #StreetwearPH #BoxyFit",
                'tiktok_hook' => "POV: You finally found a {$gsm} streetwear piece that holds its boxy silhouette after 50 washes.",
                'ad_headline' => Str::limit("NEW DROP: {$title} — ₱" . number_format($economics['recommended_price'], 0), 45, ''),
            ],
            'pod_print_notes' => "Use 300 DPI transparent PNG artwork centered 2.5\" below the collar for {$supplierLabel}. High-contrast {$ink} ink on {$base} fabric ensures crisp DTG saturation without heavy underbase cracking.",
        ];
    }

    private function defaultSilhouetteForCategory(string $category): string
    {
        return match ($category) {
            'HOODIES' => 'hoodie',
            'OUTERWEAR' => 'jacket',
            'BOTTOMS' => 'pants',
            'HEADWEAR' => 'cap',
            'WOMENS' => 'baby-tee',
            'RUNNING' => 'jersey',
            default => 'tee',
        };
    }

    private function sanitizeHex(string $value, string $fallback): string
    {
        $v = trim($value);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $v) ? strtolower($v) : $fallback;
    }
}
