<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PromoCode;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            return response()->json([
                'ok' => true,
                'redirect' => Auth::user()->is_admin ? route('admin.dashboard') : route('account'),
            ]);
        }

        return response()->json(['error' => 'Invalid email or password.'], 422);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'ok' => true,
            'redirect' => route('account'),
        ]);
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);
        $email = strtolower(trim($data['email']));
        $code = (string) random_int(100000, 999999);

        Cache::put('kotb_otp_' . md5($email), $code, now()->addMinutes(10));

        $mailSent = false;
        if (config('mail.default') === 'smtp' && config('mail.mailers.smtp.username')) {
            try {
                Mail::raw(
                    "Your KIDS OWN THE BLOCK verification code is: {$code}\n\nThis code expires in 10 minutes.",
                    fn ($m) => $m->to($email)->subject("KOTB Login Code: {$code}")
                );
                $mailSent = true;
            } catch (\Throwable $e) {
                $mailSent = false;
            }
        }

        return response()->json([
            'ok' => true,
            'mail_sent' => $mailSent,
            // In local dev without SMTP configured, show the code so testing works immediately
            'dev_code' => (!$mailSent && app()->environment('local')) ? $code : null,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($data['email']));
        $cached = Cache::get('kotb_otp_' . md5($email));

        if (!$cached || $cached !== trim($data['code'])) {
            return response()->json(['error' => 'Invalid or expired 6-digit code.'], 422);
        }

        Cache::forget('kotb_otp_' . md5($email));

        $user = User::where('email', $email)->first();
        if (!$user) {
            $namePart = ucfirst(explode('@', $email)[0]);
            $user = User::create([
                'name' => $namePart,
                'email' => $email,
                'password' => null,
                'is_admin' => false,
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'ok' => true,
            'redirect' => $user->is_admin ? route('admin.dashboard') : route('account'),
        ]);
    }

    public function redirectToGoogle()
    {
        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            return redirect()->route('shop')->with('error', 'Google OAuth not configured—use Gmail 6-Digit Code instead.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('shop')->with('error', 'Google sign-in failed or was cancelled.');
        }

        $email = strtolower(trim($googleUser->getEmail()));
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: 'Block Member',
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => null,
                'is_admin' => false,
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect($user->is_admin ? route('admin.dashboard') : route('account'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shop');
    }

    public function account()
    {
        if (!Auth::check()) {
            return redirect()->route('shop');
        }

        $user = Auth::user();
        $orders = collect();
        $wishlistItems = collect();
        try {
            $orders = Order::where('user_id', $user->id)
                ->orWhere('customer_email', $user->email)
                ->latest()
                ->get();
            $wishlistItems = Wishlist::with('product')
                ->where('user_id', $user->id)
                ->latest()
                ->get()
                ->filter(fn ($w) => $w->product !== null);
        } catch (\Throwable $e) {
            // Graceful fallback
        }

        return view('pages.account', compact('user', 'orders', 'wishlistItems'));
    }

    public function redeemPoints(Request $request)
    {
        $user = Auth::user();
        if (!$user || ($user->points ?? 0) < 100) {
            return back()->with('error', 'You need at least 100 Block Points to redeem a 15% reward code.');
        }

        $code = 'KOTB15-' . strtoupper(Str::random(4));
        PromoCode::create([
            'code' => $code,
            'discount_percent' => 15,
            'active' => true,
        ]);

        $user->decrement('points', 100);

        return back()->with('redeemed_code', $code);
    }
}
