<?php

use App\Extensions\Pharmacy\Controllers\ControlledMedicineController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('stockflow')->name('stockflow.')->group(function () {
    Route::middleware('stockflow.feature:controlled-medicines')->group(function () {
        Route::get('/controlled-medicines', [ControlledMedicineController::class, 'index'])->name('controlled-medicines.index');
        Route::post('/controlled-medicines', [ControlledMedicineController::class, 'store'])->name('controlled-medicines.store');
    });
});
