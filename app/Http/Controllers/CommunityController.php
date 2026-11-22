<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\NewsletterSubscriber;
use App\Models\Product;
use App\Models\RestockAlert;
use App\Models\RunClubRsvp;
use App\Models\SupportTicket;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = strtolower(trim((string) $request->input('email')));

        try {
            NewsletterSubscriber::firstOrCreate(['email' => $email]);
        } catch (\Throwable $e) {
            // Graceful fallback
        }

        return response()->json(['ok' => true, 'email' => $email]);
    }

    public function rsvp(Request $request)
    {
        $locationId = (string) $request->input('location_id', 'downtown');
        $city = (string) $request->input('city', 'DOWNTOWN CORE');

        try {
            RunClubRsvp::create([
                'location_id' => $locationId,
                'city' => $city,
                'email' => $request->input('email'),
            ]);
        } catch (\Throwable $e) {
            // Graceful fallback
        }

        return response()->json(['ok' => true, 'city' => $city]);
    }

    public function ticket(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'message' => 'required|string|min:3',
        ]);

        try {
            SupportTicket::create([
                'email' => (string) $request->input('email'),
                'order_number' => $request->input('order_number'),
                'topic' => (string) $request->input('topic', 'order'),
                'message' => (string) $request->input('message'),
            ]);
        } catch (\Throwable $e) {
            // Graceful fallback
        }

        return response()->json(['ok' => true]);
    }

    public function notifyRestock(Request $request, string $slug)
    {
        $data = $request->validate(['email' => 'required|email']);
        $product = Product::where('slug', $slug)->firstOrFail();

        RestockAlert::firstOrCreate([
            'product_id' => $product->id,
            'email' => strtolower(trim($data['email'])),
        ]);

        return response()->json(['ok' => true]);
    }

    public function toggleWishlist(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Please log in to save garments.'], 401);
        }

        $productId = (int) $request->input('product_id');
        $existing = Wishlist::where('user_id', auth()->id())->where('product_id', $productId)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['saved' => false]);
        }

        Wishlist::create(['user_id' => auth()->id(), 'product_id' => $productId]);
        return response()->json(['saved' => true]);
    }

    public function storeReview(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'reviewer_name' => 'required|string|max:80',
            'rating' => 'required|integer|min:1|max:5',
            'fit_feedback' => 'required|in:Runs Small,True to Size,Boxy Oversized',
            'comment' => 'required|string|min:3|max:600',
        ]);

        $verifiedBuyer = false;
        if (auth()->check()) {
            $verifiedBuyer = \App\Models\Order::where('user_id', auth()->id())
                ->get()
                ->contains(fn ($o) => collect($o->items ?? [])->contains('slug', $slug));
        }

        \App\Models\ProductReview::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'reviewer_name' => $data['reviewer_name'],
            'rating' => $data['rating'],
            'fit_feedback' => $data['fit_feedback'],
            'comment' => $data['comment'],
            'verified_buyer' => $verifiedBuyer,
        ]);

        return back()->with('review_success', 'Thank you! Your fit review has been posted.');
    }

    public function chatSend(Request $request)
    {
        $data = $request->validate(['message' => 'required|string|max:500']);
        $sessionId = $request->session()->getId();
        $visitorName = auth()->user()?->name ?? 'Guest #' . substr($sessionId, 0, 5);
        $text = trim($data['message']);

        ChatMessage::create([
            'session_id' => $sessionId,
            'sender' => 'visitor',
            'visitor_name' => $visitorName,
            'message' => $text,
            'is_read' => false,
        ]);

        // Smart KOTB Chatbot instant reply (staff can still override/follow up in /admin)
        $botReply = $this->generateBotReply($text);
        if ($botReply) {
            ChatMessage::create([
                'session_id' => $sessionId,
                'sender' => 'admin',
                'visitor_name' => 'KOTB BOT',
                'message' => $botReply,
                'is_read' => true,
            ]);
        }

        return $this->chatPoll($request);
    }

    private function generateBotReply(string $text): string
    {
        $q = strtolower($text);

        if (str_contains($q, 'size') || str_contains($q, 'fit') || str_contains($q, 'measurement')) {
            return 'Our tees & hoodies use a boxy street fit (240–460 GSM). Take your true size for a relaxed drape, or use the "What\'s my size?" calculator on any product page.';
        }
        if (str_contains($q, 'ship') || str_contains($q, 'delivery') || str_contains($q, 'fee')) {
            return 'Metro Manila & domestic shipping is ₱150.00 (2–4 business days) and FREE on orders ₱3,500+! We also support Cash on Delivery (COD).';
        }
        if (str_contains($q, 'gcash') || str_contains($q, 'maya') || str_contains($q, 'pay') || str_contains($q, 'cod')) {
            return 'We accept GCash / Maya (upload receipt), Bank Transfer, PayMongo Online (Card/E-Wallet), and Cash on Delivery (COD) at checkout.';
        }
        if (str_contains($q, 'promo') || str_contains($q, 'discount') || str_contains($q, 'code')) {
            return 'Try promo code BLOCK10 at checkout for 10% off, or earn Block Points (1 pt per ₱1 spent) to redeem 15% off in your Account!';
        }
        if (str_contains($q, 'track') || str_contains($q, 'order') || str_contains($q, 'status')) {
            return 'You can track your order anytime at /track-order using your Order # (e.g. KOTB-2026-1001) and checkout email.';
        }
        if (str_contains($q, 'run') || str_contains($q, 'club')) {
            return 'KOTB Run Club meets weekly across Downtown Core, Westside Pier, East Block, and North Track. Visit /run-club to RSVP!';
        }
        if (str_contains($q, 'restock') || str_contains($q, 'sold out')) {
            return 'Core tees restock monthly. On any Sold Out garment page, enter your email in "NOTIFY ME WHEN RESTOCKED" to get first dibs!';
        }

        return 'Got it! Our studio staff has received your message and can reply here shortly. Meanwhile, ask me about Sizing, Shipping, GCash/COD, Promo Codes, or Track Order.';
    }

    public function chatPoll(Request $request)
    {
        $sessionId = $request->session()->getId();
        $messages = ChatMessage::where('session_id', $sessionId)
            ->orderBy('id')
            ->get(['sender', 'message', 'created_at']);

        return response()->json(['messages' => $messages]);
    }
}
