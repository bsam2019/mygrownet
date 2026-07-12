<?php

use App\Http\Controllers\GrowStorage\GuestController;
use App\Http\Controllers\Storage\SubscriptionController;
use App\Http\Controllers\Storage\FileShareController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Helper register all authenticated GrowStorage routes ──
$registerGrowStorageAuthRoutes = function (string $prefix, string $namePrefix) {
    Route::prefix($prefix)->name($namePrefix)->middleware(['auth'])->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            return Inertia::render('GrowBackup/Dashboard');
        })->name('dashboard');

        // Subscription
        Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription');
        Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
    });
};

// ============================================================
// SUBDOMAIN ROUTES (growstorage.mygrownet.com/)
// ============================================================
Route::domain('growstorage.mygrownet.com')->group(function () use ($registerGrowStorageAuthRoutes) {

    // Public welcome page at root
    Route::get('/', function () {
        return Inertia::render('GrowBackup/Welcome');
    })->name('growstorage.sub.welcome');

    // My Storage page
    Route::get('/my-storage', function () {
        return Inertia::render('Storage/Index');
    })->name('growstorage.sub.storage');

    // Authenticated routes (served at root, no prefix)
    $registerGrowStorageAuthRoutes('', 'growstorage.sub.');

    // Public file sharing routes
    Route::prefix('share')->name('growstorage.sub.share.')->withoutMiddleware(['auth'])->group(function () {
        Route::get('/{token}', [FileShareController::class, 'show'])->name('view');
        Route::post('/{token}/verify', [FileShareController::class, 'verifyPassword'])->name('verify');
        Route::get('/{token}/stream', [FileShareController::class, 'stream'])->name('stream');
        Route::get('/{token}/download', [FileShareController::class, 'download'])->name('download');
    });

    // Guest-only auth routes
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', [GuestController::class, 'login'])->name('growstorage.sub.login');
        Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
        Route::get('/register', [GuestController::class, 'register'])->name('growstorage.sub.register');
        Route::post('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);

        Route::get('/forgot-password', [GuestController::class, 'forgotPassword'])->name('growstorage.sub.password.request');
        Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->name('growstorage.sub.password.email');
        Route::get('/reset-password/{token}', [GuestController::class, 'resetPassword'])->name('growstorage.sub.password.reset');
        Route::post('/reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->name('growstorage.sub.password.update');

        Route::get('/auth/google', [\App\Http\Controllers\Auth\SocialiteController::class, 'redirectToGoogle'])->name('growstorage.sub.auth.google');
        Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\SocialiteController::class, 'handleGoogleCallback'])->name('growstorage.sub.auth.google.callback');
    });
});
