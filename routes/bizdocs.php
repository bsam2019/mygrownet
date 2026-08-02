<?php

use App\Http\Controllers\BizDocs\AuthController;
use App\Http\Controllers\BizDocs\BusinessProfileController;
use App\Http\Controllers\BizDocs\CustomerController;
use App\Http\Controllers\BizDocs\DocumentController;
use App\Http\Controllers\BizDocs\GuestController;
use App\Http\Controllers\BizDocs\SettingsController;
use App\Http\Controllers\BizDocs\StationeryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Helper register all authenticated BizDocs routes ──
// These are the same routes on both domains, just with different prefix/name
// $extraMiddleware is prepended BEFORE 'auth' so identity redirect runs first
$registerBizDocsAuthRoutes = function (string $prefix, string $namePrefix, string $dashboardPath, array $extraMiddleware = []) {
    Route::prefix($prefix)->name($namePrefix)->middleware(array_merge($extraMiddleware, ['auth']))->group(function () use ($dashboardPath) {

        // Subscription & plans — unified PawaPay checkout
        Route::get('/subscription', fn() => redirect()->route('subscriptions.plans', ['module' => 'bizdocs']))->name('subscription');

        // Business Profile Setup
        Route::get('/setup', [BusinessProfileController::class, 'setup'])->name('setup');
        Route::post('/setup', [BusinessProfileController::class, 'store'])->name('setup.store');
        Route::get('/profile/edit', [BusinessProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [BusinessProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile', [BusinessProfileController::class, 'update'])->name('profile.update.post');

        // Dashboard
        Route::get($dashboardPath, [BusinessProfileController::class, 'dashboard'])->name('dashboard');

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
};

// ============================================================
// 1. MAIN DOMAIN ROUTES (mygrownet.com/bizdocs/*)
// ============================================================
$registerBizDocsAuthRoutes('bizdocs', 'bizdocs.', '/dashboard');

// ============================================================
// 2. SUBDOMAIN ROUTES (bizdocs.mygrownet.com/)
// ============================================================
Route::domain('bizdocs.mygrownet.com')->group(function () use ($registerBizDocsAuthRoutes) {

    // Public welcome page at root
    Route::get('/', function () {
        if (Auth::guard('web')->check()) {
            return redirect()->route('bizdocs.sub.dashboard');
        }

        return Inertia::render('BizDocs/Welcome');
    })->name('bizdocs.sub.welcome');

    // Authenticated routes (served at root, no prefix)
    // identity.redirect:bizdocs prepended so unauthenticated users go to auth.mygrownet.com
    $registerBizDocsAuthRoutes('', 'bizdocs.sub.', '/dashboard', ['identity.redirect:bizdocs']);

    // Guest-only auth routes — all redirect to MyGrow Identity Gateway
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('bizdocs.sub.login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/register', [AuthController::class, 'showRegister'])->name('bizdocs.sub.register');
        Route::post('/register', [AuthController::class, 'register']);

        // Password reset — delegate to identity gateway
        Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('bizdocs.sub.password.request');
        Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('bizdocs.sub.password.reset');

        // Social Login - Google
        Route::get('/auth/google', [\App\Http\Controllers\Auth\SocialiteController::class, 'redirectToGoogle'])->name('bizdocs.sub.auth.google');
        Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\SocialiteController::class, 'handleGoogleCallback'])->name('bizdocs.sub.auth.google.callback');
    });
});
