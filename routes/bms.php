<?php

use App\Http\Controllers\BMS\AuthController;
use App\Http\Controllers\BMS\AccountingController;
use App\Http\Controllers\BMS\CustomerController;
use App\Http\Controllers\BMS\DashboardController;
use App\Http\Controllers\BMS\ExpenseController;
use App\Http\Controllers\BMS\ExpenseCategoryController;
use App\Http\Controllers\BMS\InventoryController;
use App\Http\Controllers\BMS\InvoiceController;
use App\Http\Controllers\BMS\JobController;
use App\Http\Controllers\BMS\MeasurementController;
use App\Http\Controllers\BMS\PaymentController;
use App\Http\Controllers\BMS\PricingRulesController;
use App\Http\Controllers\BMS\QuotationController;
use App\Http\Controllers\BMS\ReportController;
use App\Http\Controllers\BMS\BudgetController;
use App\Http\Controllers\BMS\ScheduledReportController;
use App\Http\Controllers\BMS\ProjectController;
use App\Http\Controllers\BMS\SubcontractorController;
use App\Http\Controllers\BMS\EquipmentController;
use App\Http\Controllers\BMS\LabourController;
use App\Http\Controllers\BMS\BOQController;
use App\Http\Controllers\BMS\ProgressBillingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| BMS Routes
|--------------------------------------------------------------------------
|
| Routes for the Business Management System (SME Operating System)
|
*/

// Public BMS Routes (Landing & Auth)
Route::prefix('bms')->name('bms.')->group(function () {
    // Landing Page — redirect to hub if already authenticated
    Route::get('/', function () {
        if (auth()->check()) {
            return redirect()->route('bms.companies.hub');
        }
        return Inertia::render('BMS/Landing', [
            'routePrefix' => 'bms'
        ]);
    })->name('landing');

    // Offline Page (for PWA)
    Route::get('/offline', function () {
        return Inertia::render('BMS/Offline');
    })->name('offline');

    // Template preview — signed URL, no auth required (iframe safe)
    Route::get('/settings/document-templates/{id}/preview', [\App\Http\Controllers\BMS\DocumentTemplatesController::class, 'preview'])->name('settings.document-templates.preview');

    // Signed quotation PDF — for WhatsApp links (no auth required)
    Route::get('/quotations/{id}/pdf/signed', [\App\Http\Controllers\BMS\QuotationController::class, 'downloadPdfSigned'])->name('quotations.pdf.signed');

    // Email Unsubscribe (Public)
    Route::get('/email/unsubscribe', [\App\Http\Controllers\BMS\EmailSettingsController::class, 'unsubscribe'])->name('email.unsubscribe');

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

    // Switch active company (authenticated users)
    Route::post('/switch-company', [AuthController::class, 'switchCompany'])
        ->middleware('auth')
        ->name('switch-company');

    // Company Hub — auth required but no company needed yet
    Route::middleware('auth')->group(function () {
        Route::get('/companies/hub', [\App\Http\Controllers\BMS\CompanyController::class, 'hub'])->name('companies.hub');
        Route::get('/companies/create', [\App\Http\Controllers\BMS\CompanyController::class, 'create'])->name('companies.create');
        Route::post('/companies', [\App\Http\Controllers\BMS\CompanyController::class, 'store'])->name('companies.store');
        Route::post('/companies/{companyId}/enter', [\App\Http\Controllers\BMS\CompanyController::class, 'enter'])->name('companies.enter');
        Route::post('/companies/set-default', [\App\Http\Controllers\BMS\CompanyController::class, 'setDefault'])->name('companies.set-default');
    });
});

