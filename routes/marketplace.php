<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketplace\HomeController;
use App\Http\Controllers\Marketplace\CartController;
use App\Http\Controllers\Marketplace\CheckoutController;
use App\Http\Controllers\Marketplace\OrderController;
use App\Http\Controllers\Marketplace\SellerDashboardController;
use App\Http\Controllers\Marketplace\SellerProductController;
use App\Http\Controllers\Marketplace\SellerOrderController;
use App\Http\Controllers\Marketplace\SellerShopController;

/*
|--------------------------------------------------------------------------
| Marketplace Routes
|--------------------------------------------------------------------------
|
| MyGrowNet Marketplace - Trust-first community marketplace
| with escrow payments and seller verification
|
*/

// Public routes (no auth required)
Route::prefix('growmarket')->name('marketplace.')->middleware('marketplace.data')->group(function () {
    
    // Home & Browse
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/search', [HomeController::class, 'search'])->name('search');
    Route::get('/category/{slug}', [HomeController::class, 'category'])->name('category');
    Route::get('/product/{slug}', [HomeController::class, 'product'])->name('product');
    Route::get('/shop/{id}', [HomeController::class, 'seller'])->name('seller.show')->whereNumber('id');
    
    // Static Pages
    Route::get('/help', [HomeController::class, 'helpCenter'])->name('help');
    Route::get('/buyer-protection', [HomeController::class, 'buyerProtection'])->name('buyer-protection');
    Route::get('/seller-guide', [HomeController::class, 'sellerGuide'])->name('seller-guide');
    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
    Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
    
    // Cart (session-based, works without auth)
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
});

// Authenticated buyer routes
Route::middleware(['auth', 'verified', 'marketplace.data'])
    ->prefix('growmarket')
    ->name('marketplace.')
    ->group(function () {
        
        // Checkout
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/orders/{id}/payment', [CheckoutController::class, 'payment'])->name('orders.payment');
        Route::post('/orders/{id}/payment', [CheckoutController::class, 'confirmPayment'])->name('orders.confirm-payment');
        
        // Buyer Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{id}/dispute', [OrderController::class, 'dispute'])->name('orders.dispute');
        
        // Reviews
        Route::post('/reviews', [\App\Http\Controllers\Marketplace\ReviewController::class, 'store'])->name('reviews.store');
        Route::post('/reviews/{id}/vote', [\App\Http\Controllers\Marketplace\ReviewController::class, 'vote'])->name('reviews.vote');
        
        // Seller Shop Interactions
        Route::post('/shop/{sellerId}/follow', [SellerShopController::class, 'follow'])->name('shop.follow');
        Route::delete('/shop/{sellerId}/unfollow', [SellerShopController::class, 'unfollow'])->name('shop.unfollow');
    });

// Public seller landing page (no auth required)
Route::prefix('growmarket/seller')->name('marketplace.seller.')->middleware('marketplace.data')->group(function () {
    Route::get('/join', [SellerDashboardController::class, 'join'])->name('join');
});

// Seller routes (auth required)
Route::middleware(['auth', 'verified', 'marketplace.data'])
    ->prefix('growmarket/seller')
    ->name('marketplace.seller.')
    ->group(function () {
        
        // Registration (for authenticated users who are NOT yet sellers)
        Route::get('/register', [SellerDashboardController::class, 'register'])->name('register');
        Route::post('/register', [SellerDashboardController::class, 'store'])->name('store');
    });

// Seller dashboard routes (requires registered seller)
Route::middleware(['auth', 'verified', 'marketplace.data', 'marketplace.seller'])
    ->prefix('growmarket/seller')
    ->name('marketplace.seller.')
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
        
        // Profile
        Route::get('/profile', [SellerDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [SellerDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/upload-logo', [SellerDashboardController::class, 'uploadLogo'])->name('profile.upload-logo');
        Route::post('/profile/upload-cover', [SellerDashboardController::class, 'uploadCover'])->name('profile.upload-cover');
        
        // Media Library
        Route::get('/media', [\App\Http\Controllers\Marketplace\SellerMediaController::class, 'index'])->name('media.index');
        Route::post('/media', [\App\Http\Controllers\Marketplace\SellerMediaController::class, 'store'])->name('media.store');
        Route::post('/media/base64', [\App\Http\Controllers\Marketplace\SellerMediaController::class, 'storeBase64'])->name('media.store-base64');
        Route::delete('/media/{mediaId}', [\App\Http\Controllers\Marketplace\SellerMediaController::class, 'destroy'])->name('media.destroy');
        
        // Products
        Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [SellerProductController::class, 'create'])->name('products.create');
        Route::post('/products', [SellerProductController::class, 'store'])->name('products.store');
        Route::get('/products/{id}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
        Route::match(['put', 'post'], '/products/{id}', [SellerProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}', [SellerProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/{id}/appeal', [SellerProductController::class, 'appeal'])->name('products.appeal');
        
        // Payouts
        Route::get('/payouts', [\App\Http\Controllers\Marketplace\SellerPayoutController::class, 'index'])->name('payouts.index');
        Route::get('/payouts/request', [\App\Http\Controllers\Marketplace\SellerPayoutController::class, 'create'])->name('payouts.create');
        Route::post('/payouts', [\App\Http\Controllers\Marketplace\SellerPayoutController::class, 'store'])->name('payouts.store');
        Route::get('/payouts/{id}', [\App\Http\Controllers\Marketplace\SellerPayoutController::class, 'show'])->name('payouts.show');
        
        // Orders
        Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [SellerOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/ship', [SellerOrderController::class, 'ship'])->name('orders.ship');
        Route::post('/orders/{id}/deliver', [SellerOrderController::class, 'deliver'])->name('orders.deliver');
        Route::post('/orders/{id}/cancel', [SellerOrderController::class, 'cancel'])->name('orders.cancel');
    });
