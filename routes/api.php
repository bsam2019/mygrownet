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
Route::prefix('webhooks/payments')->name('webhooks.')->group(function () {
    Route::post('/moneyunify', [PaymentWebhookController::class, 'moneyUnify'])->name('moneyunify');
    Route::post('/pawapay', [PaymentWebhookController::class, 'pawapay'])->name('pawapay');
    Route::post('/sparco', [PaymentWebhookController::class, 'sparco'])->name('sparco');
    Route::post('/nowpayments', [\App\Http\Controllers\Webhooks\NOWPaymentsWebhookController::class, 'handle'])->name('nowpayments');
});

// Currency Routes (public and authenticated)
Route::prefix('currency')->name('currency.')->group(function () {
    Route::get('/detect', [\App\Http\Controllers\CurrencyController::class, 'detect'])->name('detect');
    Route::get('/popular', [\App\Http\Controllers\CurrencyController::class, 'popular'])->name('popular');
    Route::post('/convert', [\App\Http\Controllers\CurrencyController::class, 'convert'])->name('convert');
    Route::get('/rate', [\App\Http\Controllers\CurrencyController::class, 'rate'])->name('rate');
    
    // Authenticated only
    Route::middleware(['web', 'auth'])->group(function () {
        Route::post('/set', [\App\Http\Controllers\CurrencyController::class, 'setCurrency'])->name('set');
    });
});

// Set user currency for testing (uses web middleware for auth)
Route::middleware(['web', 'auth'])->post('/set-user-currency', function(\Illuminate\Http\Request $request) {
    $user = $request->user();
    $currency = strtoupper($request->input('currency', 'ZMW'));
    
    if (!in_array($currency, ['ZMW', 'USD'])) {
        return response()->json(['success' => false, 'message' => 'Invalid currency'], 400);
    }
    
    $user->user_currency = $currency;
    $user->preferred_currency = $currency;
    $user->save();
    
    return response()->json([
        'success' => true,
        'currency' => $currency,
        'balance' => $user->balance,
        'message' => "Currency set to {$currency}"
    ]);
})->name('api.set-user-currency');

// Crypto Payment Routes
Route::middleware(['web', 'auth'])->prefix('payments/crypto')->name('payments.crypto.')->group(function () {
    Route::post('/create', [\App\Http\Controllers\CryptoPaymentController::class, 'create'])->name('create');
    Route::get('/status/{transactionId}', [\App\Http\Controllers\CryptoPaymentController::class, 'status'])->name('status');
});

// Benefits API Routes (using web auth for Inertia compatibility)
Route::middleware(['web', 'auth'])->prefix('benefits')->group(function () {
    Route::get('/', [\App\Http\Controllers\BenefitController::class, 'index']);
    Route::get('/my-benefits', [\App\Http\Controllers\BenefitController::class, 'myBenefits']);
});

// Storage API Routes (using web auth for Inertia compatibility)
Route::middleware(['web', 'auth'])->prefix('storage')->group(function () {
    // Folders
    Route::get('/folders', [\App\Http\Controllers\Storage\StorageFolderController::class, 'index']);
    Route::post('/folders', [\App\Http\Controllers\Storage\StorageFolderController::class, 'store']);
    Route::patch('/folders/{folder}', [\App\Http\Controllers\Storage\StorageFolderController::class, 'update']);
    Route::post('/folders/{folder}/move', [\App\Http\Controllers\Storage\StorageFolderController::class, 'move']);
    Route::delete('/folders/{folder}', [\App\Http\Controllers\Storage\StorageFolderController::class, 'destroy']);
    
    // Files
    Route::post('/files/upload-init', [\App\Http\Controllers\Storage\StorageFileController::class, 'uploadInit']);
    Route::post('/files/upload-complete', [\App\Http\Controllers\Storage\StorageFileController::class, 'uploadComplete']);
    Route::get('/files/{file}/stream', [\App\Http\Controllers\Storage\StorageFileController::class, 'stream']); // Stream for preview
    Route::get('/files/{file}/download', [\App\Http\Controllers\Storage\StorageFileController::class, 'forceDownload']); // Force download
    Route::patch('/files/{file}', [\App\Http\Controllers\Storage\StorageFileController::class, 'update']);
    Route::post('/files/{file}/move', [\App\Http\Controllers\Storage\StorageFileController::class, 'move']);
    Route::delete('/files/{file}', [\App\Http\Controllers\Storage\StorageFileController::class, 'destroy']);
    
    // File Sharing
    Route::post('/files/{file}/shares', [\App\Http\Controllers\Storage\FileShareController::class, 'store']);
    Route::get('/files/{file}/shares', [\App\Http\Controllers\Storage\FileShareController::class, 'index']);
    Route::delete('/shares/{share}', [\App\Http\Controllers\Storage\FileShareController::class, 'destroy']);
    
    // Usage
    Route::get('/usage', [\App\Http\Controllers\Storage\StorageUsageController::class, 'show']);
});
