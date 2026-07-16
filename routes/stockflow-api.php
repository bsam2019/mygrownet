<?php

use App\Http\Controllers\StockFlow\ItemController;
use App\Http\Controllers\StockFlow\SaleController;
use App\Http\Controllers\StockFlow\InvoiceController;
use App\Http\Controllers\StockFlow\QuotationController;
use App\Http\Controllers\StockFlow\PurchaseOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| StockFlow API Routes
|--------------------------------------------------------------------------
|
| These routes are protected by API key authentication.
| Clients must send Authorization: Bearer <api-key> header.
|
*/

Route::prefix('api/stockflow')->middleware('stockflow.api')->group(function () {
    // Items
    Route::get('/items', [ItemController::class, 'index']);
    Route::get('/items/{item}', [ItemController::class, 'show']);
    Route::post('/items', [ItemController::class, 'store']);
    Route::put('/items/{item}', [ItemController::class, 'update']);
    Route::delete('/items/{item}', [ItemController::class, 'destroy']);

    // Sales
    Route::get('/sales', [SaleController::class, 'index']);
    Route::get('/sales/{sale}', [SaleController::class, 'show']);
    Route::post('/sales', [SaleController::class, 'store']);

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::post('/invoices', [InvoiceController::class, 'store']);

    // Quotations
    Route::get('/quotations', [QuotationController::class, 'index']);
    Route::get('/quotations/{quotation}', [QuotationController::class, 'show']);
    Route::post('/quotations', [QuotationController::class, 'store']);

    // Purchase Orders
    Route::get('/purchases', [PurchaseOrderController::class, 'index']);
    Route::get('/purchases/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
    Route::post('/purchases', [PurchaseOrderController::class, 'store']);
});
