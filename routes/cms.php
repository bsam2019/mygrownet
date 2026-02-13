<?php

use App\Http\Controllers\CMS\AuthController;
use App\Http\Controllers\CMS\AccountingController;
use App\Http\Controllers\CMS\CustomerController;
use App\Http\Controllers\CMS\DashboardController;
use App\Http\Controllers\CMS\ExpenseController;
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
| CMS Routes
|--------------------------------------------------------------------------
|
| Routes for the Company Management System (SME Operating System)
|
*/

// Public CMS Routes (Landing & Auth)
Route::prefix('cms')->name('cms.')->group(function () {
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
});

// Protected CMS Routes
Route::prefix('cms')
    ->name('cms.')
    ->middleware(['auth', 'verified', 'cms.access', \App\Http\Middleware\CMS\EnforcePasswordChange::class])
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
        Route::post('/customers/{customer}/documents', [CustomerController::class, 'uploadDocument'])->name('customers.documents.upload');
        Route::post('/customers/{customer}/contacts', [CustomerController::class, 'storeContact'])->name('customers.contacts.store');
        Route::put('/customers/{customer}/contacts/{contact}', [CustomerController::class, 'updateContact'])->name('customers.contacts.update');
        Route::delete('/customers/{customer}/contacts/{contact}', [CustomerController::class, 'deleteContact'])->name('customers.contacts.delete');

        // Invoices
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::get('/create', [InvoiceController::class, 'create'])->name('create');
            Route::post('/', [InvoiceController::class, 'store'])->name('store');
            Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
            Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
            Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
            Route::post('/{invoice}/send', [InvoiceController::class, 'send'])->name('send');
            Route::post('/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
            Route::post('/{invoice}/void', [InvoiceController::class, 'void'])->name('void');
            Route::get('/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('pdf');
            Route::get('/{invoice}/preview', [InvoiceController::class, 'previewPdf'])->name('preview');
        });

        // Recurring Invoices
        Route::prefix('recurring-invoices')->name('recurring-invoices.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\RecurringInvoiceController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\RecurringInvoiceController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\RecurringInvoiceController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\CMS\RecurringInvoiceController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\CMS\RecurringInvoiceController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\CMS\RecurringInvoiceController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\CMS\RecurringInvoiceController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/pause', [\App\Http\Controllers\CMS\RecurringInvoiceController::class, 'pause'])->name('pause');
            Route::post('/{id}/resume', [\App\Http\Controllers\CMS\RecurringInvoiceController::class, 'resume'])->name('resume');
            Route::post('/{id}/cancel', [\App\Http\Controllers\CMS\RecurringInvoiceController::class, 'cancel'])->name('cancel');
            Route::post('/{id}/generate', [\App\Http\Controllers\CMS\RecurringInvoiceController::class, 'generateNow'])->name('generate');
        });

        // Payments
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/create', [PaymentController::class, 'create'])->name('create');
            Route::post('/', [PaymentController::class, 'store'])->name('store');
            Route::get('/daily-summary', [PaymentController::class, 'dailySummary'])->name('daily-summary');
            Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
            Route::post('/{payment}/void', [PaymentController::class, 'void'])->name('void');
            Route::post('/{payment}/allocate', [PaymentController::class, 'allocate'])->name('allocate');
            Route::get('/{payment}/receipt/download', [PaymentController::class, 'downloadReceipt'])->name('receipt.download');
            Route::get('/{payment}/receipt/preview', [PaymentController::class, 'previewReceipt'])->name('receipt.preview');
        });

        // Customer Credit
        Route::prefix('customers/{customer}/credit')->name('customers.credit.')->group(function () {
            Route::get('/', [PaymentController::class, 'customerCredit'])->name('summary');
            Route::post('/apply', [PaymentController::class, 'applyCredit'])->name('apply');
        });

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        // Budgets
        Route::resource('budgets', BudgetController::class);

        // Scheduled Reports
        Route::resource('scheduled-reports', ScheduledReportController::class)->except(['show']);
        Route::post('/scheduled-reports/{id}/toggle', [ScheduledReportController::class, 'toggle'])->name('scheduled-reports.toggle');

        // Expenses
        Route::prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', [ExpenseController::class, 'index'])->name('index');
            Route::post('/', [ExpenseController::class, 'store'])->name('store');
            Route::post('/{expense}/approve', [ExpenseController::class, 'approve'])->name('approve');
            Route::post('/{expense}/reject', [ExpenseController::class, 'reject'])->name('reject');
        });

        // Quotations
        Route::prefix('quotations')->name('quotations.')->group(function () {
            Route::get('/', [QuotationController::class, 'index'])->name('index');
            Route::get('/create', [QuotationController::class, 'create'])->name('create');
            Route::post('/', [QuotationController::class, 'store'])->name('store');
            Route::get('/{quotation}', [QuotationController::class, 'show'])->name('show');
            Route::post('/{quotation}/send', [QuotationController::class, 'send'])->name('send');
            Route::post('/{quotation}/convert', [QuotationController::class, 'convertToJob'])->name('convert');
        });

        // Inventory
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/create', [InventoryController::class, 'create'])->name('create');
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::get('/low-stock-alerts', [InventoryController::class, 'lowStockAlerts'])->name('low-stock-alerts');
            Route::get('/{inventory}', [InventoryController::class, 'show'])->name('show');
            Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
            Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
            Route::post('/{inventory}/movement', [InventoryController::class, 'recordMovement'])->name('movement');
        });

        // Assets
        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\AssetController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\AssetController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\AssetController::class, 'store'])->name('store');
            Route::get('/maintenance', [\App\Http\Controllers\CMS\AssetController::class, 'maintenance'])->name('maintenance');
            Route::get('/{asset}', [\App\Http\Controllers\CMS\AssetController::class, 'show'])->name('show');
            Route::get('/{asset}/edit', [\App\Http\Controllers\CMS\AssetController::class, 'edit'])->name('edit');
            Route::put('/{asset}', [\App\Http\Controllers\CMS\AssetController::class, 'update'])->name('update');
            Route::post('/{asset}/assign', [\App\Http\Controllers\CMS\AssetController::class, 'assign'])->name('assign');
            Route::post('/{asset}/schedule-maintenance', [\App\Http\Controllers\CMS\AssetController::class, 'scheduleMaintenance'])->name('schedule-maintenance');
            Route::post('/assignments/{assignment}/return', [\App\Http\Controllers\CMS\AssetController::class, 'returnAsset'])->name('return');
            Route::post('/maintenance/{maintenance}/complete', [\App\Http\Controllers\CMS\AssetController::class, 'completeMaintenance'])->name('maintenance.complete');
        });

        // Payroll & Workers
        Route::prefix('payroll')->name('payroll.')->group(function () {
            // Workers
            Route::prefix('workers')->name('workers.')->group(function () {
                Route::get('/', [\App\Http\Controllers\CMS\PayrollController::class, 'workersIndex'])->name('index');
                Route::get('/create', [\App\Http\Controllers\CMS\PayrollController::class, 'workersCreate'])->name('create');
                Route::post('/', [\App\Http\Controllers\CMS\PayrollController::class, 'workersStore'])->name('store');
                Route::get('/{worker}', [\App\Http\Controllers\CMS\PayrollController::class, 'workersShow'])->name('show');
            });

            // Attendance
            Route::post('/attendance', [\App\Http\Controllers\CMS\PayrollController::class, 'attendanceStore'])->name('attendance.store');
            Route::post('/attendance/{attendance}/approve', [\App\Http\Controllers\CMS\PayrollController::class, 'attendanceApprove'])->name('attendance.approve');

            // Commissions
            Route::post('/commissions', [\App\Http\Controllers\CMS\PayrollController::class, 'commissionStore'])->name('commissions.store');
            Route::post('/commissions/{commission}/approve', [\App\Http\Controllers\CMS\PayrollController::class, 'commissionApprove'])->name('commissions.approve');

            // Payroll Runs
            Route::get('/', [\App\Http\Controllers\CMS\PayrollController::class, 'payrollIndex'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\PayrollController::class, 'payrollCreate'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\PayrollController::class, 'payrollStore'])->name('store');
            Route::get('/{payrollRun}', [\App\Http\Controllers\CMS\PayrollController::class, 'payrollShow'])->name('show');
            Route::post('/{payrollRun}/approve', [\App\Http\Controllers\CMS\PayrollController::class, 'payrollApprove'])->name('approve');
            Route::post('/{payrollRun}/mark-paid', [\App\Http\Controllers\CMS\PayrollController::class, 'payrollMarkPaid'])->name('mark-paid');
        });

        // Time Tracking
        Route::prefix('time-tracking')->name('time-tracking.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'index'])->name('index');
            Route::post('/start-timer', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'startTimer'])->name('start-timer');
            Route::post('/{entry}/stop-timer', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'stopTimer'])->name('stop-timer');
            Route::post('/', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'store'])->name('store');
            Route::put('/{entry}', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'update'])->name('update');
            Route::delete('/{entry}', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'destroy'])->name('destroy');
            Route::post('/{entry}/submit', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'submit'])->name('submit');
            Route::post('/{entry}/approve', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'approve'])->name('approve');
            Route::post('/{entry}/reject', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'reject'])->name('reject');
            
            // Timesheets
            Route::get('/timesheets', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'timesheets'])->name('timesheets.index');
            Route::post('/timesheets/generate', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'generateTimesheet'])->name('timesheets.generate');
            Route::post('/timesheets/{timesheet}/submit', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'submitTimesheet'])->name('timesheets.submit');
            Route::post('/timesheets/{timesheet}/approve', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'approveTimesheet'])->name('timesheets.approve');
            Route::post('/timesheets/{timesheet}/reject', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'rejectTimesheet'])->name('timesheets.reject');
            
            // Reports
            Route::get('/reports', [\App\Http\Controllers\CMS\TimeTrackingController::class, 'reports'])->name('reports');
        });

        // Analytics
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/operations', [\App\Http\Controllers\CMS\AnalyticsController::class, 'operations'])->name('operations');
            Route::get('/finance', [\App\Http\Controllers\CMS\AnalyticsController::class, 'finance'])->name('finance');
        });

        // Approvals
        Route::prefix('approvals')->name('approvals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\ApprovalController::class, 'index'])->name('index');
            Route::get('/{approval}', [\App\Http\Controllers\CMS\ApprovalController::class, 'show'])->name('show');
            Route::post('/{approval}/approve', [\App\Http\Controllers\CMS\ApprovalController::class, 'approve'])->name('approve');
            Route::post('/{approval}/reject', [\App\Http\Controllers\CMS\ApprovalController::class, 'reject'])->name('reject');
            Route::post('/{approval}/cancel', [\App\Http\Controllers\CMS\ApprovalController::class, 'cancel'])->name('cancel');
            
            // Approval Chains Management
            Route::get('/chains/manage', [\App\Http\Controllers\CMS\ApprovalController::class, 'chains'])->name('chains.index');
            Route::post('/chains', [\App\Http\Controllers\CMS\ApprovalController::class, 'storeChain'])->name('chains.store');
            Route::put('/chains/{chain}', [\App\Http\Controllers\CMS\ApprovalController::class, 'updateChain'])->name('chains.update');
            Route::delete('/chains/{chain}', [\App\Http\Controllers\CMS\ApprovalController::class, 'deleteChain'])->name('chains.delete');
        });

        // Settings - Industry Presets
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\SettingsController::class, 'index'])->name('index');
            Route::post('/business-hours', [\App\Http\Controllers\CMS\SettingsController::class, 'updateBusinessHours'])->name('business-hours.update');
            Route::post('/tax', [\App\Http\Controllers\CMS\SettingsController::class, 'updateTaxSettings'])->name('tax.update');
            Route::post('/approval-thresholds', [\App\Http\Controllers\CMS\SettingsController::class, 'updateApprovalThresholds'])->name('approval-thresholds.update');
            Route::post('/invoice', [\App\Http\Controllers\CMS\SettingsController::class, 'updateInvoiceSettings'])->name('invoice.update');
            Route::post('/notifications', [\App\Http\Controllers\CMS\SettingsController::class, 'updateNotificationSettings'])->name('notifications.update');
            Route::post('/payment-instructions', [\App\Http\Controllers\CMS\SettingsController::class, 'updatePaymentInstructions'])->name('payment-instructions.update');
            Route::post('/branding', [\App\Http\Controllers\CMS\SettingsController::class, 'updateBrandingSettings'])->name('branding.update');
            Route::post('/logo', [\App\Http\Controllers\CMS\SettingsController::class, 'uploadLogo'])->name('logo.upload');
            Route::delete('/logo', [\App\Http\Controllers\CMS\SettingsController::class, 'deleteLogo'])->name('logo.delete');
            Route::post('/sms', [\App\Http\Controllers\CMS\SettingsController::class, 'updateSmsSettings'])->name('sms.update');
            Route::post('/reset-defaults', [\App\Http\Controllers\CMS\SettingsController::class, 'resetToDefaults'])->name('reset-defaults');
            
            Route::get('/industry-presets', [\App\Http\Controllers\CMS\IndustryPresetController::class, 'index'])->name('industry-presets.index');
            Route::get('/industry-presets/{code}', [\App\Http\Controllers\CMS\IndustryPresetController::class, 'show'])->name('industry-presets.show');
            Route::post('/industry-presets/apply', [\App\Http\Controllers\CMS\IndustryPresetController::class, 'apply'])->name('industry-presets.apply');
            
            // Email Settings
            Route::get('/email', [\App\Http\Controllers\CMS\EmailSettingsController::class, 'index'])->name('email.index');
            Route::post('/email', [\App\Http\Controllers\CMS\EmailSettingsController::class, 'update'])->name('email.update');
            Route::post('/email/test-connection', [\App\Http\Controllers\CMS\EmailSettingsController::class, 'testConnection'])->name('email.test-connection');
            Route::get('/email/logs', [\App\Http\Controllers\CMS\EmailSettingsController::class, 'logs'])->name('email.logs');
            Route::get('/email/templates', [\App\Http\Controllers\CMS\EmailSettingsController::class, 'templates'])->name('email.templates');
            Route::put('/email/templates/{id}', [\App\Http\Controllers\CMS\EmailSettingsController::class, 'updateTemplate'])->name('email.templates.update');
            
            // SMS Settings
            Route::get('/sms', [\App\Http\Controllers\CMS\SmsSettingsController::class, 'index'])->name('sms.index');
            Route::post('/sms', [\App\Http\Controllers\CMS\SmsSettingsController::class, 'update'])->name('sms.update');
            Route::post('/sms/test-connection', [\App\Http\Controllers\CMS\SmsSettingsController::class, 'testConnection'])->name('sms.test-connection');
            Route::get('/sms/logs', [\App\Http\Controllers\CMS\SmsSettingsController::class, 'logs'])->name('sms.logs');
            
            // Currency Settings
            Route::get('/currency', [\App\Http\Controllers\CMS\CurrencyController::class, 'index'])->name('currency.index');
            Route::post('/currency', [\App\Http\Controllers\CMS\CurrencyController::class, 'updateSettings'])->name('currency.update');
            Route::post('/currency/exchange-rate', [\App\Http\Controllers\CMS\CurrencyController::class, 'setExchangeRate'])->name('currency.exchange-rate.set');
            Route::get('/currency/exchange-rate', [\App\Http\Controllers\CMS\CurrencyController::class, 'getExchangeRate'])->name('currency.exchange-rate.get');
            Route::post('/currency/convert', [\App\Http\Controllers\CMS\CurrencyController::class, 'convert'])->name('currency.convert');
            Route::get('/currency/history', [\App\Http\Controllers\CMS\CurrencyController::class, 'history'])->name('currency.history');
            Route::post('/currency/fetch-live-rates', [\App\Http\Controllers\CMS\CurrencyController::class, 'fetchLiveRates'])->name('currency.fetch-live-rates');
        });

        // Onboarding
        Route::prefix('onboarding')->name('onboarding.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\OnboardingController::class, 'index'])->name('index');
            Route::get('/status', [\App\Http\Controllers\CMS\OnboardingController::class, 'status'])->name('status');
            Route::post('/company-info', [\App\Http\Controllers\CMS\OnboardingController::class, 'updateCompanyInfo'])->name('company-info');
            Route::post('/apply-preset', [\App\Http\Controllers\CMS\OnboardingController::class, 'applyPreset'])->name('apply-preset');
            Route::post('/configure-settings', [\App\Http\Controllers\CMS\OnboardingController::class, 'configureSettings'])->name('configure-settings');
            Route::post('/complete-step', [\App\Http\Controllers\CMS\OnboardingController::class, 'completeStep'])->name('complete-step');
            Route::post('/skip-step', [\App\Http\Controllers\CMS\OnboardingController::class, 'skipStep'])->name('skip-step');
            Route::post('/complete', [\App\Http\Controllers\CMS\OnboardingController::class, 'complete'])->name('complete');
            
            // Sample Data
            Route::post('/generate-sample-data', [\App\Http\Controllers\CMS\OnboardingController::class, 'generateSampleData'])->name('generate-sample-data');
            Route::post('/clear-sample-data', [\App\Http\Controllers\CMS\OnboardingController::class, 'clearSampleData'])->name('clear-sample-data');
            
            // Progress Persistence
            Route::post('/save-progress', [\App\Http\Controllers\CMS\OnboardingController::class, 'saveProgress'])->name('save-progress');
            Route::get('/saved-progress', [\App\Http\Controllers\CMS\OnboardingController::class, 'getSavedProgress'])->name('saved-progress');
            
            // Guided Tour
            Route::get('/tour/status', [\App\Http\Controllers\CMS\OnboardingController::class, 'tourStatus'])->name('tour.status');
            Route::post('/tour/complete-step', [\App\Http\Controllers\CMS\OnboardingController::class, 'completeTourStep'])->name('tour.complete-step');
            Route::post('/tour/skip', [\App\Http\Controllers\CMS\OnboardingController::class, 'skipTour'])->name('tour.skip');
        });

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\NotificationController::class, 'index'])->name('index');
            Route::get('/recent', [\App\Http\Controllers\CMS\NotificationController::class, 'recent'])->name('recent');
            Route::post('/{id}/mark-read', [\App\Http\Controllers\CMS\NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::post('/mark-all-read', [\App\Http\Controllers\CMS\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('/{id}', [\App\Http\Controllers\CMS\NotificationController::class, 'destroy'])->name('destroy');
        });

        // Security
        Route::prefix('security')->name('security.')->group(function () {
            Route::get('/settings', [\App\Http\Controllers\CMS\SecurityController::class, 'settings'])->name('settings');
            Route::post('/settings', [\App\Http\Controllers\CMS\SecurityController::class, 'updateSettings'])->name('settings.update');
            Route::get('/audit-logs', [\App\Http\Controllers\CMS\SecurityController::class, 'auditLogs'])->name('audit-logs');
            Route::get('/suspicious-activity', [\App\Http\Controllers\CMS\SecurityController::class, 'suspiciousActivity'])->name('suspicious-activity');
            Route::post('/suspicious-activity/{activity}/review', [\App\Http\Controllers\CMS\SecurityController::class, 'markActivityReviewed'])->name('suspicious-activity.review');
            
            // 2FA
            Route::get('/2fa/enable', [\App\Http\Controllers\CMS\SecurityController::class, 'enable2FA'])->name('2fa.enable');
            Route::post('/2fa/verify', [\App\Http\Controllers\CMS\SecurityController::class, 'verify2FA'])->name('2fa.verify');
            Route::post('/2fa/disable', [\App\Http\Controllers\CMS\SecurityController::class, 'disable2FA'])->name('2fa.disable');
        });

        // Accounting / Chart of Accounts
        Route::prefix('accounting')->name('accounting.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\AccountingController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\AccountingController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\AccountingController::class, 'store'])->name('store');
            Route::get('/trial-balance', [\App\Http\Controllers\CMS\AccountingController::class, 'trialBalance'])->name('trial-balance');
            Route::get('/journal-entries', [\App\Http\Controllers\CMS\AccountingController::class, 'journalEntries'])->name('journal-entries');
            Route::post('/initialize', [\App\Http\Controllers\CMS\AccountingController::class, 'initializeAccounts'])->name('initialize');
            Route::get('/{id}', [\App\Http\Controllers\CMS\AccountingController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\CMS\AccountingController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\CMS\AccountingController::class, 'destroy'])->name('destroy');
        });
    });
