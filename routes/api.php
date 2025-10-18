<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InvestmentMetricsController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\Admin\ReferralController as AdminReferralController;

// API routes have been moved to web.php

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/investment-metrics', [InvestmentMetricsController::class, 'getMetrics']);
    Route::get('/metrics', [InvestmentMetricsController::class, 'getMetrics']);
    Route::prefix('referral')->group(function () {
        Route::get('/stats', [ReferralController::class, 'stats']);
        Route::get('/tree', [ReferralController::class, 'tree']);
        Route::get('/commissions', [ReferralController::class, 'commissions']);
        Route::get('/link', [ReferralController::class, 'link']);
    });
});

// Admin Referral Routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::prefix('admin/referral')->group(function () {
        Route::get('/stats', [AdminReferralController::class, 'stats']);
        Route::get('/top-referrers', [AdminReferralController::class, 'topReferrers']);
        Route::get('/monthly-stats', [AdminReferralController::class, 'monthlyStats']);
        Route::post('/process-commissions', [AdminReferralController::class, 'processCommissions']);
    });
});
