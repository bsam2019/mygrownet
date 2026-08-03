<?php

use App\Domain\GrowStream\Presentation\Http\Controllers\Admin\CreatorAdminController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Admin\ModerationController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Web\Creator\CreatorOnboardingController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Web\Creator\CreatorVideoController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Web\GrowStreamWebController;
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
    Route::middleware(['web', 'auth'])->prefix($prefix)->name($namePrefix)->group(function () {
        Route::get('/subscription', fn () => redirect()->route('subscriptions.plans', ['module' => 'growstream']))->name('subscription');
        Route::get('/my-videos', [GrowStreamWebController::class, 'myVideos'])->name('my-videos');

        // Creator onboarding
        Route::get('/creator/register', [CreatorOnboardingController::class, 'showRegister'])->name('creator.register');
        Route::post('/creator/register', [CreatorOnboardingController::class, 'storeRegistration'])->name('creator.register.store');
        Route::get('/creator/pending', [CreatorOnboardingController::class, 'pendingApproval'])->name('creator.pending');
        Route::get('/creator/dashboard', [CreatorOnboardingController::class, 'dashboard'])->name('creator.dashboard');

        // Creator self-service content
        Route::get('/creator/videos', [CreatorVideoController::class, 'index'])->name('creator.videos.index');
        Route::get('/creator/videos/create', [CreatorVideoController::class, 'create'])->name('creator.videos.create');
        Route::post('/creator/videos', [CreatorVideoController::class, 'store'])->name('creator.videos.store');
        Route::get('/creator/videos/{id}/edit', [CreatorVideoController::class, 'edit'])->name('creator.videos.edit');
        Route::put('/creator/videos/{id}', [CreatorVideoController::class, 'update'])->name('creator.videos.update');
        Route::delete('/creator/videos/{id}', [CreatorVideoController::class, 'destroy'])->name('creator.videos.destroy');
        Route::get('/creator/analytics', [CreatorVideoController::class, 'analytics'])->name('creator.analytics');
    });

    Route::middleware(['web', 'auth', 'role:admin'])->prefix($prefix.'/admin')->name($namePrefix.'admin.')->group(function () {
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
    });
};

$registerGrowStreamPublicRoutes = function (string $prefix, string $namePrefix) {
    Route::middleware('web')->prefix($prefix)->name($namePrefix)->group(function () {
        Route::get('/', [GrowStreamWebController::class, 'home'])->name('home');
        Route::get('/browse', [GrowStreamWebController::class, 'browse'])->name('browse');
        Route::get('/video/{slug}', [GrowStreamWebController::class, 'videoDetail'])->name('video.detail');
        Route::get('/series/{slug}', [GrowStreamWebController::class, 'seriesDetail'])->name('series.detail');
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
