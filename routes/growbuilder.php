<?php

use App\Http\Controllers\GrowBuilder\AIController;
use App\Http\Controllers\GrowBuilder\CheckoutController;
use App\Http\Controllers\GrowBuilder\EditorController;
use App\Http\Controllers\GrowBuilder\MediaController;
use App\Http\Controllers\GrowBuilder\OrderController;
use App\Http\Controllers\GrowBuilder\PaymentSettingsController;
use App\Http\Controllers\GrowBuilder\ProductController;
use App\Http\Controllers\GrowBuilder\SiteAuthController;
use App\Http\Controllers\GrowBuilder\SiteBlogController;
use App\Http\Controllers\GrowBuilder\SiteContactController;
use App\Http\Controllers\GrowBuilder\SiteMemberController;
use App\Http\Controllers\GrowBuilder\SitePostController;
use App\Http\Controllers\GrowBuilder\SiteUserManagementController;
use App\Http\Controllers\GrowBuilder\SiteController;
use App\Http\Controllers\GrowBuilder\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GrowBuilder Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('growbuilder')->name('growbuilder.')->group(function () {
    // Dashboard
    Route::get('/', [SiteController::class, 'index'])->name('index');

    // Subscription/Pricing
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/purchase', [SubscriptionController::class, 'purchase'])->name('subscription.purchase');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');

    // Sites
    Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::get('/sites/{id}', [SiteController::class, 'show'])->name('sites.show');
    Route::get('/sites/{id}/settings', [SiteController::class, 'settings'])->name('sites.settings');
    Route::get('/sites/{id}/analytics', [SiteController::class, 'analytics'])->name('sites.analytics');
    Route::put('/sites/{id}', [SiteController::class, 'update'])->name('sites.update');
    Route::delete('/sites/{id}', [SiteController::class, 'destroy'])->name('sites.destroy');
    Route::post('/sites/{id}/restore', [SiteController::class, 'restore'])->name('sites.restore');
    Route::post('/sites/{id}/publish', [SiteController::class, 'publish'])->name('sites.publish');
    Route::post('/sites/{id}/unpublish', [SiteController::class, 'unpublish'])->name('sites.unpublish');

    // Site Users Management (for site owners)
    Route::get('/sites/{id}/users', [SiteController::class, 'users'])->name('sites.users');
    Route::post('/sites/{id}/users', [SiteController::class, 'createUser'])->name('sites.users.create');
    Route::put('/sites/{id}/users/{userId}/role', [SiteController::class, 'updateUserRole'])->name('sites.users.role');
    Route::delete('/sites/{id}/users/{userId}', [SiteController::class, 'deleteUser'])->name('sites.users.delete');

    // Site Roles Management (for site owners)
    Route::get('/sites/{id}/roles', [SiteController::class, 'roles'])->name('sites.roles');
    Route::post('/sites/{id}/roles', [SiteController::class, 'createRole'])->name('sites.roles.create');
    Route::put('/sites/{id}/roles/{roleId}', [SiteController::class, 'updateRole'])->name('sites.roles.update');
    Route::delete('/sites/{id}/roles/{roleId}', [SiteController::class, 'deleteRole'])->name('sites.roles.delete');
    Route::get('/sites/{id}/permissions', [SiteController::class, 'permissions'])->name('sites.permissions');

    // Editor
    Route::get('/editor/{siteId}', [EditorController::class, 'index'])->name('editor');
    Route::get('/editor/{siteId}/page/{pageId}', [EditorController::class, 'editPage'])->name('editor.page');
    Route::post('/editor/{siteId}/pages', [EditorController::class, 'savePage'])->name('editor.save');
    Route::post('/editor/{siteId}/pages/{pageId}/save', [EditorController::class, 'savePageContent'])->name('editor.saveContent');
    Route::put('/editor/{siteId}/pages/{pageId}', [EditorController::class, 'updatePageMeta'])->name('editor.updateMeta');
    Route::delete('/editor/{siteId}/pages/{pageId}', [EditorController::class, 'deletePage'])->name('editor.delete');
    Route::post('/editor/{siteId}/settings', [EditorController::class, 'saveSiteSettings'])->name('editor.saveSettings');

    // Media (support both URL patterns)
    Route::get('/media/{siteId}', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media/{siteId}', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{siteId}/{mediaId}', [MediaController::class, 'destroy'])->name('media.destroy');

    // Media
    Route::get('/sites/{siteId}/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/sites/{siteId}/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/sites/{siteId}/media/{mediaId}', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::post('/sites/{siteId}/generate-favicon', [MediaController::class, 'generateFavicon'])->name('media.generate-favicon');

    // Products
    Route::get('/sites/{siteId}/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/sites/{siteId}/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/sites/{siteId}/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/sites/{siteId}/products/{productId}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/sites/{siteId}/products/{productId}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/sites/{siteId}/products/{productId}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Orders
    Route::get('/sites/{siteId}/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/sites/{siteId}/orders/{orderId}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/sites/{siteId}/orders/{orderId}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/sites/{siteId}/orders/{orderId}/mark-paid', [OrderController::class, 'markAsPaid'])->name('orders.mark-paid');

    // Payment Settings
    Route::get('/sites/{siteId}/payments', [PaymentSettingsController::class, 'index'])->name('payments.index');
    Route::put('/sites/{siteId}/payments', [PaymentSettingsController::class, 'update'])->name('payments.update');

    // AI Content Generation
    Route::get('/ai/status', [AIController::class, 'status'])->name('ai.status');
    Route::post('/sites/{siteId}/ai/feedback', [AIController::class, 'recordFeedback'])->name('ai.feedback');
    Route::get('/sites/{siteId}/ai/feedback-stats', [AIController::class, 'getFeedbackStats'])->name('ai.feedback-stats');
    Route::post('/sites/{siteId}/ai/smart-chat', [AIController::class, 'smartChat'])->name('ai.smart-chat');
    Route::post('/sites/{siteId}/ai/classify-intent', [AIController::class, 'classifyIntent'])->name('ai.classify-intent');
    Route::post('/sites/{siteId}/ai/generate-content', [AIController::class, 'generateContent'])->name('ai.generate-content');
    Route::post('/sites/{siteId}/ai/generate-meta', [AIController::class, 'generateMeta'])->name('ai.generate-meta');
    Route::post('/sites/{siteId}/ai/suggest-colors', [AIController::class, 'suggestColors'])->name('ai.suggest-colors');
    Route::post('/sites/{siteId}/ai/improve-text', [AIController::class, 'improveText'])->name('ai.improve-text');
    Route::post('/sites/{siteId}/ai/translate', [AIController::class, 'translate'])->name('ai.translate');
    Route::post('/sites/{siteId}/ai/suggest-images', [AIController::class, 'suggestImages'])->name('ai.suggest-images');
    Route::post('/sites/{siteId}/ai/generate-testimonials', [AIController::class, 'generateTestimonials'])->name('ai.generate-testimonials');
    Route::post('/sites/{siteId}/ai/generate-faqs', [AIController::class, 'generateFAQs'])->name('ai.generate-faqs');
    Route::post('/sites/{siteId}/ai/generate-page', [AIController::class, 'generatePage'])->name('ai.generate-page');
    Route::post('/sites/{siteId}/ai/generate-page-detailed', [AIController::class, 'generatePageDetailed'])->name('ai.generate-page-detailed');
});

// Public checkout API (for rendered sites)
Route::prefix('gb-api/{subdomain}')->name('growbuilder.api.')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'createOrder'])->name('checkout');
    Route::get('/orders/{orderId}/payment-status', [CheckoutController::class, 'checkPaymentStatus'])->name('payment-status');
    Route::post('/contact', [SiteContactController::class, 'store'])->name('contact');
});

