<?php

use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalContractController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalInvoiceController;
use App\Http\Controllers\Portal\PortalPasswordResetController;
use App\Http\Controllers\Portal\PortalPaymentController;
use App\Http\Controllers\Portal\PortalQuotationController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/login', [PortalAuthController::class, 'login'])->name('login');
    Route::post('/login', [PortalAuthController::class, 'storeLogin'])->name('store-login');
    Route::get('/register', [PortalAuthController::class, 'register'])->name('register');
    Route::post('/register', [PortalAuthController::class, 'storeRegister'])->name('store-register');
    Route::post('/logout', [PortalAuthController::class, 'logout'])->name('logout');

    // Password reset
    Route::get('/forgot-password', [PortalPasswordResetController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PortalPasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PortalPasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PortalPasswordResetController::class, 'resetPassword'])->name('password.update');

    // Protected routes
    Route::middleware(['auth', 'portal.auth'])->group(function () {
        Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');
        Route::get('/invoices', [PortalInvoiceController::class, 'index'])->name('invoices');
        Route::get('/invoices/{id}', [PortalInvoiceController::class, 'show'])->name('invoice-detail');
        Route::get('/quotes', [PortalQuotationController::class, 'index'])->name('quotes');
        Route::get('/quotes/{id}', [PortalQuotationController::class, 'show'])->name('quote-detail');
        Route::get('/payments', [PortalPaymentController::class, 'index'])->name('payments');
        Route::get('/contracts', [PortalContractController::class, 'index'])->name('contracts');
        Route::get('/contracts/{id}', [PortalContractController::class, 'show'])->name('contract-detail');
    });
});
