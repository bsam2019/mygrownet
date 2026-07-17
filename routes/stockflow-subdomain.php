<?php

use App\Http\Controllers\StockFlow\ActivityLogController;
use App\Http\Controllers\StockFlow\AuditController;
use App\Http\Controllers\StockFlow\AuthController;
use App\Http\Controllers\StockFlow\BinController;
use App\Http\Controllers\StockFlow\CashController;
use App\Http\Controllers\StockFlow\CommentController;
use App\Http\Controllers\StockFlow\CompanyController;
use App\Http\Controllers\StockFlow\DashboardController;
use App\Http\Controllers\StockFlow\DepartmentController;
use App\Http\Controllers\StockFlow\EmployeeController;
use App\Http\Controllers\StockFlow\ItemController;
use App\Http\Controllers\StockFlow\LandingController;
use App\Http\Controllers\StockFlow\MessageController;
use App\Http\Controllers\StockFlow\NotificationController as StockFlowNotificationController;
use App\Http\Controllers\StockFlow\PhysicalCountController;
use App\Http\Controllers\StockFlow\PurchaseOrderController;
use App\Http\Controllers\StockFlow\ReportController;
use App\Http\Controllers\StockFlow\RoleController;
use App\Http\Controllers\StockFlow\SaleController;
use App\Http\Controllers\StockFlow\SearchController;
use App\Http\Controllers\StockFlow\SettingsController;
use App\Http\Controllers\StockFlow\StockMovementController;
use App\Http\Controllers\StockFlow\QuotationController;
use App\Http\Controllers\StockFlow\InvoiceController;
use App\Http\Controllers\StockFlow\ReceiptController;
use App\Http\Controllers\StockFlow\CustomerController;
use App\Http\Controllers\StockFlow\TaxRateController;
use App\Http\Controllers\StockFlow\CurrencyController;
use App\Http\Controllers\StockFlow\WarehouseController;
use App\Http\Controllers\StockFlow\LotController;
use App\Http\Controllers\StockFlow\RequisitionController;
use App\Http\Controllers\StockFlow\PaymentController;
use App\Http\Controllers\StockFlow\CategoryController;
use App\Http\Controllers\StockFlow\SaleReturnController;
use App\Http\Controllers\StockFlow\SupplierReturnController;
use App\Http\Controllers\StockFlow\BranchController;
use App\Http\Controllers\StockFlow\TransferController;
use App\Http\Controllers\StockFlow\AdvancedReportController;
use App\Http\Controllers\StockFlow\ImportController;
use App\Http\Controllers\StockFlow\ItemSupplierController;
use App\Http\Controllers\StockFlow\ItemBulkController;
use App\Http\Controllers\StockFlow\LabelController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| StockFlow Company Subdomain Routes
|--------------------------------------------------------------------------
|
| These routes handle company-specific subdomains like taradasi.mygrownet.com.
| They serve the StockFlow module at the subdomain root (no /stockflow prefix).
| The stockflow.company middleware validates the subdomain is an active company.
|
| IMPORTANT: This file MUST be loaded BEFORE web.php so the wildcard domain
| route matches before the main site's domain-less GET / route.
*/

