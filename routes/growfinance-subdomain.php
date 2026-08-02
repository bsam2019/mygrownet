<?php

use App\Http\Controllers\GrowFinance\AuthController;
use App\Http\Controllers\GrowFinance\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| GrowFinance Subdomain Routes
|--------------------------------------------------------------------------
| These routes handle growfinance.mygrownet.com subdomain
| Routes are accessible without the /growfinance prefix on the subdomain
*/

Route::domain('growfinance.mygrownet.com')->name('growfinance.subdomain.')->group(function () {
    // Landing Page
    Route::get('/', function () {
        if (Auth::guard('web')->check()) {
            return redirect()->route('growfinance.subdomain.dashboard');
        }

        return Inertia::render('GrowFinance/Landing', [
            'routePrefix' => 'growfinance.subdomain'
        ]);
    })->name('landing');

    // Authentication Routes (Guest only) — all redirect to MyGrow Identity Gateway
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);

        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register']);

        // Social Login - Google (subdomain)
        Route::get('/auth/google', [\App\Http\Controllers\Auth\SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
        Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');
    });

    // Logout (Authenticated only)
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    // Protected GrowFinance Routes
    // identity.redirect:growfinance prepended so unauthenticated users go to auth.mygrownet.com
    Route::middleware(['identity.redirect:growfinance', 'auth', 'verified'])
        ->group(function () {
            
            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            
            // Placeholder routes for future implementation
            Route::get('/accounts', function () {
                return Inertia::render('GrowFinance/Accounts/Index');
            })->name('accounts.index');
            
            Route::get('/transactions', function () {
                return Inertia::render('GrowFinance/Transactions/Index');
            })->name('transactions.index');
            
            Route::get('/reports', function () {
                return Inertia::render('GrowFinance/Reports/Index');
            })->name('reports.index');
        });
});
