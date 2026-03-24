<?php

use App\Http\Controllers\BizDocs\BusinessProfileController;
use App\Http\Controllers\BizDocs\CustomerController;
use App\Http\Controllers\BizDocs\DocumentController;
use App\Http\Controllers\BizDocs\SettingsController;
use App\Http\Controllers\BizDocs\StationeryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('bizdocs')->name('bizdocs.')->group(function () {
    
    // Business Profile Setup
    Route::get('/setup', [BusinessProfileController::class, 'setup'])->name('setup');
    Route::post('/setup', [BusinessProfileController::class, 'store'])->name('setup.store');
    Route::get('/profile/edit', [BusinessProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [BusinessProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile', [BusinessProfileController::class, 'update'])->name('profile.update.post');

    // Dashboard
    Route::get('/dashboard', [BusinessProfileController::class, 'dashboard'])->name('dashboard');

    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('update');
    });

    // Documents
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/create', [DocumentController::class, 'create'])->name('create');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('/{id}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DocumentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DocumentController::class, 'update'])->name('update');
        Route::post('/{id}/finalize', [DocumentController::class, 'finalize'])->name('finalize');
        Route::post('/{id}/record-payment', [DocumentController::class, 'recordPayment'])->name('record-payment');
        Route::post('/{id}/cancel', [DocumentController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/void', [DocumentController::class, 'void'])->name('void');
        Route::post('/{id}/convert-to-invoice', [DocumentController::class, 'convertToInvoice'])->name('convert-to-invoice');
        Route::post('/{id}/duplicate', [DocumentController::class, 'duplicate'])->name('duplicate');
        Route::get('/{id}/status-history', [DocumentController::class, 'statusHistory'])->name('status-history');
        Route::get('/{id}/download-pdf', [DocumentController::class, 'downloadPdf'])->name('download-pdf');
        Route::get('/{id}/preview-pdf', [DocumentController::class, 'previewPdf'])->name('preview-pdf');
        Route::post('/{id}/share-pdf', [DocumentController::class, 'sharePdf'])->name('share-pdf');
    });

    // Templates
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/gallery', [DocumentController::class, 'templateGallery'])->name('gallery');
        Route::post('/{id}/set-default', [DocumentController::class, 'setDefaultTemplate'])->name('set-default');
        Route::get('/{id}/preview', [DocumentController::class, 'templatePreview'])->name('preview');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/numbering', [SettingsController::class, 'numbering'])->name('numbering');
        Route::post('/numbering', [SettingsController::class, 'updateNumbering'])->name('numbering.update');
    });

    // Stationery Generator
    Route::prefix('stationery')->name('stationery.')->group(function () {
        Route::get('/', [StationeryController::class, 'index'])->name('index');
        Route::post('/preview', [StationeryController::class, 'preview'])->name('preview');
        Route::post('/generate', [StationeryController::class, 'generate'])->name('generate');
    });
});
