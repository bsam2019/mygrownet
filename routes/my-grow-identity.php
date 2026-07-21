<?php

use App\Http\Controllers\Identity\EmailVerificationController;
use App\Http\Controllers\Identity\LoginController;
use App\Http\Controllers\Identity\LogoutController;
use App\Http\Controllers\Identity\PasswordResetController;
use App\Http\Controllers\Identity\RegisterController;
use App\Http\Controllers\Identity\SessionValidationController;
use App\Http\Controllers\Identity\TwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MyGrow Identity Gateway Routes
|--------------------------------------------------------------------------
|
| These routes are served exclusively by auth.mygrownet.com.
| They are the ONLY authentication routes on the entire platform.
| No application may expose its own login, register, or password reset pages.
|
| Served via DetectSubdomain middleware when host is auth.mygrownet.com.
|
*/

// Guest routes — unauthenticated users
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('identity.login');
    Route::post('/login', [LoginController::class, 'login'])->name('identity.login.store');

    Route::get('/register', [RegisterController::class, 'showRegister'])->name('identity.register');
    Route::post('/register', [RegisterController::class, 'register'])->name('identity.register.store');

    Route::get('/password/reset', [PasswordResetController::class, 'showForgotPassword'])->name('identity.password.request');
    Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])->name('identity.password.email');
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('identity.password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->name('identity.password.update');
});

// Authenticated routes
Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('identity.logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'showNotice'])->name('identity.verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('identity.verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('identity.verification.send');

    Route::get('/2fa/setup', [TwoFactorController::class, 'showSetup'])->name('identity.2fa.setup');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('identity.2fa.verify');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('identity.2fa.disable');
});

// Session validation API — used by applications to verify tokens
Route::get('/session/validate', [SessionValidationController::class, 'validate'])
    ->name('identity.session.validate');
