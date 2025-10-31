<?php

use App\Http\Controllers\Admin\VentureAdminController;
use App\Http\Controllers\MyGrowNet\VentureController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Venture Builder Routes
|--------------------------------------------------------------------------
*/

// Public Venture Routes (No authentication required)
Route::prefix('ventures')->name('ventures.')->group(function () {
    // Information pages
    Route::get('/about', fn() => Inertia::render('Ventures/About'))->name('about');
    Route::get('/policy', fn() => Inertia::render('Ventures/Policy'))->name('policy');
    
    // Browse and view ventures (public access)
    Route::get('/', [VentureController::class, 'index'])->name('index');
    Route::get('/{venture:slug}', [VentureController::class, 'show'])->name('show');
});

// Member Routes (MyGrowNet) - Requires authentication
Route::middleware(['auth', 'verified'])->prefix('mygrownet')->name('mygrownet.')->group(function () {
    
    // Investment
    Route::get('/ventures/{venture}/invest', [VentureController::class, 'showInvestForm'])->name('ventures.invest');
    Route::post('/ventures/{venture}/invest', [VentureController::class, 'invest'])->name('ventures.invest.submit');
    Route::get('/ventures/investment-success/{investment}', [VentureController::class, 'investmentSuccess'])->name('ventures.investment-success');
    Route::get('/my-investments', [VentureController::class, 'myInvestments'])->name('ventures.my-investments');
    Route::get('/my-investments/{investment}', [VentureController::class, 'investmentDetails'])->name('ventures.investment-details');
    
    // Documents
    Route::get('/ventures/{venture}/documents/{document}/download', [VentureController::class, 'downloadDocument'])
        ->name('ventures.documents.download');
    
    // Portfolio
    Route::get('/portfolio', [VentureController::class, 'portfolio'])->name('ventures.portfolio');
    Route::get('/dividends', [VentureController::class, 'dividends'])->name('ventures.dividends');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin/ventures')->name('admin.ventures.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [VentureAdminController::class, 'dashboard'])->name('dashboard');
    
    // Venture Management
    Route::get('/', [VentureAdminController::class, 'index'])->name('index');
    Route::get('/create', [VentureAdminController::class, 'create'])->name('create');
    Route::post('/', [VentureAdminController::class, 'store'])->name('store');
    Route::get('/{venture}/edit', [VentureAdminController::class, 'edit'])->name('edit');
    Route::put('/{venture}', [VentureAdminController::class, 'update'])->name('update');
    Route::delete('/{venture}', [VentureAdminController::class, 'destroy'])->name('destroy');
    
    // Status Management
    Route::post('/{venture}/approve', [VentureAdminController::class, 'approve'])->name('approve');
    Route::post('/{venture}/launch-funding', [VentureAdminController::class, 'launchFunding'])->name('launch-funding');
    Route::post('/{venture}/close-funding', [VentureAdminController::class, 'closeFunding'])->name('close-funding');
    Route::post('/{venture}/activate', [VentureAdminController::class, 'activate'])->name('activate');
    
    // Investment Management
    Route::get('/investments', [VentureAdminController::class, 'allInvestments'])->name('investments.index');
    Route::get('/{venture}/investments', [VentureAdminController::class, 'investments'])->name('investments.show');
    Route::post('/investments/{investment}/confirm', [VentureAdminController::class, 'confirmInvestment'])->name('investments.confirm');
    Route::post('/investments/{investment}/refund', [VentureAdminController::class, 'refundInvestment'])->name('investments.refund');
    
    // Shareholder Management
    Route::get('/{venture}/shareholders', [VentureAdminController::class, 'shareholders'])->name('shareholders');
    Route::post('/{venture}/register-shareholders', [VentureAdminController::class, 'registerShareholders'])->name('register-shareholders');
    
    // Document Management
    Route::get('/{venture}/documents', [VentureAdminController::class, 'documents'])->name('documents');
    Route::post('/{venture}/documents', [VentureAdminController::class, 'uploadDocument'])->name('documents.upload');
    Route::delete('/documents/{document}', [VentureAdminController::class, 'deleteDocument'])->name('documents.delete');
    
    // Updates
    Route::get('/{venture}/updates', [VentureAdminController::class, 'updates'])->name('updates');
    Route::post('/{venture}/updates', [VentureAdminController::class, 'createUpdate'])->name('updates.create');
    Route::put('/updates/{update}', [VentureAdminController::class, 'updateUpdate'])->name('updates.update');
    Route::delete('/updates/{update}', [VentureAdminController::class, 'deleteUpdate'])->name('updates.delete');
    
    // Dividends
    Route::get('/{venture}/dividends', [VentureAdminController::class, 'dividends'])->name('dividends');
    Route::post('/{venture}/dividends/declare', [VentureAdminController::class, 'declareDividend'])->name('dividends.declare');
    Route::post('/dividends/{dividend}/process', [VentureAdminController::class, 'processDividend'])->name('dividends.process');
    
    // Categories
    Route::get('/categories', [VentureAdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [VentureAdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [VentureAdminController::class, 'updateCategory'])->name('categories.update');
    
    // Analytics
    Route::get('/analytics', [VentureAdminController::class, 'analytics'])->name('analytics');
});
