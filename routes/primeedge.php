<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrimeEdge\PublicController;
use App\Http\Controllers\PrimeEdge\Auth\RegisteredUserController;
use App\Http\Controllers\PrimeEdge\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PrimeEdge\DashboardController;
use App\Http\Controllers\PrimeEdge\ServiceController;
use App\Http\Controllers\PrimeEdge\InquiryController;
use App\Http\Controllers\PrimeEdge\EngagementController;
use App\Http\Controllers\PrimeEdge\DocumentController;
use App\Http\Controllers\PrimeEdge\ComplianceController;
use App\Http\Controllers\PrimeEdge\InvoiceController;
use App\Http\Controllers\PrimeEdge\AppointmentController;
use App\Http\Controllers\PrimeEdge\ProfileController;

$registerPrimeEdgeRoutes = function (string $prefix, string $namePrefix) {
    // Public routes
    Route::prefix($prefix)->name($namePrefix)->group(function () {
        Route::get('/', [PublicController::class, 'landing'])->name('public.landing');
        Route::get('/services', [PublicController::class, 'services'])->name('public.services');
        Route::get('/about', [PublicController::class, 'about'])->name('public.about');
        Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');
        Route::post('/contact', [PublicController::class, 'submitContact'])->name('public.contact.submit');
    });

    // Guest routes
    Route::prefix($prefix)->name($namePrefix)->middleware('guest')->group(function () {
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
        Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
    });

    // Authenticated routes
    Route::prefix($prefix)->name($namePrefix)->middleware(['identity.redirect:primeedge', 'auth:web'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Services (authenticated users can browse)
        Route::get('/services/list', [ServiceController::class, 'index'])->name('services');

        // Inquiries
        Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/create', [InquiryController::class, 'create'])->name('inquiries.create');
        Route::post('/inquiries', [InquiryController::class, 'store'])->name('inquiries.store');

        // Engagements
        Route::get('/engagements', [EngagementController::class, 'index'])->name('engagements.index');
        Route::get('/engagements/{id}', [EngagementController::class, 'show'])->name('engagements.show');

        // Documents
        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');

        // Compliance
        Route::get('/compliance', [ComplianceController::class, 'index'])->name('compliance.index');

        // Invoices
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');

        // Appointments
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Logout
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
};

// Main domain: mygrownet.com/primeedge/*
$registerPrimeEdgeRoutes(prefix: 'primeedge', namePrefix: 'primeedge.');

// Subdomain: primeedge.mygrownet.com/ — served at root with distinct name prefix
Route::domain('primeedge.mygrownet.com')->group(function () use ($registerPrimeEdgeRoutes) {
    $registerPrimeEdgeRoutes(prefix: '', namePrefix: 'primeedge.sub.');
});
