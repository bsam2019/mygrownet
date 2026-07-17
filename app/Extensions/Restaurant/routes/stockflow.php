<?php

use App\Extensions\Restaurant\Controllers\RecipeController;
use App\Extensions\Restaurant\Controllers\WastageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('stockflow')->name('stockflow.')->group(function () {
    Route::middleware('stockflow.feature:recipes')->group(function () {
        Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
        Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');
    });
    Route::middleware('stockflow.feature:wastage')->group(function () {
        Route::get('/wastage', [WastageController::class, 'index'])->name('wastage.index');
        Route::post('/wastage', [WastageController::class, 'store'])->name('wastage.store');
    });
});
