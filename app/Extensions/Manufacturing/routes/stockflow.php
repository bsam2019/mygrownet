<?php

use App\Extensions\Manufacturing\Controllers\BillOfMaterialsController;
use App\Extensions\Manufacturing\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('stockflow')->name('stockflow.')->group(function () {
    Route::middleware('stockflow.feature:bill-of-materials')->group(function () {
        Route::get('/boms', [BillOfMaterialsController::class, 'index'])->name('boms.index');
        Route::post('/boms', [BillOfMaterialsController::class, 'store'])->name('boms.store');
    });
    Route::middleware('stockflow.feature:work-orders')->group(function () {
        Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
        Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
    });
});
