<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MarketplaceAdminController;

/*
|--------------------------------------------------------------------------
| Marketplace Admin Routes
|--------------------------------------------------------------------------
|
| Admin panel for managing GrowNet Market
| - Seller approval
| - Product moderation
| - Order monitoring
| - Dispute resolution
|
*/

Route::middleware(['auth', 'verified', App\Http\Middleware\MarketplaceAdmin::class])
    ->prefix('admin/marketplace')
    ->name('admin.marketplace.')
    ->group(function () {
        
        // Dashboard
        Route::get('/', [MarketplaceAdminController::class, 'dashboard'])->name('dashboard');
        
        // Seller Management
        Route::get('/sellers', [MarketplaceAdminController::class, 'sellers'])->name('sellers.index');
        Route::get('/sellers/{id}', [MarketplaceAdminController::class, 'sellerShow'])->name('sellers.show');
        Route::post('/sellers/{id}/approve', [MarketplaceAdminController::class, 'approveSeller'])->name('sellers.approve');
        Route::post('/sellers/{id}/reject', [MarketplaceAdminController::class, 'rejectSeller'])->name('sellers.reject');
        Route::post('/sellers/{id}/suspend', [MarketplaceAdminController::class, 'suspendSeller'])->name('sellers.suspend');
        Route::post('/sellers/{id}/activate', [MarketplaceAdminController::class, 'activateSeller'])->name('sellers.activate');
        
        // Product Moderation
        Route::get('/products', [MarketplaceAdminController::class, 'products'])->name('products.index');
        Route::get('/products/{id}', [MarketplaceAdminController::class, 'productShow'])->name('products.show');
        Route::post('/products/{id}/approve', [MarketplaceAdminController::class, 'approveProduct'])->name('products.approve');
        Route::post('/products/{id}/reject', [MarketplaceAdminController::class, 'rejectProduct'])->name('products.reject');
        
        // Order Monitoring
        Route::get('/orders', [MarketplaceAdminController::class, 'orders'])->name('orders.index');
        Route::get('/orders/{id}', [MarketplaceAdminController::class, 'orderShow'])->name('orders.show');
        
        // Dispute Resolution
        Route::get('/disputes', [MarketplaceAdminController::class, 'disputes'])->name('disputes.index');
        Route::get('/disputes/{id}', [MarketplaceAdminController::class, 'disputeShow'])->name('disputes.show');
        Route::post('/disputes/{id}/resolve', [MarketplaceAdminController::class, 'resolveDispute'])->name('disputes.resolve');
        
        // Reviews Moderation
        Route::get('/reviews', [MarketplaceAdminController::class, 'reviews'])->name('reviews.index');
        Route::post('/reviews/{id}/approve', [MarketplaceAdminController::class, 'approveReview'])->name('reviews.approve');
        Route::post('/reviews/{id}/reject', [MarketplaceAdminController::class, 'rejectReview'])->name('reviews.reject');
        
        // Categories Management
        Route::get('/categories', [MarketplaceAdminController::class, 'categories'])->name('categories.index');
        Route::post('/categories', [MarketplaceAdminController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{id}', [MarketplaceAdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{id}', [MarketplaceAdminController::class, 'deleteCategory'])->name('categories.destroy');
        
        // Analytics
        Route::get('/analytics', [MarketplaceAdminController::class, 'analytics'])->name('analytics');
        
        // Settings
        Route::get('/settings', [MarketplaceAdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [MarketplaceAdminController::class, 'updateSettings'])->name('settings.update');
    });
