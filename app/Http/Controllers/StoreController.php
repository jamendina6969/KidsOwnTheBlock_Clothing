<?php

namespace App\Http\Controllers;

use App\Models\Lookbook;
use App\Models\Product;
use App\Models\Story;
use App\Models\Wishlist;
use Database\Seeders\ProductSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StoreController extends Controller
{
    private function getProducts(): Collection
    {
        try {
            $rows = Product::all();
            if ($rows->isNotEmpty()) {
                return $rows;
            }
        } catch (\Throwable $e) {
            // Fallback if MySQL is not yet started/migrated
        }

        return collect(ProductSeeder::defaultProducts())->map(function ($item, $idx) {
            $obj = new Product($item);
            $obj->id = $idx + 1;
            return $obj;
        });
    }

    public function splash()
    {
        return view('pages.splash');
    }

    private function checkVipGate()
    {
        $vipFile = storage_path('app/vip_lock.json');
        if (file_exists($vipFile)) {
            $vip = json_decode(file_get_contents($vipFile), true);
            if (!empty($vip['enabled']) && !session('vip_unlocked') && !(auth()->check() && auth()->user()->is_admin)) {
                return view('pages.vip-gate');
            }
        }
        return null;
    }

    public function shop(Request $request)
    {
        if ($gate = $this->checkVipGate()) return $gate;

        $all = $this->getProducts();
        $category = $request->query('category', 'ALL PRODUCTS');
        $availability = $request->query('availability', 'all');
        $priceRange = $request->query('price', 'all');
        $sort = $request->query('sort', 'featured');
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 8;

        $filtered = $all->when($category !== 'ALL PRODUCTS', fn ($c) => $c->where('category', $category))
            ->when($availability === 'in-stock', fn ($c) => $c->where('sold_out', false))
            ->when($availability === 'sold-out', fn ($c) => $c->where('sold_out', true))
            ->when($priceRange === 'under-1000', fn ($c) => $c->filter(fn ($p) => $p->price < 1000))
            ->when($priceRange === '1000-2500', fn ($c) => $c->filter(fn ($p) => $p->price >= 1000 && $p->price <= 2500))
            ->when($priceRange === '2500-plus', fn ($c) => $c->filter(fn ($p) => $p->price > 2500));

        $filtered = match ($sort) {
            'best-selling' => $filtered->sortBy('best_seller_rank'),
            'az' => $filtered->sortBy('title'),
            'price-asc' => $filtered->sortBy('price'),
            'price-desc' => $filtered->sortByDesc('price'),
            default => $filtered,
        };

        $filtered = $filtered->values();
        $totalCount = $filtered->count();
        $totalPages = max(1, (int) ceil($totalCount / $perPage));
        $safePage = min($page, $totalPages);
        $pageItems = $filtered->slice(($safePage - 1) * $perPage, $perPage)->values();

        $bannerFile = storage_path('app/store_banner.json');
        $storeBanner = file_exists($bannerFile)
            ? json_decode(file_get_contents($bannerFile), true)
            : [
                'marquee_text' => 'DROP 04 LIVE · 240 GSM HEAVYWEIGHT · KIDS OWN THE BLOCK · WORLDWIDE DISPATCH ·',
                'drop_target_iso' => now()->addDays(2)->addHours(14)->toIso8601String(),
            ];

        return view('pages.shop', [
            'newCollection' => $all->filter(fn ($p) => in_array('new', $p->bands ?? []))->values(),
            'restocked' => $all->filter(fn ($p) => in_array('restocked', $p->bands ?? []))->values(),
            'heritage' => $all->filter(fn ($p) => in_array('heritage', $p->bands ?? []))->values(),
            'products' => $pageItems,
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
            'currentPage' => $safePage,
            'category' => $category,
            'availability' => $availability,
            'priceRange' => $priceRange,
            'sort' => $sort,
            'storeBanner' => $storeBanner,
        ]);
    }

    public function product(string $slug)
    {
        if ($gate = $this->checkVipGate()) return $gate;

        $all = $this->getProducts()->values();
        $idx = $all->search(fn ($p) => $p->slug === $slug);
        if ($idx === false) {
            $idx = 0;
        }
        $product = $all[$idx];
        $count = $all->count();
        $prevProduct = $all[($idx - 1 + $count) % $count];
        $nextProduct = $all[($idx + 1) % $count];

        $inWishlist = false;
        if (auth()->check()) {
            try {
                $inWishlist = Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists();
            } catch (\Throwable $e) {
                $inWishlist = false;
            }
        }

        $completeTheFit = $all->where('slug', '!=', $product->slug)
            ->where('sold_out', false)
            ->take(4)
            ->values();

        $reviews = collect();
        try {
            $reviews = \App\Models\ProductReview::where('product_id', $product->id)->latest()->get();
        } catch (\Throwable $e) {
            // Fallback
        }

        return view('pages.product', compact('product', 'prevProduct', 'nextProduct', 'inWishlist', 'completeTheFit', 'reviews'));
    }

    public function search(Request $request)
    {
        if ($this->checkVipGate()) {
            return response()->json(['results' => []]);
        }

        $q = strtolower(trim((string) $request->query('q', '')));
        $all = $this->getProducts();

        $matches = $q === ''
            ? $all->take(4)
            : $all->filter(fn ($p) =>
                str_contains(strtolower($p->title), $q) ||
                str_contains(strtolower($p->category), $q) ||
                str_contains(strtolower($p->motif), $q)
            )->take(4);

        return response()->json([
            'results' => $matches->map(fn ($p) => [
                'slug' => $p->slug,
                'title' => $p->title,
                'price' => number_format((float) $p->price, 2),
                'category' => $p->category,
                'url' => route('product.show', ['slug' => $p->slug]),
                'svg' => view('components.garment-svg', ['product' => $p, 'view' => 'front'])->render(),
            ])->values(),
        ]);
    }

    public function lookbooks()
    {
        try {
            $rows = Lookbook::orderBy('look_number')->get();
            if ($rows->isNotEmpty()) {
                $collections = $rows->groupBy('collection_title')->map(function ($group, $title) {
                    return [
                        'id' => \Illuminate\Support\Str::slug($title),
                        'title' => $title,
                        'season' => $group->first()->season,
                        'slides' => $group->map(fn ($s) => [
                            'look' => $s->look_number,
                            'title' => $s->title,
                            'garments' => $s->garments,
                            'image_path' => $s->image_path ? asset($s->image_path) : null,
                            'bg' => $s->bg_color,
                            'drape' => $s->drape_color,
                            'sil' => $s->sil_color,
                            'acc' => $s->acc_color,
                        ])->values()->toArray(),
                    ];
                })->values()->toArray();

                return view('pages.lookbooks', [
                    'collections' => $collections,
                    'lookProducts' => $this->getProducts()->take(4)->values(),
                ]);
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        $lookProducts = $this->getProducts()->take(4)->values();

        $collections = [
            [
                'id' => 'block-party-2026',
                'title' => 'BLOCK PARTY COLLECTION 2026',
                'season' => 'FALL / WINTER 2026',
                'slides' => [
                    ['look' => 1, 'title' => 'KOTB Block Heavyweight Tee + Midnight Utility Shorts', 'garments' => '240 GSM Washed Onyx Tee / Ripstop Cargo', 'bg' => '#6e0e0e', 'drape' => '#3d0404', 'sil' => '#0d0d0d', 'acc' => '#ffffff'],
                    ['look' => 2, 'title' => 'Concrete Youth Zip Hoodie + Stone Wash Baggy Denim', 'garments' => '460 GSM Charcoal Fleece / 14oz Skate Denim', 'bg' => '#161b26', 'drape' => '#090c12', 'sil' => '#222428', 'acc' => '#d9ff00'],
                    ['look' => 3, 'title' => 'Two-Block Cropped Baby Tee + Cobalt Coach Jacket', 'garments' => 'Bone Rib Knit / Water-Resistant Taslan', 'bg' => '#751010', 'drape' => '#2b0505', 'sil' => '#162742', 'acc' => '#f4f2ed'],
                    ['look' => 4, 'title' => 'Night Shift Work Jacket + Archive 6-Panel Cap', 'garments' => '14oz Bull Denim Canvas / Brushed Twill', 'bg' => '#181818', 'drape' => '#070707', 'sil' => '#262626', 'acc' => '#c9a96e'],
                    ['look' => 5, 'title' => 'Block Run Club Mesh Jersey + Subterranean Track Pants', 'garments' => '165 GSM Micro-Mesh / Crinkle Nylon', 'bg' => '#111927', 'drape' => '#05080f', 'sil' => '#0e0e0e', 'acc' => '#ffffff'],
                ],
            ],
            [
                'id' => 'concrete-youth-2025',
                'title' => 'CONCRETE YOUTH ARCHIVE 2025',
                'season' => 'SPRING / SUMMER 2025',
                'slides' => [
                    ['look' => 1, 'title' => 'Heritage 001 Box Hoodie + Utility Shorts', 'garments' => '480 GSM Heather Stone / 310 GSM Ripstop', 'bg' => '#2c2c2c', 'drape' => '#111111', 'sil' => '#cfccc4', 'acc' => '#111111'],
                    ['look' => 2, 'title' => 'Crimson Curtain Graphic Tee + Baggy Denim', 'garments' => '230 GSM Off-White Jersey / 14oz Denim', 'bg' => '#5a0c0c', 'drape' => '#240303', 'sil' => '#eceae4', 'acc' => '#a81818'],
                    ['look' => 3, 'title' => 'Full Monochrome Block Uniform', 'garments' => 'Onyx Heavyweight Tee / Archive 6-Panel', 'bg' => '#0a0a0a', 'drape' => '#000000', 'sil' => '#1a1a1a', 'acc' => '#ffffff'],
                ],
            ],
        ];

        return view('pages.lookbooks', compact('collections', 'lookProducts'));
    }

    public function archive()
    {
        $all = $this->getProducts();
        $drops = [
            ['drop' => 'DROP 04', 'date' => 'NOV 2026', 'title' => 'BLOCK PARTY COLLECTION', 'note' => '240 GSM combed cotton tees, 460 GSM charcoal zip hoodies & utility cargo.', 'items' => $all->take(4)->values()],
            ['drop' => 'DROP 03', 'date' => 'AUG 2026', 'title' => 'NIGHT SHIFT WORKWEAR', 'note' => '14oz bull denim canvas work jackets, baggy skate denim & studio coach jackets.', 'items' => $all->slice(4, 4)->values()],
            ['drop' => 'DROP 02', 'date' => 'MAY 2026', 'title' => 'RUN CLUB DIVISION', 'note' => '165 GSM micro-mesh running singlets & crinkle nylon subterranean track pants.', 'items' => $all->slice(8, 4)->values()],
        ];

        return view('pages.archive', compact('drops'));
    }

    public function story()
    {
        try {
            $rows = Story::latest()->get();
            if ($rows->isNotEmpty()) {
                $videos = $rows->map(fn ($s) => [
                    'id' => 'st-' . $s->id,
                    'title' => $s->title,
                    'subtitle' => $s->subtitle,
                    'duration' => $s->duration,
                    'date' => $s->date_label,
                    'tag' => $s->tag,
                    'video_url' => $s->video_url,
                ])->toArray();

                return view('pages.story', compact('videos'));
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        $videos = [
            ['id' => 'st-1', 'title' => 'BLOCK PARTY 2026 — CAMPAIGN FILM', 'subtitle' => 'Shot on 16mm & digital across downtown rooftops and late-night basketball courts.', 'duration' => '02:48', 'date' => 'NOV 2026', 'tag' => 'CAMPAIGN'],
            ['id' => 'st-2', 'title' => 'BEHIND THE PRINT: 240 GSM SILKSCREEN', 'subtitle' => 'Inside our studio print room testing high-density plastisol inks for Drop 04.', 'duration' => '04:12', 'date' => 'OCT 2026', 'tag' => 'STUDIO BTS'],
            ['id' => 'st-3', 'title' => 'KOTB RUN CLUB: NIGHT 5K SESSION', 'subtitle' => '140 runners, one loop through the city core. Post-run espresso & tees.', 'duration' => '01:55', 'date' => 'OCT 2026', 'tag' => 'COMMUNITY'],
            ['id' => 'st-4', 'title' => 'DESIGNING THE NIGHT SHIFT JACKET', 'subtitle' => 'Pattern drafting, enzyme washes, and chain-stitch embroidery breakdown.', 'duration' => '03:20', 'date' => 'SEP 2026', 'tag' => 'ARCHIVE'],
        ];

        return view('pages.story', compact('videos'));
    }

    public function runClub()
    {
        $locations = [
            ['id' => 'downtown', 'city' => 'DOWNTOWN CORE', 'district' => 'DISTRICT 01', 'distance' => '5.2 KM', 'pace' => '5:30 / KM', 'nextDate' => 'THU · NOV 26 · 7:30 PM', 'meetupSpot' => 'KOTB Flagship Steps, 4th & Mercer', 'x' => 46, 'y' => 42],
            ['id' => 'westside', 'city' => 'WESTSIDE PIER', 'district' => 'HARBOR LOOP', 'distance' => '7.0 KM', 'pace' => '5:15 / KM', 'nextDate' => 'SUN · NOV 29 · 7:00 AM', 'meetupSpot' => 'Pier 14 Concrete Ramp', 'x' => 24, 'y' => 58],
            ['id' => 'eastblock', 'city' => 'EAST BLOCK', 'district' => 'INDUSTRIAL', 'distance' => '6.0 KM', 'pace' => '5:45 / KM', 'nextDate' => 'TUE · DEC 01 · 8:00 PM', 'meetupSpot' => 'Old Rail Yard Mural Wall', 'x' => 74, 'y' => 36],
            ['id' => 'northpark', 'city' => 'NORTH TRACK', 'district' => 'STADIUM', 'distance' => '400M INTERVALS', 'pace' => 'OPEN PACE', 'nextDate' => 'THU · DEC 03 · 7:30 PM', 'meetupSpot' => 'North Oval Gate B', 'x' => 58, 'y' => 20],
        ];

        return view('pages.run-club', compact('locations'));
    }

    public function help()
    {
        $faqs = [
            ['id' => 'faq-1', 'topic' => 'Orders', 'question' => 'Can I modify or cancel my order after checkout?', 'answer' => 'Orders can be modified or cancelled within 2 hours of placement before our warehouse begins packing. Email support with your order number.'],
            ['id' => 'faq-2', 'topic' => 'Shipping', 'question' => 'How long does domestic and international shipping take?', 'answer' => 'Domestic orders dispatch within 1–2 business days and arrive in 2–4 days. International express shipping takes 5–9 business days with full tracking.'],
            ['id' => 'faq-3', 'topic' => 'Shipping', 'question' => 'Do you offer Cash on Delivery (COD)?', 'answer' => 'Yes, Cash on Delivery is available for eligible metro areas at checkout with zero extra handling fees.'],
            ['id' => 'faq-4', 'topic' => 'Sizing', 'question' => 'How do your heavyweight tees and hoodies fit?', 'answer' => 'Our tees and hoodies feature a boxy, slightly cropped street fit with dropped shoulders. Take your true size for a relaxed drape or size down for a closer fit.'],
            ['id' => 'faq-5', 'topic' => 'Returns', 'question' => 'What is your return and size exchange policy?', 'answer' => 'Unworn items with original tags may be returned or exchanged within 14 days of delivery. Heritage and limited drops are eligible for store credit or size swap while stock lasts.'],
            ['id' => 'faq-6', 'topic' => 'Orders', 'question' => 'When will sold-out pieces be restocked?', 'answer' => 'Core heavyweight tees and caps restock monthly. Numbered Heritage pieces are single-run only. Subscribe to the newsletter for early drop access.'],
        ];

        return view('pages.help', compact('faqs'));
    }

    public function logoStudio()
    {
        return view('pages.logo-studio');
    }

    public function applyLogo(Request $request)
    {
        $svg = (string) $request->input('svg', '');
        if (str_contains($svg, '<svg')) {
            $dir = public_path('images');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($dir . '/active-logo.svg', $svg);
        }

        return response()->json(['ok' => true]);
    }

    public function collections()
    {
        if ($gate = $this->checkVipGate()) return $gate;

        $all = $this->getProducts();
        $categories = [
            'ALL PRODUCTS',
            'TEES',
            'HOODIES',
            'BOTTOMS',
            'OUTERWEAR',
            'HEADWEAR',
            'WOMENS',
            'RUNNING',
            'HERITAGE PIECES',
        ];

        $collections = collect($categories)->map(function ($cat) use ($all) {
            $items = $cat === 'ALL PRODUCTS' ? $all : $all->where('category', $cat)->values();
            return [
                'name' => $cat,
                'count' => $items->count(),
                'preview' => $items->first() ?? $all->first(),
            ];
        });

        return view('pages.collections', compact('collections'));
    }

    public function trackOrder(Request $request)
    {
        $orderNumber = strtoupper(trim((string) $request->query('order_number', '')));
        $email = strtolower(trim((string) $request->query('email', '')));
        $order = null;
        $searched = ($orderNumber !== '' && $email !== '');

        if ($searched) {
            $order = \App\Models\Order::where('order_number', $orderNumber)
                ->whereRaw('LOWER(customer_email) = ?', [$email])
                ->first();
        }

        return view('pages.track-order', compact('order', 'orderNumber', 'email', 'searched'));
    }

    public function vipUnlock(Request $request)
    {
        $vipFile = storage_path('app/vip_lock.json');
        $vip = file_exists($vipFile) ? json_decode(file_get_contents($vipFile), true) : ['passcode' => 'BLOCK2026'];
        $pass = strtoupper(trim((string) $request->input('passcode', '')));

        if ($pass === strtoupper($vip['passcode'] ?? 'BLOCK2026')) {
            session(['vip_unlocked' => true]);
            return redirect()->route('shop');
        }

        return back()->with('error', 'Invalid VIP drop passcode.');
    }
}
