<?php

use App\Http\Controllers\StockAudit\AuditController;
use App\Http\Controllers\StockAudit\BinController;
use App\Http\Controllers\StockAudit\CashController;
use App\Http\Controllers\StockAudit\CompanyController;
use App\Http\Controllers\StockAudit\DashboardController;
use App\Http\Controllers\StockAudit\DepartmentController;
use App\Http\Controllers\StockAudit\EmployeeController;
use App\Http\Controllers\StockAudit\ItemController;
use App\Http\Controllers\StockAudit\PhysicalCountController;
use App\Http\Controllers\StockAudit\PurchaseOrderController;
use App\Http\Controllers\StockAudit\RoleController;
use App\Http\Controllers\StockAudit\SaleController;
use App\Http\Controllers\StockAudit\StockMovementController;
use App\Http\Controllers\StockAudit\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('stock-audit')->name('stock-audit.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Company switch
    Route::post('/switch-company', [DashboardController::class, 'switchCompany'])->name('switch-company');

    // Companies
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');

    // Departments
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    // Bins
    Route::get('/bins', [BinController::class, 'index'])->name('bins.index');
    Route::post('/bins', [BinController::class, 'store'])->name('bins.store');
    Route::put('/bins/{bin}', [BinController::class, 'update'])->name('bins.update');
    Route::delete('/bins/{bin}', [BinController::class, 'destroy'])->name('bins.destroy');

    // Items
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::post('/items/import', [ItemController::class, 'import'])->name('items.import');

    // Suppliers
    Route::get('/suppliers', [PurchaseOrderController::class, 'suppliers'])->name('suppliers.index');
    Route::post('/suppliers', [PurchaseOrderController::class, 'storeSupplier'])->name('suppliers.store');
    Route::delete('/suppliers/{supplier}', [PurchaseOrderController::class, 'destroySupplier'])->name('suppliers.destroy');

    // Purchases
    Route::get('/purchases', [PurchaseOrderController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseOrderController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseOrderController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchases.show');
    Route::post('/purchases/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchases.receive');

    // Sales
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales-report', [SaleController::class, 'report'])->name('sales.report');
    Route::get('/api/sales/by-date', [SaleController::class, 'byDate'])->name('sales.by-date');

    // Cash Register
    Route::get('/cash', [CashController::class, 'index'])->name('cash.index');
    Route::post('/cash/open', [CashController::class, 'open'])->name('cash.open');
    Route::get('/cash/{cashRegister}', [CashController::class, 'show'])->name('cash.show');
    Route::post('/cash/{cashRegister}/movement', [CashController::class, 'addMovement'])->name('cash.movement');
    Route::post('/cash/{cashRegister}/close', [CashController::class, 'close'])->name('cash.close');
    Route::post('/cash/{cashRegister}/verify', [CashController::class, 'verify'])->name('cash.verify');
    Route::get('/cash-summary', [CashController::class, 'summary'])->name('cash.summary');

    // Stock Movements
    Route::get('/movements', [StockMovementController::class, 'index'])->name('movements.index');
    Route::get('/api/movements/by-item/{item}', [StockMovementController::class, 'byItem'])->name('movements.by-item');

    // Stock Adjustments
    Route::post('/items/{item}/adjust', [ItemController::class, 'adjustStock'])->name('items.adjust');

    // Physical Counts
    Route::get('/physical-counts', [PhysicalCountController::class, 'index'])->name('physical-counts.index');
    Route::post('/physical-counts', [PhysicalCountController::class, 'store'])->name('physical-counts.store');
    Route::get('/physical-counts/{physicalCount}', [PhysicalCountController::class, 'show'])->name('physical-counts.show');
    Route::put('/physical-counts/{physicalCount}/items', [PhysicalCountController::class, 'updateItems'])->name('physical-counts.update-items');
    Route::post('/physical-counts/{physicalCount}/complete', [PhysicalCountController::class, 'complete'])->name('physical-counts.complete');
    Route::post('/physical-counts/{physicalCount}/generate-audit', [PhysicalCountController::class, 'generateAudit'])->name('physical-counts.generate-audit');

    // Audits
    Route::get('/audits', [AuditController::class, 'index'])->name('audits.index');
    Route::post('/audits', [AuditController::class, 'store'])->name('audits.store');
    Route::get('/audits/{audit}', [AuditController::class, 'show'])->name('audits.show');
    Route::post('/audits/{audit}/finalize', [AuditController::class, 'finalize'])->name('audits.finalize');
    Route::get('/audits/{audit}/export-csv', [AuditController::class, 'exportCsv'])->name('audits.export-csv');

    // Roles & Employees (require employees.roles / employees.view permissions)
    Route::middleware('stockflow.permission:employees.roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::middleware('stockflow.permission:employees.view')->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees/invite', [EmployeeController::class, 'invite'])->name('employees.invite');
        Route::post('/employees/{employee}/accept', [EmployeeController::class, 'accept'])->name('employees.accept');
        Route::put('/employees/{employee}/role', [EmployeeController::class, 'updateRole'])->name('employees.update-role');
        Route::post('/employees/{employee}/suspend', [EmployeeController::class, 'suspend'])->name('employees.suspend');
        Route::post('/employees/{employee}/reactivate', [EmployeeController::class, 'reactivate'])->name('employees.reactivate');
        Route::post('/employees/{employee}/remove', [EmployeeController::class, 'remove'])->name('employees.remove');
    });
});
