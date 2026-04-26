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
use App\Http\Controllers\CMS\MeasurementController;
use App\Http\Controllers\CMS\PaymentController;
use App\Http\Controllers\CMS\PricingRulesController;
use App\Http\Controllers\CMS\QuotationController;
use App\Http\Controllers\CMS\ReportController;
use App\Http\Controllers\CMS\BudgetController;
use App\Http\Controllers\CMS\ScheduledReportController;
use App\Http\Controllers\CMS\ProjectController;
use App\Http\Controllers\CMS\SubcontractorController;
use App\Http\Controllers\CMS\EquipmentController;
use App\Http\Controllers\CMS\LabourController;
use App\Http\Controllers\CMS\BOQController;
use App\Http\Controllers\CMS\ProgressBillingController;
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
        return Inertia::render('CMS/Landing', [
            'routePrefix' => 'cms'
        ]);
    })->name('landing');

    // Offline Page (for PWA)
    Route::get('/offline', function () {
        return Inertia::render('CMS/Offline');
    })->name('offline');

    // Template preview — signed URL, no auth required (iframe safe)
    Route::get('/settings/document-templates/{id}/preview', [\App\Http\Controllers\CMS\DocumentTemplatesController::class, 'preview'])->name('settings.document-templates.preview');

    // Signed quotation PDF — for WhatsApp links (no auth required)
    Route::get('/quotations/{id}/pdf/signed', [\App\Http\Controllers\CMS\QuotationController::class, 'downloadPdfSigned'])->name('quotations.pdf.signed');

    // Email Unsubscribe (Public)
    Route::get('/email/unsubscribe', [\App\Http\Controllers\CMS\EmailSettingsController::class, 'unsubscribe'])->name('email.unsubscribe');

    // Switch active company (authenticated users)
    Route::post('/switch-company', [AuthController::class, 'switchCompany'])
        ->middleware('auth')
        ->name('switch-company');

    // Company Hub — auth required but no company needed yet
    Route::middleware('auth')->group(function () {
        Route::get('/companies/hub', [\App\Http\Controllers\CMS\CompanyController::class, 'hub'])->name('companies.hub');
        Route::get('/companies/create', [\App\Http\Controllers\CMS\CompanyController::class, 'create'])->name('companies.create');
        Route::post('/companies', [\App\Http\Controllers\CMS\CompanyController::class, 'store'])->name('companies.store');
        Route::post('/companies/{companyId}/enter', [\App\Http\Controllers\CMS\CompanyController::class, 'enter'])->name('companies.enter');
    });
});