Route::domain('{account}.mygrownet.com')
    ->middleware(['web', 'stockflow.company'])
    ->where(['account' => '^(?!stockflow$)[a-z0-9-]+$']) // Exclude 'stockflow' subdomain
    ->group(function () {
        // Public routes (no auth required)
        Route::get('/login', [AuthController::class, 'showLogin'])->name('stockflow.sub.login');
        Route::post('/login', [AuthController::class, 'login'])->name('stockflow.sub.login.store');
        Route::post('/logout', [AuthController::class, 'logout'])->name('stockflow.sub.logout');

        // Root — show company landing page if unauthenticated, dashboard if authenticated
        Route::get('/', [LandingController::class, 'index'])->name('stockflow.sub.dashboard');

        // Authenticated routes (using stockflow guard)
        Route::middleware('auth:stockflow')->group(function () {

            // Comments API (cross-cutting, no feature flag)
            Route::prefix('comments')->name('stockflow.sub.comments.')->group(function () {
                Route::get('/', [CommentController::class, 'index'])->name('index');
                Route::post('/', [CommentController::class, 'store'])->name('store');
                Route::put('/{commentId}', [CommentController::class, 'update'])->name('update');
                Route::delete('/{commentId}', [CommentController::class, 'destroy'])->name('destroy');
            });

            // Messages
            Route::prefix('messages')->name('stockflow.sub.messages.')->group(function () {
                Route::get('/', [MessageController::class, 'inbox'])->name('inbox');
                Route::get('/sent', [MessageController::class, 'sent'])->name('sent');
                Route::get('/compose', [MessageController::class, 'compose'])->name('compose');
                Route::get('/unread-count', [MessageController::class, 'unreadCount'])->name('unread-count');
                Route::get('/{userId}', [MessageController::class, 'show'])->name('show');
                Route::post('/', [MessageController::class, 'store'])->name('store');
                Route::post('/{messageId}/read', [MessageController::class, 'markAsRead'])->name('read');
                Route::post('/read-all', [MessageController::class, 'markAllAsRead'])->name('read-all');
            });

            // Notifications API
            Route::prefix('notifications')->name('stockflow.sub.notifications.')->group(function () {
                Route::get('/count', [StockFlowNotificationController::class, 'count'])->name('count');
                Route::get('/list', [StockFlowNotificationController::class, 'list'])->name('list');
                Route::post('/{notificationId}/read', [StockFlowNotificationController::class, 'markAsRead'])->name('read');
                Route::post('/read-all', [StockFlowNotificationController::class, 'markAllAsRead'])->name('read-all');
            });

            // Import/Export (cross-cutting, no feature flag — templates always downloadable)
            Route::prefix('templates')->name('stockflow.sub.templates.')->group(function () {
                Route::get('/items', [ImportController::class, 'downloadItemTemplate'])->name('items');
                Route::get('/suppliers', [ImportController::class, 'downloadSupplierTemplate'])->name('suppliers');
                Route::get('/bins', [ImportController::class, 'downloadBinTemplate'])->name('bins');
            });
            Route::middleware('stockflow.feature:items')->post('/items/import-csv', [ImportController::class, 'importItems'])->name('stockflow.sub.items.import-csv');
            Route::middleware('stockflow.feature:suppliers')->post('/suppliers/import-csv', [ImportController::class, 'importSuppliers'])->name('stockflow.sub.suppliers.import-csv');
            Route::middleware('stockflow.feature:bins')->post('/bins/import-csv', [ImportController::class, 'importBins'])->name('stockflow.sub.bins.import-csv');

            // Activity Log
            Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('stockflow.sub.activity-log.index');

            // Search
            Route::get('/search', [SearchController::class, 'search'])->name('stockflow.sub.search');

            // Help
            Route::get('/help', fn() => Inertia::render('StockFlow/Help/Index'))->name('stockflow.sub.help.index');
            Route::get('/help/{topic}', fn() => Inertia::render('StockFlow/Help/Index'))->name('stockflow.sub.help.topic');

            // Reports hub
            Route::get('/reports', [ReportController::class, 'index'])->name('stockflow.sub.reports.index');

            // Advanced Reports
            Route::middleware('stockflow.feature:reports')->group(function () {
                Route::get('/reports/abc', [AdvancedReportController::class, 'abcAnalysis'])->name('stockflow.sub.reports.abc');
                Route::get('/reports/aging', [AdvancedReportController::class, 'stockAging'])->name('stockflow.sub.reports.aging');
                Route::get('/reports/turnover', [AdvancedReportController::class, 'inventoryTurnover'])->name('stockflow.sub.reports.turnover');
            });

            // Inventory report
            Route::middleware('stockflow.feature:items')->group(function () {
                Route::get('/inventory/report', [ItemController::class, 'inventoryReport'])->name('stockflow.sub.inventory.report');
                Route::get('/inventory/report/pdf', [ItemController::class, 'inventoryReportPdf'])->name('stockflow.sub.inventory.report.pdf');
            });

            // Company switch
            Route::post('/switch-company', [DashboardController::class, 'switchCompany'])->name('stockflow.sub.switch-company');

            // Companies
            Route::get('/companies', [CompanyController::class, 'index'])->name('stockflow.sub.companies.index');
            Route::get('/companies/create', [CompanyController::class, 'create'])->name('stockflow.sub.companies.create');
            Route::post('/companies', [CompanyController::class, 'store'])->name('stockflow.sub.companies.store');
            Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('stockflow.sub.companies.show');

            // Branches
            Route::middleware('stockflow.feature:branches')->group(function () {
                Route::get('/branches', [BranchController::class, 'index'])->name('stockflow.sub.branches.index');
                Route::post('/branches', [BranchController::class, 'store'])->name('stockflow.sub.branches.store');
                Route::put('/branches/{id}', [BranchController::class, 'update'])->name('stockflow.sub.branches.update');
                Route::delete('/branches/{id}', [BranchController::class, 'destroy'])->name('stockflow.sub.branches.destroy');
            });

            // Departments
            Route::middleware('stockflow.feature:departments')->group(function () {
                Route::get('/departments', [DepartmentController::class, 'index'])->name('stockflow.sub.departments.index');
                Route::post('/departments', [DepartmentController::class, 'store'])->name('stockflow.sub.departments.store');
                Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('stockflow.sub.departments.update');
                Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('stockflow.sub.departments.destroy');
            });

            // Bins
            Route::middleware('stockflow.feature:bins')->group(function () {
                Route::get('/bins', [BinController::class, 'index'])->name('stockflow.sub.bins.index');
                Route::get('/bins/{bin}', [BinController::class, 'show'])->name('stockflow.sub.bins.show');
                Route::post('/bins', [BinController::class, 'store'])->name('stockflow.sub.bins.store');
                Route::put('/bins/{bin}', [BinController::class, 'update'])->name('stockflow.sub.bins.update');
                Route::delete('/bins/{bin}', [BinController::class, 'destroy'])->name('stockflow.sub.bins.destroy');
            });

            // Items
            Route::middleware('stockflow.feature:items')->group(function () {
                Route::get('/items', [ItemController::class, 'index'])->name('stockflow.sub.items.index');
                Route::get('/items/create', [ItemController::class, 'create'])->name('stockflow.sub.items.create');
                Route::post('/items', [ItemController::class, 'store'])->name('stockflow.sub.items.store');
                Route::get('/items/{item}', [ItemController::class, 'show'])->name('stockflow.sub.items.show');
                Route::put('/items/{item}', [ItemController::class, 'update'])->name('stockflow.sub.items.update');
                Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('stockflow.sub.items.destroy');
                Route::post('/items/import', [ItemController::class, 'import'])->name('stockflow.sub.items.import');
                Route::post('/items/{item}/adjust', [ItemController::class, 'adjustStock'])->name('stockflow.sub.items.adjust');
                Route::get('/items/{item}/ledger', [ItemController::class, 'ledger'])->name('stockflow.sub.items.ledger');
                Route::get('/items/{item}/label', [LabelController::class, 'printLabel'])->name('stockflow.sub.items.label');

                // Bulk operations
                Route::post('/items/bulk-delete', [ItemBulkController::class, 'bulkDelete'])->name('stockflow.sub.items.bulk-delete');
                Route::post('/items/bulk-adjust-stock', [ItemBulkController::class, 'bulkAdjustStock'])->name('stockflow.sub.items.bulk-adjust-stock');
                Route::post('/items/bulk-update-price', [ItemBulkController::class, 'bulkUpdatePrice'])->name('stockflow.sub.items.bulk-update-price');

                // Supplier linking
                Route::get('/items/{item}/suppliers', [ItemSupplierController::class, 'index'])->name('stockflow.sub.items.suppliers');
                Route::post('/items/{item}/suppliers', [ItemSupplierController::class, 'store'])->name('stockflow.sub.items.suppliers.store');
                Route::delete('/items/{item}/suppliers/{supplier}', [ItemSupplierController::class, 'destroy'])->name('stockflow.sub.items.suppliers.destroy');
            });

            // Customers
            Route::middleware('stockflow.feature:customers')->group(function () {
                Route::get('/customers', [CustomerController::class, 'index'])->name('stockflow.sub.customers.index');
                Route::get('/customers/create', [CustomerController::class, 'create'])->name('stockflow.sub.customers.create');
                Route::post('/customers', [CustomerController::class, 'store'])->name('stockflow.sub.customers.store');
                Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('stockflow.sub.customers.show');
                Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('stockflow.sub.customers.update');
                Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('stockflow.sub.customers.destroy');
            });

            // Suppliers
            Route::middleware('stockflow.feature:suppliers')->group(function () {
                Route::get('/suppliers', [PurchaseOrderController::class, 'suppliers'])->name('stockflow.sub.suppliers.index');
                Route::post('/suppliers', [PurchaseOrderController::class, 'storeSupplier'])->name('stockflow.sub.suppliers.store');
                Route::put('/suppliers/{supplier}', [PurchaseOrderController::class, 'updateSupplier'])->name('stockflow.sub.suppliers.update');
                Route::delete('/suppliers/{supplier}', [PurchaseOrderController::class, 'destroySupplier'])->name('stockflow.sub.suppliers.destroy');
            });

            // Purchases
            Route::middleware('stockflow.feature:purchases')->group(function () {
                Route::get('/purchases', [PurchaseOrderController::class, 'index'])->name('stockflow.sub.purchases.index');
                Route::get('/purchases/create', [PurchaseOrderController::class, 'create'])->name('stockflow.sub.purchases.create');
                Route::post('/purchases', [PurchaseOrderController::class, 'store'])->name('stockflow.sub.purchases.store');
                Route::get('/purchases/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('stockflow.sub.purchases.show');
                Route::post('/purchases/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('stockflow.sub.purchases.receive');
                Route::get('/purchases/report', [PurchaseOrderController::class, 'report'])->name('stockflow.sub.purchases.report');
                Route::get('/purchases/report/pdf', [PurchaseOrderController::class, 'reportPdf'])->name('stockflow.sub.purchases.report.pdf');
            });

            // Sales
            Route::middleware('stockflow.feature:sales')->group(function () {
                Route::get('/sales', [SaleController::class, 'index'])->name('stockflow.sub.sales.index');
                Route::get('/sales/create', [SaleController::class, 'create'])->name('stockflow.sub.sales.create');
                Route::post('/sales', [SaleController::class, 'store'])->name('stockflow.sub.sales.store');
                Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('stockflow.sub.sales.show');
                Route::get('/sales-report', [SaleController::class, 'report'])->name('stockflow.sub.sales.report');
                Route::get('/sales-report/pdf', [SaleController::class, 'reportPdf'])->name('stockflow.sub.sales.report.pdf');
                Route::get('/api/sales/by-date', [SaleController::class, 'byDate'])->name('stockflow.sub.sales.by-date');
            });

            // Quotations
            Route::middleware('stockflow.feature:quotations')->group(function () {
                Route::get('/quotations', [QuotationController::class, 'index'])->name('stockflow.sub.quotations.index');
                Route::get('/quotations/create', [QuotationController::class, 'create'])->name('stockflow.sub.quotations.create');
                Route::post('/quotations', [QuotationController::class, 'store'])->name('stockflow.sub.quotations.store');
                Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('stockflow.sub.quotations.show');
                Route::post('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('stockflow.sub.quotations.status');
            });

            // Invoices
            Route::middleware('stockflow.feature:invoices')->group(function () {
                Route::get('/invoices', [InvoiceController::class, 'index'])->name('stockflow.sub.invoices.index');
                Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('stockflow.sub.invoices.create');
                Route::post('/invoices', [InvoiceController::class, 'store'])->name('stockflow.sub.invoices.store');
                Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('stockflow.sub.invoices.show');
                Route::post('/invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('stockflow.sub.invoices.payment');
                Route::post('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('stockflow.sub.invoices.status');
            });

            // Receipts
            Route::middleware('stockflow.feature:receipts')->group(function () {
                Route::get('/receipts', [ReceiptController::class, 'index'])->name('stockflow.sub.receipts.index');
                Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('stockflow.sub.receipts.show');
            });

            // Cash Register
            Route::middleware('stockflow.feature:cash')->group(function () {
                Route::get('/cash', [CashController::class, 'index'])->name('stockflow.sub.cash.index');
                Route::post('/cash/open', [CashController::class, 'open'])->name('stockflow.sub.cash.open');
                Route::get('/cash/{cashRegister}', [CashController::class, 'show'])->name('stockflow.sub.cash.show');
                Route::post('/cash/{cashRegister}/movement', [CashController::class, 'addMovement'])->name('stockflow.sub.cash.movement');
                Route::post('/cash/{cashRegister}/close', [CashController::class, 'close'])->name('stockflow.sub.cash.close');
                Route::post('/cash/{cashRegister}/verify', [CashController::class, 'verify'])->name('stockflow.sub.cash.verify');
                Route::get('/cash-summary', [CashController::class, 'summary'])->name('stockflow.sub.cash.summary');
                Route::get('/cash-summary/pdf', [CashController::class, 'summaryPdf'])->name('stockflow.sub.cash.summary.pdf');
            });

            // Stock Movements
            Route::middleware('stockflow.feature:movements')->group(function () {
                Route::get('/movements', [StockMovementController::class, 'index'])->name('stockflow.sub.movements.index');
                Route::get('/api/movements/by-item/{item}', [StockMovementController::class, 'byItem'])->name('stockflow.sub.movements.by-item');
            });

            // Transfers
            Route::middleware('stockflow.feature:transfers')->group(function () {
                Route::get('/transfers', [TransferController::class, 'index'])->name('stockflow.sub.transfers.index');
                Route::get('/transfers/create', [TransferController::class, 'create'])->name('stockflow.sub.transfers.create');
                Route::post('/transfers', [TransferController::class, 'store'])->name('stockflow.sub.transfers.store');
                Route::get('/transfers/{transfer}', [TransferController::class, 'show'])->name('stockflow.sub.transfers.show');
                Route::post('/transfers/{transfer}/receive', [TransferController::class, 'receive'])->name('stockflow.sub.transfers.receive');
                Route::post('/transfers/{transfer}/cancel', [TransferController::class, 'cancel'])->name('stockflow.sub.transfers.cancel');
            });

            // Physical Counts
            Route::middleware('stockflow.feature:counts')->group(function () {
                Route::get('/physical-counts', [PhysicalCountController::class, 'index'])->name('stockflow.sub.physical-counts.index');
                Route::post('/physical-counts', [PhysicalCountController::class, 'store'])->name('stockflow.sub.physical-counts.store');
                Route::get('/physical-counts/{physicalCount}', [PhysicalCountController::class, 'show'])->name('stockflow.sub.physical-counts.show');
                Route::put('/physical-counts/{physicalCount}/items', [PhysicalCountController::class, 'updateItems'])->name('stockflow.sub.physical-counts.update-items');
                Route::post('/physical-counts/{physicalCount}/complete', [PhysicalCountController::class, 'complete'])->name('stockflow.sub.physical-counts.complete');
                Route::post('/physical-counts/{physicalCount}/generate-audit', [PhysicalCountController::class, 'generateAudit'])->name('stockflow.sub.physical-counts.generate-audit');
            });

            // Audits
            Route::middleware('stockflow.feature:audits')->group(function () {
                Route::get('/audits', [AuditController::class, 'index'])->name('stockflow.sub.audits.index');
                Route::post('/audits', [AuditController::class, 'store'])->name('stockflow.sub.audits.store');
                Route::get('/audits/{audit}', [AuditController::class, 'show'])->name('stockflow.sub.audits.show');
                Route::post('/audits/{audit}/finalize', [AuditController::class, 'finalize'])->name('stockflow.sub.audits.finalize');
                Route::get('/audits/{audit}/export-csv', [AuditController::class, 'exportCsv'])->name('stockflow.sub.audits.export-csv');
                Route::get('/audits/{audit}/pdf', [AuditController::class, 'exportPdf'])->name('stockflow.sub.audits.export-pdf');
            });

            // Tax Rates
            Route::middleware('stockflow.feature:tax')->group(function () {
                Route::get('/tax-rates', [TaxRateController::class, 'index'])->name('stockflow.sub.tax-rates.index');
                Route::post('/tax-rates', [TaxRateController::class, 'store'])->name('stockflow.sub.tax-rates.store');
                Route::put('/tax-rates/{id}', [TaxRateController::class, 'update'])->name('stockflow.sub.tax-rates.update');
                Route::delete('/tax-rates/{id}', [TaxRateController::class, 'destroy'])->name('stockflow.sub.tax-rates.destroy');
            });

            // Exchange Rates (multi-currency)
            Route::middleware('stockflow.feature:currency')->group(function () {
                Route::get('/exchange-rates', [CurrencyController::class, 'index'])->name('stockflow.sub.exchange-rates.index');
                Route::post('/exchange-rates', [CurrencyController::class, 'store'])->name('stockflow.sub.exchange-rates.store');
                Route::put('/exchange-rates/{id}', [CurrencyController::class, 'update'])->name('stockflow.sub.exchange-rates.update');
                Route::delete('/exchange-rates/{id}', [CurrencyController::class, 'destroy'])->name('stockflow.sub.exchange-rates.destroy');
            });

            // Warehouses
            Route::middleware('stockflow.feature:warehouses')->group(function () {
                Route::get('/warehouses', [WarehouseController::class, 'index'])->name('stockflow.sub.warehouses.index');
                Route::post('/warehouses', [WarehouseController::class, 'store'])->name('stockflow.sub.warehouses.store');
                Route::put('/warehouses/{id}', [WarehouseController::class, 'update'])->name('stockflow.sub.warehouses.update');
                Route::delete('/warehouses/{id}', [WarehouseController::class, 'destroy'])->name('stockflow.sub.warehouses.destroy');
            });

            // Lot/Batch Tracking
            Route::middleware('stockflow.feature:lots')->group(function () {
                Route::get('/lots', [LotController::class, 'index'])->name('stockflow.sub.lots.index');
                Route::post('/lots', [LotController::class, 'store'])->name('stockflow.sub.lots.store');
                Route::put('/lots/{id}', [LotController::class, 'update'])->name('stockflow.sub.lots.update');
                Route::delete('/lots/{id}', [LotController::class, 'destroy'])->name('stockflow.sub.lots.destroy');
                Route::get('/lots/by-item/{itemId}', [LotController::class, 'byItem'])->name('stockflow.sub.lots.by-item');
                Route::get('/lots/{lot}/traceability', [LotController::class, 'traceability'])->name('stockflow.sub.lots.traceability');
                Route::get('/items/{item}/lots', [LotController::class, 'byItem'])->name('stockflow.sub.items.lots');
            });

            // Purchase Requisitions
            Route::middleware('stockflow.feature:requisitions')->group(function () {
                Route::get('/requisitions', [RequisitionController::class, 'index'])->name('stockflow.sub.requisitions.index');
                Route::post('/requisitions', [RequisitionController::class, 'store'])->name('stockflow.sub.requisitions.store');
                Route::get('/requisitions/{id}', [RequisitionController::class, 'show'])->name('stockflow.sub.requisitions.show');
                Route::post('/requisitions/{id}/approve', [RequisitionController::class, 'approve'])->name('stockflow.sub.requisitions.approve');
                Route::post('/requisitions/{id}/reject', [RequisitionController::class, 'reject'])->name('stockflow.sub.requisitions.reject');
                Route::delete('/requisitions/{id}', [RequisitionController::class, 'destroy'])->name('stockflow.sub.requisitions.destroy');
            });

            // Payment Transactions
            Route::middleware('stockflow.feature:payments')->group(function () {
                Route::get('/payments', [PaymentController::class, 'index'])->name('stockflow.sub.payments.index');
                Route::post('/payments', [PaymentController::class, 'store'])->name('stockflow.sub.payments.store');
                Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('stockflow.sub.payments.show');
            });

            // Categories (nested)
            Route::middleware('stockflow.feature:items')->group(function () {
                Route::get('/categories', [CategoryController::class, 'index'])->name('stockflow.sub.categories.index');
                Route::post('/categories', [CategoryController::class, 'store'])->name('stockflow.sub.categories.store');
                Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('stockflow.sub.categories.update');
                Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('stockflow.sub.categories.destroy');
            });

            // Sale Returns
            Route::middleware('stockflow.feature:sales')->group(function () {
                Route::get('/sale-returns', [SaleReturnController::class, 'index'])->name('stockflow.sub.sale-returns.index');
                Route::post('/sale-returns', [SaleReturnController::class, 'store'])->name('stockflow.sub.sale-returns.store');
            });

            // Supplier Returns
            Route::middleware('stockflow.feature:purchases')->group(function () {
                Route::get('/supplier-returns', [SupplierReturnController::class, 'index'])->name('stockflow.sub.supplier-returns.index');
                Route::post('/supplier-returns', [SupplierReturnController::class, 'store'])->name('stockflow.sub.supplier-returns.store');
            });

            // Supplier Portal
            Route::middleware('stockflow.feature:suppliers')->prefix('supplier-portal')->name('stockflow.sub.supplier-portal.')->group(function () {
                Route::get('/', [\App\Http\Controllers\StockFlow\SupplierPortalController::class, 'dashboard'])->name('dashboard');
                Route::get('/orders', [\App\Http\Controllers\StockFlow\SupplierPortalController::class, 'orders'])->name('orders');
                Route::get('/profile', [\App\Http\Controllers\StockFlow\SupplierPortalController::class, 'profile'])->name('profile');
            });

            // Settings
            Route::get('/settings', [SettingsController::class, 'index'])->name('stockflow.sub.settings.index');
            Route::put('/settings/company', [SettingsController::class, 'updateCompany'])->name('stockflow.sub.settings.update-company');
            Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('stockflow.sub.settings.update-profile');
            Route::post('/settings/backup', [SettingsController::class, 'updateBackup'])->name('stockflow.sub.settings.update-backup');
            Route::post('/settings/domains', [SettingsController::class, 'addDomain'])->name('stockflow.sub.settings.domains.add');
            Route::delete('/settings/domains/{domainId}', [SettingsController::class, 'deleteDomain'])->name('stockflow.sub.settings.domains.delete');
            Route::post('/settings/email', [SettingsController::class, 'updateEmailSettings'])->name('stockflow.sub.settings.update-email');
            Route::post('/settings/api-keys', [SettingsController::class, 'generateApiKey'])->name('stockflow.sub.settings.api-keys.generate');
            Route::post('/settings/api-keys/{keyId}/revoke', [SettingsController::class, 'revokeApiKey'])->name('stockflow.sub.settings.api-keys.revoke');

            // Roles & Employees
            Route::middleware(['stockflow.feature:roles', 'stockflow.permission:employees.roles'])->group(function () {
                Route::get('/roles', [RoleController::class, 'index'])->name('stockflow.sub.roles.index');
                Route::post('/roles', [RoleController::class, 'store'])->name('stockflow.sub.roles.store');
                Route::put('/roles/{role}', [RoleController::class, 'update'])->name('stockflow.sub.roles.update');
                Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('stockflow.sub.roles.destroy');
            });

            Route::middleware(['stockflow.feature:employees', 'stockflow.permission:employees.view'])->group(function () {
                Route::get('/employees', [EmployeeController::class, 'index'])->name('stockflow.sub.employees.index');
                Route::post('/employees/invite', [EmployeeController::class, 'invite'])->name('stockflow.sub.employees.invite');
                Route::post('/employees/{employee}/accept', [EmployeeController::class, 'accept'])->name('stockflow.sub.employees.accept');
                Route::put('/employees/{employee}/role', [EmployeeController::class, 'updateRole'])->name('stockflow.sub.employees.update-role');
                Route::post('/employees/{employee}/suspend', [EmployeeController::class, 'suspend'])->name('stockflow.sub.employees.suspend');
                Route::post('/employees/{employee}/reactivate', [EmployeeController::class, 'reactivate'])->name('stockflow.sub.employees.reactivate');
                Route::post('/employees/{employee}/remove', [EmployeeController::class, 'remove'])->name('stockflow.sub.employees.remove');
            });
        });
    });
