<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketplace\HomeController;
use App\Http\Controllers\Marketplace\CartController;
use App\Http\Controllers\Marketplace\CheckoutController;
use App\Http\Controllers\Marketplace\OrderController;
use App\Http\Controllers\Marketplace\SellerDashboardController;
use App\Http\Controllers\Marketplace\SellerProductController;
use App\Http\Controllers\Marketplace\SellerOrderController;

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
Route::prefix('marketplace')->name('marketplace.')->middleware('marketplace.data')->group(function () {
    
    // Home & Browse
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/search', [HomeController::class, 'search'])->name('search');
    Route::get('/category/{slug}', [HomeController::class, 'category'])->name('category');
    Route::get('/product/{slug}', [HomeController::class, 'product'])->name('product');
    Route::get('/seller/{id}', [HomeController::class, 'seller'])->name('seller.show');
    
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
    ->prefix('marketplace')
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
    });

// Seller routes
Route::middleware(['auth', 'verified', 'marketplace.data'])
    ->prefix('marketplace/seller')
    ->name('marketplace.seller.')
    ->group(function () {
        
        // Registration
        Route::get('/register', [SellerDashboardController::class, 'register'])->name('register');
        Route::post('/register', [SellerDashboardController::class, 'store'])->name('store');
        
        // Dashboard
        Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
        
        // Profile
        Route::get('/profile', [SellerDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [SellerDashboardController::class, 'updateProfile'])->name('profile.update');
        
        // Products
        Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [SellerProductController::class, 'create'])->name('products.create');
        Route::post('/products', [SellerProductController::class, 'store'])->name('products.store');
        Route::get('/products/{id}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{id}', [SellerProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}', [SellerProductController::class, 'destroy'])->name('products.destroy');
        
        // Orders
        Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [SellerOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/ship', [SellerOrderController::class, 'ship'])->name('orders.ship');
        Route::post('/orders/{id}/deliver', [SellerOrderController::class, 'deliver'])->name('orders.deliver');
        Route::post('/orders/{id}/cancel', [SellerOrderController::class, 'cancel'])->name('orders.cancel');
    });
