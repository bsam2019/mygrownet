<?php

use App\Http\Controllers\Admin\QuickInvoiceAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Quick Invoice Admin Routes
|--------------------------------------------------------------------------
|
| Admin routes for Quick Invoice management, analytics, and monetization.
| Requires admin authentication and permissions.
|
*/

Route::prefix('admin/quick-invoice')->name('admin.quick-invoice.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [QuickInvoiceAdminController::class, 'dashboard'])->name('dashboard');
    
    // Analytics
    Route::get('/analytics', [QuickInvoiceAdminController::class, 'usageAnalytics'])->name('analytics');
    
    // User Management
    Route::get('/users', [QuickInvoiceAdminController::class, 'userManagement'])->name('users');
    
    // Monetization Settings
    Route::post('/monetization-settings', [QuickInvoiceAdminController::class, 'updateMonetizationSettings'])->name('monetization-settings.update');
    Route::post('/toggle-usage-limits', [QuickInvoiceAdminController::class, 'toggleUsageLimits'])->name('toggle-usage-limits');
    
    // Trial Settings
    Route::post('/trial-settings', [QuickInvoiceAdminController::class, 'updateTrialSettings'])->name('trial-settings.update');
    
    // Tier (Plan) Management
    Route::get('/tiers', [QuickInvoiceAdminController::class, 'tiers'])->name('tiers');
    Route::get('/tiers/create', [QuickInvoiceAdminController::class, 'createTier'])->name('tiers.create');
    Route::post('/tiers', [QuickInvoiceAdminController::class, 'storeTier'])->name('tiers.store');
    Route::get('/tiers/{id}/edit', [QuickInvoiceAdminController::class, 'editTier'])->name('tiers.edit');
    Route::put('/tiers/{id}', [QuickInvoiceAdminController::class, 'updateTier'])->name('tiers.update');
    Route::delete('/tiers/{id}', [QuickInvoiceAdminController::class, 'destroyTier'])->name('tiers.destroy');
});