<?php

use App\Http\Controllers\Admin\BgfAdminController;
use App\Http\Controllers\MyGrowNet\BgfController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Business Growth Fund Routes
|--------------------------------------------------------------------------
*/

// Public BGF Routes (no auth required)
Route::prefix('bgf')->name('bgf.')->group(function () {
    Route::get('/about', fn() => Inertia::render('BGF/About'))->name('about');
    Route::get('/policy', fn() => Inertia::render('BGF/Policy'))->name('policy');
    Route::get('/terms', fn() => Inertia::render('BGF/Terms'))->name('terms');
    Route::get('/how-it-works', fn() => Inertia::render('BGF/HowItWorks'))->name('how-it-works');
});

// Member BGF Routes
Route::middleware(['auth', 'verified'])->prefix('mygrownet/bgf')->name('mygrownet.bgf.')->group(function () {
    Route::get('/', [BgfController::class, 'index'])->name('index');
    Route::get('/apply', [BgfController::class, 'create'])->name('create');
    Route::post('/apply', [BgfController::class, 'store'])->name('store');
    Route::get('/applications', [BgfController::class, 'applications'])->name('applications');
    Route::get('/applications/{application}', [BgfController::class, 'show'])->name('show');
    Route::post('/applications/{application}/submit', [BgfController::class, 'submit'])->name('submit');
    
    Route::get('/projects', [BgfController::class, 'projects'])->name('projects');
    Route::get('/projects/{project}', [BgfController::class, 'showProject'])->name('projects.show');
});

// Admin BGF Routes
Route::middleware(['auth', 'verified'])->prefix('admin/bgf')->name('admin.bgf.')->group(function () {
    Route::get('/dashboard', [BgfAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/analytics', [BgfAdminController::class, 'analytics'])->name('analytics');
    
    // Applications
    Route::get('/applications', [BgfAdminController::class, 'applications'])->name('applications');
    Route::get('/applications/{application}', [BgfAdminController::class, 'showApplication'])->name('applications.show');
    Route::post('/applications/{application}/evaluate', [BgfAdminController::class, 'evaluate'])->name('applications.evaluate');
    Route::post('/applications/{application}/approve', [BgfAdminController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [BgfAdminController::class, 'reject'])->name('applications.reject');
    
    // Projects
    Route::get('/projects', [BgfAdminController::class, 'projects'])->name('projects.index');
    Route::get('/projects/{project}', [BgfAdminController::class, 'showProject'])->name('projects.show');
    
    // Disbursements
    Route::get('/disbursements', [BgfAdminController::class, 'disbursements'])->name('disbursements.index');
    
    // Repayments
    Route::get('/repayments', [BgfAdminController::class, 'repayments'])->name('repayments.index');
    
    // Evaluations
    Route::get('/evaluations', [BgfAdminController::class, 'evaluations'])->name('evaluations.index');
    
    // Contracts
    Route::get('/contracts', [BgfAdminController::class, 'contracts'])->name('contracts.index');
});
