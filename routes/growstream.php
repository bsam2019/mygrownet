<?php

use App\Domain\GrowStream\Presentation\Http\Controllers\Admin\CreatorAdminController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Admin\ModerationController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Admin\SponsorshipController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Web\Creator\CreatorOnboardingController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Web\Creator\CreatorSponsorshipController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Web\Creator\CreatorVideoController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Web\GrowStreamWebController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Web\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GrowStream Routes
|--------------------------------------------------------------------------
|
| MyGrowNet Video Streaming & Learning Platform
| - Subdomain: growstream.mygrownet.com �+' routes served at root /
| - Main domain: mygrownet.com/growstream �+' routes served under /growstream prefix
|
*/

$registerGrowStreamRoutes = function (string $prefix, string $namePrefix) {
    Route::middleware(['web', 'identity.redirect:growstream', 'auth'])->prefix($prefix)->name($namePrefix)->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::get('/subscription', [\App\Http\Controllers\SubscriptionCheckoutController::class, 'pricing'])
            ->defaults('moduleId', 'growstream')
            ->name('subscription');
        Route::get('/subscriptions/growstream/checkout', [\App\Http\Controllers\SubscriptionCheckoutController::class, 'show'])
            ->defaults('moduleId', 'growstream')
            ->name('checkout');
        Route::post('/videos/{video}/rent', [\App\Http\Controllers\GrowStream\VideoRentalController::class, 'store'])->name('rent');
        Route::get('/videos/rental-status/{reference}', [\App\Http\Controllers\GrowStream\VideoRentalController::class, 'status'])->name('rental-status');
        // Admin video upload via web session (avoids sanctum role:admin mismatch)
        Route::post('/admin/videos/upload', [\App\Domain\GrowStream\Presentation\Http\Controllers\Admin\VideoManagementController::class, 'upload'])->name('admin.videos.upload');
        Route::post('/admin/videos/tus-init', [\App\Domain\GrowStream\Presentation\Http\Controllers\Admin\VideoManagementController::class, 'tusInit'])->name('admin.videos.tus-init');
        Route::post('/admin/videos/{id}/tus-complete', [\App\Domain\GrowStream\Presentation\Http\Controllers\Admin\VideoManagementController::class, 'tusComplete'])->name('admin.videos.tus-complete');
        Route::delete('/admin/videos/{id}', [\App\Domain\GrowStream\Presentation\Http\Controllers\Admin\VideoManagementController::class, 'destroy'])->name('admin.videos.delete');
        Route::post('/admin/videos/bulk-delete', [\App\Domain\GrowStream\Presentation\Http\Controllers\Admin\VideoManagementController::class, 'bulkDelete'])->name('admin.videos.bulk-delete');
        Route::get('/my-videos', [GrowStreamWebController::class, 'myVideos'])->name('my-videos');
        Route::get('/downloads', [GrowStreamWebController::class, 'downloads'])->name('downloads');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::post('/notifications/{id}/archive', [NotificationController::class, 'archive'])->name('notifications.archive');
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

        // Creator onboarding
        Route::get('/creator/register', [CreatorOnboardingController::class, 'showRegister'])->name('creator.register');
        Route::post('/creator/register', [CreatorOnboardingController::class, 'storeRegistration'])->name('creator.register.store');
        Route::get('/creator/pending', [CreatorOnboardingController::class, 'pendingApproval'])->name('creator.pending');
        Route::get('/creator/dashboard', [CreatorOnboardingController::class, 'dashboard'])->name('creator.dashboard');

        // Creator self-service content
        Route::get('/creator/videos', [CreatorVideoController::class, 'index'])->name('creator.videos.index');
        Route::get('/creator/videos/create', [CreatorVideoController::class, 'create'])->name('creator.videos.create');
        Route::post('/creator/videos', [CreatorVideoController::class, 'store'])->name('creator.videos.store');
        Route::post('/creator/videos/tus-init', [CreatorVideoController::class, 'tusInit'])->name('creator.videos.tus-init');
        Route::post('/creator/videos/{id}/tus-complete', [CreatorVideoController::class, 'tusComplete'])->name('creator.videos.tus-complete');
        Route::get('/creator/videos/{id}/edit', [CreatorVideoController::class, 'edit'])->name('creator.videos.edit');
        Route::put('/creator/videos/{id}', [CreatorVideoController::class, 'update'])->name('creator.videos.update');
        Route::delete('/creator/videos/{id}', [CreatorVideoController::class, 'destroy'])->name('creator.videos.destroy');
        Route::get('/creator/analytics', [CreatorVideoController::class, 'analytics'])->name('creator.analytics');
        Route::get('/creator/payouts', [CreatorVideoController::class, 'payouts'])->name('creator.payouts');

        // Creator sponsorship fund
        Route::get('/creator/sponsorship', [CreatorSponsorshipController::class, 'index'])->name('creator.sponsorship.index');
        Route::post('/creator/sponsorship', [CreatorSponsorshipController::class, 'store'])->name('creator.sponsorship.store');
    });

    Route::middleware(['web', 'auth', 'admin.or.role'])->prefix($prefix.'/admin')->name($namePrefix.'admin.')->group(function () {
        Route::get('/videos', [GrowStreamWebController::class, 'adminVideos'])->name('videos');
        Route::get('/videos/{id}/edit', [GrowStreamWebController::class, 'adminVideoEdit'])->name('videos.edit');
        Route::get('/analytics', [GrowStreamWebController::class, 'adminAnalytics'])->name('analytics');
        Route::get('/creators', [GrowStreamWebController::class, 'adminCreators'])->name('creators');
        Route::post('/creators/{id}/approve', [CreatorAdminController::class, 'approve'])->name('creators.approve');
        Route::post('/creators/{id}/reject', [CreatorAdminController::class, 'reject'])->name('creators.reject');

        // Content moderation queue
        Route::get('/moderation', [ModerationController::class, 'queue'])->name('moderation');
        Route::post('/moderation/{id}/approve', [ModerationController::class, 'approve'])->name('moderation.approve');
        Route::post('/moderation/{id}/publish', [ModerationController::class, 'publish'])->name('moderation.publish');
        Route::post('/moderation/{id}/reject', [ModerationController::class, 'reject'])->name('moderation.reject');

        // Creator sponsorship fund
        Route::get('/sponsorship', [SponsorshipController::class, 'index'])->name('sponsorship');
        Route::post('/sponsorship/{id}/approve', [SponsorshipController::class, 'approve'])->name('sponsorship.approve');
        Route::post('/sponsorship/{id}/reject', [SponsorshipController::class, 'reject'])->name('sponsorship.reject');
        Route::post('/sponsorship/{id}/disburse', [SponsorshipController::class, 'disburse'])->name('sponsorship.disburse');
        Route::post('/sponsorship/{id}/complete', [SponsorshipController::class, 'complete'])->name('sponsorship.complete');
    });
};

