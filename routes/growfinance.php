<?php

use App\Http\Controllers\GrowFinance\AccountController;
use App\Http\Controllers\GrowFinance\AgingReportController;
use App\Http\Controllers\GrowFinance\AnalyticsController;
use App\Http\Controllers\GrowFinance\AnalyticsDashboardController;
use App\Http\Controllers\GrowFinance\AnomalyController;
use App\Http\Controllers\GrowFinance\NlpQueryController;
use App\Http\Controllers\GrowFinance\RecommendationController;
use App\Http\Controllers\GrowFinance\ApiTokenController;
use App\Http\Controllers\GrowFinance\AuditSnapshotController;
use App\Http\Controllers\GrowFinance\BankingController;
use App\Http\Controllers\GrowFinance\BudgetController;
use App\Http\Controllers\GrowFinance\CashFlowForecastController;
use App\Http\Controllers\GrowFinance\CustomerController;
use App\Http\Controllers\GrowFinance\DashboardController;
use App\Http\Controllers\GrowFinance\ExpenseController;
use App\Http\Controllers\GrowFinance\FixedAssetController;
use App\Http\Controllers\GrowFinance\InvoiceController;
use App\Http\Controllers\GrowFinance\InvoiceTemplateController;
use App\Http\Controllers\GrowFinance\JournalEntryController;
use App\Http\Controllers\GrowFinance\MatchingController;
use App\Http\Controllers\GrowFinance\PeriodController;
use App\Http\Controllers\GrowFinance\QuotationController;
use App\Http\Controllers\GrowFinance\MessageController;
use App\Http\Controllers\GrowFinance\NotificationController;
use App\Http\Controllers\GrowFinance\PeriodEndController;
use App\Http\Controllers\GrowFinance\RecurringController;
use App\Http\Controllers\GrowFinance\ReportScheduleController;
use App\Http\Controllers\GrowFinance\ReportsController;
use App\Http\Controllers\GrowFinance\RevenuePredictionController;
use App\Http\Controllers\GrowFinance\SalesController;
use App\Http\Controllers\GrowFinance\ScenarioController;
use App\Http\Controllers\GrowFinance\SetupController;
use App\Http\Controllers\GrowFinance\ConsolidationController;
use App\Http\Controllers\GrowFinance\ProfitabilityController;
use App\Http\Controllers\GrowFinance\FinancialRatioController;
use App\Http\Controllers\GrowFinance\DashboardWidgetController;
use App\Http\Controllers\GrowFinance\ExportController;
use App\Http\Controllers\GrowFinance\SubscriptionController;
use App\Http\Controllers\GrowFinance\SupportController;
use App\Http\Controllers\GrowFinance\TaxController;
use App\Http\Controllers\GrowFinance\TeamController;
use App\Http\Controllers\GrowFinance\VendorController;
use App\Http\Controllers\GrowFinance\WhiteLabelController;
use App\Http\Controllers\GrowFinance\WorkflowAdminController;
use App\Http\Controllers\GrowFinance\WorkflowController;
use App\Http\Middleware\GrowFinanceStandalone;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', GrowFinanceStandalone::class])->prefix('growfinance')->name('growfinance.')->group(function () {
    // Dashboard (use /dashboard path to avoid conflict with public welcome page at /growfinance)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard Widgets (G4.10)
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/widgets', [DashboardWidgetController::class, 'index'])->name('widgets');
        Route::get('/widgets/cash', [DashboardWidgetController::class, 'cashPosition'])->name('widgets.cash');
        Route::get('/widgets/revenue', [DashboardWidgetController::class, 'revenueTrend'])->name('widgets.revenue');
        Route::get('/widgets/ar-ap', [DashboardWidgetController::class, 'arApSummary'])->name('widgets.ar-ap');
    });

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

    // Journals (Manual Journal Entries)
    Route::prefix('journals')->name('journals.')->group(function () {
        Route::get('/', [JournalEntryController::class, 'index'])->name('index');
        Route::get('/create', [JournalEntryController::class, 'create'])->name('create');
        Route::post('/', [JournalEntryController::class, 'store'])->name('store');
        Route::get('/{journal}', [JournalEntryController::class, 'show'])->name('show');
        Route::post('/{journal}/post', [JournalEntryController::class, 'post'])->name('post');
        Route::post('/{journal}/reverse', [JournalEntryController::class, 'reverse'])->name('reverse');
    });

    // Periods (Fiscal Years & Accounting Periods)
    Route::prefix('periods')->name('periods.')->group(function () {
        Route::get('/', [PeriodController::class, 'index'])->name('index');
        Route::get('/create', [PeriodController::class, 'create'])->name('create');
        Route::post('/', [PeriodController::class, 'store'])->name('store');
        Route::post('/{period}/close', [PeriodController::class, 'close'])->name('close');
        Route::post('/{period}/reopen', [PeriodController::class, 'reopen'])->name('reopen');
    });

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

    // Fixed Assets
    Route::prefix('fixed-assets')->name('fixed-assets.')->group(function () {
        Route::get('/', [FixedAssetController::class, 'index'])->name('index');
        Route::get('/create', [FixedAssetController::class, 'create'])->name('create');
        Route::post('/', [FixedAssetController::class, 'store'])->name('store');
        Route::get('/{fixedAsset}', [FixedAssetController::class, 'show'])->name('show');
        Route::post('/{fixedAsset}/depreciate', [FixedAssetController::class, 'runDepreciation'])->name('depreciate');
        Route::post('/depreciate-all', [FixedAssetController::class, 'runAllDepreciation'])->name('depreciate-all');
        Route::post('/{fixedAsset}/dispose', [FixedAssetController::class, 'dispose'])->name('dispose');
        Route::delete('/{fixedAsset}', [FixedAssetController::class, 'destroy'])->name('destroy');
    });

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
        Route::post('/import-statement', [BankingController::class, 'importStatement'])->name('import-statement');
        Route::post('/match', [BankingController::class, 'matchTransaction'])->name('match');
        Route::post('/unmatch/{matchId}', [BankingController::class, 'unmatchTransaction'])->name('unmatch');
    });

    // Period-End Procedures
    Route::prefix('period-end')->name('period-end.')->group(function () {
        Route::get('/', [PeriodEndController::class, 'index'])->name('index');
        Route::post('/generate', [PeriodEndController::class, 'generate'])->name('generate');
        Route::post('/{checklistId}/complete-task', [PeriodEndController::class, 'completeTask'])->name('complete-task');
        Route::post('/{checklistId}/run-depreciation', [PeriodEndController::class, 'runDepreciation'])->name('run-depreciation');
        Route::post('/{checklistId}/snapshot-reports', [PeriodEndController::class, 'snapshotReports'])->name('snapshot-reports');
        Route::post('/{checklistId}/close-period', [PeriodEndController::class, 'closePeriod'])->name('close-period');
    });

    // Report Schedules
    Route::prefix('report-schedules')->name('report-schedules.')->group(function () {
        Route::get('/', [ReportScheduleController::class, 'index'])->name('index');
        Route::post('/', [ReportScheduleController::class, 'store'])->name('store');
        Route::put('/{schedule}', [ReportScheduleController::class, 'update'])->name('update');
        Route::delete('/{schedule}', [ReportScheduleController::class, 'destroy'])->name('destroy');
    });

    // Auto-Journal Mappings (Settings)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/auto-journal-mappings', function (\Illuminate\Http\Request $r) {
            return \Inertia\Inertia::render('GrowFinance/Settings/AutoJournalMappings');
        })->name('auto-journal-mappings');
    });

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Anomaly Detection (G5.1)
    Route::prefix('anomalies')->name('anomalies.')->group(function () {
        Route::get('/', [AnomalyController::class, 'index'])->name('index');
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

    // Tax Reports
    Route::prefix('tax')->name('tax.')->group(function () {
        Route::get('/vat-return', [TaxController::class, 'vatReturn'])->name('vat-return');
        Route::get('/withholding-schedule', [TaxController::class, 'withholdingSchedule'])->name('withholding-schedule');
        Route::post('/save-return', [TaxController::class, 'saveReturn'])->name('save-return');
    });

    // AR/AP Aging Reports
    Route::prefix('aging')->name('aging.')->group(function () {
        Route::get('/ar', [AgingReportController::class, 'arAging'])->name('ar');
        Route::get('/ap', [AgingReportController::class, 'apAging'])->name('ap');
        Route::get('/customer/{customerId}', [AgingReportController::class, 'customerDetail'])->name('customer');
        Route::get('/vendor/{vendorId}', [AgingReportController::class, 'vendorDetail'])->name('vendor');
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

    // Group consolidation
    Route::prefix('consolidation')->name('consolidation.')->group(function () {
        Route::get('/', [ConsolidationController::class, 'index'])->name('index');
        Route::post('/run', [ConsolidationController::class, 'consolidate'])->name('run');
        Route::get('/{id}', [ConsolidationController::class, 'show'])->name('show');
    });

    // Profitability (Branch/Department P&L)
    Route::prefix('profitability')->name('profitability.')->group(function () {
        Route::get('/', [ProfitabilityController::class, 'index'])->name('index');
    });

    // Financial Ratios & Trend Analysis
    Route::prefix('ratios')->name('ratios.')->group(function () {
        Route::get('/', [FinancialRatioController::class, 'index'])->name('index');
        Route::get('/trend', [FinancialRatioController::class, 'trend'])->name('trend');
    });

    // Export routes (G4.11)
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/csv', [ExportController::class, 'csv'])->name('csv');
        Route::get('/pdf', [ExportController::class, 'pdf'])->name('pdf');
    });

    // Workflow Engine & Journal Approval
    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::get('/', [WorkflowController::class, 'index'])->name('index');
        Route::post('/templates', [WorkflowController::class, 'storeTemplate'])->name('templates.store');
        Route::post('/{instance}/approve', [WorkflowController::class, 'approve'])->name('approve');
        Route::post('/{instance}/reject', [WorkflowController::class, 'reject'])->name('reject');
        Route::get('/templates', [WorkflowController::class, 'templates'])->name('templates');
    });

    // G5.2 Cash Flow Forecasting
    Route::prefix('forecast')->name('forecast.')->group(function () {
        Route::get('/', [CashFlowForecastController::class, 'index'])->name('index');
    });

    // G5.3 Revenue & Expense Prediction
    Route::prefix('predictions')->name('predictions.')->group(function () {
        Route::get('/', [RevenuePredictionController::class, 'index'])->name('index');
    });

    // G5.4 Scenario Modelling (What-If Analysis)
    Route::prefix('scenarios')->name('scenarios.')->group(function () {
        Route::get('/', [ScenarioController::class, 'index'])->name('index');
        Route::post('/simulate', [ScenarioController::class, 'simulate'])->name('simulate');
        Route::post('/save', [ScenarioController::class, 'save'])->name('save');
        Route::get('/{id}', [ScenarioController::class, 'show'])->name('show');
    });

    // G5.5 NLP Query
    Route::prefix('nlp')->name('nlp.')->group(function () {
        Route::get('/', function () { return \Inertia\Inertia::render('GrowFinance/Reports/NlpQuery'); })->name('query');
        Route::post('/ask', [NlpQueryController::class, 'ask'])->name('ask');
    });

    // G5.6 Automated Recommendations
    Route::prefix('recommendations')->name('recommendations.')->group(function () {
        Route::get('/', [RecommendationController::class, 'index'])->name('index');
    });

    // G5.7 Financial Analytics Dashboard
    Route::prefix('analytics-dashboard')->name('analytics-dashboard.')->group(function () {
        Route::get('/', [AnalyticsDashboardController::class, 'index'])->name('dashboard');
    });

    // G6.1 Offline-First Mode
    Route::prefix('offline')->name('offline.')->group(function () {
        Route::post('/sync/journal', [OfflineSyncController::class, 'syncJournal'])->name('sync.journal');
        Route::get('/status', [OfflineSyncController::class, 'status'])->name('status');
    });

    // G6.2 ZRA SmartInvoice API Integration
    Route::prefix('zra')->name('zra.')->group(function () {
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::post('/{invoiceId}/submit', [ZraController::class, 'submitInvoice'])->name('submit');
            Route::post('/verify', [ZraController::class, 'verifyInvoice'])->name('verify');
        });
        Route::get('/health', [ZraController::class, 'health'])->name('health');
    });

    // G6.3 Audit Snapshot Management
    Route::prefix('audit-snapshots')->name('audit-snapshots.')->group(function () {
        Route::get('/', [AuditSnapshotController::class, 'index'])->name('index');
        Route::post('/take', [AuditSnapshotController::class, 'take'])->name('take');
        Route::get('/{id}/verify', [AuditSnapshotController::class, 'verify'])->name('verify');
        Route::post('/{id}/lock', [AuditSnapshotController::class, 'lock'])->name('lock');
    });

    // G6.4 Three-Way Matching
    Route::prefix('matching')->name('matching.')->group(function () {
        Route::get('/', [MatchingController::class, 'index'])->name('index');
        Route::post('/confirm', [MatchingController::class, 'confirm'])->name('confirm');
    });

    // G6.5 Workflow Admin (SLA Management)
    Route::prefix('workflow-admin')->name('workflow-admin.')->group(function () {
        Route::get('/', [WorkflowAdminController::class, 'index'])->name('index');
        Route::post('/{id}/sla', [WorkflowAdminController::class, 'updateSla'])->name('sla');
    });

    // G6.6 Electronic Tax Return Submission
    Route::prefix('tax-returns')->name('tax-returns.')->group(function () {
        Route::get('/submission', [TaxReturnSubmissionController::class, 'index'])->name('submission');
        Route::post('/submit', [TaxReturnSubmissionController::class, 'submit'])->name('submit');
        Route::post('/status', [TaxReturnSubmissionController::class, 'status'])->name('status');
    });
});
