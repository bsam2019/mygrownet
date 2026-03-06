<?php

use App\Http\Controllers\CMS\AuthController;
use App\Http\Controllers\CMS\AccountingController;
use App\Http\Controllers\CMS\CustomerController;
use App\Http\Controllers\CMS\DashboardController;
use App\Http\Controllers\CMS\ExpenseController;
use App\Http\Controllers\CMS\ExpenseCategoryController;
use App\Http\Controllers\CMS\InventoryController;
use App\Http\Controllers\CMS\InvoiceController;
use App\Http\Controllers\CMS\JobController;
use App\Http\Controllers\CMS\PaymentController;
use App\Http\Controllers\CMS\QuotationController;
use App\Http\Controllers\CMS\ReportController;
use App\Http\Controllers\CMS\BudgetController;
use App\Http\Controllers\CMS\ScheduledReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| CMS Subdomain Routes
|--------------------------------------------------------------------------
| These routes handle cms.mygrownet.com subdomain
| Routes are accessible without the /cms prefix on the subdomain
*/

Route::domain('cms.mygrownet.com')->name('cms.subdomain.')->group(function () {
    // Landing Page
    Route::get('/', function () {
        return Inertia::render('CMS/Landing');
    })->name('landing');

    // Offline Page (for PWA)
    Route::get('/offline', function () {
        return Inertia::render('CMS/Offline');
    })->name('offline');

    // Email Unsubscribe (Public)
    Route::get('/email/unsubscribe', [\App\Http\Controllers\CMS\EmailSettingsController::class, 'unsubscribe'])->name('email.unsubscribe');

    // Authentication Routes (Guest only)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register']);
    });

    // Logout (Authenticated only)
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    // Password Change (Authenticated only)
    Route::middleware('auth')->group(function () {
        Route::get('/password/change', [AuthController::class, 'showPasswordChange'])->name('password.change');
        Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.update');
    });


    // Protected CMS Routes
    Route::middleware(['auth', 'verified', 'cms.auto-login', 'cms.access', \App\Http\Middleware\CMS\EnforcePasswordChange::class])
        ->group(function () {
            
            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Jobs
            Route::prefix('jobs')->name('jobs.')->group(function () {
                Route::get('/', [JobController::class, 'index'])->name('index');
                Route::get('/create', [JobController::class, 'create'])->name('create');
                Route::post('/', [JobController::class, 'store'])->name('store');
                Route::get('/{job}', [JobController::class, 'show'])->name('show');
                Route::post('/{job}/assign', [JobController::class, 'assign'])->name('assign');
                Route::post('/{job}/complete', [JobController::class, 'complete'])->name('complete');
                Route::post('/{job}/attachments', [JobController::class, 'uploadAttachment'])->name('attachments.upload');
            });

            // Customers
            Route::resource('customers', CustomerController::class);

            // Invoices
            Route::prefix('invoices')->name('invoices.')->group(function () {
                Route::get('/', [InvoiceController::class, 'index'])->name('index');
                Route::get('/create', [InvoiceController::class, 'create'])->name('create');
                Route::post('/', [InvoiceController::class, 'store'])->name('store');
                Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
                Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
                Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
                Route::post('/{invoice}/send', [InvoiceController::class, 'send'])->name('send');
                Route::get('/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('pdf');
            });

            // Payments
            Route::prefix('payments')->name('payments.')->group(function () {
                Route::get('/', [PaymentController::class, 'index'])->name('index');
                Route::get('/create', [PaymentController::class, 'create'])->name('create');
                Route::post('/', [PaymentController::class, 'store'])->name('store');
            });

            // Quotations
            Route::prefix('quotations')->name('quotations.')->group(function () {
                Route::get('/', [QuotationController::class, 'index'])->name('index');
                Route::get('/create', [QuotationController::class, 'create'])->name('create');
                Route::post('/', [QuotationController::class, 'store'])->name('store');
            });

            // Inventory
            Route::prefix('inventory')->name('inventory.')->group(function () {
                Route::get('/', [InventoryController::class, 'index'])->name('index');
                Route::get('/create', [InventoryController::class, 'create'])->name('create');
                Route::post('/', [InventoryController::class, 'store'])->name('store');
            });

            // Expenses
            Route::prefix('expenses')->name('expenses.')->group(function () {
                Route::get('/', [ExpenseController::class, 'index'])->name('index');
                Route::get('/create', [ExpenseController::class, 'create'])->name('create');
                Route::post('/', [ExpenseController::class, 'store'])->name('store');
            });

            // Reports
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

            // Budgets
            Route::resource('budgets', BudgetController::class);

            // Settings
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [\App\Http\Controllers\CMS\SettingsController::class, 'index'])->name('index');
            });
        });
});
