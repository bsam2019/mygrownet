<?php

use App\Http\Controllers\GrowFinance\AccountController;
use App\Http\Controllers\GrowFinance\ApiTokenController;
use App\Http\Controllers\GrowFinance\BankingController;
use App\Http\Controllers\GrowFinance\BudgetController;
use App\Http\Controllers\GrowFinance\CustomerController;
use App\Http\Controllers\GrowFinance\DashboardController;
use App\Http\Controllers\GrowFinance\ExpenseController;
use App\Http\Controllers\GrowFinance\InvoiceController;
use App\Http\Controllers\GrowFinance\InvoiceTemplateController;
use App\Http\Controllers\GrowFinance\QuotationController;
use App\Http\Controllers\GrowFinance\MessageController;
use App\Http\Controllers\GrowFinance\NotificationController;
use App\Http\Controllers\GrowFinance\RecurringController;
use App\Http\Controllers\GrowFinance\ReportsController;
use App\Http\Controllers\GrowFinance\SalesController;
use App\Http\Controllers\GrowFinance\SetupController;
use App\Http\Controllers\GrowFinance\SubscriptionController;
use App\Http\Controllers\GrowFinance\SupportController;
use App\Http\Controllers\GrowFinance\TeamController;
use App\Http\Controllers\GrowFinance\VendorController;
use App\Http\Controllers\GrowFinance\WhiteLabelController;
use App\Http\Middleware\GrowFinanceStandalone;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', GrowFinanceStandalone::class])->prefix('growfinance')->name('growfinance.')->group(function () {
    // Dashboard (use /dashboard path to avoid conflict with public welcome page at /growfinance)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Setup (Legacy - simple initialization)
    Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
    Route::post('/setup/initialize', [SetupController::class, 'initialize'])->name('setup.initialize');

    // Profile Setup Wizard
    Route::prefix('setup')->name('setup.')->group(function () {
        Route::get('/wizard', [SetupController::class, 'wizard'])->name('wizard');
        Route::post('/income', [SetupController::class, 'saveIncome'])->name('income');
        Route::post('/categories', [SetupController::class, 'saveCategories'])->name('categories');
        Route::post('/goals', [SetupController::class, 'saveGoals'])->name('goals');
        Route::post('/preferences', [SetupController::class, 'savePreferences'])->name('preferences');
        Route::post('/complete', [SetupController::class, 'complete'])->name('complete');
        Route::get('/skip', [SetupController::class, 'skip'])->name('skip');
        Route::get('/summary', [SetupController::class, 'summary'])->name('summary');
    });

    // Accounts (Chart of Accounts)
    Route::resource('accounts', AccountController::class);

    // Sales (Quick Add)
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');

    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('/invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'previewPdf'])->name('invoices.preview');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'printView'])->name('invoices.print');
    Route::post('/invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.send-email');
    Route::post('/invoices/{invoice}/update-template', [InvoiceController::class, 'updateTemplate'])->name('invoices.update-template');

    // Quotations / Estimates
    Route::resource('quotations', QuotationController::class);
    Route::post('/quotations/{quotation}/send', [QuotationController::class, 'send'])->name('quotations.send');
    Route::post('/quotations/{quotation}/accept', [QuotationController::class, 'accept'])->name('quotations.accept');
    Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convertToInvoice'])->name('quotations.convert');
    Route::post('/quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('quotations.duplicate');

    // Expenses
    Route::resource('expenses', ExpenseController::class);
    Route::post('/expenses/{expense}/receipt', [ExpenseController::class, 'uploadReceipt'])->name('expenses.receipt.upload');
    Route::get('/expenses/{expense}/receipt', [ExpenseController::class, 'viewReceipt'])->name('expenses.receipt.view');
    Route::delete('/expenses/{expense}/receipt', [ExpenseController::class, 'deleteReceipt'])->name('expenses.receipt.delete');

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
        // PDF export routes (Professional+ only)
        Route::get('/pdf/profit-loss', [ReportsController::class, 'export'])->name('pdf.profit-loss')->defaults('type', 'profit-loss')->defaults('format', 'pdf');
        Route::get('/pdf/balance-sheet', [ReportsController::class, 'export'])->name('pdf.balance-sheet')->defaults('type', 'balance-sheet')->defaults('format', 'pdf');
        Route::get('/pdf/cash-flow', [ReportsController::class, 'export'])->name('pdf.cash-flow')->defaults('type', 'cash-flow')->defaults('format', 'pdf');
        Route::get('/pdf/trial-balance', [ReportsController::class, 'export'])->name('pdf.trial-balance')->defaults('type', 'trial-balance')->defaults('format', 'pdf');
        Route::get('/pdf/general-ledger', [ReportsController::class, 'export'])->name('pdf.general-ledger')->defaults('type', 'general-ledger')->defaults('format', 'pdf');
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

    // Recurring Transactions (Professional+)
    Route::prefix('recurring')->name('recurring.')->group(function () {
        Route::get('/', [RecurringController::class, 'index'])->name('index');
        Route::get('/create', [RecurringController::class, 'create'])->name('create');
        Route::post('/', [RecurringController::class, 'store'])->name('store');
        Route::get('/{recurring}', [RecurringController::class, 'show'])->name('show');
        Route::get('/{recurring}/edit', [RecurringController::class, 'edit'])->name('edit');
        Route::put('/{recurring}', [RecurringController::class, 'update'])->name('update');
        Route::delete('/{recurring}', [RecurringController::class, 'destroy'])->name('destroy');
        Route::post('/{recurring}/pause', [RecurringController::class, 'pause'])->name('pause');
        Route::post('/{recurring}/resume', [RecurringController::class, 'resume'])->name('resume');
        Route::post('/process', [RecurringController::class, 'process'])->name('process');
    });

    // Budget Tracking (Professional+)
    Route::prefix('budgets')->name('budgets.')->group(function () {
        Route::get('/', [BudgetController::class, 'index'])->name('index');
        Route::get('/create', [BudgetController::class, 'create'])->name('create');
        Route::post('/', [BudgetController::class, 'store'])->name('store');
        Route::get('/{budget}', [BudgetController::class, 'show'])->name('show');
        Route::get('/{budget}/edit', [BudgetController::class, 'edit'])->name('edit');
        Route::put('/{budget}', [BudgetController::class, 'update'])->name('update');
        Route::delete('/{budget}', [BudgetController::class, 'destroy'])->name('destroy');
        Route::post('/{budget}/recalculate', [BudgetController::class, 'recalculate'])->name('recalculate');
        Route::post('/{budget}/rollover', [BudgetController::class, 'rollover'])->name('rollover');
    });

    // Subscription & Upgrade
    Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
    Route::get('/checkout', [SubscriptionController::class, 'checkout'])->name('checkout');
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
    Route::get('/usage', [SubscriptionController::class, 'usage'])->name('usage');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    
    // Settings - Subscription (in-app with wallet)
    Route::get('/settings/subscription', [SubscriptionController::class, 'settings'])->name('settings.subscription');
    Route::post('/subscription/purchase', [SubscriptionController::class, 'purchase'])->name('subscription.purchase');

    // Team Management (Business+)
    Route::prefix('team')->name('team.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::post('/invite', [TeamController::class, 'invite'])->name('invite');
        Route::get('/accept/{token}', [TeamController::class, 'acceptInvitation'])->name('accept');
        Route::put('/{id}', [TeamController::class, 'update'])->name('update');
        Route::delete('/{id}', [TeamController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/suspend', [TeamController::class, 'suspend'])->name('suspend');
        Route::post('/{id}/reactivate', [TeamController::class, 'reactivate'])->name('reactivate');
    });

    // Invoice Templates (Professional+)
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [InvoiceTemplateController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceTemplateController::class, 'create'])->name('create');
        Route::post('/', [InvoiceTemplateController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [InvoiceTemplateController::class, 'edit'])->name('edit');
        Route::put('/{id}', [InvoiceTemplateController::class, 'update'])->name('update');
        Route::delete('/{id}', [InvoiceTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/default', [InvoiceTemplateController::class, 'setDefault'])->name('set-default');
        Route::get('/{id}/preview', [InvoiceTemplateController::class, 'preview'])->name('preview');
    });

    // API Access (Business+)
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/', [ApiTokenController::class, 'index'])->name('index');
        Route::post('/', [ApiTokenController::class, 'store'])->name('store');
        Route::post('/{id}/revoke', [ApiTokenController::class, 'revoke'])->name('revoke');
        Route::delete('/{id}', [ApiTokenController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/regenerate', [ApiTokenController::class, 'regenerate'])->name('regenerate');
        Route::get('/documentation', [ApiTokenController::class, 'documentation'])->name('documentation');
    });

    // White-Label Branding (Business+)
    Route::prefix('white-label')->name('white-label.')->group(function () {
        Route::get('/', [WhiteLabelController::class, 'index'])->name('index');
        Route::put('/', [WhiteLabelController::class, 'update'])->name('update');
        Route::post('/logo', [WhiteLabelController::class, 'uploadLogo'])->name('logo');
        Route::post('/favicon', [WhiteLabelController::class, 'uploadFavicon'])->name('favicon');
        Route::delete('/logo', [WhiteLabelController::class, 'removeLogo'])->name('logo.remove');
        Route::post('/validate-domain', [WhiteLabelController::class, 'validateDomain'])->name('validate-domain');
    });
});
