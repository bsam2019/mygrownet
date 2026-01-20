<?php

use App\Http\Controllers\GrowBuilder\RenderController;
use App\Http\Controllers\GrowBuilder\SiteAuthController;
use App\Http\Controllers\GrowBuilder\SiteBlogController;
use App\Http\Controllers\GrowBuilder\SiteContactController;
use App\Http\Controllers\GrowBuilder\SiteMemberController;
use App\Http\Controllers\GrowBuilder\CheckoutController;
use App\Http\Controllers\GrowBuilder\SiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GrowBuilder Subdomain Routes
|--------------------------------------------------------------------------
| These routes handle actual subdomain requests like mysite.mygrownet.com
*/

Route::domain('{subdomain}.mygrownet.com')->middleware('subdomain.check')->group(function () {
    // Sitemap and robots.txt
    Route::get('/sitemap.xml', [RenderController::class, 'sitemap'])->name('subdomain.sitemap');
    Route::get('/robots.txt', [RenderController::class, 'robots'])->name('subdomain.robots');

    // Public site pages
    Route::get('/{slug?}', [RenderController::class, 'render'])
        ->where('slug', '^(?!login$|register$|forgot-password$|reset-password$|dashboard|blog/|product/|checkout$|gb-api/|sitemap\.xml$|robots\.txt$).*')
        ->name('subdomain.render');

    // Blog
    Route::get('/blog', [SiteBlogController::class, 'index'])->name('subdomain.blog.index');
    Route::get('/blog/{slug}', [SiteBlogController::class, 'show'])->name('subdomain.blog.show');

    // Product detail
    Route::get('/product/{slug}', [SiteController::class, 'showProduct'])->name('subdomain.product.show');

    // Checkout
    Route::get('/checkout', [SiteController::class, 'checkout'])->name('subdomain.checkout');

    // Contact API
    Route::post('/gb-api/contact', [SiteContactController::class, 'store'])->name('subdomain.api.contact');

    // Checkout API
    Route::post('/gb-api/checkout', [CheckoutController::class, 'createOrder'])->name('subdomain.api.checkout');
    Route::get('/gb-api/orders/{orderId}/payment-status', [CheckoutController::class, 'checkPaymentStatus'])->name('subdomain.api.payment-status');

    // Authentication
    Route::get('/login', [SiteAuthController::class, 'showLogin'])->name('subdomain.login');
    Route::post('/login', [SiteAuthController::class, 'login'])->name('subdomain.login.submit');
    Route::get('/register', [SiteAuthController::class, 'showRegister'])->name('subdomain.register');
    Route::post('/register', [SiteAuthController::class, 'register'])->name('subdomain.register.submit');
    Route::post('/logout', [SiteAuthController::class, 'logout'])->name('subdomain.logout');

    // Password reset
    Route::get('/forgot-password', [SiteAuthController::class, 'showForgotPassword'])->name('subdomain.password.request');
    Route::post('/forgot-password', [SiteAuthController::class, 'sendResetLink'])->name('subdomain.password.email');
    Route::get('/reset-password/{token}', [SiteAuthController::class, 'showResetPassword'])->name('subdomain.password.reset');
    Route::post('/reset-password', [SiteAuthController::class, 'resetPassword'])->name('subdomain.password.update');

    // Member dashboard
    Route::middleware('site.auth')->prefix('dashboard')->name('subdomain.member.')->group(function () {
        Route::get('/', [SiteMemberController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [SiteMemberController::class, 'profile'])->name('profile');
        Route::put('/profile', [SiteMemberController::class, 'updateProfile'])->name('profile.update');
        Route::get('/orders', [SiteMemberController::class, 'orders'])->name('orders');
        Route::get('/orders/{id}', [SiteMemberController::class, 'orderDetail'])->name('orders.show');
    });
});
