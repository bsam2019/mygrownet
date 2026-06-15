<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GrowMart\Admin\DashboardController;
use App\Http\Controllers\GrowMart\Admin\CategoryController;
use App\Http\Controllers\GrowMart\Admin\ImageUploadController;
use App\Http\Controllers\GrowMart\Admin\InventoryController;
use App\Http\Controllers\GrowMart\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\GrowMart\Admin\ProductController as AdminProductController;
use App\Http\Controllers\GrowMart\Admin\WarehouseController;
use App\Http\Controllers\GrowMart\Admin\CouponController;
use App\Http\Controllers\GrowMart\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\GrowMart\HomeController;
use App\Http\Controllers\GrowMart\ProductController;
use App\Http\Controllers\GrowMart\CartController;
use App\Http\Controllers\GrowMart\CheckoutController;
use App\Http\Controllers\GrowMart\OrderController;
use App\Http\Controllers\GrowMart\ReviewController;
use App\Http\Controllers\GrowMart\WishlistController;
use App\Http\Controllers\GrowMart\PaymentController;

/*
|--------------------------------------------------------------------------
| GrowMart Routes
|--------------------------------------------------------------------------
|
| MyGrowNet Online Grocery Supermarket
| - Subdomain: growmart.mygrownet.com → routes served at root /
| - Main domain: mygrownet.com/growmart → routes served under /growmart prefix
|
*/

$registerGrowMartRoutes = function (string $customerPrefix, string $adminPrefix, string $namePrefix) {
    // Customer frontend (public)
    Route::prefix($customerPrefix)->name($namePrefix)->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{product}/reviews', [ReviewController::class, 'product'])->name('products.reviews');
        Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
        Route::get('/refund-policy', [HomeController::class, 'refund'])->name('refund');
        Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    });

    // Customer frontend (authenticated)
    Route::prefix($customerPrefix)->name($namePrefix)->middleware('auth')->group(function () {
        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
        Route::put('/cart/update', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
        Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::post('/checkout/validate-coupon', [CheckoutController::class, 'validateCoupon'])->name('checkout.validate-coupon');

        Route::get('/payment', [PaymentController::class, 'showCheckout'])->name('payment.show');
        Route::post('/payment', [PaymentController::class, 'process'])->name('payment.process');
        Route::post('/payment/crypto', [PaymentController::class, 'createCryptoInvoice'])->name('payment.crypto');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
        Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
        Route::delete('/wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');

        Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\GrowMart\NotificationController::class, 'page'])->name('page');
            Route::get('/list', [\App\Http\Controllers\GrowMart\NotificationController::class, 'index'])->name('index');
            Route::get('/unread-count', [\App\Http\Controllers\GrowMart\NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::post('/{id}/read', [\App\Http\Controllers\GrowMart\NotificationController::class, 'markAsRead'])->name('read');
            Route::post('/mark-all-read', [\App\Http\Controllers\GrowMart\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('/{id}', [\App\Http\Controllers\GrowMart\NotificationController::class, 'destroy'])->name('destroy');
        });
    });

    // Admin routes
    Route::middleware(['auth', 'admin'])
        ->prefix($adminPrefix)
        ->name($namePrefix . 'admin.')
        ->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            Route::post('/uploads', [ImageUploadController::class, 'store'])->name('uploads.store');

            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

            Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
            Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
            Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
            Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
            Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

            Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::put('/orders/{order}/payment', [AdminOrderController::class, 'updatePayment'])->name('orders.update-payment');
            Route::put('/orders/{order}/tracking', [AdminOrderController::class, 'updateTracking'])->name('orders.update-tracking');

            Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
            Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');

            Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
            Route::get('/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
            Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
            Route::get('/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
            Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
            Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');

            Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
            Route::get('/coupons/create', [CouponController::class, 'create'])->name('coupons.create');
            Route::post('/coupons', [CouponController::class, 'store'])->name('coupons.store');
            Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
            Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
            Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');

            Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
            Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
            Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
            Route::post('/reviews/{review}/respond', [AdminReviewController::class, 'respond'])->name('reviews.respond');
            Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
        });
};

// 1. Main domain routes (mygrownet.com) — served under /growmart prefix
Route::prefix('growmart')->group(function () use ($registerGrowMartRoutes) {
    $registerGrowMartRoutes(
        customerPrefix: '',
        adminPrefix: 'admin/growmart',
        namePrefix: 'growmart.'
    );
});

// 2. Subdomain routes (growmart.mygrownet.com) — served at root
// Registered LAST so route() generates clean URLs for the subdomain
Route::domain('growmart.mygrownet.com')->group(function () use ($registerGrowMartRoutes) {
    $registerGrowMartRoutes(
        customerPrefix: '',
        adminPrefix: 'admin',
        namePrefix: 'growmart.'
    );
});
