<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GrowStart\DashboardController;
use App\Http\Controllers\GrowStart\JourneyController;
use App\Http\Controllers\GrowStart\StageController;
use App\Http\Controllers\GrowStart\TaskController;
use App\Http\Controllers\GrowStart\TemplateController;
use App\Http\Controllers\GrowStart\ProviderController;
use App\Http\Controllers\GrowStart\BadgeController;

/*
|--------------------------------------------------------------------------
| GrowStart Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('growstart')->name('growstart.')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Onboarding
    Route::get('/onboarding', [JourneyController::class, 'onboarding'])->name('onboarding');
    Route::post('/onboarding', [JourneyController::class, 'startJourney'])->name('onboarding.start');
    
    // Journey Management
    Route::get('/journey', [JourneyController::class, 'show'])->name('journey.show');
    Route::put('/journey', [JourneyController::class, 'update'])->name('journey.update');
    Route::post('/journey/pause', [JourneyController::class, 'pause'])->name('journey.pause');
    Route::post('/journey/resume', [JourneyController::class, 'resume'])->name('journey.resume');
    Route::get('/journey/progress', [JourneyController::class, 'progress'])->name('journey.progress');
    
    // Stages
    Route::get('/stages', [StageController::class, 'index'])->name('stages.index');
    Route::get('/stages/{slug}', [StageController::class, 'show'])->name('stages.show');
    
    // Tasks
    Route::get('/stages/{slug}/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{id}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('/tasks/{id}/skip', [TaskController::class, 'skip'])->name('tasks.skip');
    Route::post('/tasks/{id}/start', [TaskController::class, 'start'])->name('tasks.start');
    Route::put('/tasks/{id}/notes', [TaskController::class, 'updateNotes'])->name('tasks.notes');
    
    // Templates
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/{id}', [TemplateController::class, 'show'])->name('templates.show');
    Route::get('/templates/{id}/download', [TemplateController::class, 'download'])->name('templates.download');
    
    // Providers Directory
    Route::get('/providers', [ProviderController::class, 'index'])->name('providers.index');
    Route::get('/providers/{id}', [ProviderController::class, 'show'])->name('providers.show');
    
    // Badges
    Route::get('/badges', [BadgeController::class, 'index'])->name('badges.index');
    Route::get('/badges/earned', [BadgeController::class, 'earned'])->name('badges.earned');
});

// API Routes for GrowStart
Route::middleware(['auth:sanctum'])->prefix('api/growstart')->name('api.growstart.')->group(function () {
    
    // Journey API
    Route::get('/journey', [JourneyController::class, 'apiShow'])->name('journey.show');
    Route::post('/journey', [JourneyController::class, 'apiStore'])->name('journey.store');
    Route::get('/journey/progress', [JourneyController::class, 'apiProgress'])->name('journey.progress');
    
    // Industries & Countries
    Route::get('/industries', [JourneyController::class, 'industries'])->name('industries');
    Route::get('/countries', [JourneyController::class, 'countries'])->name('countries');
    
    // Stages API
    Route::get('/stages', [StageController::class, 'apiIndex'])->name('stages.index');
    Route::get('/stages/{slug}', [StageController::class, 'apiShow'])->name('stages.show');
    
    // Tasks API
    Route::get('/stages/{slug}/tasks', [TaskController::class, 'apiIndex'])->name('tasks.index');
    Route::post('/tasks/{id}/complete', [TaskController::class, 'apiComplete'])->name('tasks.complete');
    Route::post('/tasks/{id}/skip', [TaskController::class, 'apiSkip'])->name('tasks.skip');
    
    // Templates API
    Route::get('/templates', [TemplateController::class, 'apiIndex'])->name('templates.index');
    
    // Providers API
    Route::get('/providers', [ProviderController::class, 'apiIndex'])->name('providers.index');
    
    // Badges API
    Route::get('/badges', [BadgeController::class, 'apiIndex'])->name('badges.index');
});
