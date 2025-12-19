<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BizBoost\DashboardController;
use App\Http\Controllers\BizBoost\SetupController;
use App\Http\Controllers\BizBoost\BusinessController;
use App\Http\Controllers\BizBoost\ProductController;
use App\Http\Controllers\BizBoost\CustomerController;
use App\Http\Controllers\BizBoost\PostController;
use App\Http\Controllers\BizBoost\AiContentController;
use App\Http\Controllers\BizBoost\SubscriptionController;
use App\Http\Controllers\BizBoost\WelcomeController;

/*
|--------------------------------------------------------------------------
| BizBoost Routes
|--------------------------------------------------------------------------
|
| Routes for BizBoost - Marketing & Growth Assistant for SMEs
| Mobile-first PWA with standalone app support
|
*/

// Public Welcome/Landing Page (no auth required)
Route::get('/bizboost/welcome', [WelcomeController::class, 'index'])->name('bizboost.welcome');

// Public mini-website routes (no auth required)
Route::prefix('biz')->name('bizboost.public.')->group(function () {
    Route::get('/{slug}', [BusinessController::class, 'publicPage'])->name('business');
    Route::get('/{slug}/products', [BusinessController::class, 'publicProducts'])->name('products');
    Route::get('/{slug}/product/{productId}', [BusinessController::class, 'publicProduct'])->name('product');
    Route::post('/{slug}/contact', [BusinessController::class, 'publicContact'])->name('contact');
});

// Public Marketplace (no auth required)
Route::prefix('marketplace')->name('marketplace.')->group(function () {
    Route::get('/', [App\Http\Controllers\BizBoost\MarketplaceController::class, 'publicIndex'])->name('index');
    Route::get('/category/{category}', [App\Http\Controllers\BizBoost\MarketplaceController::class, 'publicCategory'])->name('category');
    Route::get('/search', [App\Http\Controllers\BizBoost\MarketplaceController::class, 'publicSearch'])->name('search');
});

// QR code redirect
Route::get('/qr/{code}', [App\Http\Controllers\BizBoost\QrCodeController::class, 'redirect'])->name('bizboost.qr.redirect');

