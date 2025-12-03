<?php

use App\Http\Controllers\GrowFinance\AccountController;
use App\Http\Controllers\GrowFinance\BankingController;
use App\Http\Controllers\GrowFinance\CustomerController;
use App\Http\Controllers\GrowFinance\DashboardController;
use App\Http\Controllers\GrowFinance\ExpenseController;
use App\Http\Controllers\GrowFinance\InvoiceController;
use App\Http\Controllers\GrowFinance\MessageController;
use App\Http\Controllers\GrowFinance\NotificationController;
use App\Http\Controllers\GrowFinance\ReportsController;
use App\Http\Controllers\GrowFinance\SalesController;
use App\Http\Controllers\GrowFinance\SetupController;
use App\Http\Controllers\GrowFinance\SupportController;
use App\Http\Controllers\GrowFinance\VendorController;
use App\Http\Middleware\GrowFinanceStandalone;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', GrowFinanceStandalone::class])->prefix('growfinance')->name('growfinance.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Setup
    Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
    Route::post('/setup/initialize', [SetupController::class, 'initialize'])->name('setup.initialize');

    // Accounts (Chart of Accounts)
    Route::resource('accounts', AccountController::class);

    // Sales (Quick Add)
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');

    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('/invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');

    // Expenses
    Route::resource('expenses', ExpenseController::class);

    // Customers
    Route::resource('customers', CustomerController::class);

    // Vendors
    Route::resource('vendors', VendorController::class);

    // Banking
    Route::prefix('banking')->name('banking.')->group(function () {
        Route::get('/', [BankingController::class, 'index'])->name('index');
        Route::post('/deposit', [BankingController::class, 'deposit'])->name('deposit');
        Route::post('/withdrawal', [BankingController::class, 'withdrawal'])->name('withdrawal');
        Route::post('/transfer', [BankingController::class, 'transfer'])->name('transfer');
        Route::get('/reconcile', [BankingController::class, 'reconcile'])->name('reconcile');
        Route::post('/reconcile', [BankingController::class, 'storeReconciliation'])->name('reconcile.store');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/profit-loss', [ReportsController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/balance-sheet', [ReportsController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/cash-flow', [ReportsController::class, 'cashFlow'])->name('cash-flow');
        Route::get('/trial-balance', [ReportsController::class, 'trialBalance'])->name('trial-balance');
        Route::get('/general-ledger', [ReportsController::class, 'generalLedger'])->name('general-ledger');
        Route::get('/export/{type}', [ReportsController::class, 'export'])->name('export');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Messages (using centralized messaging system with growfinance module)
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/{id}', [MessageController::class, 'show'])->name('show');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::post('/{id}/read', [MessageController::class, 'markAsRead'])->name('read');
    });

    // Support (using centralized support system with growfinance module)
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::get('/create', [SupportController::class, 'create'])->name('create');
        Route::post('/', [SupportController::class, 'store'])->name('store');
        Route::get('/{id}', [SupportController::class, 'show'])->name('show');
        Route::post('/{id}/comment', [SupportController::class, 'addComment'])->name('comment');
        Route::post('/{id}/rate', [SupportController::class, 'rate'])->name('rate');
    });
});
