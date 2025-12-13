<?php

use App\Http\Controllers\POS\POSController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Standalone POS Routes
|--------------------------------------------------------------------------
|
| These routes are for the standalone POS module.
| For integrated POS (via GrowBiz, BizBoost), see respective module routes.
|
*/

Route::middleware(['auth', 'verified'])->prefix('pos')->name('pos.')->group(function () {
    // Dashboard
    Route::get('/', [POSController::class, 'index'])->name('dashboard');
    
    // Terminal
    Route::get('/terminal', [POSController::class, 'terminal'])->name('terminal');
    
    // Shifts
    Route::get('/shifts', [POSController::class, 'shifts'])->name('shifts');
    Route::post('/shifts/start', [POSController::class, 'startShift'])->name('shifts.start');
    Route::post('/shifts/{shift}/close', [POSController::class, 'closeShift'])->name('shifts.close');
    
    // Sales
    Route::get('/sales', [POSController::class, 'sales'])->name('sales');
    Route::post('/sales', [POSController::class, 'createSale'])->name('sales.store');
    Route::get('/sales/{sale}', [POSController::class, 'getSale'])->name('sales.show');
    Route::post('/sales/{sale}/void', [POSController::class, 'voidSale'])->name('sales.void');
    
    // Reports
    Route::get('/reports', [POSController::class, 'reports'])->name('reports');
    Route::get('/reports/daily', [POSController::class, 'dailyReport'])->name('reports.daily');
    
    // Settings (includes quick products management)
    Route::get('/settings', [POSController::class, 'settings'])->name('settings');
    Route::put('/settings', [POSController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/quick-products', [POSController::class, 'syncQuickProducts'])->name('settings.quick-products');
});
