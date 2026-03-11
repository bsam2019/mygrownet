<?php

use App\Domain\GrowStream\Presentation\Http\Controllers\Web\GrowStreamWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GrowStream Web Routes
|--------------------------------------------------------------------------
|
| These routes handle the frontend pages for GrowStream using Inertia.js
|
*/

Route::middleware(['web'])->prefix('growstream')->name('growstream.')->group(function () {
    // Public routes
    Route::get('/', [GrowStreamWebController::class, 'home'])->name('home');
    Route::get('/browse', [GrowStreamWebController::class, 'browse'])->name('browse');
    Route::get('/video/{slug}', [GrowStreamWebController::class, 'videoDetail'])->name('video.detail');
    Route::get('/series/{slug}', [GrowStreamWebController::class, 'seriesDetail'])->name('series.detail');

    // Authenticated user routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/my-videos', [GrowStreamWebController::class, 'myVideos'])->name('my-videos');
    });

    // Admin routes
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/videos', [GrowStreamWebController::class, 'adminVideos'])->name('videos');
        Route::get('/videos/{video}/edit', [GrowStreamWebController::class, 'adminVideoEdit'])->name('videos.edit');
        Route::get('/analytics', [GrowStreamWebController::class, 'adminAnalytics'])->name('analytics');
        Route::get('/creators', [GrowStreamWebController::class, 'adminCreators'])->name('creators');
    });
});