// Authenticated BizBoost routes
Route::middleware(['auth', 'verified'])
    ->prefix('bizboost')
    ->name('bizboost.')
    ->group(function () {
        
    // Setup Wizard
    Route::get('/setup', [SetupController::class, 'index'])->name('setup');
    Route::get('/setup/step/{step}', [SetupController::class, 'index'])->name('setup.step')->where('step', '[1-6]');
    Route::post('/setup/business', [SetupController::class, 'storeBusinessInfo'])->name('setup.business');
    Route::post('/setup/location', [SetupController::class, 'storeLocation'])->name('setup.location');
    Route::post('/setup/hours', [SetupController::class, 'storeHours'])->name('setup.hours');
    Route::post('/setup/social', [SetupController::class, 'storeSocial'])->name('setup.social');
    Route::post('/setup/logo', [SetupController::class, 'uploadLogo'])->name('setup.logo');
    Route::post('/setup/complete', [SetupController::class, 'completeSetup'])->name('setup.complete');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Business Profile & Settings
    Route::prefix('business')->name('business.')->group(function () {
        Route::get('/profile', [BusinessController::class, 'profile'])->name('profile');
        Route::put('/profile', [BusinessController::class, 'updateProfile'])->name('profile.update');
        Route::get('/settings', [BusinessController::class, 'settings'])->name('settings');
        Route::put('/settings', [BusinessController::class, 'updateSettings'])->name('settings.update');
        Route::get('/mini-website', [BusinessController::class, 'miniWebsite'])->name('mini-website');
        Route::post('/mini-website', [BusinessController::class, 'updateMiniWebsite'])->name('mini-website.update');
        Route::post('/mini-website/publish', [BusinessController::class, 'publishMiniWebsite'])->name('mini-website.publish');
        Route::post('/mini-website/unpublish', [BusinessController::class, 'unpublishMiniWebsite'])->name('mini-website.unpublish');
    });

    // Products
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        
        // Categories (must be before /{id} routes to avoid conflict)
        Route::get('/categories/manage', [ProductController::class, 'categories'])->name('categories');
        Route::post('/categories', [ProductController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{id}', [ProductController::class, 'updateCategory'])->name('categories.update')->where('id', '[0-9]+');
        Route::delete('/categories/{id}', [ProductController::class, 'destroyCategory'])->name('categories.destroy')->where('id', '[0-9]+');
        Route::post('/categories/migrate-legacy', [ProductController::class, 'migrateLegacyCategory'])->name('categories.migrate-legacy');
        
        // Product-specific routes (with {id} parameter)
        Route::get('/{id}', [ProductController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
        Route::post('/{id}/images', [ProductController::class, 'uploadImage'])->name('images.upload')->where('id', '[0-9]+');
        Route::delete('/{id}/images/{imageId}', [ProductController::class, 'deleteImage'])->name('images.delete')->where('id', '[0-9]+');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/export', [CustomerController::class, 'export'])->name('export');
        Route::get('/{id}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy');
        
        // Customer Tags
        Route::post('/tags', [CustomerController::class, 'storeTags'])->name('tags.store');
        Route::delete('/tags/{tagId}', [CustomerController::class, 'destroyTag'])->name('tags.destroy');
    });

    // Posts
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('index');
        Route::get('/create', [PostController::class, 'create'])->name('create');
        Route::post('/', [PostController::class, 'store'])->name('store');
        Route::get('/{id}', [PostController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PostController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PostController::class, 'update'])->name('update');
        Route::delete('/{id}', [PostController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/duplicate', [PostController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/publish-now', [PostController::class, 'publishNow'])->name('publish-now');
        Route::post('/{id}/retry', [PostController::class, 'retry'])->name('retry');
        Route::get('/{id}/share-links', [PostController::class, 'getShareLinks'])->name('share-links');
    });

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\SalesController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\BizBoost\SalesController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\BizBoost\SalesController::class, 'store'])->name('store');
        Route::get('/reports', [App\Http\Controllers\BizBoost\SalesController::class, 'reports'])->name('reports');
        Route::delete('/{id}', [App\Http\Controllers\BizBoost\SalesController::class, 'destroy'])->name('destroy');
    });

    // Templates
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\TemplateController::class, 'index'])->name('index');
        Route::get('/my', [App\Http\Controllers\BizBoost\TemplateController::class, 'myTemplates'])->name('my');
        Route::post('/{id}/use', [App\Http\Controllers\BizBoost\TemplateController::class, 'useTemplate'])->name('use');
        Route::get('/{id}', [App\Http\Controllers\BizBoost\TemplateController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\BizBoost\TemplateController::class, 'edit'])->name('edit');
        Route::post('/{id}/save', [App\Http\Controllers\BizBoost\TemplateController::class, 'saveCustom'])->name('save');
        Route::delete('/custom/{id}', [App\Http\Controllers\BizBoost\TemplateController::class, 'deleteCustom'])->name('custom.delete');
    });

    // Calendar
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\CalendarController::class, 'index'])->name('index');
        Route::get('/events', [App\Http\Controllers\BizBoost\CalendarController::class, 'events'])->name('events');
        Route::post('/posts/{id}/reschedule', [App\Http\Controllers\BizBoost\CalendarController::class, 'reschedule'])->name('reschedule');
        Route::post('/weekly-themes', [App\Http\Controllers\BizBoost\CalendarController::class, 'storeWeeklyTheme'])->name('weekly-themes.store');
        Route::delete('/weekly-themes/{id}', [App\Http\Controllers\BizBoost\CalendarController::class, 'destroyWeeklyTheme'])->name('weekly-themes.destroy');
        Route::post('/posting-times', [App\Http\Controllers\BizBoost\CalendarController::class, 'updatePostingTimes'])->name('posting-times.update');
        Route::delete('/posting-times', [App\Http\Controllers\BizBoost\CalendarController::class, 'resetPostingTimes'])->name('posting-times.reset');
    });

    // Integrations (Social Media)
    Route::prefix('integrations')->name('integrations.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\SocialMediaIntegrationController::class, 'index'])->name('index');
        
        // New unified OAuth flow for all platforms
        Route::get('/{provider}/connect', [App\Http\Controllers\BizBoost\SocialMediaIntegrationController::class, 'connect'])
            ->name('connect')
            ->where('provider', 'facebook|instagram|whatsapp|tiktok');
        Route::get('/{provider}/callback', [App\Http\Controllers\BizBoost\SocialMediaIntegrationController::class, 'callback'])
            ->name('callback')
            ->where('provider', 'facebook|instagram|whatsapp|tiktok');
        Route::delete('/{provider}', [App\Http\Controllers\BizBoost\SocialMediaIntegrationController::class, 'disconnect'])
            ->name('disconnect')
            ->where('provider', 'facebook|instagram|whatsapp|tiktok');
        Route::post('/{provider}/refresh', [App\Http\Controllers\BizBoost\SocialMediaIntegrationController::class, 'refresh'])
            ->name('refresh')
            ->where('provider', 'facebook|instagram|whatsapp|tiktok');
        
        // Legacy routes (for backward compatibility)
        Route::get('/facebook/connect', [App\Http\Controllers\BizBoost\IntegrationController::class, 'connectFacebook'])->name('facebook.connect.legacy');
        Route::get('/facebook/callback', [App\Http\Controllers\BizBoost\IntegrationController::class, 'facebookCallback'])->name('facebook.callback.legacy');
        Route::get('/facebook/select', [App\Http\Controllers\BizBoost\IntegrationController::class, 'selectFacebookPage'])->name('facebook.select');
        Route::post('/facebook/store', [App\Http\Controllers\BizBoost\IntegrationController::class, 'storeFacebookPage'])->name('facebook.store');
    });

    // Industry Kits
    Route::prefix('industry-kits')->name('industry-kits.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\IndustryKitController::class, 'index'])->name('index');
        Route::get('/{industry}', [App\Http\Controllers\BizBoost\IndustryKitController::class, 'show'])->name('show');
        Route::post('/{industry}/apply', [App\Http\Controllers\BizBoost\IndustryKitController::class, 'applyKit'])->name('apply');
    });

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\AnalyticsController::class, 'index'])->name('index');
        Route::get('/posts/{id}', [App\Http\Controllers\BizBoost\AnalyticsController::class, 'postDetail'])->name('posts.show');
    });

    // QR Codes
    Route::prefix('qr-codes')->name('qr-codes.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\QrCodeController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\BizBoost\QrCodeController::class, 'store'])->name('store');
        Route::delete('/{id}', [App\Http\Controllers\BizBoost\QrCodeController::class, 'destroy'])->name('destroy');
    });

    // AI Content Generator
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', [AiContentController::class, 'index'])->name('index');
        Route::post('/generate', [AiContentController::class, 'generate'])->name('generate');
    });

    // Campaigns
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\CampaignController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\BizBoost\CampaignController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\BizBoost\CampaignController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\BizBoost\CampaignController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\BizBoost\CampaignController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\BizBoost\CampaignController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\BizBoost\CampaignController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/start', [App\Http\Controllers\BizBoost\CampaignController::class, 'start'])->name('start');
        Route::post('/{id}/pause', [App\Http\Controllers\BizBoost\CampaignController::class, 'pause'])->name('pause');
        Route::post('/{id}/resume', [App\Http\Controllers\BizBoost\CampaignController::class, 'resume'])->name('resume');
    });

    // WhatsApp Tools
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/broadcasts', [App\Http\Controllers\BizBoost\WhatsAppController::class, 'broadcasts'])->name('broadcasts');
        Route::get('/broadcasts/create', [App\Http\Controllers\BizBoost\WhatsAppController::class, 'createBroadcast'])->name('broadcasts.create');
        Route::post('/broadcasts', [App\Http\Controllers\BizBoost\WhatsAppController::class, 'storeBroadcast'])->name('broadcasts.store');
        Route::get('/templates', [App\Http\Controllers\BizBoost\WhatsAppController::class, 'templates'])->name('templates');
        Route::post('/generate-message', [App\Http\Controllers\BizBoost\WhatsAppController::class, 'generateMessage'])->name('generate-message');
        Route::get('/export-customers', [App\Http\Controllers\BizBoost\WhatsAppController::class, 'exportCustomers'])->name('export-customers');
    });

    // AI Business Advisor
    Route::prefix('advisor')->name('advisor.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\AdvisorController::class, 'index'])->name('index');
        Route::post('/chat', [App\Http\Controllers\BizBoost\AdvisorController::class, 'chat'])->name('chat');
        Route::get('/recommendations', [App\Http\Controllers\BizBoost\AdvisorController::class, 'recommendations'])->name('recommendations');
    });

    // Follow-up Reminders
    Route::prefix('reminders')->name('reminders.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\ReminderController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\BizBoost\ReminderController::class, 'store'])->name('store');
        Route::put('/{id}', [App\Http\Controllers\BizBoost\ReminderController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\BizBoost\ReminderController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/complete', [App\Http\Controllers\BizBoost\ReminderController::class, 'complete'])->name('complete');
        Route::post('/{id}/snooze', [App\Http\Controllers\BizBoost\ReminderController::class, 'snooze'])->name('snooze');
    });

    // Subscription & Billing
    Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/checkout', [SubscriptionController::class, 'checkout'])->name('checkout');
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
    Route::get('/usage', [SubscriptionController::class, 'usage'])->name('usage');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    
    // Settings - Subscription (in-app with wallet)
    Route::get('/settings/subscription', [SubscriptionController::class, 'settings'])->name('settings.subscription');
    Route::post('/subscription/purchase', [SubscriptionController::class, 'purchase'])->name('subscription.purchase');

    // Feature upgrade required page
    Route::get('/feature-upgrade', function (\Illuminate\Http\Request $request) {
        return \Inertia\Inertia::render('BizBoost/FeatureUpgradeRequired', [
            'feature' => $request->feature ?? 'this feature',
        ]);
    })->name('feature-upgrade');

    // ========================================
    // Phase 4 - Enterprise & Learning Routes
    // ========================================

    // Locations (Multi-location support)
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\LocationController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\BizBoost\LocationController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\BizBoost\LocationController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\BizBoost\LocationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\BizBoost\LocationController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\BizBoost\LocationController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/set-primary', [App\Http\Controllers\BizBoost\LocationController::class, 'setPrimary'])->name('set-primary');
    });

    // Team Management
    Route::prefix('team')->name('team.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\TeamController::class, 'index'])->name('index');
        Route::get('/invite', [App\Http\Controllers\BizBoost\TeamController::class, 'invite'])->name('invite');
        Route::post('/invite', [App\Http\Controllers\BizBoost\TeamController::class, 'sendInvite'])->name('send-invite');
        Route::put('/{id}/role', [App\Http\Controllers\BizBoost\TeamController::class, 'updateRole'])->name('update-role');
        Route::delete('/{id}', [App\Http\Controllers\BizBoost\TeamController::class, 'remove'])->name('remove');
        Route::delete('/invitations/{id}', [App\Http\Controllers\BizBoost\TeamController::class, 'cancelInvitation'])->name('cancel-invitation');
    });

    // Team invitation acceptance (public-ish, but needs auth)
    Route::get('/team/accept/{token}', [App\Http\Controllers\BizBoost\TeamController::class, 'acceptInvitation'])->name('team.accept');

    // Learning Hub
    Route::prefix('learning')->name('learning.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\LearningHubController::class, 'index'])->name('index');
        Route::get('/certificates', [App\Http\Controllers\BizBoost\LearningHubController::class, 'certificates'])->name('certificates');
        Route::get('/certificates/{id}/download', [App\Http\Controllers\BizBoost\LearningHubController::class, 'downloadCertificate'])->name('certificates.download');
        Route::get('/{slug}', [App\Http\Controllers\BizBoost\LearningHubController::class, 'show'])->name('course');
        Route::get('/{slug}/{lessonSlug}', [App\Http\Controllers\BizBoost\LearningHubController::class, 'lesson'])->name('lesson');
        Route::post('/{slug}/{lessonSlug}/complete', [App\Http\Controllers\BizBoost\LearningHubController::class, 'completeLesson'])->name('lesson.complete');
    });

    // API Tokens
    Route::prefix('api-tokens')->name('api.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\ApiTokenController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\BizBoost\ApiTokenController::class, 'store'])->name('store');
        Route::delete('/{id}', [App\Http\Controllers\BizBoost\ApiTokenController::class, 'destroy'])->name('destroy');
        Route::get('/documentation', [App\Http\Controllers\BizBoost\ApiTokenController::class, 'documentation'])->name('documentation');
    });

    // White-label Settings
    Route::prefix('white-label')->name('white-label.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\WhiteLabelController::class, 'index'])->name('index');
        Route::put('/', [App\Http\Controllers\BizBoost\WhiteLabelController::class, 'update'])->name('update');
        Route::post('/logo', [App\Http\Controllers\BizBoost\WhiteLabelController::class, 'uploadLogo'])->name('logo');
    });

    // Marketplace Integration
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\MarketplaceController::class, 'index'])->name('index');
        Route::post('/toggle', [App\Http\Controllers\BizBoost\MarketplaceController::class, 'toggleListing'])->name('toggle');
        Route::put('/listing', [App\Http\Controllers\BizBoost\MarketplaceController::class, 'updateListing'])->name('update-listing');
        Route::get('/browse', [App\Http\Controllers\BizBoost\MarketplaceController::class, 'browse'])->name('browse');
    });

    // ========================================
    // Notifications (Centralized System Integration)
    // ========================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\NotificationController::class, 'index'])->name('index');
        Route::get('/dropdown', [App\Http\Controllers\BizBoost\NotificationController::class, 'dropdown'])->name('dropdown');
        Route::get('/unread-count', [App\Http\Controllers\BizBoost\NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [App\Http\Controllers\BizBoost\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [App\Http\Controllers\BizBoost\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{id}/archive', [App\Http\Controllers\BizBoost\NotificationController::class, 'archive'])->name('archive');
        Route::delete('/{id}', [App\Http\Controllers\BizBoost\NotificationController::class, 'destroy'])->name('destroy');
    });

    // ========================================
    // Messages (Centralized Messaging System)
    // ========================================
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [App\Http\Controllers\BizBoost\MessageController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\BizBoost\MessageController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\BizBoost\MessageController::class, 'store'])->name('store');
        Route::get('/unread-count', [App\Http\Controllers\BizBoost\MessageController::class, 'unreadCount'])->name('unread-count');
        Route::get('/{id}', [App\Http\Controllers\BizBoost\MessageController::class, 'show'])->name('show');
        Route::post('/{id}/reply', [App\Http\Controllers\BizBoost\MessageController::class, 'reply'])->name('reply');
        Route::post('/{id}/read', [App\Http\Controllers\BizBoost\MessageController::class, 'markAsRead'])->name('read');
        Route::delete('/{id}', [App\Http\Controllers\BizBoost\MessageController::class, 'destroy'])->name('destroy');
    });
});