// Protected BMS Routes
Route::prefix('bms')
    ->name('bms.')
    ->middleware(['auth', 'verified', 'bms.auto-login', 'bms.access', \App\Http\Middleware\BMS\EnforcePasswordChange::class])
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // KPIs
        Route::prefix('kpis')->name('kpis.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\KpiController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\KpiController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\KpiController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\BMS\KpiController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\BMS\KpiController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\BMS\KpiController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\BMS\KpiController::class, 'destroy'])->name('delete');
            Route::post('/{id}/values', [\App\Http\Controllers\BMS\KpiController::class, 'recordValue'])->name('values.store');
            Route::delete('/{id}/values/{valueId}', [\App\Http\Controllers\BMS\KpiController::class, 'deleteValue'])->name('values.delete');
        });

        // Planning
        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\PlanController::class, 'index'])->name('index');
            Route::get('/command-center', [\App\Http\Controllers\BMS\PlanController::class, 'commandCenter'])->name('command-center');
            Route::get('/create', [\App\Http\Controllers\BMS\PlanController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\PlanController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\BMS\PlanController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\BMS\PlanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\BMS\PlanController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\BMS\PlanController::class, 'destroy'])->name('delete');

            // Plan Objectives
            Route::prefix('{planId}/objectives')->name('objectives.')->group(function () {
                Route::post('/', [\App\Http\Controllers\BMS\PlanObjectiveController::class, 'store'])->name('store');
                Route::put('/{id}', [\App\Http\Controllers\BMS\PlanObjectiveController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\BMS\PlanObjectiveController::class, 'destroy'])->name('delete');

                // Entity linking
                Route::post('/{id}/link', [\App\Http\Controllers\BMS\PlanObjectiveController::class, 'link'])->name('link');
                Route::delete('/{id}/link/{linkId}', [\App\Http\Controllers\BMS\PlanObjectiveController::class, 'unlink'])->name('unlink');
                Route::post('/{id}/link/{linkId}/sync', [\App\Http\Controllers\BMS\PlanObjectiveController::class, 'syncLink'])->name('sync-link');
                Route::post('/{id}/sync-kpi', [\App\Http\Controllers\BMS\PlanObjectiveController::class, 'syncKpi'])->name('sync-kpi');
            });

            // Entity search (outside objectives prefix — for linking UI)
            Route::get('/entity-search', [\App\Http\Controllers\BMS\PlanObjectiveController::class, 'searchEntities'])->name('entity-search');
            Route::get('/entity-fields', [\App\Http\Controllers\BMS\PlanObjectiveController::class, 'availableFields'])->name('entity-fields');
        });

        // Business Plans
        Route::prefix('business-plans')->name('business-plans.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\BusinessPlanController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\BusinessPlanController::class, 'create'])->name('create');
            Route::get('/{planId}/edit', [\App\Http\Controllers\BMS\BusinessPlanController::class, 'edit'])->name('edit');
            Route::post('/save', [\App\Http\Controllers\BMS\BusinessPlanController::class, 'save'])->name('save');
            Route::post('/complete', [\App\Http\Controllers\BMS\BusinessPlanController::class, 'complete'])->name('complete');
            Route::get('/{planId}', [\App\Http\Controllers\BMS\BusinessPlanController::class, 'show'])->name('show');
            Route::post('/generate-ai', [\App\Http\Controllers\BMS\BusinessPlanController::class, 'generateAI'])->name('generate-ai');
            Route::post('/chat', [\App\Http\Controllers\BMS\BusinessPlanController::class, 'chat'])->name('chat');
            Route::post('/export', [\App\Http\Controllers\BMS\BusinessPlanController::class, 'export'])->name('export');
            Route::delete('/{planId}', [\App\Http\Controllers\BMS\BusinessPlanController::class, 'delete'])->name('delete');
        });

        // Measurements (Aluminium Fabrication)
        Route::prefix('measurements')->name('measurements.')->group(function () {
            Route::get('/', [MeasurementController::class, 'index'])->name('index');
            Route::get('/create', [MeasurementController::class, 'create'])->name('create');
            Route::post('/', [MeasurementController::class, 'store'])->name('store');
            Route::get('/{id}', [MeasurementController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [MeasurementController::class, 'edit'])->name('edit');
            Route::put('/{id}', [MeasurementController::class, 'update'])->name('update');
            Route::delete('/{id}', [MeasurementController::class, 'destroy'])->name('destroy');
            // Item management
            Route::post('/{id}/items', [MeasurementController::class, 'storeItem'])->name('items.store');
            Route::put('/{id}/items/{itemId}', [MeasurementController::class, 'updateItem'])->name('items.update');
            Route::delete('/{id}/items/{itemId}', [MeasurementController::class, 'destroyItem'])->name('items.destroy');
            // Actions
            Route::post('/{id}/complete', [MeasurementController::class, 'complete'])->name('complete');
            Route::post('/{id}/generate-quotation', [MeasurementController::class, 'generateQuotation'])->name('generate-quotation');
        });

        // Pricing Rules (Company Settings)
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/pricing-rules', [PricingRulesController::class, 'show'])->name('pricing-rules');
            Route::put('/pricing-rules', [PricingRulesController::class, 'update'])->name('pricing-rules.update');
            Route::post('/fabrication-module', [\App\Http\Controllers\BMS\SettingsController::class, 'toggleFabricationModule'])->name('fabrication-module.toggle');
            Route::post('/bizdocs-module', [\App\Http\Controllers\BMS\SettingsController::class, 'toggleBizDocsModule'])->name('bizdocs-module.toggle');
            Route::post('/material-planning-module', [\App\Http\Controllers\BMS\SettingsController::class, 'toggleMaterialPlanningModule'])->name('material-planning-module.toggle');
            Route::post('/construction-modules', [\App\Http\Controllers\BMS\SettingsController::class, 'toggleConstructionModules'])->name('construction-modules.toggle');
            Route::post('/growfinance-module', [\App\Http\Controllers\BMS\SettingsController::class, 'toggleGrowFinanceModule'])->name('growfinance-module.toggle');
            Route::post('/operations-module', [\App\Http\Controllers\BMS\SettingsController::class, 'toggleOperationsModule'])->name('operations-module.toggle');

            // Document Templates (BizDocs)
            Route::get('/document-templates', [\App\Http\Controllers\BMS\DocumentTemplatesController::class, 'index'])->name('document-templates.index');
            Route::post('/document-templates/set', [\App\Http\Controllers\BMS\DocumentTemplatesController::class, 'setTemplate'])->name('document-templates.set');
            Route::get('/document-templates/{id}/preview-url', [\App\Http\Controllers\BMS\DocumentTemplatesController::class, 'previewUrl'])->name('document-templates.preview-url');
        });

        // Jobs
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [JobController::class, 'index'])->name('index');
            Route::get('/create', [JobController::class, 'create'])->name('create');
            Route::post('/', [JobController::class, 'store'])->name('store');
            Route::get('/{job}', [JobController::class, 'show'])->name('show');
            Route::post('/{job}/assign', [JobController::class, 'assign'])->name('assign');
            Route::post('/{job}/complete', [JobController::class, 'complete'])->name('complete');
            Route::post('/{job}/status', [JobController::class, 'updateStatus'])->name('status');
            Route::post('/{job}/attachments', [JobController::class, 'uploadAttachment'])->name('attachments.upload');
            
            // Material Planning for Jobs
            Route::prefix('{job}/materials')->name('materials.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\JobMaterialPlanningController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\BMS\JobMaterialPlanningController::class, 'store'])->name('store');
                Route::put('/{plan}', [\App\Http\Controllers\BMS\JobMaterialPlanningController::class, 'update'])->name('update');
                Route::delete('/{plan}', [\App\Http\Controllers\BMS\JobMaterialPlanningController::class, 'destroy'])->name('destroy');
                Route::post('/apply-template', [\App\Http\Controllers\BMS\JobMaterialPlanningController::class, 'applyTemplate'])->name('apply-template');
                Route::post('/{plan}/actual-costs', [\App\Http\Controllers\BMS\JobMaterialPlanningController::class, 'updateActualCosts'])->name('actual-costs');
                Route::post('/bulk-status', [\App\Http\Controllers\BMS\JobMaterialPlanningController::class, 'bulkUpdateStatus'])->name('bulk-status');
            });
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
            Route::get('/', [\App\Http\Controllers\BMS\RecurringInvoiceController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\RecurringInvoiceController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\RecurringInvoiceController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\BMS\RecurringInvoiceController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\BMS\RecurringInvoiceController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\BMS\RecurringInvoiceController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\BMS\RecurringInvoiceController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/pause', [\App\Http\Controllers\BMS\RecurringInvoiceController::class, 'pause'])->name('pause');
            Route::post('/{id}/resume', [\App\Http\Controllers\BMS\RecurringInvoiceController::class, 'resume'])->name('resume');
            Route::post('/{id}/cancel', [\App\Http\Controllers\BMS\RecurringInvoiceController::class, 'cancel'])->name('cancel');
            Route::post('/{id}/generate', [\App\Http\Controllers\BMS\RecurringInvoiceController::class, 'generateNow'])->name('generate');
        });

        // Payments
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/create', [PaymentController::class, 'create'])->name('create');
            Route::post('/', [PaymentController::class, 'store'])->name('store');
            Route::get('/daily-summary', [PaymentController::class, 'dailySummary'])->name('daily-summary');
            Route::get('/customer/{customer}/invoices', [PaymentController::class, 'customerInvoices'])->name('customer-invoices');
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
        
        // GrowFinance-powered Reports
        Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
        Route::get('/reports/cash-flow-statement', [ReportController::class, 'cashFlowStatement'])->name('reports.cash-flow-statement');
        Route::get('/reports/general-ledger', [ReportController::class, 'generalLedger'])->name('reports.general-ledger');
        Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance');

        // Budgets
        Route::resource('budgets', BudgetController::class);

        // Scheduled Reports
        Route::resource('scheduled-reports', ScheduledReportController::class)->except(['show']);
        Route::post('/scheduled-reports/{id}/toggle', [ScheduledReportController::class, 'toggle'])->name('scheduled-reports.toggle');

        // Expenses
        Route::prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', [ExpenseController::class, 'index'])->name('index');
            Route::get('/create', [ExpenseController::class, 'create'])->name('create');
            Route::post('/', [ExpenseController::class, 'store'])->name('store');
            Route::post('/{expense}/approve', [ExpenseController::class, 'approve'])->name('approve');
            Route::post('/{expense}/reject', [ExpenseController::class, 'reject'])->name('reject');
        });

        // Expense Categories
        Route::prefix('expense-categories')->name('expense-categories.')->group(function () {
            Route::get('/', [ExpenseCategoryController::class, 'index'])->name('index');
            Route::post('/', [ExpenseCategoryController::class, 'store'])->name('store');
            Route::put('/{category}', [ExpenseCategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [ExpenseCategoryController::class, 'destroy'])->name('destroy');
        });

        // Quotations
        Route::prefix('quotations')->name('quotations.')->group(function () {
            Route::get('/', [QuotationController::class, 'index'])->name('index');
            Route::get('/create', [QuotationController::class, 'create'])->name('create');
            Route::post('/', [QuotationController::class, 'store'])->name('store');
            Route::get('/{quotation}', [QuotationController::class, 'show'])->name('show');
            Route::post('/{quotation}/send', [QuotationController::class, 'send'])->name('send');
            Route::post('/{quotation}/send-email', [QuotationController::class, 'sendViaEmail'])->name('send-email');
            Route::get('/{quotation}/whatsapp-link', [QuotationController::class, 'whatsappLink'])->name('whatsapp-link');
            Route::post('/{quotation}/convert', [QuotationController::class, 'convertToJob'])->name('convert');
            Route::get('/{quotation}/pdf', [QuotationController::class, 'downloadPdf'])->name('pdf');
            Route::get('/{quotation}/preview', [QuotationController::class, 'previewPdf'])->name('preview');
        });

        // Inventory
        Route::prefix('inventory')->name('inventory.')->group(function () {
            // Static routes MUST come before parameterized {inventory}
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/create', [InventoryController::class, 'create'])->name('create');
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::get('/low-stock-alerts', [InventoryController::class, 'lowStockAlerts'])->name('low-stock-alerts');
            // Stock Locations
            Route::get('/locations', [InventoryController::class, 'locations'])->name('locations.index');
            Route::post('/locations', [InventoryController::class, 'storeLocation'])->name('locations.store');
            // Stock Levels
            Route::get('/stock-levels', [InventoryController::class, 'stockLevels'])->name('stock-levels.index');
            // Stock Transfers
            Route::get('/transfers', [InventoryController::class, 'transfers'])->name('transfers.index');
            Route::post('/transfers', [InventoryController::class, 'createTransfer'])->name('transfers.store');
            Route::post('/transfers/{id}/approve', [InventoryController::class, 'approveTransfer'])->name('transfers.approve');
            Route::post('/transfers/{id}/receive', [InventoryController::class, 'receiveTransfer'])->name('transfers.receive');
            // Stock Adjustments
            Route::get('/adjustments', [InventoryController::class, 'adjustments'])->name('adjustments.index');
            Route::post('/adjustments', [InventoryController::class, 'createAdjustment'])->name('adjustments.store');
            Route::post('/adjustments/{id}/approve', [InventoryController::class, 'approveAdjustment'])->name('adjustments.approve');
            // Stock Counts
            Route::get('/counts', [InventoryController::class, 'counts'])->name('counts.index');
            Route::post('/counts', [InventoryController::class, 'createCount'])->name('counts.store');
            Route::post('/counts/{id}/complete', [InventoryController::class, 'completeCount'])->name('counts.complete');
            // Stock Valuation
            Route::get('/valuation', [InventoryController::class, 'valuations'])->name('valuation.index');
            // Parameterized routes (must be last)
            Route::get('/{inventory}', [InventoryController::class, 'show'])->name('show');
            Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
            Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
            Route::post('/{inventory}/movement', [InventoryController::class, 'recordMovement'])->name('movement');
        });

        // Materials Management
        Route::prefix('materials')->name('materials.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\MaterialController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\MaterialController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\MaterialController::class, 'store'])->name('store');
            Route::get('/{material}/edit', [\App\Http\Controllers\BMS\MaterialController::class, 'edit'])->name('edit');
            Route::put('/{material}', [\App\Http\Controllers\BMS\MaterialController::class, 'update'])->name('update');
            Route::delete('/{material}', [\App\Http\Controllers\BMS\MaterialController::class, 'destroy'])->name('destroy');
            Route::get('/{material}/price-history', [\App\Http\Controllers\BMS\MaterialController::class, 'priceHistory'])->name('price-history');
            Route::post('/bulk-update-prices', [\App\Http\Controllers\BMS\MaterialController::class, 'bulkUpdatePrices'])->name('bulk-update-prices');
        });

        // Material Categories
        Route::prefix('material-categories')->name('material-categories.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\MaterialCategoryController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\BMS\MaterialCategoryController::class, 'store'])->name('store');
            Route::put('/{category}', [\App\Http\Controllers\BMS\MaterialCategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [\App\Http\Controllers\BMS\MaterialCategoryController::class, 'destroy'])->name('destroy');
        });

        // Purchase Orders
        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\PurchaseOrderController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\PurchaseOrderController::class, 'store'])->name('store');
            Route::get('/{purchaseOrder}', [\App\Http\Controllers\BMS\PurchaseOrderController::class, 'show'])->name('show');
            Route::post('/{purchaseOrder}/approve', [\App\Http\Controllers\BMS\PurchaseOrderController::class, 'approve'])->name('approve');
            Route::post('/{purchaseOrder}/receive', [\App\Http\Controllers\BMS\PurchaseOrderController::class, 'receive'])->name('receive');
            Route::post('/{purchaseOrder}/cancel', [\App\Http\Controllers\BMS\PurchaseOrderController::class, 'cancel'])->name('cancel');
            Route::get('/{purchaseOrder}/pdf', [\App\Http\Controllers\BMS\PurchaseOrderController::class, 'downloadPdf'])->name('pdf');
            Route::get('/{purchaseOrder}/pdf/preview', [\App\Http\Controllers\BMS\PurchaseOrderController::class, 'previewPdf'])->name('pdf.preview');
            
            // Create PO from Job
            Route::get('/jobs/{job}/create', [\App\Http\Controllers\BMS\PurchaseOrderController::class, 'createFromJob'])->name('create-from-job');
            Route::post('/jobs/{job}', [\App\Http\Controllers\BMS\PurchaseOrderController::class, 'storeFromJob'])->name('store-from-job');
        });

        // Vendors
        Route::prefix('vendors')->name('vendors.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\VendorController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\VendorController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\VendorController::class, 'store'])->name('store');
            Route::get('/{vendor}', [\App\Http\Controllers\BMS\VendorController::class, 'show'])->name('show');
            Route::get('/{vendor}/edit', [\App\Http\Controllers\BMS\VendorController::class, 'edit'])->name('edit');
            Route::put('/{vendor}', [\App\Http\Controllers\BMS\VendorController::class, 'update'])->name('update');
        });

        // Production Management
        Route::prefix('production')->name('production.')->group(function () {
            // Production Orders
            Route::get('/', [\App\Http\Controllers\BMS\ProductionController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\ProductionController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\ProductionController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\BMS\ProductionController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\BMS\ProductionController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\BMS\ProductionController::class, 'destroy'])->name('destroy');
            
            // Production Tracking
            Route::post('/{id}/tracking', [\App\Http\Controllers\BMS\ProductionController::class, 'updateTracking'])->name('tracking.update');
            
            // Quality Checkpoints
            Route::post('/checkpoints/{checkpointId}', [\App\Http\Controllers\BMS\ProductionController::class, 'updateCheckpoint'])->name('checkpoints.update');
            
            // Material Usage
            Route::post('/{id}/material-usage', [\App\Http\Controllers\BMS\ProductionController::class, 'recordMaterialUsage'])->name('material-usage.record');
            
            // Cutting Lists
            Route::get('/cutting-lists', [\App\Http\Controllers\BMS\ProductionController::class, 'cuttingLists'])->name('cutting-lists.index');
            Route::get('/{id}/cutting-lists/create', [\App\Http\Controllers\BMS\ProductionController::class, 'createCuttingList'])->name('cutting-lists.create');
            Route::post('/{id}/cutting-lists', [\App\Http\Controllers\BMS\ProductionController::class, 'storeCuttingList'])->name('cutting-lists.store');
            Route::get('/cutting-lists/{id}', [\App\Http\Controllers\BMS\ProductionController::class, 'showCuttingList'])->name('cutting-lists.show');
            Route::post('/cutting-lists/{id}/optimize', [\App\Http\Controllers\BMS\ProductionController::class, 'optimizeCuttingList'])->name('cutting-lists.optimize');
            
            // Waste Tracking
            Route::get('/waste', [\App\Http\Controllers\BMS\ProductionController::class, 'wasteTracking'])->name('waste.index');
            Route::post('/waste', [\App\Http\Controllers\BMS\ProductionController::class, 'storeWaste'])->name('waste.store');
        });

        // Operations Module (Unified Task Management)
        Route::prefix('operations')->name('operations.')->group(function () {
            // Dashboard
            Route::get('/dashboard', [\App\Http\Controllers\BMS\OperationsController::class, 'dashboard'])->name('dashboard');
            
            // My Tasks (Worker View)
            Route::get('/my-tasks', [\App\Http\Controllers\BMS\OperationsController::class, 'myTasks'])->name('my-tasks');
            
            // Tasks
            Route::prefix('tasks')->name('tasks.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\OperationsController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\BMS\OperationsController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\BMS\OperationsController::class, 'store'])->name('store');
                Route::get('/{id}', [\App\Http\Controllers\BMS\OperationsController::class, 'show'])->name('show');
                Route::put('/{id}', [\App\Http\Controllers\BMS\OperationsController::class, 'update'])->name('update');
                
                // Task Actions
                Route::post('/{id}/start', [\App\Http\Controllers\BMS\OperationsController::class, 'start'])->name('start');
                Route::post('/{id}/complete', [\App\Http\Controllers\BMS\OperationsController::class, 'complete'])->name('complete');
                Route::post('/{id}/block', [\App\Http\Controllers\BMS\OperationsController::class, 'block'])->name('block');
                Route::post('/{id}/unblock', [\App\Http\Controllers\BMS\OperationsController::class, 'unblock'])->name('unblock');
                Route::post('/{id}/reassign', [\App\Http\Controllers\BMS\OperationsController::class, 'reassign'])->name('reassign');
                Route::post('/{id}/move', [\App\Http\Controllers\BMS\OperationsController::class, 'moveTask'])->name('move');
                
                // Comments
                Route::post('/{id}/comments', [\App\Http\Controllers\BMS\OperationsController::class, 'storeComment'])->name('comments.store');
                
                // Attachments
                Route::post('/{id}/attachments', [\App\Http\Controllers\BMS\OperationsController::class, 'storeAttachment'])->name('attachments.store');
                Route::delete('/{id}/attachments/{attachmentId}', [\App\Http\Controllers\BMS\OperationsController::class, 'deleteAttachment'])->name('attachments.delete');
                
                // Time Entries
                Route::post('/{id}/time-entries', [\App\Http\Controllers\BMS\OperationsController::class, 'storeTimeEntry'])->name('time-entries.store');
                Route::post('/{id}/time-entries/{entryId}/stop', [\App\Http\Controllers\BMS\OperationsController::class, 'stopTimeEntry'])->name('time-entries.stop');
                
                // Dependencies
                Route::post('/{id}/dependencies', [\App\Http\Controllers\BMS\OperationsController::class, 'storeDependency'])->name('dependencies.store');
                Route::delete('/{id}/dependencies/{dependencyId}', [\App\Http\Controllers\BMS\OperationsController::class, 'deleteDependency'])->name('dependencies.delete');
                
                // Watchers
                Route::post('/{id}/watchers', [\App\Http\Controllers\BMS\OperationsController::class, 'addWatcher'])->name('watchers.add');
                Route::delete('/{id}/watchers/{watcherId}', [\App\Http\Controllers\BMS\OperationsController::class, 'removeWatcher'])->name('watchers.remove');
            });
            
            // Templates
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\OperationsController::class, 'templates'])->name('index');
                Route::post('/', [\App\Http\Controllers\BMS\OperationsController::class, 'storeTemplate'])->name('store');
                Route::post('/{id}/create-task', [\App\Http\Controllers\BMS\OperationsController::class, 'createFromTemplate'])->name('create-task');
            });
            
            // Recurring Tasks
            Route::prefix('recurring-tasks')->name('recurring-tasks.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\OperationsController::class, 'recurringTasks'])->name('index');
                Route::post('/', [\App\Http\Controllers\BMS\OperationsController::class, 'storeRecurringTask'])->name('store');
                Route::post('/{id}/toggle', [\App\Http\Controllers\BMS\OperationsController::class, 'toggleRecurringTask'])->name('toggle');
            });
            
            // Workflows
            Route::get('/workflows', [\App\Http\Controllers\BMS\OperationsController::class, 'workflows'])->name('workflows.index');
            Route::post('/workflows', [\App\Http\Controllers\BMS\OperationsController::class, 'storeWorkflow'])->name('workflows.store');
            Route::put('/workflows/{workflowId}', [\App\Http\Controllers\BMS\OperationsController::class, 'updateWorkflow'])->name('workflows.update');
            Route::post('/workflows/{workflowId}/stages', [\App\Http\Controllers\BMS\OperationsController::class, 'storeWorkflowStage'])->name('workflows.stages.store');
            
            // Kanban Board
            Route::get('/kanban', [\App\Http\Controllers\BMS\OperationsController::class, 'kanban'])->name('kanban');
            
            // Planning Dashboard
            Route::get('/planning', [\App\Http\Controllers\BMS\OperationsController::class, 'planning'])->name('planning');
            
            // ========================================
            // ADVANCED FEATURES
            // ========================================
            
            // Workload Balancing
            Route::get('/workload-balance', [\App\Http\Controllers\BMS\OperationsController::class, 'workloadBalance'])->name('workload-balance');
            
            // Capacity Forecast
            Route::get('/capacity-forecast', [\App\Http\Controllers\BMS\OperationsController::class, 'capacityForecast'])->name('capacity-forecast');
            
            // Bulk Operations
            Route::post('/bulk/reassign', [\App\Http\Controllers\BMS\OperationsController::class, 'bulkReassign'])->name('bulk.reassign');
            Route::post('/bulk/priority', [\App\Http\Controllers\BMS\OperationsController::class, 'bulkUpdatePriority'])->name('bulk.priority');
            Route::post('/bulk/reschedule', [\App\Http\Controllers\BMS\OperationsController::class, 'bulkReschedule'])->name('bulk.reschedule');
            
            // What-If Scenarios
            Route::get('/scenarios', [\App\Http\Controllers\BMS\OperationsController::class, 'scenarios'])->name('scenarios.index');
            Route::post('/scenarios', [\App\Http\Controllers\BMS\OperationsController::class, 'createScenario'])->name('scenarios.store');
            Route::post('/scenarios/{scenarioId}/apply', [\App\Http\Controllers\BMS\OperationsController::class, 'applyScenario'])->name('scenarios.apply');
            Route::post('/scenarios/{scenarioId}/reject', [\App\Http\Controllers\BMS\OperationsController::class, 'rejectScenario'])->name('scenarios.reject');
            
            // Resource Allocation
            Route::post('/resources/allocate', [\App\Http\Controllers\BMS\OperationsController::class, 'allocateResource'])->name('resources.allocate');
            Route::get('/resources/availability', [\App\Http\Controllers\BMS\OperationsController::class, 'resourceAvailability'])->name('resources.availability');
            Route::post('/resources/unavailability', [\App\Http\Controllers\BMS\OperationsController::class, 'setResourceUnavailability'])->name('resources.unavailability');
            
            // Analytics
            Route::get('/analytics', [\App\Http\Controllers\BMS\OperationsController::class, 'analytics'])->name('analytics');
            Route::get('/analytics/user/{userId}', [\App\Http\Controllers\BMS\OperationsController::class, 'userProductivity'])->name('analytics.user');
            Route::get('/gantt', [\App\Http\Controllers\BMS\OperationsController::class, 'gantt'])->name('gantt');
            
            // Integrations
            Route::post('/integrations/create-task-from-lead', [\App\Http\Controllers\BMS\OperationsController::class, 'createTaskFromLead'])->name('integrations.create-task-from-lead');
            Route::post('/integrations/create-invoice/{taskId}', [\App\Http\Controllers\BMS\OperationsController::class, 'createInvoiceFromTask'])->name('integrations.create-invoice');
            Route::get('/integrations/available-employees', [\App\Http\Controllers\BMS\OperationsController::class, 'availableEmployees'])->name('integrations.available-employees');
            Route::post('/integrations/setup-trigger', [\App\Http\Controllers\BMS\OperationsController::class, 'setupTrigger'])->name('integrations.setup-trigger');
        });

        // Assets
        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\AssetController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\AssetController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\AssetController::class, 'store'])->name('store');
            Route::get('/maintenance', [\App\Http\Controllers\BMS\AssetController::class, 'maintenance'])->name('maintenance');
            Route::get('/depreciation-register', [\App\Http\Controllers\BMS\AssetController::class, 'depreciationRegister'])->name('depreciation-register');
            Route::get('/{asset}', [\App\Http\Controllers\BMS\AssetController::class, 'show'])->name('show');
            Route::get('/{asset}/edit', [\App\Http\Controllers\BMS\AssetController::class, 'edit'])->name('edit');
            Route::put('/{asset}', [\App\Http\Controllers\BMS\AssetController::class, 'update'])->name('update');
            Route::post('/{asset}/assign', [\App\Http\Controllers\BMS\AssetController::class, 'assign'])->name('assign');
            Route::post('/{asset}/setup-depreciation', [\App\Http\Controllers\BMS\AssetController::class, 'setupDepreciation'])->name('setup-depreciation');
            Route::post('/{asset}/apply-depreciation', [\App\Http\Controllers\BMS\AssetController::class, 'applyDepreciation'])->name('apply-depreciation');
            Route::post('/{asset}/schedule-maintenance', [\App\Http\Controllers\BMS\AssetController::class, 'scheduleMaintenance'])->name('schedule-maintenance');
            Route::post('/assignments/{assignment}/return', [\App\Http\Controllers\BMS\AssetController::class, 'returnAsset'])->name('return');
            Route::post('/maintenance/{maintenance}/complete', [\App\Http\Controllers\BMS\AssetController::class, 'completeMaintenance'])->name('maintenance.complete');
        });

        // Contract Management
        Route::prefix('contracts')->name('contracts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\ContractController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\ContractController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\ContractController::class, 'store'])->name('store');
            Route::get('/{contract}', [\App\Http\Controllers\BMS\ContractController::class, 'show'])->name('show');
            Route::get('/{contract}/edit', [\App\Http\Controllers\BMS\ContractController::class, 'edit'])->name('edit');
            Route::put('/{contract}', [\App\Http\Controllers\BMS\ContractController::class, 'update'])->name('update');
            Route::post('/{contract}/activate', [\App\Http\Controllers\BMS\ContractController::class, 'activate'])->name('activate');
            Route::post('/{contract}/terminate', [\App\Http\Controllers\BMS\ContractController::class, 'terminate'])->name('terminate');
            Route::post('/{contract}/sign', [\App\Http\Controllers\BMS\ContractController::class, 'sign'])->name('sign');
            Route::post('/{contract}/send-for-signing', [\App\Http\Controllers\BMS\ContractController::class, 'sendForSigning'])->name('send-for-signing');
            Route::get('/{contract}/sign/{token}', [\App\Http\Controllers\BMS\ContractController::class, 'showSigningPage'])->name('sign-with-token');
            Route::post('/{contract}/sign/{token}', [\App\Http\Controllers\BMS\ContractController::class, 'submitCustomerSignature'])->name('submit-signature');
            Route::get('/{contract}/signed-confirmation', [\App\Http\Controllers\BMS\ContractController::class, 'signedConfirmation'])->name('signed-confirmation');
            Route::get('/{contract}/download-pdf', [\App\Http\Controllers\BMS\ContractController::class, 'downloadPdf'])->name('download-pdf');
            Route::post('/{contract}/renew', [\App\Http\Controllers\BMS\ContractController::class, 'renew'])->name('renew');
        });

        // Loans Receivable (Company-scoped loan management)
        Route::prefix('loans')->name('loans.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\LoanController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\LoanController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\LoanController::class, 'store'])->name('store');
            Route::get('/{loan}', [\App\Http\Controllers\BMS\LoanController::class, 'show'])->name('show');
            Route::get('/{loan}/payment', [\App\Http\Controllers\BMS\LoanController::class, 'paymentForm'])->name('payment');
            Route::post('/{loan}/payment', [\App\Http\Controllers\BMS\LoanController::class, 'recordPayment'])->name('payment.store');
            Route::get('/reports/aging', [\App\Http\Controllers\BMS\LoanController::class, 'agingReport'])->name('reports.aging');
        });

        // Payroll & Workers
        Route::prefix('payroll')->name('payroll.')->group(function () {
            // Workers
            Route::prefix('workers')->name('workers.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\PayrollController::class, 'workersIndex'])->name('index');
                Route::get('/create', [\App\Http\Controllers\BMS\PayrollController::class, 'workersCreate'])->name('create');
                Route::post('/', [\App\Http\Controllers\BMS\PayrollController::class, 'workersStore'])->name('store');
                Route::get('/{worker}', [\App\Http\Controllers\BMS\PayrollController::class, 'workersShow'])->name('show');
                
                // Worker Allowances
                Route::get('/{worker}/allowances', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'workerAllowancesIndex'])->name('allowances.index');
                Route::post('/{worker}/allowances', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'storeWorkerAllowance'])->name('allowances.store');
                Route::put('/{worker}/allowances/{allowance}', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'updateWorkerAllowance'])->name('allowances.update');
                Route::delete('/{worker}/allowances/{allowance}', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'deleteWorkerAllowance'])->name('allowances.delete');
                
                // Worker Deductions
                Route::get('/{worker}/deductions', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'workerDeductionsIndex'])->name('deductions.index');
                Route::post('/{worker}/deductions', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'storeWorkerDeduction'])->name('deductions.store');
                Route::put('/{worker}/deductions/{deduction}', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'updateWorkerDeduction'])->name('deductions.update');
                Route::delete('/{worker}/deductions/{deduction}', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'deleteWorkerDeduction'])->name('deductions.delete');
            });

            // Payroll Configuration
            Route::prefix('configuration')->name('configuration.')->group(function () {
                // Allowance Types
                Route::get('/allowance-types', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'allowanceTypesIndex'])->name('allowance-types.index');
                Route::post('/allowance-types', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'storeAllowanceType'])->name('allowance-types.store');
                Route::put('/allowance-types/{allowanceType}', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'updateAllowanceType'])->name('allowance-types.update');
                
                // Deduction Types
                Route::get('/deduction-types', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'deductionTypesIndex'])->name('deduction-types.index');
                Route::post('/deduction-types', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'storeDeductionType'])->name('deduction-types.store');
                Route::put('/deduction-types/{deductionType}', [\App\Http\Controllers\BMS\PayrollConfigurationController::class, 'updateDeductionType'])->name('deduction-types.update');
            });

            // Attendance
            Route::post('/attendance', [\App\Http\Controllers\BMS\PayrollController::class, 'attendanceStore'])->name('attendance.store');
            Route::post('/attendance/{attendance}/approve', [\App\Http\Controllers\BMS\PayrollController::class, 'attendanceApprove'])->name('attendance.approve');

            // Commissions
            Route::post('/commissions', [\App\Http\Controllers\BMS\PayrollController::class, 'commissionStore'])->name('commissions.store');
            Route::post('/commissions/{commission}/approve', [\App\Http\Controllers\BMS\PayrollController::class, 'commissionApprove'])->name('commissions.approve');

            // Payroll Runs
            Route::get('/', [\App\Http\Controllers\BMS\PayrollController::class, 'payrollIndex'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\PayrollController::class, 'payrollCreate'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\PayrollController::class, 'payrollStore'])->name('store');
            Route::get('/{payrollRun}', [\App\Http\Controllers\BMS\PayrollController::class, 'payrollShow'])->name('show');
            Route::post('/{payrollRun}/approve', [\App\Http\Controllers\BMS\PayrollController::class, 'payrollApprove'])->name('approve');
            Route::post('/{payrollRun}/mark-paid', [\App\Http\Controllers\BMS\PayrollController::class, 'payrollMarkPaid'])->name('mark-paid');
        });

        // HRMS - Branches
        Route::resource('branches', \App\Http\Controllers\BMS\BranchController::class)->only([
            'index', 'create', 'store', 'edit', 'update', 'destroy'
        ]);

        // HRMS - Departments
        Route::prefix('departments')->name('departments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\DepartmentController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\DepartmentController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\DepartmentController::class, 'store'])->name('store');
            Route::get('/{department}/edit', [\App\Http\Controllers\BMS\DepartmentController::class, 'edit'])->name('edit');
            Route::put('/{department}', [\App\Http\Controllers\BMS\DepartmentController::class, 'update'])->name('update');
            Route::delete('/{department}', [\App\Http\Controllers\BMS\DepartmentController::class, 'destroy'])->name('destroy');
        });

        // HRMS - Leave Management
        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\LeaveController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\LeaveController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\LeaveController::class, 'store'])->name('store');
            Route::get('/{leave}', [\App\Http\Controllers\BMS\LeaveController::class, 'show'])->name('show');
            Route::post('/{leave}/approve', [\App\Http\Controllers\BMS\LeaveController::class, 'approve'])->name('approve');
            Route::post('/{leave}/reject', [\App\Http\Controllers\BMS\LeaveController::class, 'reject'])->name('reject');
            Route::get('/balance/view', [\App\Http\Controllers\BMS\LeaveController::class, 'balance'])->name('balance');
        });

        // HRMS - Shift Management
        Route::resource('shifts', \App\Http\Controllers\BMS\ShiftController::class);
        Route::post('/shifts/{shift}/assign', [\App\Http\Controllers\BMS\ShiftController::class, 'assign'])->name('shifts.assign');

        // HRMS - Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\AttendanceController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\BMS\AttendanceController::class, 'store'])->name('store');
            Route::post('/clock-in', [\App\Http\Controllers\BMS\AttendanceController::class, 'clockIn'])->name('clock-in');
            Route::post('/{record}/clock-out', [\App\Http\Controllers\BMS\AttendanceController::class, 'clockOut'])->name('clock-out');
            Route::get('/summary', [\App\Http\Controllers\BMS\AttendanceController::class, 'summary'])->name('summary');
            Route::get('/calendar', [\App\Http\Controllers\BMS\AttendanceController::class, 'calendar'])->name('calendar');
        });

        // HRMS - Overtime
        Route::prefix('overtime')->name('overtime.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\OvertimeController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\BMS\OvertimeController::class, 'store'])->name('store');
            Route::post('/{record}/approve', [\App\Http\Controllers\BMS\OvertimeController::class, 'approve'])->name('approve');
            Route::post('/{record}/reject', [\App\Http\Controllers\BMS\OvertimeController::class, 'reject'])->name('reject');
            Route::get('/summary', [\App\Http\Controllers\BMS\OvertimeController::class, 'summary'])->name('summary');
        });

        // Time Tracking
        Route::prefix('time-tracking')->name('time-tracking.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'index'])->name('index');
            Route::post('/start-timer', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'startTimer'])->name('start-timer');
            Route::post('/{entry}/stop-timer', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'stopTimer'])->name('stop-timer');
            Route::post('/', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'store'])->name('store');
            Route::put('/{entry}', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'update'])->name('update');
            Route::delete('/{entry}', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'destroy'])->name('destroy');
            Route::post('/{entry}/submit', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'submit'])->name('submit');
            Route::post('/{entry}/approve', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'approve'])->name('approve');
            Route::post('/{entry}/reject', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'reject'])->name('reject');
            
            // Timesheets
            Route::get('/timesheets', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'timesheets'])->name('timesheets.index');
            Route::post('/timesheets/generate', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'generateTimesheet'])->name('timesheets.generate');
            Route::post('/timesheets/{timesheet}/submit', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'submitTimesheet'])->name('timesheets.submit');
            Route::post('/timesheets/{timesheet}/approve', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'approveTimesheet'])->name('timesheets.approve');
            Route::post('/timesheets/{timesheet}/reject', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'rejectTimesheet'])->name('timesheets.reject');
            
            // Reports
            Route::get('/reports', [\App\Http\Controllers\BMS\TimeTrackingController::class, 'reports'])->name('reports');
        });

        // Analytics
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/overview', [\App\Http\Controllers\BMS\AnalyticsController::class, 'overview'])->name('overview');
            Route::get('/operations', [\App\Http\Controllers\BMS\AnalyticsController::class, 'operations'])->name('operations');
            Route::get('/finance', [\App\Http\Controllers\BMS\AnalyticsController::class, 'finance'])->name('finance');
            Route::get('/procurement', [\App\Http\Controllers\BMS\AnalyticsController::class, 'procurement'])->name('procurement');
            Route::get('/finance/export-csv', [\App\Http\Controllers\BMS\AnalyticsController::class, 'exportCsv'])->name('finance.export-csv');
        });

        // Approvals
        Route::prefix('approvals')->name('approvals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\ApprovalController::class, 'index'])->name('index');
            Route::get('/{approval}', [\App\Http\Controllers\BMS\ApprovalController::class, 'show'])->name('show');
            Route::post('/{approval}/approve', [\App\Http\Controllers\BMS\ApprovalController::class, 'approve'])->name('approve');
            Route::post('/{approval}/reject', [\App\Http\Controllers\BMS\ApprovalController::class, 'reject'])->name('reject');
            Route::post('/{approval}/cancel', [\App\Http\Controllers\BMS\ApprovalController::class, 'cancel'])->name('cancel');
            
            // Approval Chains Management
            Route::get('/chains/manage', [\App\Http\Controllers\BMS\ApprovalController::class, 'chains'])->name('chains.index');
            Route::post('/chains', [\App\Http\Controllers\BMS\ApprovalController::class, 'storeChain'])->name('chains.store');
            Route::put('/chains/{chain}', [\App\Http\Controllers\BMS\ApprovalController::class, 'updateChain'])->name('chains.update');
            Route::delete('/chains/{chain}', [\App\Http\Controllers\BMS\ApprovalController::class, 'deleteChain'])->name('chains.delete');
        });

        // Settings - Industry Presets
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\SettingsController::class, 'index'])->name('index');
            Route::post('/business-hours', [\App\Http\Controllers\BMS\SettingsController::class, 'updateBusinessHours'])->name('business-hours.update');
            Route::post('/tax', [\App\Http\Controllers\BMS\SettingsController::class, 'updateTaxSettings'])->name('tax.update');
            Route::post('/approval-thresholds', [\App\Http\Controllers\BMS\SettingsController::class, 'updateApprovalThresholds'])->name('approval-thresholds.update');
            Route::post('/invoice', [\App\Http\Controllers\BMS\SettingsController::class, 'updateInvoiceSettings'])->name('invoice.update');
            Route::post('/notifications', [\App\Http\Controllers\BMS\SettingsController::class, 'updateNotificationSettings'])->name('notifications.update');
            Route::post('/payment-instructions', [\App\Http\Controllers\BMS\SettingsController::class, 'updatePaymentInstructions'])->name('payment-instructions.update');
            Route::post('/branding', [\App\Http\Controllers\BMS\SettingsController::class, 'updateBrandingSettings'])->name('branding.update');
            Route::post('/logo', [\App\Http\Controllers\BMS\SettingsController::class, 'uploadLogo'])->name('logo.upload');
            Route::delete('/logo', [\App\Http\Controllers\BMS\SettingsController::class, 'deleteLogo'])->name('logo.delete');
            Route::post('/sms', [\App\Http\Controllers\BMS\SettingsController::class, 'updateSmsSettings'])->name('sms.update');
            Route::post('/reset-defaults', [\App\Http\Controllers\BMS\SettingsController::class, 'resetToDefaults'])->name('reset-defaults');
            Route::post('/document-defaults', [\App\Http\Controllers\BMS\SettingsController::class, 'updateDocumentDefaults'])->name('document-defaults.update');
            Route::post('/signature', [\App\Http\Controllers\BMS\SettingsController::class, 'uploadSignature'])->name('signature.upload');
            Route::delete('/signature', [\App\Http\Controllers\BMS\SettingsController::class, 'deleteSignature'])->name('signature.delete');
            
            Route::get('/industry-presets', [\App\Http\Controllers\BMS\IndustryPresetController::class, 'index'])->name('industry-presets.index');
            Route::get('/industry-presets/{code}', [\App\Http\Controllers\BMS\IndustryPresetController::class, 'show'])->name('industry-presets.show');
            Route::post('/industry-presets/apply', [\App\Http\Controllers\BMS\IndustryPresetController::class, 'apply'])->name('industry-presets.apply');
            
            // Email Settings
            Route::get('/email', [\App\Http\Controllers\BMS\EmailSettingsController::class, 'index'])->name('email.index');
            Route::post('/email', [\App\Http\Controllers\BMS\EmailSettingsController::class, 'update'])->name('email.update');
            Route::post('/email/test-connection', [\App\Http\Controllers\BMS\EmailSettingsController::class, 'testConnection'])->name('email.test-connection');
            Route::get('/email/logs', [\App\Http\Controllers\BMS\EmailSettingsController::class, 'logs'])->name('email.logs');
            Route::get('/email/templates', [\App\Http\Controllers\BMS\EmailSettingsController::class, 'templates'])->name('email.templates');
            Route::put('/email/templates/{id}', [\App\Http\Controllers\BMS\EmailSettingsController::class, 'updateTemplate'])->name('email.templates.update');
            
            // SMS Settings
            Route::get('/sms', [\App\Http\Controllers\BMS\SmsSettingsController::class, 'index'])->name('sms.index');
            Route::post('/sms', [\App\Http\Controllers\BMS\SmsSettingsController::class, 'update'])->name('sms.update');
            Route::post('/sms/test-connection', [\App\Http\Controllers\BMS\SmsSettingsController::class, 'testConnection'])->name('sms.test-connection');
            Route::get('/sms/logs', [\App\Http\Controllers\BMS\SmsSettingsController::class, 'logs'])->name('sms.logs');
            
            // Currency Settings
            Route::get('/currency', [\App\Http\Controllers\BMS\CurrencyController::class, 'index'])->name('currency.index');
            Route::post('/currency', [\App\Http\Controllers\BMS\CurrencyController::class, 'updateSettings'])->name('currency.update');
            Route::post('/currency/exchange-rate', [\App\Http\Controllers\BMS\CurrencyController::class, 'setExchangeRate'])->name('currency.exchange-rate.set');
            Route::get('/currency/exchange-rate', [\App\Http\Controllers\BMS\CurrencyController::class, 'getExchangeRate'])->name('currency.exchange-rate.get');
            Route::post('/currency/convert', [\App\Http\Controllers\BMS\CurrencyController::class, 'convert'])->name('currency.convert');
            Route::get('/currency/history', [\App\Http\Controllers\BMS\CurrencyController::class, 'history'])->name('currency.history');
            Route::post('/currency/fetch-live-rates', [\App\Http\Controllers\BMS\CurrencyController::class, 'fetchLiveRates'])->name('currency.fetch-live-rates');
        });

        // Onboarding
        Route::prefix('onboarding')->name('onboarding.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\OnboardingController::class, 'index'])->name('index');
            Route::get('/status', [\App\Http\Controllers\BMS\OnboardingController::class, 'status'])->name('status');
            Route::post('/company-info', [\App\Http\Controllers\BMS\OnboardingController::class, 'updateCompanyInfo'])->name('company-info');
            Route::post('/apply-preset', [\App\Http\Controllers\BMS\OnboardingController::class, 'applyPreset'])->name('apply-preset');
            Route::post('/configure-settings', [\App\Http\Controllers\BMS\OnboardingController::class, 'configureSettings'])->name('configure-settings');
            Route::post('/complete-step', [\App\Http\Controllers\BMS\OnboardingController::class, 'completeStep'])->name('complete-step');
            Route::post('/skip-step', [\App\Http\Controllers\BMS\OnboardingController::class, 'skipStep'])->name('skip-step');
            Route::post('/complete', [\App\Http\Controllers\BMS\OnboardingController::class, 'complete'])->name('complete');
            
            // Sample Data
            Route::post('/generate-sample-data', [\App\Http\Controllers\BMS\OnboardingController::class, 'generateSampleData'])->name('generate-sample-data');
            Route::post('/clear-sample-data', [\App\Http\Controllers\BMS\OnboardingController::class, 'clearSampleData'])->name('clear-sample-data');
            
            // Progress Persistence
            Route::post('/save-progress', [\App\Http\Controllers\BMS\OnboardingController::class, 'saveProgress'])->name('save-progress');
            Route::get('/saved-progress', [\App\Http\Controllers\BMS\OnboardingController::class, 'getSavedProgress'])->name('saved-progress');
            
            // Guided Tour
            Route::get('/tour/status', [\App\Http\Controllers\BMS\OnboardingController::class, 'tourStatus'])->name('tour.status');
            Route::post('/tour/complete-step', [\App\Http\Controllers\BMS\OnboardingController::class, 'completeTourStep'])->name('tour.complete-step');
            Route::post('/tour/skip', [\App\Http\Controllers\BMS\OnboardingController::class, 'skipTour'])->name('tour.skip');
        });

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\NotificationController::class, 'index'])->name('index');
            Route::get('/recent', [\App\Http\Controllers\BMS\NotificationController::class, 'recent'])->name('recent');
            Route::post('/{id}/mark-read', [\App\Http\Controllers\BMS\NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::post('/mark-all-read', [\App\Http\Controllers\BMS\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('/{id}', [\App\Http\Controllers\BMS\NotificationController::class, 'destroy'])->name('destroy');
        });

        // Security
        Route::prefix('security')->name('security.')->group(function () {
            Route::get('/settings', [\App\Http\Controllers\BMS\SecurityController::class, 'settings'])->name('settings');
            Route::post('/settings', [\App\Http\Controllers\BMS\SecurityController::class, 'updateSettings'])->name('settings.update');
            Route::get('/audit-logs', [\App\Http\Controllers\BMS\SecurityController::class, 'auditLogs'])->name('audit-logs');
            Route::get('/suspicious-activity', [\App\Http\Controllers\BMS\SecurityController::class, 'suspiciousActivity'])->name('suspicious-activity');
            Route::post('/suspicious-activity/{activity}/review', [\App\Http\Controllers\BMS\SecurityController::class, 'markActivityReviewed'])->name('suspicious-activity.review');
            
            // 2FA
            Route::get('/2fa/enable', [\App\Http\Controllers\BMS\SecurityController::class, 'enable2FA'])->name('2fa.enable');
            Route::post('/2fa/verify', [\App\Http\Controllers\BMS\SecurityController::class, 'verify2FA'])->name('2fa.verify');
            Route::post('/2fa/disable', [\App\Http\Controllers\BMS\SecurityController::class, 'disable2FA'])->name('2fa.disable');
        });

        // Accounting / Chart of Accounts
        Route::prefix('accounting')->name('accounting.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\AccountingController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\AccountingController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\AccountingController::class, 'store'])->name('store');
            Route::get('/trial-balance', [\App\Http\Controllers\BMS\AccountingController::class, 'trialBalance'])->name('trial-balance');
            Route::get('/journal-entries', [\App\Http\Controllers\BMS\AccountingController::class, 'journalEntries'])->name('journal-entries');
            Route::post('/initialize', [\App\Http\Controllers\BMS\AccountingController::class, 'initializeAccounts'])->name('initialize');
            Route::get('/{id}', [\App\Http\Controllers\BMS\AccountingController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\BMS\AccountingController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\BMS\AccountingController::class, 'destroy'])->name('destroy');
        });

        // HRMS - Recruitment
        Route::prefix('recruitment')->name('recruitment.')->group(function () {
            // Job Postings
            Route::get('/job-postings', [\App\Http\Controllers\BMS\RecruitmentController::class, 'jobPostingsIndex'])->name('job-postings.index');
            Route::get('/job-postings/create', [\App\Http\Controllers\BMS\RecruitmentController::class, 'jobPostingsCreate'])->name('job-postings.create');
            Route::post('/job-postings', [\App\Http\Controllers\BMS\RecruitmentController::class, 'jobPostingsStore'])->name('job-postings.store');
            
            // Applications
            Route::get('/job-postings/{jobPostingId}/applications', [\App\Http\Controllers\BMS\RecruitmentController::class, 'applicationsIndex'])->name('applications.index');
            Route::put('/applications/{applicationId}/status', [\App\Http\Controllers\BMS\RecruitmentController::class, 'applicationsUpdateStatus'])->name('applications.update-status');
            
            // Interviews
            Route::get('/interviews', [\App\Http\Controllers\BMS\RecruitmentController::class, 'interviewsIndex'])->name('interviews.index');
            Route::post('/interviews', [\App\Http\Controllers\BMS\RecruitmentController::class, 'interviewsStore'])->name('interviews.store');
            Route::post('/interviews/{interviewId}/evaluations', [\App\Http\Controllers\BMS\RecruitmentController::class, 'evaluationsStore'])->name('evaluations.store');
        });

        // HRMS - Onboarding
        Route::prefix('hrms-onboarding')->name('hrms-onboarding.')->group(function () {
            // Templates
            Route::get('/templates', [\App\Http\Controllers\BMS\OnboardingController::class, 'templatesIndex'])->name('templates.index');
            Route::post('/templates', [\App\Http\Controllers\BMS\OnboardingController::class, 'templatesStore'])->name('templates.store');
            Route::post('/templates/{templateId}/tasks', [\App\Http\Controllers\BMS\OnboardingController::class, 'tasksStore'])->name('tasks.store');
            
            // Employee Onboarding
            Route::get('/employee', [\App\Http\Controllers\BMS\OnboardingController::class, 'employeeOnboardingIndex'])->name('employee.index');
            Route::post('/employee', [\App\Http\Controllers\BMS\OnboardingController::class, 'employeeOnboardingStore'])->name('employee.store');
            Route::get('/employee/{workerId}', [\App\Http\Controllers\BMS\OnboardingController::class, 'employeeOnboardingShow'])->name('employee.show');
            Route::post('/task-progress/{taskProgressId}/complete', [\App\Http\Controllers\BMS\OnboardingController::class, 'taskProgressComplete'])->name('task-progress.complete');
        });

        // HRMS - Performance Management
        Route::prefix('performance')->name('performance.')->group(function () {
            // Performance Cycles
            Route::get('/cycles', [\App\Http\Controllers\BMS\PerformanceController::class, 'cyclesIndex'])->name('cycles.index');
            Route::post('/cycles', [\App\Http\Controllers\BMS\PerformanceController::class, 'cyclesStore'])->name('cycles.store');
            Route::post('/cycles/{cycleId}/activate', [\App\Http\Controllers\BMS\PerformanceController::class, 'cyclesActivate'])->name('cycles.activate');
            
            // Performance Reviews
            Route::get('/reviews', [\App\Http\Controllers\BMS\PerformanceController::class, 'reviewsIndex'])->name('reviews.index');
            Route::get('/reviews/create', [\App\Http\Controllers\BMS\PerformanceController::class, 'reviewsCreate'])->name('reviews.create');
            Route::post('/reviews', [\App\Http\Controllers\BMS\PerformanceController::class, 'reviewsStore'])->name('reviews.store');
            Route::get('/reviews/{reviewId}', [\App\Http\Controllers\BMS\PerformanceController::class, 'reviewsShow'])->name('reviews.show');
            Route::post('/reviews/{reviewId}/submit', [\App\Http\Controllers\BMS\PerformanceController::class, 'reviewsSubmit'])->name('reviews.submit');
            
            // Goals
            Route::get('/goals', [\App\Http\Controllers\BMS\PerformanceController::class, 'goalsIndex'])->name('goals.index');
            Route::post('/goals', [\App\Http\Controllers\BMS\PerformanceController::class, 'goalsStore'])->name('goals.store');
            Route::post('/goals/{goalId}/progress', [\App\Http\Controllers\BMS\PerformanceController::class, 'goalsUpdateProgress'])->name('goals.update-progress');
            
            // Performance Improvement Plans
            Route::get('/pips', [\App\Http\Controllers\BMS\PerformanceController::class, 'pipsIndex'])->name('pips.index');
            Route::post('/pips', [\App\Http\Controllers\BMS\PerformanceController::class, 'pipsStore'])->name('pips.store');
            Route::post('/pips/milestones/{milestoneId}/complete', [\App\Http\Controllers\BMS\PerformanceController::class, 'pipsMilestoneComplete'])->name('pips.milestone-complete');
            Route::post('/pips/{pipId}/close', [\App\Http\Controllers\BMS\PerformanceController::class, 'pipsClose'])->name('pips.close');
        });

        // Training & Development (Phase 6)
        Route::prefix('training')->name('training.')->group(function () {
            // Training Programs
            Route::get('/programs', [\App\Http\Controllers\BMS\TrainingController::class, 'programs'])->name('programs');
            Route::post('/programs', [\App\Http\Controllers\BMS\TrainingController::class, 'createProgram'])->name('programs.create');
            
            // Training Sessions
            Route::get('/sessions', [\App\Http\Controllers\BMS\TrainingController::class, 'sessions'])->name('sessions');
            
            // Skills Catalog
            Route::get('/skills', [\App\Http\Controllers\BMS\TrainingController::class, 'skills'])->name('skills');
            
            // Certifications
            Route::get('/certifications', [\App\Http\Controllers\BMS\TrainingController::class, 'certifications'])->name('certifications');
        });

        // Employee Self-Service Portal (Phase 8)
        Route::prefix('employee')->name('employee.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\BMS\EmployeePortalController::class, 'dashboard'])->name('dashboard');
            Route::get('/profile', [\App\Http\Controllers\BMS\EmployeePortalController::class, 'profile'])->name('profile');
            Route::get('/documents', [\App\Http\Controllers\BMS\EmployeePortalController::class, 'documents'])->name('documents');
            Route::post('/documents/request', [\App\Http\Controllers\BMS\EmployeePortalController::class, 'requestDocument'])->name('documents.request');
            Route::post('/feedback', [\App\Http\Controllers\BMS\EmployeePortalController::class, 'submitFeedback'])->name('feedback.submit');
        });

        // HR Reports & Analytics (Phase 9)
        Route::prefix('hr-reports')->name('hr-reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\HRReportsController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\BMS\HRReportsController::class, 'generate'])->name('generate');
            Route::get('/{id}', [\App\Http\Controllers\BMS\HRReportsController::class, 'show'])->name('show');
            Route::delete('/{id}', [\App\Http\Controllers\BMS\HRReportsController::class, 'destroy'])->name('destroy');
        });

        // Construction Modules - Projects
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\ProjectController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\ProjectController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\ProjectController::class, 'store'])->name('store');
            Route::get('/{project}', [\App\Http\Controllers\BMS\ProjectController::class, 'show'])->name('show');
            Route::get('/{project}/edit', [\App\Http\Controllers\BMS\ProjectController::class, 'edit'])->name('edit');
            Route::put('/{project}', [\App\Http\Controllers\BMS\ProjectController::class, 'update'])->name('update');
            Route::delete('/{project}', [\App\Http\Controllers\BMS\ProjectController::class, 'destroy'])->name('destroy');
            Route::post('/{project}/status', [\App\Http\Controllers\BMS\ProjectController::class, 'updateStatus'])->name('status');
            Route::post('/{project}/milestones', [\App\Http\Controllers\BMS\ProjectController::class, 'storeMilestone'])->name('milestones.store');
            Route::post('/{project}/milestones/{milestone}/complete', [\App\Http\Controllers\BMS\ProjectController::class, 'completeMilestone'])->name('milestones.complete');
            Route::get('/{project}/timeline', [\App\Http\Controllers\BMS\ProjectController::class, 'timeline'])->name('timeline');
        });

        // Construction Modules - Subcontractors
        Route::prefix('subcontractors')->name('subcontractors.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\SubcontractorController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\SubcontractorController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\SubcontractorController::class, 'store'])->name('store');
            Route::get('/{subcontractor}', [\App\Http\Controllers\BMS\SubcontractorController::class, 'show'])->name('show');
            Route::get('/{subcontractor}/edit', [\App\Http\Controllers\BMS\SubcontractorController::class, 'edit'])->name('edit');
            Route::put('/{subcontractor}', [\App\Http\Controllers\BMS\SubcontractorController::class, 'update'])->name('update');
            Route::delete('/{subcontractor}', [\App\Http\Controllers\BMS\SubcontractorController::class, 'destroy'])->name('destroy');
            Route::post('/{subcontractor}/assign', [\App\Http\Controllers\BMS\SubcontractorController::class, 'assign'])->name('assign');
            Route::post('/{subcontractor}/payments', [\App\Http\Controllers\BMS\SubcontractorController::class, 'recordPayment'])->name('payments.store');
            Route::post('/{subcontractor}/rate', [\App\Http\Controllers\BMS\SubcontractorController::class, 'rate'])->name('rate');
        });

        // Construction Modules - Equipment
        Route::prefix('equipment')->name('equipment.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\EquipmentController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\EquipmentController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\EquipmentController::class, 'store'])->name('store');
            Route::get('/{equipment}', [\App\Http\Controllers\BMS\EquipmentController::class, 'show'])->name('show');
            Route::get('/{equipment}/edit', [\App\Http\Controllers\BMS\EquipmentController::class, 'edit'])->name('edit');
            Route::put('/{equipment}', [\App\Http\Controllers\BMS\EquipmentController::class, 'update'])->name('update');
            Route::delete('/{equipment}', [\App\Http\Controllers\BMS\EquipmentController::class, 'destroy'])->name('destroy');
            Route::post('/{equipment}/maintenance', [\App\Http\Controllers\BMS\EquipmentController::class, 'scheduleMaintenance'])->name('maintenance.schedule');
            Route::post('/maintenance/{maintenance}/complete', [\App\Http\Controllers\BMS\EquipmentController::class, 'completeMaintenance'])->name('maintenance.complete');
            Route::post('/{equipment}/usage', [\App\Http\Controllers\BMS\EquipmentController::class, 'recordUsage'])->name('usage.store');
            Route::post('/{equipment}/rentals', [\App\Http\Controllers\BMS\EquipmentController::class, 'createRental'])->name('rentals.store');
        });

        // Construction Modules - Labour
        Route::prefix('labour')->name('labour.')->group(function () {
            // Crews
            Route::prefix('crews')->name('crews.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\LabourController::class, 'crewsIndex'])->name('index');
                Route::get('/create', [\App\Http\Controllers\BMS\LabourController::class, 'crewsCreate'])->name('create');
                Route::post('/', [\App\Http\Controllers\BMS\LabourController::class, 'crewsStore'])->name('store');
                Route::get('/{crew}', [\App\Http\Controllers\BMS\LabourController::class, 'crewsShow'])->name('show');
                Route::put('/{crew}', [\App\Http\Controllers\BMS\LabourController::class, 'crewsUpdate'])->name('update');
                Route::post('/{crew}/members', [\App\Http\Controllers\BMS\LabourController::class, 'addCrewMember'])->name('members.add');
                Route::delete('/{crew}/members/{member}', [\App\Http\Controllers\BMS\LabourController::class, 'removeCrewMember'])->name('members.remove');
            });
            
            // Timesheets
            Route::prefix('timesheets')->name('timesheets.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\LabourController::class, 'timesheetsIndex'])->name('index');
                Route::get('/create', [\App\Http\Controllers\BMS\LabourController::class, 'timesheetsCreate'])->name('create');
                Route::post('/', [\App\Http\Controllers\BMS\LabourController::class, 'timesheetsStore'])->name('store');
                Route::post('/{timesheet}/approve', [\App\Http\Controllers\BMS\LabourController::class, 'timesheetsApprove'])->name('approve');
                Route::post('/{timesheet}/reject', [\App\Http\Controllers\BMS\LabourController::class, 'timesheetsReject'])->name('reject');
            });
            
            // Productivity
            Route::get('/productivity', [\App\Http\Controllers\BMS\LabourController::class, 'productivity'])->name('productivity');
        });

        // Construction Modules - BOQ
        Route::prefix('boq')->name('boq.')->group(function () {
            // Templates
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\BOQController::class, 'templatesIndex'])->name('index');
                Route::post('/', [\App\Http\Controllers\BMS\BOQController::class, 'templatesStore'])->name('store');
            });
            
            // BOQs
            Route::get('/', [\App\Http\Controllers\BMS\BOQController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\BMS\BOQController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\BMS\BOQController::class, 'store'])->name('store');
            Route::get('/{boq}', [\App\Http\Controllers\BMS\BOQController::class, 'show'])->name('show');
            Route::get('/{boq}/edit', [\App\Http\Controllers\BMS\BOQController::class, 'edit'])->name('edit');
            Route::put('/{boq}', [\App\Http\Controllers\BMS\BOQController::class, 'update'])->name('update');
            Route::delete('/{boq}', [\App\Http\Controllers\BMS\BOQController::class, 'destroy'])->name('destroy');
            Route::post('/{boq}/variations', [\App\Http\Controllers\BMS\BOQController::class, 'addVariation'])->name('variations.add');
            Route::post('/{boq}/variations/{variation}/approve', [\App\Http\Controllers\BMS\BOQController::class, 'approveVariation'])->name('variations.approve');
            Route::post('/{boq}/actuals', [\App\Http\Controllers\BMS\BOQController::class, 'updateActuals'])->name('actuals.update');
            Route::get('/{boq}/export', [\App\Http\Controllers\BMS\BOQController::class, 'export'])->name('export');
        });

        // Construction Modules - Progress Billing
        Route::prefix('progress-billing')->name('progress-billing.')->group(function () {
            // Progress Certificates
            Route::prefix('certificates')->name('certificates.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'certificatesIndex'])->name('index');
                Route::get('/create', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'certificatesCreate'])->name('create');
                Route::post('/', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'certificatesStore'])->name('store');
                Route::get('/{certificate}', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'certificatesShow'])->name('show');
                Route::post('/{certificate}/approve', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'certificatesApprove'])->name('approve');
                Route::post('/{certificate}/reject', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'certificatesReject'])->name('reject');
            });
            
            // Payment Applications
            Route::prefix('applications')->name('applications.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'applicationsIndex'])->name('index');
                Route::post('/', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'applicationsStore'])->name('store');
                Route::post('/{application}/approve', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'applicationsApprove'])->name('approve');
            });
            
            // Retention
            Route::prefix('retention')->name('retention.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'retentionIndex'])->name('index');
                Route::post('/release', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'releaseRetention'])->name('release');
            });
            
            // Billing Stages
            Route::prefix('projects/{project}/stages')->name('stages.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'stagesIndex'])->name('index');
                Route::post('/', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'stagesStore'])->name('store');
                Route::post('/{stage}/complete', [\App\Http\Controllers\BMS\ProgressBillingController::class, 'stagesComplete'])->name('complete');
            });
        });

        // Installation Management
        Route::prefix('installation')->name('installation.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\InstallationController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\BMS\InstallationController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\BMS\InstallationController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\BMS\InstallationController::class, 'update'])->name('update');
            
            // Site Visits
            Route::post('/{scheduleId}/visits', [\App\Http\Controllers\BMS\InstallationController::class, 'recordVisit'])->name('visits.store');
            
            // Customer Signoff
            Route::post('/{scheduleId}/signoff', [\App\Http\Controllers\BMS\InstallationController::class, 'recordSignoff'])->name('signoff.store');
            
            // Defects
            Route::get('/defects', [\App\Http\Controllers\BMS\InstallationController::class, 'defects'])->name('defects.index');
            Route::post('/defects', [\App\Http\Controllers\BMS\InstallationController::class, 'storeDefect'])->name('defects.store');
            Route::post('/defects/{defectId}/resolve', [\App\Http\Controllers\BMS\InstallationController::class, 'resolveDefect'])->name('defects.resolve');
        });

        // Inventory routes are defined in the main inventory group above

        // Vehicle/Fleet Management
        Route::prefix('fleet')->name('fleet.')->group(function () {
            // Vehicles
            Route::get('/', [\App\Http\Controllers\BMS\FleetController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\BMS\FleetController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\BMS\FleetController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\BMS\FleetController::class, 'update'])->name('update');
            
            // Fuel Records
            Route::post('/{vehicleId}/fuel', [\App\Http\Controllers\BMS\FleetController::class, 'recordFuel'])->name('fuel.store');
            
            // Maintenance
            Route::get('/maintenance', [\App\Http\Controllers\BMS\FleetController::class, 'maintenance'])->name('maintenance.index');
            Route::post('/{vehicleId}/maintenance', [\App\Http\Controllers\BMS\FleetController::class, 'scheduleMaintenance'])->name('maintenance.store');
            Route::post('/maintenance/{id}/complete', [\App\Http\Controllers\BMS\FleetController::class, 'completeMaintenance'])->name('maintenance.complete');
            
            // Trip Logs
            Route::post('/{vehicleId}/trips', [\App\Http\Controllers\BMS\FleetController::class, 'recordTrip'])->name('trips.store');
            
            // Expenses
            Route::post('/{vehicleId}/expenses', [\App\Http\Controllers\BMS\FleetController::class, 'recordExpense'])->name('expenses.store');
        });

        // Document Management
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\DocumentController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\BMS\DocumentController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\BMS\DocumentController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\BMS\DocumentController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\BMS\DocumentController::class, 'destroy'])->name('destroy');
            
            // Versions
            Route::post('/{id}/versions', [\App\Http\Controllers\BMS\DocumentController::class, 'uploadVersion'])->name('versions.store');
            
            // Sharing
            Route::post('/{id}/share', [\App\Http\Controllers\BMS\DocumentController::class, 'share'])->name('share');
            
            // Signatures
            Route::post('/{id}/sign', [\App\Http\Controllers\BMS\DocumentController::class, 'sign'])->name('sign');
        });

        // Safety Management
        Route::prefix('safety')->name('safety.')->group(function () {
            // Incidents
            Route::get('/incidents', [\App\Http\Controllers\BMS\SafetyController::class, 'incidents'])->name('incidents.index');
            Route::post('/incidents', [\App\Http\Controllers\BMS\SafetyController::class, 'storeIncident'])->name('incidents.store');
            Route::get('/incidents/{id}', [\App\Http\Controllers\BMS\SafetyController::class, 'showIncident'])->name('incidents.show');
            
            // Inspections
            Route::get('/inspections', [\App\Http\Controllers\BMS\SafetyController::class, 'inspections'])->name('inspections.index');
            Route::post('/inspections', [\App\Http\Controllers\BMS\SafetyController::class, 'storeInspection'])->name('inspections.store');
            
            // PPE
            Route::get('/ppe', [\App\Http\Controllers\BMS\SafetyController::class, 'ppe'])->name('ppe.index');
            Route::post('/ppe', [\App\Http\Controllers\BMS\SafetyController::class, 'storePPE'])->name('ppe.store');
            Route::post('/ppe/{id}/distribute', [\App\Http\Controllers\BMS\SafetyController::class, 'distributePPE'])->name('ppe.distribute');
            
            // Training
            Route::get('/training', [\App\Http\Controllers\BMS\SafetyController::class, 'training'])->name('training.index');
            Route::post('/training', [\App\Http\Controllers\BMS\SafetyController::class, 'storeTraining'])->name('training.store');
            Route::post('/training/{id}/record', [\App\Http\Controllers\BMS\SafetyController::class, 'recordTraining'])->name('training.record');
        });

        // Quality Control
        Route::prefix('quality')->name('quality.')->group(function () {
            // Inspections
            Route::get('/inspections', [\App\Http\Controllers\BMS\QualityController::class, 'inspections'])->name('inspections.index');
            Route::post('/inspections', [\App\Http\Controllers\BMS\QualityController::class, 'storeInspection'])->name('inspections.store');
            Route::get('/inspections/{id}', [\App\Http\Controllers\BMS\QualityController::class, 'showInspection'])->name('inspections.show');
            
            // Non-Conformances
            Route::get('/ncr', [\App\Http\Controllers\BMS\QualityController::class, 'ncr'])->name('ncr.index');
            Route::post('/ncr', [\App\Http\Controllers\BMS\QualityController::class, 'storeNCR'])->name('ncr.store');
            Route::post('/ncr/{id}/corrective-action', [\App\Http\Controllers\BMS\QualityController::class, 'storeCorrectiveAction'])->name('ncr.corrective-action');
            
            // Customer Complaints
            Route::get('/complaints', [\App\Http\Controllers\BMS\QualityController::class, 'complaints'])->name('complaints.index');
            Route::post('/complaints', [\App\Http\Controllers\BMS\QualityController::class, 'storeComplaint'])->name('complaints.store');
            
            // Rework
            Route::get('/rework', [\App\Http\Controllers\BMS\QualityController::class, 'rework'])->name('rework.index');
            Route::post('/rework', [\App\Http\Controllers\BMS\QualityController::class, 'storeRework'])->name('rework.store');
        });

        // Portal User Management
        Route::prefix('portal-users')->name('portal-users.')->group(function () {
            Route::get('/', [\App\Http\Controllers\BMS\PortalUserController::class, 'index'])->name('index');
            Route::post('/link', [\App\Http\Controllers\BMS\PortalUserController::class, 'link'])->name('link');
            Route::delete('/unlink/{userId}/{customerId}', [\App\Http\Controllers\BMS\PortalUserController::class, 'unlink'])->name('unlink');
        });
    });
