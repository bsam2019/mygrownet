<?php

use App\Http\Controllers\Inventory\InventoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Standalone Inventory Routes
|--------------------------------------------------------------------------
|
| These routes are for the standalone Inventory module.
| For integrated inventory (via GrowBiz, BizBoost), see respective module routes.
|
*/

Route::middleware(['auth', 'verified'])->prefix('inventory')->name('inventory.')->group(function () {
    // Dashboard
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    
    // Settings
    Route::get('/settings', [InventoryController::class, 'settings'])->name('settings');
    
    // Items
    Route::get('/items', [InventoryController::class, 'items'])->name('items');
    Route::get('/items/create', [InventoryController::class, 'createItem'])->name('items.create');
    Route::post('/items', [InventoryController::class, 'storeItem'])->name('items.store');
    Route::get('/items/{item}', [InventoryController::class, 'showItem'])->name('items.show');
    Route::get('/items/{item}/edit', [InventoryController::class, 'editItem'])->name('items.edit');
    Route::put('/items/{item}', [InventoryController::class, 'updateItem'])->name('items.update');
    Route::delete('/items/{item}', [InventoryController::class, 'deleteItem'])->name('items.destroy');
    Route::post('/items/{item}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('items.adjust-stock');
    
    // Categories
    Route::get('/categories', [InventoryController::class, 'categories'])->name('categories');
    Route::post('/categories', [InventoryController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [InventoryController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [InventoryController::class, 'deleteCategory'])->name('categories.destroy');
    
    // Stock Movements
    Route::get('/movements', [InventoryController::class, 'movements'])->name('movements');
    
    // Alerts
    Route::get('/alerts', [InventoryController::class, 'alerts'])->name('alerts');
    Route::post('/alerts/{alert}/acknowledge', [InventoryController::class, 'acknowledgeAlert'])->name('alerts.acknowledge');
    
    // API endpoints (for POS and other modules)
    Route::get('/api/items', [InventoryController::class, 'apiItems'])->name('api.items');
    Route::get('/api/find', [InventoryController::class, 'findByCode'])->name('api.find');
});
