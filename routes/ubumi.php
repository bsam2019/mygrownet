<?php

use App\Http\Controllers\Ubumi\DashboardController;
use App\Http\Controllers\Ubumi\FamilyController;
use App\Http\Controllers\Ubumi\PersonController;
use App\Http\Controllers\Ubumi\RelationshipController;
use App\Http\Controllers\Ubumi\CheckInController;
use App\Infrastructure\Ubumi\Eloquent\FamilyModel;
use App\Infrastructure\Ubumi\Eloquent\PersonModel;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ubumi Routes
|--------------------------------------------------------------------------
|
| Family lineage and health check-in platform routes
|
*/

Route::middleware(['auth'])->prefix('ubumi')->name('ubumi.')->group(function () {
    
    // Dashboard - Main app entry point
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    
    // Photo upload endpoint
    Route::post('photos/upload', [PersonController::class, 'uploadPhoto'])->name('photos.upload');
    
    // Duplicate check endpoint
    Route::post('families/{familySlug}/persons/check-duplicates', [PersonController::class, 'checkDuplicates'])->name('families.persons.check-duplicates');
    
    // Family routes (using slug)
    Route::get('families', [FamilyController::class, 'index'])->name('families.index');
    Route::get('families/create', [FamilyController::class, 'create'])->name('families.create');
    Route::post('families', [FamilyController::class, 'store'])->name('families.store');
    Route::get('families/{familySlug}', [FamilyController::class, 'show'])->name('families.show');
    Route::get('families/{familySlug}/edit', [FamilyController::class, 'edit'])->name('families.edit');
    Route::put('families/{familySlug}', [FamilyController::class, 'update'])->name('families.update');
    Route::delete('families/{familySlug}', [FamilyController::class, 'destroy'])->name('families.destroy');
    
    // Standalone persons list (all persons across all families)
    Route::get('persons', [PersonController::class, 'indexAll'])->name('persons.index');
    
    // Person routes (nested under families, using slugs)
    Route::prefix('families/{familySlug}')->name('families.')->group(function () {
        Route::get('persons/create', [PersonController::class, 'create'])->name('persons.create');
        Route::post('persons', [PersonController::class, 'store'])->name('persons.store');
        Route::get('persons/{personSlug}', [PersonController::class, 'show'])->name('persons.show');
        Route::get('persons/{personSlug}/edit', [PersonController::class, 'edit'])->name('persons.edit');
        Route::put('persons/{personSlug}', [PersonController::class, 'update'])->name('persons.update');
        Route::delete('persons/{personSlug}', [PersonController::class, 'destroy'])->name('persons.destroy');
        
        // Relationship routes
        Route::post('persons/{personSlug}/relationships', [PersonController::class, 'addRelationship'])->name('persons.relationships.store');
        Route::delete('persons/{personSlug}/relationships/{relationship}', [PersonController::class, 'removeRelationship'])->name('persons.relationships.destroy');
    });
    
    // Relationship routes (nested under persons)
    Route::prefix('persons/{personSlug}')->name('persons.')->group(function () {
        Route::get('relationships', [RelationshipController::class, 'index'])->name('relationships.index');
        Route::post('relationships', [RelationshipController::class, 'store'])->name('relationships.store');
        Route::delete('relationships/{relationship}', [RelationshipController::class, 'destroy'])->name('relationships.destroy');
    });
    
    // Check-in routes
    Route::prefix('families/{familySlug}')->name('families.')->group(function () {
        // Family-wide check-in dashboard
        Route::get('check-ins', [CheckInController::class, 'familyDashboard'])->name('check-ins.dashboard');
        
        // Person check-ins
        Route::get('persons/{personSlug}/check-ins', [CheckInController::class, 'index'])->name('persons.check-ins.index');
        Route::post('persons/{personSlug}/check-ins', [CheckInController::class, 'store'])->name('persons.check-ins.store');
    });
    
    // Alert acknowledgement
    Route::post('alerts/{alert}/acknowledge', [CheckInController::class, 'acknowledgeAlert'])->name('alerts.acknowledge');
    
});
