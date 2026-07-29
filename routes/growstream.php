<?php

use App\Domain\GrowStream\Presentation\Http\Controllers\Web\GrowStreamWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GrowStream Routes
|--------------------------------------------------------------------------
|
| MyGrowNet Video Streaming & Learning Platform
| - Subdomain: growstream.mygrownet.com → routes served at root /
| - Main domain: mygrownet.com/growstream → routes served under /growstream prefix
|
*/

$registerGrowStreamRoutes = function (string $prefix, string $namePrefix) {
    Route::middleware(['web', 'auth'])->prefix($prefix)->name($namePrefix)->group(function () {
        Route::get('/my-videos', [GrowStreamWebController::class, 'myVideos'])->name('my-videos');
    });

    Route::middleware(['web', 'auth', 'role:admin'])->prefix($prefix . '/admin')->name($namePrefix . 'admin.')->group(function () {
        Route::get('/videos', [GrowStreamWebController::class, 'adminVideos'])->name('videos');
        Route::get('/videos/{id}/edit', [GrowStreamWebController::class, 'adminVideoEdit'])->name('videos.edit');
        Route::get('/analytics', [GrowStreamWebController::class, 'adminAnalytics'])->name('analytics');
        Route::get('/creators', [GrowStreamWebController::class, 'adminCreators'])->name('creators');
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

// ── Main domain routes (mygrownet.com) — served under /growstream prefix ──
$registerGrowStreamPublicRoutes(
    prefix: 'growstream',
    namePrefix: 'growstream.main.'
);

// ── Subdomain routes (growstream.mygrownet.com) — served at root ──
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