$registerGrowStreamPublicRoutes = function (string $prefix, string $namePrefix) {
    Route::middleware('web')->prefix($prefix)->name($namePrefix)->group(function () {
        Route::get('/', [GrowStreamWebController::class, 'home'])->name('home');
        Route::get('/browse', [GrowStreamWebController::class, 'browse'])->name('browse');
        Route::get('/search', [GrowStreamWebController::class, 'search'])->name('search');
        Route::get('/c/{slug}', [GrowStreamWebController::class, 'channel'])->name('channel');
        Route::get('/channel/{slug}', [GrowStreamWebController::class, 'creatorProfile'])->name('creator.profile');
        Route::get('/video/{slug}', [GrowStreamWebController::class, 'videoDetail'])->name('video.detail');
        Route::get('/series/{slug}', [GrowStreamWebController::class, 'seriesDetail'])->name('series.detail');
        Route::get('/login', [GrowStreamWebController::class, 'redirectToLogin'])->name('login');
        Route::get('/register', [GrowStreamWebController::class, 'redirectToRegister'])->name('register');
    });
};

// �"?�"? Main domain routes (mygrownet.com) �?" served under /growstream prefix �"?�"?�??
$registerGrowStreamPublicRoutes(
    prefix: 'growstream',
    namePrefix: 'growstream.main.'
);

// �"?�"? Subdomain routes (growstream.mygrownet.com) �?" served at root �"?�"?�??
Route::domain('growstream.mygrownet.com')->group(function () use ($registerGrowStreamPublicRoutes, $registerGrowStreamRoutes) {
    $registerGrowStreamPublicRoutes(
        prefix: '',
        namePrefix: 'growstream.'
    );

    $registerGrowStreamRoutes(
        prefix: '',
        namePrefix: 'growstream.'
    );
});
