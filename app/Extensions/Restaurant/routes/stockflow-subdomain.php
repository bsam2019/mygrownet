<?php

use App\Extensions\Restaurant\Controllers\RecipeController;
use App\Extensions\Restaurant\Controllers\WastageController;
use Illuminate\Support\Facades\Route;

Route::domain('{account}.mygrownet.com')
    ->middleware(['web', 'stockflow.company'])
    ->where(['account' => '^(?!stockflow$)[a-z0-9-]+$'])
    ->group(function () {
        Route::middleware('auth:web')->group(function () {
            Route::middleware('stockflow.feature:recipes')->group(function () {
                Route::get('/recipes', [RecipeController::class, 'index'])->name('stockflow.sub.recipes.index');
                Route::post('/recipes', [RecipeController::class, 'store'])->name('stockflow.sub.recipes.store');
            });
            Route::middleware('stockflow.feature:wastage')->group(function () {
                Route::get('/wastage', [WastageController::class, 'index'])->name('stockflow.sub.wastage.index');
                Route::post('/wastage', [WastageController::class, 'store'])->name('stockflow.sub.wastage.store');
            });
        });
    });
