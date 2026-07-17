<?php

use App\Extensions\Pharmacy\Controllers\ControlledMedicineController;
use Illuminate\Support\Facades\Route;

Route::domain('{account}.mygrownet.com')
    ->middleware(['web', 'stockflow.company'])
    ->where(['account' => '^(?!stockflow$)[a-z0-9-]+$'])
    ->group(function () {
        Route::middleware('auth:stockflow')->group(function () {
            Route::middleware('stockflow.feature:controlled-medicines')->group(function () {
                Route::get('/controlled-medicines', [ControlledMedicineController::class, 'index'])->name('stockflow.sub.controlled-medicines.index');
                Route::post('/controlled-medicines', [ControlledMedicineController::class, 'store'])->name('stockflow.sub.controlled-medicines.store');
            });
        });
    });