/*
|--------------------------------------------------------------------------
| Site User Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('sites/{subdomain}')->group(function () {
    // Public auth pages
    Route::get('/login', [SiteAuthController::class, 'showLogin'])->name('site.login');
    Route::post('/login', [SiteAuthController::class, 'login'])->name('site.login.submit');
    Route::get('/register', [SiteAuthController::class, 'showRegister'])->name('site.register');
    Route::post('/register', [SiteAuthController::class, 'register'])->name('site.register.submit');
    Route::post('/logout', [SiteAuthController::class, 'logout'])->name('site.logout');

    // Password reset
    Route::get('/forgot-password', [SiteAuthController::class, 'showForgotPassword'])->name('site.password.request');
    Route::post('/forgot-password', [SiteAuthController::class, 'sendResetLink'])->name('site.password.email');
    Route::get('/reset-password/{token}', [SiteAuthController::class, 'showResetPassword'])->name('site.password.reset');
    Route::post('/reset-password', [SiteAuthController::class, 'resetPassword'])->name('site.password.update');

    // Protected member area
    Route::middleware('site.auth')->prefix('dashboard')->name('site.member.')->group(function () {
        // Dashboard
        Route::get('/', [SiteMemberController::class, 'dashboard'])->name('dashboard');

        // Profile
        Route::get('/profile', [SiteMemberController::class, 'profile'])->name('profile');
        Route::put('/profile', [SiteMemberController::class, 'updateProfile'])->name('profile.update');

        // Orders
        Route::get('/orders', [SiteMemberController::class, 'orders'])->name('orders');
        Route::get('/orders/{id}', [SiteMemberController::class, 'orderDetail'])->name('orders.show');

        // Posts (Editors & above)
        Route::middleware('site.permission:posts.view')->group(function () {
            Route::get('/posts', [SitePostController::class, 'index'])->name('posts.index');
            Route::get('/posts/create', [SitePostController::class, 'create'])->name('posts.create');
            Route::post('/posts', [SitePostController::class, 'store'])->name('posts.store');
            Route::get('/posts/{id}/edit', [SitePostController::class, 'edit'])->name('posts.edit');
            Route::put('/posts/{id}', [SitePostController::class, 'update'])->name('posts.update');
            Route::delete('/posts/{id}', [SitePostController::class, 'destroy'])->name('posts.destroy');
        });

        // User Management (Admins only)
        Route::middleware('site.permission:users.view')->group(function () {
            Route::get('/users', [SiteUserManagementController::class, 'index'])->name('users.index');
            Route::get('/users/{id}', [SiteUserManagementController::class, 'show'])->name('users.show');
            Route::put('/users/{id}', [SiteUserManagementController::class, 'update'])->name('users.update');
            Route::put('/users/{id}/role', [SiteUserManagementController::class, 'updateRole'])->name('users.role');
            Route::post('/users/{id}/reset-password', [SiteUserManagementController::class, 'resetPassword'])->name('users.reset-password');
            Route::delete('/users/{id}', [SiteUserManagementController::class, 'destroy'])->name('users.destroy');
        });

        // Analytics (Admin or analytics.view permission)
        Route::middleware('site.permission:analytics.view')->group(function () {
            Route::get('/analytics', [SiteMemberController::class, 'analytics'])->name('analytics');
        });

        // Settings (Admin only - level 100+)
        Route::middleware('site.permission:settings.view')->group(function () {
            Route::get('/settings', [SiteMemberController::class, 'settings'])->name('settings');
            Route::put('/settings', [SiteMemberController::class, 'updateSettings'])->name('settings.update');
        });

        // Contact Messages (Admin or messages.view permission)
        Route::middleware('site.permission:messages.view')->group(function () {
            Route::get('/messages', [SiteContactController::class, 'index'])->name('messages.index');
            Route::get('/messages/{id}', [SiteContactController::class, 'show'])->name('messages.show');
            Route::post('/messages/{id}/reply', [SiteContactController::class, 'reply'])->name('messages.reply');
            Route::put('/messages/{id}/status', [SiteContactController::class, 'updateStatus'])->name('messages.status');
            Route::delete('/messages/{id}', [SiteContactController::class, 'destroy'])->name('messages.destroy');
        });
    });
});

// Public blog routes (before the catch-all preview route)
Route::get('/sites/{subdomain}/blog', [SiteBlogController::class, 'index'])->name('site.blog.index');
Route::get('/sites/{subdomain}/blog/{slug}', [SiteBlogController::class, 'show'])->name('site.blog.show');

// Local development site preview (must be last to avoid conflicts)
Route::get('/sites/{subdomain}/{page?}', [SiteController::class, 'preview'])
    ->where('page', '^(?!login|register|forgot-password|reset-password|dashboard|member|blog).*')
    ->name('growbuilder.preview');
