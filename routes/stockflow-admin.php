<?php

use App\Http\Controllers\StockAudit\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\StockAudit\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\StockAudit\Admin\AuthController as AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('stockflow-admin')
    ->middleware('web')
    ->name('stockflow.admin.')
    ->group(function () {
        // Admin login (uses stockflow guard)
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.store');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Protected admin routes
        Route::middleware('stockflow.admin')->group(function () {
            Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

            Route::get('/companies', [AdminCompanyController::class, 'index'])->name('companies.index');
            Route::get('/companies/create', [AdminCompanyController::class, 'create'])->name('companies.create');
            Route::post('/companies', [AdminCompanyController::class, 'store'])->name('companies.store');
            Route::get('/companies/{company}', [AdminCompanyController::class, 'show'])->name('companies.show');
            Route::put('/companies/{company}', [AdminCompanyController::class, 'update'])->name('companies.update');
        });
    });