// Protected CMS Routes
Route::prefix('cms')
    ->name('cms.')
    ->middleware(['auth', 'verified', 'cms.auto-login', 'cms.access', \App\Http\Middleware\CMS\EnforcePasswordChange::class])
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
            Route::post('/fabrication-module', [\App\Http\Controllers\CMS\SettingsController::class, 'toggleFabricationModule'])->name('fabrication-module.toggle');
            Route::post('/bizdocs-module', [\App\Http\Controllers\CMS\SettingsController::class, 'toggleBizDocsModule'])->name('bizdocs-module.toggle');
            Route::post('/material-planning-module', [\App\Http\Controllers\CMS\SettingsController::class, 'toggleMaterialPlanningModule'])->name('material-planning-module.toggle');
            Route::post('/construction-modules', [\App\Http\Controllers\CMS\SettingsController::class, 'toggleConstructionModules'])->name('construction-modules.toggle');

            // Document Templates (BizDocs)
            Route::get('/document-templates', [\App\Http\Controllers\CMS\DocumentTemplatesController::class, 'index'])->name('document-templates.index');
            Route::post('/document-templates/set', [\App\Http\Controllers\CMS\DocumentTemplatesController::class, 'setTemplate'])->name('document-templates.set');
            Route::get('/document-templates/{id}/preview-url', [\App\Http\Controllers\CMS\DocumentTemplatesController::class, 'previewUrl'])->name('document-templates.preview-url');
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
                Route::get('/', [\App\Http\Controllers\CMS\JobMaterialPlanningController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\CMS\JobMaterialPlanningController::class, 'store'])->name('store');
                Route::put('/{plan}', [\App\Http\Controllers\CMS\JobMaterialPlanningController::class, 'update'])->name('update');
                Route::delete('/{plan}', [\App\Http\Controllers\CMS\JobMaterialPlanningController::class, 'destroy'])->name('destroy');
                Route::post('/apply-template', [\App\Http\Controllers\CMS\JobMaterialPlanningController::class, 'applyTemplate'])->name('apply-template');
                Route::post('/{plan}/actual-costs', [\App\Http\Controllers\CMS\JobMaterialPlanningController::class, 'updateActualCosts'])->name('actual-costs');
                Route::post('/bulk-status', [\App\Http\Controllers\CMS\JobMaterialPlanningController::class, 'bulkUpdateStatus'])->name('bulk-status');
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
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/create', [InventoryController::class, 'create'])->name('create');
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::get('/low-stock-alerts', [InventoryController::class, 'lowStockAlerts'])->name('low-stock-alerts');
            Route::get('/{inventory}', [InventoryController::class, 'show'])->name('show');
            Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
            Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
            Route::post('/{inventory}/movement', [InventoryController::class, 'recordMovement'])->name('movement');
        });

        // Materials Management
        Route::prefix('materials')->name('materials.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\MaterialController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\MaterialController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\MaterialController::class, 'store'])->name('store');
            Route::get('/{material}/edit', [\App\Http\Controllers\CMS\MaterialController::class, 'edit'])->name('edit');
            Route::put('/{material}', [\App\Http\Controllers\CMS\MaterialController::class, 'update'])->name('update');
            Route::delete('/{material}', [\App\Http\Controllers\CMS\MaterialController::class, 'destroy'])->name('destroy');
            Route::get('/{material}/price-history', [\App\Http\Controllers\CMS\MaterialController::class, 'priceHistory'])->name('price-history');
            Route::post('/bulk-update-prices', [\App\Http\Controllers\CMS\MaterialController::class, 'bulkUpdatePrices'])->name('bulk-update-prices');
        });

        // Material Categories
        Route::prefix('material-categories')->name('material-categories.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\MaterialCategoryController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\CMS\MaterialCategoryController::class, 'store'])->name('store');
            Route::put('/{category}', [\App\Http\Controllers\CMS\MaterialCategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [\App\Http\Controllers\CMS\MaterialCategoryController::class, 'destroy'])->name('destroy');
        });

        // Purchase Orders
        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\PurchaseOrderController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\PurchaseOrderController::class, 'store'])->name('store');
            Route::get('/{purchaseOrder}', [\App\Http\Controllers\CMS\PurchaseOrderController::class, 'show'])->name('show');
            Route::post('/{purchaseOrder}/approve', [\App\Http\Controllers\CMS\PurchaseOrderController::class, 'approve'])->name('approve');
            Route::post('/{purchaseOrder}/receive', [\App\Http\Controllers\CMS\PurchaseOrderController::class, 'receive'])->name('receive');
            Route::post('/{purchaseOrder}/cancel', [\App\Http\Controllers\CMS\PurchaseOrderController::class, 'cancel'])->name('cancel');
            
            // Create PO from Job
            Route::get('/jobs/{job}/create', [\App\Http\Controllers\CMS\PurchaseOrderController::class, 'createFromJob'])->name('create-from-job');
            Route::post('/jobs/{job}', [\App\Http\Controllers\CMS\PurchaseOrderController::class, 'storeFromJob'])->name('store-from-job');
        });

        // Production Management
        Route::prefix('production')->name('production.')->group(function () {
            // Production Orders
            Route::get('/', [\App\Http\Controllers\CMS\ProductionController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\ProductionController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\ProductionController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\CMS\ProductionController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\CMS\ProductionController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\CMS\ProductionController::class, 'destroy'])->name('destroy');
            
            // Production Tracking
            Route::post('/{id}/tracking', [\App\Http\Controllers\CMS\ProductionController::class, 'updateTracking'])->name('tracking.update');
            
            // Quality Checkpoints
            Route::post('/checkpoints/{checkpointId}', [\App\Http\Controllers\CMS\ProductionController::class, 'updateCheckpoint'])->name('checkpoints.update');
            
            // Material Usage
            Route::post('/{id}/material-usage', [\App\Http\Controllers\CMS\ProductionController::class, 'recordMaterialUsage'])->name('material-usage.record');
            
            // Cutting Lists
            Route::get('/cutting-lists', [\App\Http\Controllers\CMS\ProductionController::class, 'cuttingLists'])->name('cutting-lists.index');
            Route::get('/{id}/cutting-lists/create', [\App\Http\Controllers\CMS\ProductionController::class, 'createCuttingList'])->name('cutting-lists.create');
            Route::post('/{id}/cutting-lists', [\App\Http\Controllers\CMS\ProductionController::class, 'storeCuttingList'])->name('cutting-lists.store');
            Route::get('/cutting-lists/{id}', [\App\Http\Controllers\CMS\ProductionController::class, 'showCuttingList'])->name('cutting-lists.show');
            Route::post('/cutting-lists/{id}/optimize', [\App\Http\Controllers\CMS\ProductionController::class, 'optimizeCuttingList'])->name('cutting-lists.optimize');
            
            // Waste Tracking
            Route::get('/waste', [\App\Http\Controllers\CMS\ProductionController::class, 'wasteTracking'])->name('waste.index');
            Route::post('/waste', [\App\Http\Controllers\CMS\ProductionController::class, 'storeWaste'])->name('waste.store');
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
        });

        // Loans Receivable (Company-scoped loan management)
        Route::prefix('loans')->name('loans.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\LoanController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\LoanController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\LoanController::class, 'store'])->name('store');
            Route::get('/{loan}', [\App\Http\Controllers\CMS\LoanController::class, 'show'])->name('show');
            Route::get('/{loan}/payment', [\App\Http\Controllers\CMS\LoanController::class, 'paymentForm'])->name('payment');
            Route::post('/{loan}/payment', [\App\Http\Controllers\CMS\LoanController::class, 'recordPayment'])->name('payment.store');
            Route::get('/reports/aging', [\App\Http\Controllers\CMS\LoanController::class, 'agingReport'])->name('reports.aging');
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
                
                // Worker Allowances
                Route::get('/{worker}/allowances', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'workerAllowancesIndex'])->name('allowances.index');
                Route::post('/{worker}/allowances', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'storeWorkerAllowance'])->name('allowances.store');
                Route::put('/{worker}/allowances/{allowance}', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'updateWorkerAllowance'])->name('allowances.update');
                Route::delete('/{worker}/allowances/{allowance}', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'deleteWorkerAllowance'])->name('allowances.delete');
                
                // Worker Deductions
                Route::get('/{worker}/deductions', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'workerDeductionsIndex'])->name('deductions.index');
                Route::post('/{worker}/deductions', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'storeWorkerDeduction'])->name('deductions.store');
                Route::put('/{worker}/deductions/{deduction}', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'updateWorkerDeduction'])->name('deductions.update');
                Route::delete('/{worker}/deductions/{deduction}', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'deleteWorkerDeduction'])->name('deductions.delete');
            });

            // Payroll Configuration
            Route::prefix('configuration')->name('configuration.')->group(function () {
                // Allowance Types
                Route::get('/allowance-types', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'allowanceTypesIndex'])->name('allowance-types.index');
                Route::post('/allowance-types', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'storeAllowanceType'])->name('allowance-types.store');
                Route::put('/allowance-types/{allowanceType}', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'updateAllowanceType'])->name('allowance-types.update');
                
                // Deduction Types
                Route::get('/deduction-types', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'deductionTypesIndex'])->name('deduction-types.index');
                Route::post('/deduction-types', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'storeDeductionType'])->name('deduction-types.store');
                Route::put('/deduction-types/{deductionType}', [\App\Http\Controllers\CMS\PayrollConfigurationController::class, 'updateDeductionType'])->name('deduction-types.update');
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

        // HRMS - Departments & Branches
        Route::prefix('departments')->name('departments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\DepartmentController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\DepartmentController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\DepartmentController::class, 'store'])->name('store');
            Route::get('/{department}/edit', [\App\Http\Controllers\CMS\DepartmentController::class, 'edit'])->name('edit');
            Route::put('/{department}', [\App\Http\Controllers\CMS\DepartmentController::class, 'update'])->name('update');
            Route::delete('/{department}', [\App\Http\Controllers\CMS\DepartmentController::class, 'destroy'])->name('destroy');
        });

        // HRMS - Leave Management
        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\LeaveController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\LeaveController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\LeaveController::class, 'store'])->name('store');
            Route::get('/{leave}', [\App\Http\Controllers\CMS\LeaveController::class, 'show'])->name('show');
            Route::post('/{leave}/approve', [\App\Http\Controllers\CMS\LeaveController::class, 'approve'])->name('approve');
            Route::post('/{leave}/reject', [\App\Http\Controllers\CMS\LeaveController::class, 'reject'])->name('reject');
            Route::get('/balance/view', [\App\Http\Controllers\CMS\LeaveController::class, 'balance'])->name('balance');
        });

        // HRMS - Shift Management
        Route::resource('shifts', \App\Http\Controllers\CMS\ShiftController::class);
        Route::post('/shifts/{shift}/assign', [\App\Http\Controllers\CMS\ShiftController::class, 'assign'])->name('shifts.assign');

        // HRMS - Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\AttendanceController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\CMS\AttendanceController::class, 'store'])->name('store');
            Route::post('/clock-in', [\App\Http\Controllers\CMS\AttendanceController::class, 'clockIn'])->name('clock-in');
            Route::post('/{record}/clock-out', [\App\Http\Controllers\CMS\AttendanceController::class, 'clockOut'])->name('clock-out');
            Route::get('/summary', [\App\Http\Controllers\CMS\AttendanceController::class, 'summary'])->name('summary');
            Route::get('/calendar', [\App\Http\Controllers\CMS\AttendanceController::class, 'calendar'])->name('calendar');
        });

        // HRMS - Overtime
        Route::prefix('overtime')->name('overtime.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\OvertimeController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\CMS\OvertimeController::class, 'store'])->name('store');
            Route::post('/{record}/approve', [\App\Http\Controllers\CMS\OvertimeController::class, 'approve'])->name('approve');
            Route::post('/{record}/reject', [\App\Http\Controllers\CMS\OvertimeController::class, 'reject'])->name('reject');
            Route::get('/summary', [\App\Http\Controllers\CMS\OvertimeController::class, 'summary'])->name('summary');
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
            Route::post('/document-defaults', [\App\Http\Controllers\CMS\SettingsController::class, 'updateDocumentDefaults'])->name('document-defaults.update');
            Route::post('/signature', [\App\Http\Controllers\CMS\SettingsController::class, 'uploadSignature'])->name('signature.upload');
            Route::delete('/signature', [\App\Http\Controllers\CMS\SettingsController::class, 'deleteSignature'])->name('signature.delete');
            
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

        // HRMS - Recruitment
        Route::prefix('recruitment')->name('recruitment.')->group(function () {
            // Job Postings
            Route::get('/job-postings', [\App\Http\Controllers\CMS\RecruitmentController::class, 'jobPostingsIndex'])->name('job-postings.index');
            Route::get('/job-postings/create', [\App\Http\Controllers\CMS\RecruitmentController::class, 'jobPostingsCreate'])->name('job-postings.create');
            Route::post('/job-postings', [\App\Http\Controllers\CMS\RecruitmentController::class, 'jobPostingsStore'])->name('job-postings.store');
            
            // Applications
            Route::get('/job-postings/{jobPostingId}/applications', [\App\Http\Controllers\CMS\RecruitmentController::class, 'applicationsIndex'])->name('applications.index');
            Route::put('/applications/{applicationId}/status', [\App\Http\Controllers\CMS\RecruitmentController::class, 'applicationsUpdateStatus'])->name('applications.update-status');
            
            // Interviews
            Route::get('/interviews', [\App\Http\Controllers\CMS\RecruitmentController::class, 'interviewsIndex'])->name('interviews.index');
            Route::post('/interviews', [\App\Http\Controllers\CMS\RecruitmentController::class, 'interviewsStore'])->name('interviews.store');
            Route::post('/interviews/{interviewId}/evaluations', [\App\Http\Controllers\CMS\RecruitmentController::class, 'evaluationsStore'])->name('evaluations.store');
        });

        // HRMS - Onboarding
        Route::prefix('hrms-onboarding')->name('hrms-onboarding.')->group(function () {
            // Templates
            Route::get('/templates', [\App\Http\Controllers\CMS\OnboardingController::class, 'templatesIndex'])->name('templates.index');
            Route::post('/templates', [\App\Http\Controllers\CMS\OnboardingController::class, 'templatesStore'])->name('templates.store');
            Route::post('/templates/{templateId}/tasks', [\App\Http\Controllers\CMS\OnboardingController::class, 'tasksStore'])->name('tasks.store');
            
            // Employee Onboarding
            Route::get('/employee', [\App\Http\Controllers\CMS\OnboardingController::class, 'employeeOnboardingIndex'])->name('employee.index');
            Route::post('/employee', [\App\Http\Controllers\CMS\OnboardingController::class, 'employeeOnboardingStore'])->name('employee.store');
            Route::get('/employee/{workerId}', [\App\Http\Controllers\CMS\OnboardingController::class, 'employeeOnboardingShow'])->name('employee.show');
            Route::post('/task-progress/{taskProgressId}/complete', [\App\Http\Controllers\CMS\OnboardingController::class, 'taskProgressComplete'])->name('task-progress.complete');
        });

        // HRMS - Performance Management
        Route::prefix('performance')->name('performance.')->group(function () {
            // Performance Cycles
            Route::get('/cycles', [\App\Http\Controllers\CMS\PerformanceController::class, 'cyclesIndex'])->name('cycles.index');
            Route::post('/cycles', [\App\Http\Controllers\CMS\PerformanceController::class, 'cyclesStore'])->name('cycles.store');
            Route::post('/cycles/{cycleId}/activate', [\App\Http\Controllers\CMS\PerformanceController::class, 'cyclesActivate'])->name('cycles.activate');
            
            // Performance Reviews
            Route::get('/reviews', [\App\Http\Controllers\CMS\PerformanceController::class, 'reviewsIndex'])->name('reviews.index');
            Route::get('/reviews/create', [\App\Http\Controllers\CMS\PerformanceController::class, 'reviewsCreate'])->name('reviews.create');
            Route::post('/reviews', [\App\Http\Controllers\CMS\PerformanceController::class, 'reviewsStore'])->name('reviews.store');
            Route::get('/reviews/{reviewId}', [\App\Http\Controllers\CMS\PerformanceController::class, 'reviewsShow'])->name('reviews.show');
            Route::post('/reviews/{reviewId}/submit', [\App\Http\Controllers\CMS\PerformanceController::class, 'reviewsSubmit'])->name('reviews.submit');
            
            // Goals
            Route::get('/goals', [\App\Http\Controllers\CMS\PerformanceController::class, 'goalsIndex'])->name('goals.index');
            Route::post('/goals', [\App\Http\Controllers\CMS\PerformanceController::class, 'goalsStore'])->name('goals.store');
            Route::post('/goals/{goalId}/progress', [\App\Http\Controllers\CMS\PerformanceController::class, 'goalsUpdateProgress'])->name('goals.update-progress');
            
            // Performance Improvement Plans
            Route::get('/pips', [\App\Http\Controllers\CMS\PerformanceController::class, 'pipsIndex'])->name('pips.index');
            Route::post('/pips', [\App\Http\Controllers\CMS\PerformanceController::class, 'pipsStore'])->name('pips.store');
            Route::post('/pips/milestones/{milestoneId}/complete', [\App\Http\Controllers\CMS\PerformanceController::class, 'pipsMilestoneComplete'])->name('pips.milestone-complete');
            Route::post('/pips/{pipId}/close', [\App\Http\Controllers\CMS\PerformanceController::class, 'pipsClose'])->name('pips.close');
        });

        // Training & Development (Phase 6)
        Route::prefix('training')->name('training.')->group(function () {
            // Training Programs
            Route::get('/programs', [\App\Http\Controllers\CMS\TrainingController::class, 'programs'])->name('programs');
            Route::post('/programs', [\App\Http\Controllers\CMS\TrainingController::class, 'createProgram'])->name('programs.create');
            
            // Training Sessions
            Route::get('/sessions', [\App\Http\Controllers\CMS\TrainingController::class, 'sessions'])->name('sessions');
            
            // Skills Catalog
            Route::get('/skills', [\App\Http\Controllers\CMS\TrainingController::class, 'skills'])->name('skills');
            
            // Certifications
            Route::get('/certifications', [\App\Http\Controllers\CMS\TrainingController::class, 'certifications'])->name('certifications');
        });

        // Employee Self-Service Portal (Phase 8)
        Route::prefix('employee')->name('employee.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\CMS\EmployeePortalController::class, 'dashboard'])->name('dashboard');
            Route::get('/profile', [\App\Http\Controllers\CMS\EmployeePortalController::class, 'profile'])->name('profile');
            Route::get('/documents', [\App\Http\Controllers\CMS\EmployeePortalController::class, 'documents'])->name('documents');
            Route::post('/documents/request', [\App\Http\Controllers\CMS\EmployeePortalController::class, 'requestDocument'])->name('documents.request');
            Route::post('/feedback', [\App\Http\Controllers\CMS\EmployeePortalController::class, 'submitFeedback'])->name('feedback.submit');
        });

        // HR Reports & Analytics (Phase 9)
        Route::prefix('hr-reports')->name('hr-reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\HRReportsController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\CMS\HRReportsController::class, 'generate'])->name('generate');
            Route::get('/{id}', [\App\Http\Controllers\CMS\HRReportsController::class, 'show'])->name('show');
            Route::delete('/{id}', [\App\Http\Controllers\CMS\HRReportsController::class, 'destroy'])->name('destroy');
        });

        // Construction Modules - Projects
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\ProjectController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\ProjectController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\ProjectController::class, 'store'])->name('store');
            Route::get('/{project}', [\App\Http\Controllers\CMS\ProjectController::class, 'show'])->name('show');
            Route::get('/{project}/edit', [\App\Http\Controllers\CMS\ProjectController::class, 'edit'])->name('edit');
            Route::put('/{project}', [\App\Http\Controllers\CMS\ProjectController::class, 'update'])->name('update');
            Route::delete('/{project}', [\App\Http\Controllers\CMS\ProjectController::class, 'destroy'])->name('destroy');
            Route::post('/{project}/status', [\App\Http\Controllers\CMS\ProjectController::class, 'updateStatus'])->name('status');
            Route::post('/{project}/milestones', [\App\Http\Controllers\CMS\ProjectController::class, 'storeMilestone'])->name('milestones.store');
            Route::post('/{project}/milestones/{milestone}/complete', [\App\Http\Controllers\CMS\ProjectController::class, 'completeMilestone'])->name('milestones.complete');
            Route::get('/{project}/timeline', [\App\Http\Controllers\CMS\ProjectController::class, 'timeline'])->name('timeline');
        });

        // Construction Modules - Subcontractors
        Route::prefix('subcontractors')->name('subcontractors.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\SubcontractorController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\SubcontractorController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\SubcontractorController::class, 'store'])->name('store');
            Route::get('/{subcontractor}', [\App\Http\Controllers\CMS\SubcontractorController::class, 'show'])->name('show');
            Route::get('/{subcontractor}/edit', [\App\Http\Controllers\CMS\SubcontractorController::class, 'edit'])->name('edit');
            Route::put('/{subcontractor}', [\App\Http\Controllers\CMS\SubcontractorController::class, 'update'])->name('update');
            Route::delete('/{subcontractor}', [\App\Http\Controllers\CMS\SubcontractorController::class, 'destroy'])->name('destroy');
            Route::post('/{subcontractor}/assign', [\App\Http\Controllers\CMS\SubcontractorController::class, 'assign'])->name('assign');
            Route::post('/{subcontractor}/payments', [\App\Http\Controllers\CMS\SubcontractorController::class, 'recordPayment'])->name('payments.store');
            Route::post('/{subcontractor}/rate', [\App\Http\Controllers\CMS\SubcontractorController::class, 'rate'])->name('rate');
        });

        // Construction Modules - Equipment
        Route::prefix('equipment')->name('equipment.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\EquipmentController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\EquipmentController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\EquipmentController::class, 'store'])->name('store');
            Route::get('/{equipment}', [\App\Http\Controllers\CMS\EquipmentController::class, 'show'])->name('show');
            Route::get('/{equipment}/edit', [\App\Http\Controllers\CMS\EquipmentController::class, 'edit'])->name('edit');
            Route::put('/{equipment}', [\App\Http\Controllers\CMS\EquipmentController::class, 'update'])->name('update');
            Route::delete('/{equipment}', [\App\Http\Controllers\CMS\EquipmentController::class, 'destroy'])->name('destroy');
            Route::post('/{equipment}/maintenance', [\App\Http\Controllers\CMS\EquipmentController::class, 'scheduleMaintenance'])->name('maintenance.schedule');
            Route::post('/maintenance/{maintenance}/complete', [\App\Http\Controllers\CMS\EquipmentController::class, 'completeMaintenance'])->name('maintenance.complete');
            Route::post('/{equipment}/usage', [\App\Http\Controllers\CMS\EquipmentController::class, 'recordUsage'])->name('usage.store');
            Route::post('/{equipment}/rentals', [\App\Http\Controllers\CMS\EquipmentController::class, 'createRental'])->name('rentals.store');
        });

        // Construction Modules - Labour
        Route::prefix('labour')->name('labour.')->group(function () {
            // Crews
            Route::prefix('crews')->name('crews.')->group(function () {
                Route::get('/', [\App\Http\Controllers\CMS\LabourController::class, 'crewsIndex'])->name('index');
                Route::get('/create', [\App\Http\Controllers\CMS\LabourController::class, 'crewsCreate'])->name('create');
                Route::post('/', [\App\Http\Controllers\CMS\LabourController::class, 'crewsStore'])->name('store');
                Route::get('/{crew}', [\App\Http\Controllers\CMS\LabourController::class, 'crewsShow'])->name('show');
                Route::put('/{crew}', [\App\Http\Controllers\CMS\LabourController::class, 'crewsUpdate'])->name('update');
                Route::post('/{crew}/members', [\App\Http\Controllers\CMS\LabourController::class, 'addCrewMember'])->name('members.add');
                Route::delete('/{crew}/members/{member}', [\App\Http\Controllers\CMS\LabourController::class, 'removeCrewMember'])->name('members.remove');
            });
            
            // Timesheets
            Route::prefix('timesheets')->name('timesheets.')->group(function () {
                Route::get('/', [\App\Http\Controllers\CMS\LabourController::class, 'timesheetsIndex'])->name('index');
                Route::get('/create', [\App\Http\Controllers\CMS\LabourController::class, 'timesheetsCreate'])->name('create');
                Route::post('/', [\App\Http\Controllers\CMS\LabourController::class, 'timesheetsStore'])->name('store');
                Route::post('/{timesheet}/approve', [\App\Http\Controllers\CMS\LabourController::class, 'timesheetsApprove'])->name('approve');
                Route::post('/{timesheet}/reject', [\App\Http\Controllers\CMS\LabourController::class, 'timesheetsReject'])->name('reject');
            });
            
            // Productivity
            Route::get('/productivity', [\App\Http\Controllers\CMS\LabourController::class, 'productivity'])->name('productivity');
        });

        // Construction Modules - BOQ
        Route::prefix('boq')->name('boq.')->group(function () {
            // Templates
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [\App\Http\Controllers\CMS\BOQController::class, 'templatesIndex'])->name('index');
                Route::post('/', [\App\Http\Controllers\CMS\BOQController::class, 'templatesStore'])->name('store');
            });
            
            // BOQs
            Route::get('/', [\App\Http\Controllers\CMS\BOQController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CMS\BOQController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CMS\BOQController::class, 'store'])->name('store');
            Route::get('/{boq}', [\App\Http\Controllers\CMS\BOQController::class, 'show'])->name('show');
            Route::get('/{boq}/edit', [\App\Http\Controllers\CMS\BOQController::class, 'edit'])->name('edit');
            Route::put('/{boq}', [\App\Http\Controllers\CMS\BOQController::class, 'update'])->name('update');
            Route::delete('/{boq}', [\App\Http\Controllers\CMS\BOQController::class, 'destroy'])->name('destroy');
            Route::post('/{boq}/variations', [\App\Http\Controllers\CMS\BOQController::class, 'addVariation'])->name('variations.add');
            Route::post('/{boq}/variations/{variation}/approve', [\App\Http\Controllers\CMS\BOQController::class, 'approveVariation'])->name('variations.approve');
            Route::post('/{boq}/actuals', [\App\Http\Controllers\CMS\BOQController::class, 'updateActuals'])->name('actuals.update');
            Route::get('/{boq}/export', [\App\Http\Controllers\CMS\BOQController::class, 'export'])->name('export');
        });

        // Construction Modules - Progress Billing
        Route::prefix('progress-billing')->name('progress-billing.')->group(function () {
            // Progress Certificates
            Route::prefix('certificates')->name('certificates.')->group(function () {
                Route::get('/', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'certificatesIndex'])->name('index');
                Route::get('/create', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'certificatesCreate'])->name('create');
                Route::post('/', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'certificatesStore'])->name('store');
                Route::get('/{certificate}', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'certificatesShow'])->name('show');
                Route::post('/{certificate}/approve', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'certificatesApprove'])->name('approve');
                Route::post('/{certificate}/reject', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'certificatesReject'])->name('reject');
            });
            
            // Payment Applications
            Route::prefix('applications')->name('applications.')->group(function () {
                Route::get('/', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'applicationsIndex'])->name('index');
                Route::post('/', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'applicationsStore'])->name('store');
                Route::post('/{application}/approve', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'applicationsApprove'])->name('approve');
            });
            
            // Retention
            Route::prefix('retention')->name('retention.')->group(function () {
                Route::get('/', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'retentionIndex'])->name('index');
                Route::post('/release', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'releaseRetention'])->name('release');
            });
            
            // Billing Stages
            Route::prefix('projects/{project}/stages')->name('stages.')->group(function () {
                Route::get('/', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'stagesIndex'])->name('index');
                Route::post('/', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'stagesStore'])->name('store');
                Route::post('/{stage}/complete', [\App\Http\Controllers\CMS\ProgressBillingController::class, 'stagesComplete'])->name('complete');
            });
        });

        // Installation Management
        Route::prefix('installation')->name('installation.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\InstallationController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\CMS\InstallationController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\CMS\InstallationController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\CMS\InstallationController::class, 'update'])->name('update');
            
            // Site Visits
            Route::post('/{scheduleId}/visits', [\App\Http\Controllers\CMS\InstallationController::class, 'recordVisit'])->name('visits.store');
            
            // Customer Signoff
            Route::post('/{scheduleId}/signoff', [\App\Http\Controllers\CMS\InstallationController::class, 'recordSignoff'])->name('signoff.store');
            
            // Defects
            Route::get('/defects', [\App\Http\Controllers\CMS\InstallationController::class, 'defects'])->name('defects.index');
            Route::post('/defects', [\App\Http\Controllers\CMS\InstallationController::class, 'storeDefect'])->name('defects.store');
            Route::post('/defects/{defectId}/resolve', [\App\Http\Controllers\CMS\InstallationController::class, 'resolveDefect'])->name('defects.resolve');
        });

        // Enhanced Inventory Management
        Route::prefix('inventory')->name('inventory.')->group(function () {
            // Stock Locations
            Route::get('/locations', [\App\Http\Controllers\CMS\InventoryController::class, 'locations'])->name('locations.index');
            Route::post('/locations', [\App\Http\Controllers\CMS\InventoryController::class, 'storeLocation'])->name('locations.store');
            
            // Stock Levels
            Route::get('/stock-levels', [\App\Http\Controllers\CMS\InventoryController::class, 'stockLevels'])->name('stock-levels.index');
            Route::get('/stock-levels/alerts', [\App\Http\Controllers\CMS\InventoryController::class, 'lowStockAlerts'])->name('stock-levels.alerts');
            
            // Stock Transfers
            Route::get('/transfers', [\App\Http\Controllers\CMS\InventoryController::class, 'transfers'])->name('transfers.index');
            Route::post('/transfers', [\App\Http\Controllers\CMS\InventoryController::class, 'createTransfer'])->name('transfers.store');
            Route::post('/transfers/{id}/approve', [\App\Http\Controllers\CMS\InventoryController::class, 'approveTransfer'])->name('transfers.approve');
            Route::post('/transfers/{id}/receive', [\App\Http\Controllers\CMS\InventoryController::class, 'receiveTransfer'])->name('transfers.receive');
            
            // Stock Adjustments
            Route::get('/adjustments', [\App\Http\Controllers\CMS\InventoryController::class, 'adjustments'])->name('adjustments.index');
            Route::post('/adjustments', [\App\Http\Controllers\CMS\InventoryController::class, 'createAdjustment'])->name('adjustments.store');
            Route::post('/adjustments/{id}/approve', [\App\Http\Controllers\CMS\InventoryController::class, 'approveAdjustment'])->name('adjustments.approve');
            
            // Stock Counts
            Route::get('/counts', [\App\Http\Controllers\CMS\InventoryController::class, 'counts'])->name('counts.index');
            Route::post('/counts', [\App\Http\Controllers\CMS\InventoryController::class, 'createCount'])->name('counts.store');
            Route::post('/counts/{id}/complete', [\App\Http\Controllers\CMS\InventoryController::class, 'completeCount'])->name('counts.complete');
        });

        // Vehicle/Fleet Management
        Route::prefix('fleet')->name('fleet.')->group(function () {
            // Vehicles
            Route::get('/', [\App\Http\Controllers\CMS\FleetController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\CMS\FleetController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\CMS\FleetController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\CMS\FleetController::class, 'update'])->name('update');
            
            // Fuel Records
            Route::post('/{vehicleId}/fuel', [\App\Http\Controllers\CMS\FleetController::class, 'recordFuel'])->name('fuel.store');
            
            // Maintenance
            Route::get('/maintenance', [\App\Http\Controllers\CMS\FleetController::class, 'maintenance'])->name('maintenance.index');
            Route::post('/{vehicleId}/maintenance', [\App\Http\Controllers\CMS\FleetController::class, 'scheduleMaintenance'])->name('maintenance.store');
            Route::post('/maintenance/{id}/complete', [\App\Http\Controllers\CMS\FleetController::class, 'completeMaintenance'])->name('maintenance.complete');
            
            // Trip Logs
            Route::post('/{vehicleId}/trips', [\App\Http\Controllers\CMS\FleetController::class, 'recordTrip'])->name('trips.store');
            
            // Expenses
            Route::post('/{vehicleId}/expenses', [\App\Http\Controllers\CMS\FleetController::class, 'recordExpense'])->name('expenses.store');
        });

        // Document Management
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CMS\DocumentController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\CMS\DocumentController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\CMS\DocumentController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\CMS\DocumentController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\CMS\DocumentController::class, 'destroy'])->name('destroy');
            
            // Versions
            Route::post('/{id}/versions', [\App\Http\Controllers\CMS\DocumentController::class, 'uploadVersion'])->name('versions.store');
            
            // Sharing
            Route::post('/{id}/share', [\App\Http\Controllers\CMS\DocumentController::class, 'share'])->name('share');
            
            // Signatures
            Route::post('/{id}/sign', [\App\Http\Controllers\CMS\DocumentController::class, 'sign'])->name('sign');
        });

        // Safety Management
        Route::prefix('safety')->name('safety.')->group(function () {
            // Incidents
            Route::get('/incidents', [\App\Http\Controllers\CMS\SafetyController::class, 'incidents'])->name('incidents.index');
            Route::post('/incidents', [\App\Http\Controllers\CMS\SafetyController::class, 'storeIncident'])->name('incidents.store');
            Route::get('/incidents/{id}', [\App\Http\Controllers\CMS\SafetyController::class, 'showIncident'])->name('incidents.show');
            
            // Inspections
            Route::get('/inspections', [\App\Http\Controllers\CMS\SafetyController::class, 'inspections'])->name('inspections.index');
            Route::post('/inspections', [\App\Http\Controllers\CMS\SafetyController::class, 'storeInspection'])->name('inspections.store');
            
            // PPE
            Route::get('/ppe', [\App\Http\Controllers\CMS\SafetyController::class, 'ppe'])->name('ppe.index');
            Route::post('/ppe', [\App\Http\Controllers\CMS\SafetyController::class, 'storePPE'])->name('ppe.store');
            Route::post('/ppe/{id}/distribute', [\App\Http\Controllers\CMS\SafetyController::class, 'distributePPE'])->name('ppe.distribute');
            
            // Training
            Route::get('/training', [\App\Http\Controllers\CMS\SafetyController::class, 'training'])->name('training.index');
            Route::post('/training', [\App\Http\Controllers\CMS\SafetyController::class, 'storeTraining'])->name('training.store');
            Route::post('/training/{id}/record', [\App\Http\Controllers\CMS\SafetyController::class, 'recordTraining'])->name('training.record');
        });

        // Quality Control
        Route::prefix('quality')->name('quality.')->group(function () {
            // Inspections
            Route::get('/inspections', [\App\Http\Controllers\CMS\QualityController::class, 'inspections'])->name('inspections.index');
            Route::post('/inspections', [\App\Http\Controllers\CMS\QualityController::class, 'storeInspection'])->name('inspections.store');
            Route::get('/inspections/{id}', [\App\Http\Controllers\CMS\QualityController::class, 'showInspection'])->name('inspections.show');
            
            // Non-Conformances
            Route::get('/ncr', [\App\Http\Controllers\CMS\QualityController::class, 'ncr'])->name('ncr.index');
            Route::post('/ncr', [\App\Http\Controllers\CMS\QualityController::class, 'storeNCR'])->name('ncr.store');
            Route::post('/ncr/{id}/corrective-action', [\App\Http\Controllers\CMS\QualityController::class, 'storeCorrectiveAction'])->name('ncr.corrective-action');
            
            // Customer Complaints
            Route::get('/complaints', [\App\Http\Controllers\CMS\QualityController::class, 'complaints'])->name('complaints.index');
            Route::post('/complaints', [\App\Http\Controllers\CMS\QualityController::class, 'storeComplaint'])->name('complaints.store');
            
            // Rework
            Route::get('/rework', [\App\Http\Controllers\CMS\QualityController::class, 'rework'])->name('rework.index');
            Route::post('/rework', [\App\Http\Controllers\CMS\QualityController::class, 'storeRework'])->name('rework.store');
        });
    });
