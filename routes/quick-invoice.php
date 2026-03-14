<?php

use App\Http\Controllers\QuickInvoiceController;
use App\Http\Controllers\QuickInvoice\DashboardController;
use App\Http\Controllers\QuickInvoice\DesignStudioController;
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
    // Main entry point - Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard'); // Alias for backward compatibility
    
    // Design Studio - Pro tier and above
    Route::middleware('auth')->group(function () {
        Route::get('/design-studio', [DesignStudioController::class, 'index'])->name('design-studio');
        Route::get('/design-studio/create', [DesignStudioController::class, 'create'])->name('design-studio.create');
        Route::post('/design-studio/store', [DesignStudioController::class, 'store'])->name('design-studio.store');
        Route::get('/design-studio/{customTemplate}/edit', [DesignStudioController::class, 'edit'])->name('design-studio.edit');
        Route::post('/design-studio/{customTemplate}/update', [DesignStudioController::class, 'update'])->name('design-studio.update');
        Route::delete('/design-studio/{customTemplate}', [DesignStudioController::class, 'destroy'])->name('design-studio.destroy');
        Route::post('/design-studio/{customTemplate}/duplicate', [DesignStudioController::class, 'duplicate'])->name('design-studio.duplicate');
    });
    
    // Document management pages
    Route::get('/create', [QuickInvoiceController::class, 'create'])->name('create');
    Route::get('/edit/{id}', [QuickInvoiceController::class, 'edit'])->name('edit');
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
        
        // Attachment library routes
        Route::get('/attachment-library', [QuickInvoiceController::class, 'getAttachmentLibrary'])->name('attachment-library');
        Route::get('/attachment-library/{id}/download', [QuickInvoiceController::class, 'downloadLibraryAttachment'])->name('attachment-library.download');
        Route::post('/attachment-library', [QuickInvoiceController::class, 'saveToLibrary'])->name('attachment-library.save');
        Route::put('/attachment-library/{id}', [QuickInvoiceController::class, 'updateLibraryAttachment'])->name('attachment-library.update');
        Route::delete('/attachment-library/{id}', [QuickInvoiceController::class, 'deleteFromLibrary'])->name('attachment-library.delete');
    });
});
