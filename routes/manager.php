<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Manager\ManagerDashboardController;

Route::group([
    'middleware' => ['auth'], 
    'prefix' => 'manager', 
    'as' => 'manager.'
], function () {
    
    // Manager Dashboard
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
    
    // Team Management
    Route::prefix('team')->name('team.')->group(function () {
        Route::get('/', [ManagerDashboardController::class, 'teamOverview'])->name('overview');
        Route::get('/performance', [ManagerDashboardController::class, 'teamPerformance'])->name('performance');
        Route::get('/activities', [ManagerDashboardController::class, 'teamActivities'])->name('activities');
    });
    
    // Approval Management (limited scope for managers)
    Route::prefix('approvals')->name('approvals.')->group(function () {
        Route::get('/withdrawals', [ManagerDashboardController::class, 'pendingWithdrawals'])->name('withdrawals');
        Route::get('/tier-upgrades', [ManagerDashboardController::class, 'pendingTierUpgrades'])->name('tier-upgrades');
    });
    
    // Reports (team-specific)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/team-performance', [ManagerDashboardController::class, 'teamPerformanceReport'])->name('team-performance');
        Route::get('/commission-summary', [ManagerDashboardController::class, 'commissionSummaryReport'])->name('commission-summary');
    });
});