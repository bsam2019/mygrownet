<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InvestmentMetricsController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentWebhookController;
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

    // Push Notification Device Registration
    Route::prefix('notifications')->group(function () {
        Route::post('/register-device', [DeviceController::class, 'register']);
        Route::post('/unregister-device', [DeviceController::class, 'unregister']);
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

// Payment Gateway Routes (using web auth for Inertia compatibility)
Route::middleware(['web', 'auth'])->prefix('payments')->group(function () {
    Route::get('/gateways', [PaymentController::class, 'gateways']);
    Route::post('/collect', [PaymentController::class, 'collect']);
    Route::post('/disburse', [PaymentController::class, 'disburse']);
    Route::get('/status/{transactionId}', [PaymentController::class, 'status']);
});

// Payment Webhooks (no auth - verified by signature)
Route::prefix('webhooks/payments')->group(function () {
    Route::post('/moneyunify', [PaymentWebhookController::class, 'moneyUnify']);
    Route::post('/pawapay', [PaymentWebhookController::class, 'pawapay']);
});
