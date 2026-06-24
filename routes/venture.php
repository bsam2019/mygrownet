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
// Protected by module:venture_builder middleware
Route::middleware(['module:venture_builder'])->prefix('ventures')->name('ventures.')->group(function () {
    // Information pages
    Route::get('/about', fn() => Inertia::render('Ventures/About'))->name('about');
    Route::get('/policy', fn() => Inertia::render('Ventures/Policy'))->name('policy');
    
    // Browse and view ventures (public access)
    Route::get('/', [VentureController::class, 'index'])->name('index');
    Route::get('/{venture:slug}', [VentureController::class, 'show'])->name('show');
});

// Member Routes (MyGrowNet) - Requires authentication
Route::middleware(['auth', 'verified', 'module:venture_builder'])->prefix('mygrownet')->name('mygrownet.')->group(function () {
    
    // Investment (with rate limiting: 3 attempts per 5 minutes per user)
    Route::get('/ventures/{venture}/invest', [VentureController::class, 'showInvestForm'])->name('ventures.invest');
    Route::post('/ventures/{venture}/invest', [VentureController::class, 'invest'])->name('ventures.invest.submit')
        ->middleware('throttle:3,5');
    Route::get('/ventures/investment-success/{investment}', [VentureController::class, 'investmentSuccess'])->name('ventures.investment-success');
    Route::get('/my-investments', [VentureController::class, 'myInvestments'])->name('ventures.my-investments');
    Route::get('/my-investments/{investment}', [VentureController::class, 'investmentDetails'])->name('ventures.investment-details');
    
    // Documents
    Route::get('/ventures/{venture}/documents/{document}/download', [VentureController::class, 'downloadDocument'])
        ->name('ventures.documents.download');
    
    // Portfolio
    Route::get('/portfolio', [VentureController::class, 'portfolio'])->name('ventures.portfolio');
    Route::get('/dividends', [VentureController::class, 'dividends'])->name('ventures.dividends');

    // Withdrawal
    Route::post('/investments/{investment}/withdraw', [VentureController::class, 'withdraw'])->name('ventures.withdraw')
        ->middleware('throttle:2,5');

    // Share Transfers
    Route::post('/ventures/{venture}/transfers', [VentureController::class, 'requestTransfer'])->name('ventures.transfers.request');
    Route::get('/transfers', [VentureController::class, 'myTransfers'])->name('ventures.transfers');

    // Voting
    Route::get('/ventures/{venture}/resolutions', [VentureController::class, 'resolutions'])->name('ventures.resolutions');
    Route::get('/ventures/{venture}/resolutions/{resolution}', [VentureController::class, 'showResolution'])->name('ventures.resolutions.show');
    Route::post('/resolutions/{resolution}/vote', [VentureController::class, 'castVote'])->name('ventures.resolutions.vote');
});

// Admin Routes
Route::middleware(['auth', 'admin', 'module:venture_builder'])->prefix('admin/ventures')->name('admin.ventures.')->group(function () {
    
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
    Route::post('/{venture}/register-company', [VentureAdminController::class, 'registerCompany'])->name('register-company')
        ->middleware('throttle:5,1');
    
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
    Route::post('/categories', [VentureAdminController::class, 'storeCategory'])->name('categories.store')
        ->middleware('throttle:10,1');
    Route::put('/categories/{category}', [VentureAdminController::class, 'updateCategory'])->name('categories.update')
        ->middleware('throttle:10,1');
    
    // Share Transfer Management
    Route::get('/transfers', [VentureAdminController::class, 'allTransfers'])->name('transfers');
    Route::get('/{venture}/transfers', [VentureAdminController::class, 'transfers'])->name('transfers.show');
    Route::post('/transfers/{transfer}/approve', [VentureAdminController::class, 'approveTransfer'])->name('transfers.approve');
    Route::post('/transfers/{transfer}/reject', [VentureAdminController::class, 'rejectTransfer'])->name('transfers.reject');

    // Resolution Management
    Route::get('/{venture}/resolutions', [VentureAdminController::class, 'resolutions'])->name('resolutions');
    Route::post('/{venture}/resolutions', [VentureAdminController::class, 'createResolution'])->name('resolutions.create');
    Route::post('/resolutions/{resolution}/open-voting', [VentureAdminController::class, 'openVoting'])->name('resolutions.open-voting');
    Route::post('/resolutions/{resolution}/tally', [VentureAdminController::class, 'tallyResults'])->name('resolutions.tally');

    // Analytics
    Route::get('/analytics', [VentureAdminController::class, 'analytics'])->name('analytics');
});
