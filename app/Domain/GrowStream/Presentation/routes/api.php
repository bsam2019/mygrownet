<?php

use App\Domain\GrowStream\Presentation\Http\Controllers\Api\VideoController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Api\SeriesController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Api\WatchController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Api\CategoryController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Admin\VideoManagementController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Admin\SeriesManagementController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Admin\AnalyticsController;
use App\Domain\GrowStream\Presentation\Http\Controllers\Admin\CreatorManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/growstream')->name('api.growstream.')->group(function () {

    // Public routes
    Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/featured', [VideoController::class, 'featured'])->name('videos.featured');
    Route::get('/videos/trending', [VideoController::class, 'trending'])->name('videos.trending');
    Route::get('/videos/{slug}', [VideoController::class, 'show'])->name('videos.show');

    Route::get('/series', [SeriesController::class, 'index'])->name('series.index');
    Route::get('/series/{slug}', [SeriesController::class, 'show'])->name('series.show');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{slug}/videos', [CategoryController::class, 'videos'])->name('categories.videos');

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/watch/authorize', [WatchController::class, 'authorize'])->name('watch.authorize');
        Route::post('/watch/progress', [WatchController::class, 'updateProgress'])->name('watch.progress');
        Route::get('/watch/history', [WatchController::class, 'history'])->name('watch.history');
        Route::get('/continue-watching', [WatchController::class, 'continueWatching'])->name('continue-watching');

        Route::get('/watchlist', [WatchController::class, 'watchlist'])->name('watchlist.index');
        Route::post('/watchlist', [WatchController::class, 'addToWatchlist'])->name('watchlist.store');
        Route::delete('/watchlist/{watchlist}', [WatchController::class, 'removeFromWatchlist'])->name('watchlist.destroy');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/videos', [VideoManagementController::class, 'index'])->name('videos.index');
        Route::post('/videos/upload', [VideoManagementController::class, 'upload'])->name('videos.upload');
        Route::get('/videos/form-data', [VideoManagementController::class, 'formData'])->name('videos.form-data');
        Route::get('/videos/{id}', [VideoManagementController::class, 'show'])->name('videos.show');
        Route::put('/videos/{id}', [VideoManagementController::class, 'update'])->name('videos.update');
        Route::delete('/videos/{id}', [VideoManagementController::class, 'destroy'])->name('videos.destroy');
        Route::post('/videos/{id}/publish', [VideoManagementController::class, 'publish'])->name('videos.publish');
        Route::post('/videos/{id}/unpublish', [VideoManagementController::class, 'unpublish'])->name('videos.unpublish');
        Route::post('/videos/bulk-action', [VideoManagementController::class, 'bulkAction'])->name('videos.bulk-action');

        Route::get('/series', [SeriesManagementController::class, 'index'])->name('series.index');
        Route::post('/series', [SeriesManagementController::class, 'store'])->name('series.store');
        Route::get('/series/{id}', [SeriesManagementController::class, 'show'])->name('series.show');
        Route::put('/series/{id}', [SeriesManagementController::class, 'update'])->name('series.update');
        Route::delete('/series/{id}', [SeriesManagementController::class, 'destroy'])->name('series.destroy');
        Route::post('/series/{id}/publish', [SeriesManagementController::class, 'publish'])->name('series.publish');
        Route::post('/series/{id}/unpublish', [SeriesManagementController::class, 'unpublish'])->name('series.unpublish');
        Route::post('/series/{id}/reorder-episodes', [SeriesManagementController::class, 'reorderEpisodes'])->name('series.reorder-episodes');

        Route::get('/analytics/overview', [AnalyticsController::class, 'overview'])->name('analytics.overview');
        Route::get('/analytics/videos', [AnalyticsController::class, 'videoAnalytics'])->name('analytics.videos');
        Route::get('/analytics/videos/{id}', [AnalyticsController::class, 'videoDetails'])->name('analytics.video-details');
        Route::get('/analytics/creators', [AnalyticsController::class, 'creatorAnalytics'])->name('analytics.creators');
        Route::get('/analytics/engagement', [AnalyticsController::class, 'engagement'])->name('analytics.engagement');
        Route::get('/analytics/revenue', [AnalyticsController::class, 'revenue'])->name('analytics.revenue');

        Route::get('/creators', [CreatorManagementController::class, 'index'])->name('creators.index');
        Route::get('/creators/{id}', [CreatorManagementController::class, 'show'])->name('creators.show');
        Route::post('/creators/{id}/verify', [CreatorManagementController::class, 'verify'])->name('creators.verify');
        Route::post('/creators/{id}/unverify', [CreatorManagementController::class, 'unverify'])->name('creators.unverify');
        Route::post('/creators/{id}/suspend', [CreatorManagementController::class, 'suspend'])->name('creators.suspend');
        Route::post('/creators/{id}/unsuspend', [CreatorManagementController::class, 'unsuspend'])->name('creators.unsuspend');
        Route::put('/creators/{id}/limits', [CreatorManagementController::class, 'updateLimits'])->name('creators.update-limits');
    });
});
