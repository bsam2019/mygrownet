<?php

use App\Extensions\Manufacturing\Controllers\BillOfMaterialsController;
use App\Extensions\Manufacturing\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::domain('{account}.mygrownet.com')
    ->middleware(['web', 'stockflow.company'])
    ->where(['account' => '^(?!stockflow$)[a-z0-9-]+$'])
    ->group(function () {
        Route::middleware('auth:web')->group(function () {
            Route::middleware('stockflow.feature:bill-of-materials')->group(function () {
                Route::get('/boms', [BillOfMaterialsController::class, 'index'])->name('stockflow.sub.boms.index');
                Route::post('/boms', [BillOfMaterialsController::class, 'store'])->name('stockflow.sub.boms.store');
            });
            Route::middleware('stockflow.feature:work-orders')->group(function () {
                Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('stockflow.sub.work-orders.index');
                Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('stockflow.sub.work-orders.store');
            });
        });
    });
