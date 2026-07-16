<?php

use App\Http\Controllers\StockFlow\AuditController;
use App\Http\Controllers\StockFlow\BinController;
use App\Http\Controllers\StockFlow\CashController;
use App\Http\Controllers\StockFlow\CommentController;
use App\Http\Controllers\StockFlow\CompanyController;
use App\Http\Controllers\StockFlow\DashboardController;
use App\Http\Controllers\StockFlow\DepartmentController;
use App\Http\Controllers\StockFlow\EmployeeController;
use App\Http\Controllers\StockFlow\ItemController;
use App\Http\Controllers\StockFlow\MessageController;
use App\Http\Controllers\StockFlow\PhysicalCountController;
use App\Http\Controllers\StockFlow\NotificationController as StockFlowNotificationController;
use App\Http\Controllers\StockFlow\PurchaseOrderController;
use App\Http\Controllers\StockFlow\ReportController;
use App\Http\Controllers\StockFlow\RoleController;
use App\Http\Controllers\StockFlow\SaleController;
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
use App\Http\Controllers\StockFlow\ControlledMedicineController;
use App\Http\Controllers\StockFlow\SaleReturnController;
use App\Http\Controllers\StockFlow\SupplierReturnController;
use App\Http\Controllers\StockFlow\BranchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('stockflow')->name('stockflow.')->group(function () {
    // Comments API (cross-cutting, no feature flag)
    Route::prefix('comments')->name('comments.')->group(function () {
        Route::get('/', [CommentController::class, 'index'])->name('index');
        Route::post('/', [CommentController::class, 'store'])->name('store');
        Route::put('/{commentId}', [CommentController::class, 'update'])->name('update');
        Route::delete('/{commentId}', [CommentController::class, 'destroy'])->name('destroy');
    });

    // Messages
    Route::prefix('messages')->name('messages.')->group(function () {
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
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/count', [StockAuditNotificationController::class, 'count'])->name('count');
        Route::get('/list', [StockAuditNotificationController::class, 'list'])->name('list');
        Route::post('/{notificationId}/read', [StockAuditNotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [StockAuditNotificationController::class, 'markAllAsRead'])->name('read-all');
    });
    // Logout
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Reports hub
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{type}', [ReportController::class, 'show'])->name('reports.show');

    // Inventory report (inside items feature)
    Route::middleware('stockflow.feature:items')->group(function () {
        Route::get('/inventory/report', [ItemController::class, 'inventoryReport'])->name('inventory.report');
        Route::get('/inventory/report/pdf', [ItemController::class, 'inventoryReportPdf'])->name('inventory.report.pdf');
    });

    // Company switch
    Route::post('/switch-company', [DashboardController::class, 'switchCompany'])->name('switch-company');

    // Companies
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');

    // Branches
    Route::middleware('stockflow.feature:branches')->group(function () {
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::put('/branches/{id}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('/branches/{id}', [BranchController::class, 'destroy'])->name('branches.destroy');
    });

    // Departments
    Route::middleware('stockflow.feature:departments')->group(function () {
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    });

    // Bins
    Route::middleware('stockflow.feature:bins')->group(function () {
        Route::get('/bins', [BinController::class, 'index'])->name('bins.index');
    Route::post('/bins', [BinController::class, 'store'])->name('bins.store');
    Route::put('/bins/{bin}', [BinController::class, 'update'])->name('bins.update');
    Route::delete('/bins/{bin}', [BinController::class, 'destroy'])->name('bins.destroy');
    });

    // Items
    Route::middleware('stockflow.feature:items')->group(function () {
        Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::post('/items/import', [ItemController::class, 'import'])->name('items.import');
    });

    // Customers
    Route::middleware('stockflow.feature:customers')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Tax Rates
    Route::middleware('stockflow.feature:tax')->group(function () {
        Route::get('/tax-rates', [TaxRateController::class, 'index'])->name('tax-rates.index');
        Route::post('/tax-rates', [TaxRateController::class, 'store'])->name('tax-rates.store');
        Route::put('/tax-rates/{id}', [TaxRateController::class, 'update'])->name('tax-rates.update');
        Route::delete('/tax-rates/{id}', [TaxRateController::class, 'destroy'])->name('tax-rates.destroy');
    });

    // Exchange Rates (multi-currency)
    Route::middleware('stockflow.feature:currency')->group(function () {
        Route::get('/exchange-rates', [CurrencyController::class, 'index'])->name('exchange-rates.index');
        Route::post('/exchange-rates', [CurrencyController::class, 'store'])->name('exchange-rates.store');
        Route::put('/exchange-rates/{id}', [CurrencyController::class, 'update'])->name('exchange-rates.update');
        Route::delete('/exchange-rates/{id}', [CurrencyController::class, 'destroy'])->name('exchange-rates.destroy');
    });

    // Warehouses
    Route::middleware('stockflow.feature:warehouses')->group(function () {
        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::put('/warehouses/{id}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{id}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    });

    // Lot/Batch Tracking
    Route::middleware('stockflow.feature:lots')->group(function () {
        Route::get('/lots', [LotController::class, 'index'])->name('lots.index');
        Route::post('/lots', [LotController::class, 'store'])->name('lots.store');
        Route::put('/lots/{id}', [LotController::class, 'update'])->name('lots.update');
        Route::delete('/lots/{id}', [LotController::class, 'destroy'])->name('lots.destroy');
        Route::get('/lots/by-item/{itemId}', [LotController::class, 'byItem'])->name('lots.by-item');
    });

    // Purchase Requisitions
    Route::middleware('stockflow.feature:requisitions')->group(function () {
        Route::get('/requisitions', [RequisitionController::class, 'index'])->name('requisitions.index');
        Route::post('/requisitions', [RequisitionController::class, 'store'])->name('requisitions.store');
        Route::get('/requisitions/{id}', [RequisitionController::class, 'show'])->name('requisitions.show');
        Route::post('/requisitions/{id}/approve', [RequisitionController::class, 'approve'])->name('requisitions.approve');
        Route::post('/requisitions/{id}/reject', [RequisitionController::class, 'reject'])->name('requisitions.reject');
        Route::delete('/requisitions/{id}', [RequisitionController::class, 'destroy'])->name('requisitions.destroy');
    });

    // Payment Transactions
    Route::middleware('stockflow.feature:payments')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
    });

    // Categories (nested)
    Route::middleware('stockflow.feature:items')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Controlled Medicine Register
    Route::middleware('stockflow.feature:controlled-medicines')->group(function () {
        Route::get('/controlled-medicines', [ControlledMedicineController::class, 'index'])->name('controlled-medicines.index');
        Route::post('/controlled-medicines', [ControlledMedicineController::class, 'store'])->name('controlled-medicines.store');
    });

    // Sale Returns
    Route::middleware('stockflow.feature:sales')->group(function () {
        Route::get('/sale-returns', [SaleReturnController::class, 'index'])->name('sale-returns.index');
        Route::post('/sale-returns', [SaleReturnController::class, 'store'])->name('sale-returns.store');
    });

    // Supplier Returns
    Route::middleware('stockflow.feature:purchases')->group(function () {
        Route::get('/supplier-returns', [SupplierReturnController::class, 'index'])->name('supplier-returns.index');
        Route::post('/supplier-returns', [SupplierReturnController::class, 'store'])->name('supplier-returns.store');
    });

    // Supplier Portal
    Route::middleware('stockflow.feature:suppliers')->prefix('supplier-portal')->name('supplier-portal.')->group(function () {
        Route::get('/', [\App\Http\Controllers\StockFlow\SupplierPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [\App\Http\Controllers\StockFlow\SupplierPortalController::class, 'orders'])->name('orders');
        Route::get('/profile', [\App\Http\Controllers\StockFlow\SupplierPortalController::class, 'profile'])->name('profile');
    });

    // Settings
    Route::middleware('stockflow.feature:suppliers')->group(function () {
        Route::get('/suppliers', [PurchaseOrderController::class, 'suppliers'])->name('suppliers.index');
    Route::post('/suppliers', [PurchaseOrderController::class, 'storeSupplier'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [PurchaseOrderController::class, 'updateSupplier'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [PurchaseOrderController::class, 'destroySupplier'])->name('suppliers.destroy');
    });

    // Purchases
    Route::middleware('stockflow.feature:purchases')->group(function () {
        Route::get('/purchases', [PurchaseOrderController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseOrderController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseOrderController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchases.show');
        Route::post('/purchases/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchases.receive');
        Route::get('/purchases/report', [PurchaseOrderController::class, 'report'])->name('purchases.report');
        Route::get('/purchases/report/pdf', [PurchaseOrderController::class, 'reportPdf'])->name('purchases.report.pdf');
    });

    // Sales
    Route::middleware('stockflow.feature:sales')->group(function () {
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('/sales-report', [SaleController::class, 'report'])->name('sales.report');
        Route::get('/sales-report/pdf', [SaleController::class, 'reportPdf'])->name('sales.report.pdf');
        Route::get('/api/sales/by-date', [SaleController::class, 'byDate'])->name('sales.by-date');
    });

    // Quotations
    Route::middleware('stockflow.feature:quotations')->group(function () {
        Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('/quotations', [QuotationController::class, 'store'])->name('quotations.store');
        Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
        Route::post('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.status');
    });

    // Invoices
    Route::middleware('stockflow.feature:invoices')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('/invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');
        Route::post('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
    });

    // Receipts
    Route::middleware('stockflow.feature:receipts')->group(function () {
        Route::get('/receipts', [ReceiptController::class, 'index'])->name('receipts.index');
        Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
    });

    // Cash Register
    Route::middleware('stockflow.feature:cash')->group(function () {
        Route::get('/cash', [CashController::class, 'index'])->name('cash.index');
        Route::post('/cash/open', [CashController::class, 'open'])->name('cash.open');
        Route::get('/cash/{cashRegister}', [CashController::class, 'show'])->name('cash.show');
        Route::post('/cash/{cashRegister}/movement', [CashController::class, 'addMovement'])->name('cash.movement');
        Route::post('/cash/{cashRegister}/close', [CashController::class, 'close'])->name('cash.close');
        Route::post('/cash/{cashRegister}/verify', [CashController::class, 'verify'])->name('cash.verify');
        Route::get('/cash-summary', [CashController::class, 'summary'])->name('cash.summary');
        Route::get('/cash-summary/pdf', [CashController::class, 'summaryPdf'])->name('cash.summary.pdf');
    });

    // Stock Movements
    Route::middleware('stockflow.feature:movements')->group(function () {
        Route::get('/movements', [StockMovementController::class, 'index'])->name('movements.index');
    Route::get('/api/movements/by-item/{item}', [StockMovementController::class, 'byItem'])->name('movements.by-item');
    });

    // Stock Adjustments (covered by items feature)
    Route::post('/items/{item}/adjust', [ItemController::class, 'adjustStock'])->name('items.adjust');
    Route::get('/items/{item}/ledger', [ItemController::class, 'ledger'])->name('items.ledger');

    // Physical Counts
    Route::middleware('stockflow.feature:counts')->group(function () {
        Route::get('/physical-counts', [PhysicalCountController::class, 'index'])->name('physical-counts.index');
    Route::post('/physical-counts', [PhysicalCountController::class, 'store'])->name('physical-counts.store');
    Route::get('/physical-counts/{physicalCount}', [PhysicalCountController::class, 'show'])->name('physical-counts.show');
    Route::put('/physical-counts/{physicalCount}/items', [PhysicalCountController::class, 'updateItems'])->name('physical-counts.update-items');
    Route::post('/physical-counts/{physicalCount}/complete', [PhysicalCountController::class, 'complete'])->name('physical-counts.complete');
    Route::post('/physical-counts/{physicalCount}/generate-audit', [PhysicalCountController::class, 'generateAudit'])->name('physical-counts.generate-audit');
    });

    // Audits
    Route::middleware('stockflow.feature:audits')->group(function () {
        Route::get('/audits', [AuditController::class, 'index'])->name('audits.index');
    Route::post('/audits', [AuditController::class, 'store'])->name('audits.store');
    Route::get('/audits/{audit}', [AuditController::class, 'show'])->name('audits.show');
    Route::post('/audits/{audit}/finalize', [AuditController::class, 'finalize'])->name('audits.finalize');
    Route::get('/audits/{audit}/export-csv', [AuditController::class, 'exportCsv'])->name('audits.export-csv');
    Route::get('/audits/{audit}/pdf', [AuditController::class, 'exportPdf'])->name('audits.export-pdf');
    });

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.update-company');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.update-profile');
    Route::post('/settings/backup', [SettingsController::class, 'updateBackup'])->name('settings.update-backup');
    Route::post('/settings/domains', [SettingsController::class, 'addDomain'])->name('settings.domains.add');
    Route::delete('/settings/domains/{domainId}', [SettingsController::class, 'deleteDomain'])->name('settings.domains.delete');
    Route::post('/settings/email', [SettingsController::class, 'updateEmailSettings'])->name('settings.update-email');
    Route::post('/settings/api-keys', [SettingsController::class, 'generateApiKey'])->name('settings.api-keys.generate');
    Route::post('/settings/api-keys/{keyId}/revoke', [SettingsController::class, 'revokeApiKey'])->name('settings.api-keys.revoke');

    // Roles & Employees (require feature + permission)
    Route::middleware(['stockflow.feature:roles', 'stockflow.permission:employees.roles'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::middleware(['stockflow.feature:employees', 'stockflow.permission:employees.view'])->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees/invite', [EmployeeController::class, 'invite'])->name('employees.invite');
        Route::post('/employees/{employee}/accept', [EmployeeController::class, 'accept'])->name('employees.accept');
        Route::put('/employees/{employee}/role', [EmployeeController::class, 'updateRole'])->name('employees.update-role');
        Route::post('/employees/{employee}/suspend', [EmployeeController::class, 'suspend'])->name('employees.suspend');
        Route::post('/employees/{employee}/reactivate', [EmployeeController::class, 'reactivate'])->name('employees.reactivate');
        Route::post('/employees/{employee}/remove', [EmployeeController::class, 'remove'])->name('employees.remove');
    });
});
