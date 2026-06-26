<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZamStay\PropertyController;
use App\Http\Controllers\ZamStay\BookingController;
use App\Http\Controllers\ZamStay\HostController;
use App\Http\Controllers\ZamStay\ReviewController;
use App\Http\Controllers\ZamStay\AgentController;
use App\Http\Controllers\ZamStay\AuthController;

/*
|--------------------------------------------------------------------------
| ZamStay Routes
|--------------------------------------------------------------------------
|
| Zambia Stays Redefined by MyGrowNet
| - Subdomain: zamstay.mygrownet.com → routes served at root /
| - Main domain: mygrownet.com/zamstay → routes served under /zamstay prefix
|
*/

// ── Main domain routes (mygrownet.com/zamstay/*) ──
// These share the same controllers as subdomain routes with different prefix/name

$registerZamStayPublicRoutes = function (string $prefix, string $namePrefix) {
    Route::prefix($prefix)->name($namePrefix)->group(function () {
        Route::get('/', [PropertyController::class, 'home'])->name('home');
        Route::get('/search', [PropertyController::class, 'search'])->name('search');
        Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
        Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
        Route::get('/availability', [BookingController::class, 'checkAvailability'])->name('availability');
    });
};

$registerZamStayAuthRoutes = function (string $prefix, string $namePrefix) {
    Route::prefix($prefix)->name($namePrefix)->middleware('auth')->group(function () {
        // Checkout
        Route::get('/properties/{property}/checkout', [BookingController::class, 'checkout'])->name('checkout');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

        // My Bookings
        Route::get('/bookings', [BookingController::class, 'myBookings'])->name('bookings.index');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings/{booking}/pay', [BookingController::class, 'pay'])->name('bookings.pay');
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');

        // Reviews
        Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

        // Agent routes
        Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
        Route::get('/agents/{agent}', [AgentController::class, 'show'])->name('agents.show');
        Route::get('/agent/register', [AgentController::class, 'registerForm'])->name('agent.register-form');
        Route::post('/agent/register', [AgentController::class, 'register'])->name('agent.register');
        Route::get('/agent/dashboard', [AgentController::class, 'dashboard'])->name('agent.dashboard');

        // Host routes
        Route::prefix('host')->name('host.')->group(function () {
            Route::post('/upload-image', [HostController::class, 'uploadImage'])->name('upload-image');
            Route::get('/dashboard', [HostController::class, 'dashboard'])->name('dashboard');
            Route::get('/properties', [HostController::class, 'properties'])->name('properties');
            Route::get('/properties/create', [HostController::class, 'createProperty'])->name('properties.create');
            Route::post('/properties', [HostController::class, 'storeProperty'])->name('properties.store');
            Route::get('/properties/{property}/edit', [HostController::class, 'editProperty'])->name('properties.edit');
            Route::put('/properties/{property}', [HostController::class, 'updateProperty'])->name('properties.update');
            Route::get('/bookings', [HostController::class, 'bookings'])->name('bookings');
        });
    });
};

// ============================================================
// 1. MAIN DOMAIN ROUTES (mygrownet.com/zamstay/*)
// ============================================================

$registerZamStayPublicRoutes(
    prefix: 'zamstay',
    namePrefix: 'zamstay.'
);

$registerZamStayAuthRoutes(
    prefix: 'zamstay',
    namePrefix: 'zamstay.'
);

// Main domain guest auth routes
Route::prefix('zamstay')->name('zamstay.')->middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Main domain logout
Route::post('/zamstay/logout', [AuthController::class, 'logout'])->name('zamstay.logout')->middleware('auth');


// ============================================================
// 2. SUBDOMAIN ROUTES (zamstay.mygrownet.com/)
// ============================================================

Route::domain('zamstay.mygrownet.com')->group(function () use ($registerZamStayPublicRoutes, $registerZamStayAuthRoutes) {

    // Subdomain routes use 'zamstay.sub.' name prefix to avoid conflicts with main domain
    $registerZamStayPublicRoutes(
        prefix: '',
        namePrefix: 'zamstay.sub.'
    );

    $registerZamStayAuthRoutes(
        prefix: '',
        namePrefix: 'zamstay.sub.'
    );

    // Guest auth routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('zamstay.sub.login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/register', [AuthController::class, 'showRegister'])->name('zamstay.sub.register');
        Route::post('/register', [AuthController::class, 'register']);

        // Social Login - Google (subdomain)
        Route::get('/auth/google', [\App\Http\Controllers\Auth\SocialiteController::class, 'redirectToGoogle'])->name('zamstay.sub.auth.google');
        Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\SocialiteController::class, 'handleGoogleCallback'])->name('zamstay.sub.auth.google.callback');
    });

    // Logout (authenticated)
    Route::post('/logout', [AuthController::class, 'logout'])->name('zamstay.sub.logout')->middleware('auth');
});
