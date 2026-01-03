<?php

use App\Http\Controllers\QuickInvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Quick Invoice Routes
|--------------------------------------------------------------------------
|
| Public routes for the Quick Invoice Generator tool.
| No authentication required for basic functionality.
|
*/

Route::prefix('quick-invoice')->name('quick-invoice.')->group(function () {
    // Public pages
    Route::get('/', [QuickInvoiceController::class, 'index'])->name('index');
    Route::get('/create', [QuickInvoiceController::class, 'create'])->name('create');
    Route::get('/history', [QuickInvoiceController::class, 'history'])->name('history');
    
    // API endpoints
    Route::post('/generate', [QuickInvoiceController::class, 'generate'])->name('generate');
    Route::post('/upload-logo', [QuickInvoiceController::class, 'uploadLogo'])->name('upload-logo');
    Route::post('/upload-signature', [QuickInvoiceController::class, 'uploadSignature'])->name('upload-signature');
    Route::post('/send-email', [QuickInvoiceController::class, 'sendEmail'])->name('send-email');
    
    // PDF routes - generated on-demand
    Route::get('/download/{id}', [QuickInvoiceController::class, 'downloadPdf'])->name('download');
    Route::get('/view/{id}', [QuickInvoiceController::class, 'viewPdf'])->name('view');
    Route::get('/whatsapp/{id}', [QuickInvoiceController::class, 'getWhatsAppLink'])->name('whatsapp');
    
    // Legacy route alias for backward compatibility
    Route::get('/pdf/{id}', [QuickInvoiceController::class, 'downloadPdf'])->name('pdf');
    
    Route::delete('/{id}', [QuickInvoiceController::class, 'destroy'])->name('destroy');
    
    // Authenticated routes for profile management
    Route::middleware('auth')->group(function () {
        Route::post('/save-profile', [QuickInvoiceController::class, 'saveProfile'])->name('save-profile');
        Route::get('/profile', [QuickInvoiceController::class, 'getProfile'])->name('profile');
    });
});
