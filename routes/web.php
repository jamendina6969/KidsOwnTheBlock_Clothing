<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\PayMongoController;
use App\Http\Controllers\StoreController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

// Storefront Pages
Route::get('/', [StoreController::class, 'splash'])->name('splash');
Route::get('/shop', [StoreController::class, 'shop'])->name('shop');
Route::get('/collections', [StoreController::class, 'collections'])->name('collections');
Route::get('/track-order', [StoreController::class, 'trackOrder'])->name('order.track');
Route::post('/vip-unlock', [StoreController::class, 'vipUnlock'])->name('vip.unlock');
Route::get('/product/{slug}', [StoreController::class, 'product'])->name('product.show');
Route::get('/search', [StoreController::class, 'search'])->name('search');
Route::get('/lookbooks', [StoreController::class, 'lookbooks'])->name('lookbooks');
Route::get('/archive', [StoreController::class, 'archive'])->name('archive');
Route::get('/story', [StoreController::class, 'story'])->name('story');
Route::get('/run-club', [StoreController::class, 'runClub'])->name('run-club');
Route::get('/help', [StoreController::class, 'help'])->name('help');

// Cart & Full Checkout
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/checkout', [CartController::class, 'checkoutPage'])->name('checkout.page');
Route::post('/checkout/promo', [CartController::class, 'applyPromo'])->name('checkout.promo');
Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/webhooks/paymongo', [PayMongoController::class, 'webhook'])->name('webhooks.paymongo');
Route::get('/order/{order_number}', [CartController::class, 'receipt'])->name('order.receipt');

// Community & Product Engagement
Route::post('/newsletter', [CommunityController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/run-club/rsvp', [CommunityController::class, 'rsvp'])->name('runclub.rsvp');
Route::post('/help/ticket', [CommunityController::class, 'ticket'])->name('help.ticket');
Route::post('/chat/send', [CommunityController::class, 'chatSend'])->name('chat.send');
Route::get('/chat/poll', [CommunityController::class, 'chatPoll'])->name('chat.poll');
Route::post('/products/{slug}/notify', [CommunityController::class, 'notifyRestock'])->name('product.notify');
Route::post('/products/{slug}/reviews', [CommunityController::class, 'storeReview'])->name('product.review');
Route::post('/wishlist/toggle', [CommunityController::class, 'toggleWishlist'])->name('wishlist.toggle');

// Customer Auth, Passwordless Gmail OTP & Google OAuth
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/auth/otp/send', [AuthController::class, 'sendOtp'])->name('auth.otp.send');
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp'])->name('auth.otp.verify');
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::get('/account', [AuthController::class, 'account'])->name('account');
Route::post('/account/redeem-points', [AuthController::class, 'redeemPoints'])->name('account.redeem');

// Admin-Only Logo Studio
Route::middleware([AdminMiddleware::class])->group(function () {
    Route::get('/logo-studio', [StoreController::class, 'logoStudio'])->name('logo.studio');
    Route::post('/logo-studio/apply', [StoreController::class, 'applyLogo'])->name('logo.apply');
});

// Admin Dashboard
Route::middleware([AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/ai-studio', [AdminController::class, 'aiStudio'])->name('ai.studio');
    Route::post('/ai-studio/generate', [AdminController::class, 'aiGenerateProduct'])->name('ai.generate');
    Route::post('/ai-studio/publish', [AdminController::class, 'aiPublishProduct'])->name('ai.publish');
    Route::get('/products/create', [AdminController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{id}/edit', [AdminController::class, 'editProduct'])->name('products.edit');
    Route::post('/products/{id}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::post('/products/{id}/toggle-sold-out', [AdminController::class, 'toggleSoldOut'])->name('products.toggle');
    Route::post('/products/{id}/restock', [AdminController::class, 'restockEoq'])->name('products.restock');
    Route::get('/products/{id}/po-slip', [AdminController::class, 'poSlip'])->name('products.po');
    Route::post('/products/{id}/delete', [AdminController::class, 'deleteProduct'])->name('products.delete');
    Route::get('/orders/export', [AdminController::class, 'exportOrders'])->name('orders.export');
    Route::post('/orders/bulk-status', [AdminController::class, 'bulkOrderStatus'])->name('orders.bulk');
    Route::post('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.status');
    Route::post('/orders/{id}/verify-payment', [AdminController::class, 'verifyPayment'])->name('orders.verify');
    Route::post('/orders/{id}/reject-payment', [AdminController::class, 'rejectPayment'])->name('orders.reject');
    Route::post('/orders/{id}/send-printful', [AdminController::class, 'sendToPrintful'])->name('orders.printful');
    Route::post('/orders/{id}/send-shopify', [AdminController::class, 'sendToShopify'])->name('orders.shopify');
    Route::post('/orders/{id}/mark-tapstitch', [AdminController::class, 'markTapstitch'])->name('orders.tapstitch');
    Route::get('/orders/{id}/pod-sheet', [AdminController::class, 'podSheet'])->name('orders.podsheet');
    Route::post('/promos', [AdminController::class, 'storePromo'])->name('promos.store');
    Route::post('/promos/{id}/toggle', [AdminController::class, 'togglePromo'])->name('promos.toggle');
    Route::post('/promos/{id}/delete', [AdminController::class, 'deletePromo'])->name('promos.delete');
    Route::post('/tickets/{id}/toggle', [AdminController::class, 'toggleTicket'])->name('tickets.toggle');
    Route::post('/chat/{sessionId}/reply', [AdminController::class, 'replyChat'])->name('chat.reply');
    Route::get('/subscribers/export', [AdminController::class, 'exportSubscribers'])->name('subscribers.export');
    Route::post('/splash-video', [AdminController::class, 'uploadSplashVideo'])->name('splash.video');
    Route::post('/cms/lookbooks', [AdminController::class, 'storeLookbook'])->name('cms.lookbooks.store');
    Route::post('/cms/lookbooks/{id}/delete', [AdminController::class, 'deleteLookbook'])->name('cms.lookbooks.delete');
    Route::post('/cms/stories', [AdminController::class, 'storeStory'])->name('cms.stories.store');
    Route::post('/cms/stories/{id}/delete', [AdminController::class, 'deleteStory'])->name('cms.stories.delete');
    Route::post('/vip-lock', [AdminController::class, 'toggleVipLock'])->name('vip.lock');
    Route::post('/banner', [AdminController::class, 'updateBanner'])->name('banner.update');
});
