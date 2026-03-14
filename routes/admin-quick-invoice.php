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
});