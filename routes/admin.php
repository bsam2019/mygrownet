<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\LoanManagementController;
use App\Http\Controllers\Admin\AdminInvestmentController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\Api\InvestmentMetricsController;
use App\Http\Controllers\Admin\WithdrawalApprovalController;
use App\Http\Controllers\Admin\InvestmentCategoriesController;

Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Receipt Management
    Route::prefix('receipts')->name('receipts.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReceiptController::class, 'index'])->name('index');
        Route::get('/{receipt}', [\App\Http\Controllers\Admin\ReceiptController::class, 'show'])->name('show');
        Route::get('/{receipt}/download', [\App\Http\Controllers\Admin\ReceiptController::class, 'download'])->name('download');
        Route::post('/{receipt}/resend', [\App\Http\Controllers\Admin\ReceiptController::class, 'resend'])->name('resend');
        Route::post('/bulk-download', [\App\Http\Controllers\Admin\ReceiptController::class, 'bulkDownload'])->name('bulk-download');
    });

    // Investment Metrics
    Route::get('/investments/metrics', [InvestmentMetricsController::class, 'show'])->name('investments.metrics');
    Route::get('/investments/metrics/{period}', [InvestmentMetricsController::class, 'getMetrics'])->name('investments.metrics.period');
    Route::get('/api/metrics', [InvestmentMetricsController::class, 'getMetrics'])->name('api.metrics');

    // Investment management
    Route::get('/investments', [AdminInvestmentController::class, 'index'])->name('investments.index');
    Route::get('/investments/{investment}', [AdminInvestmentController::class, 'show'])->name('investments.show');
    Route::patch('/investments/{investment}/approve', [AdminInvestmentController::class, 'approve'])->name('investments.approve');
    Route::patch('/investments/{investment}/reject', [AdminInvestmentController::class, 'reject'])->name('investments.reject');

    // Bulk Investment Actions
    Route::post('investments/bulk/approve', [AdminInvestmentController::class, 'bulkApprove'])->name('investments.bulk.approve');
    Route::post('investments/bulk/reject', [AdminInvestmentController::class, 'bulkReject'])->name('investments.bulk.reject');
    Route::get('investments/bulk/export', [AdminInvestmentController::class, 'bulkExport'])->name('investments.bulk.export');

    // Investment reports
    Route::get('investments/reports', [AdminInvestmentController::class, 'reports'])->name('investments.reports');

    // Investment categories
    Route::resource('categories', InvestmentCategoriesController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    // VBIF Investment tiers removed - now using Venture Builder system

    // Withdrawal management
    Route::get('/withdrawals', [WithdrawalApprovalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/pending', [WithdrawalApprovalController::class, 'pending'])->name('withdrawals.pending');
    Route::patch('/withdrawals/{withdrawal}/approve', [WithdrawalApprovalController::class, 'approve'])->name('withdrawals.approve');
    Route::patch('/withdrawals/{withdrawal}/reject', [WithdrawalApprovalController::class, 'reject'])->name('withdrawals.reject');

    // User management
    Route::patch('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/update-password', [UserManagementController::class, 'updatePassword'])->name('users.update-password');
    Route::post('users/{user}/lgr-restrictions', [UserManagementController::class, 'updateLgrRestrictions'])->name('users.lgr-restrictions');
    Route::post('users/{user}/loan-limit', [UserManagementController::class, 'updateLoanLimit'])->name('users.loan-limit');
    Route::resource('users', UserManagementController::class)->names('users');
    
    // Loan management
    Route::get('loans', [LoanManagementController::class, 'index'])->name('loans.index');
    Route::post('loans/{user}/issue', [LoanManagementController::class, 'issueLoan'])->name('loans.issue');
    Route::get('loans/{user}/summary', [LoanManagementController::class, 'getLoanSummary'])->name('loans.summary');
    Route::get('loans/members-with-loans', [LoanManagementController::class, 'getMembersWithLoans'])->name('loans.members');
    Route::post('loans/applications/{application}/approve', [LoanManagementController::class, 'approveApplication'])->name('loans.applications.approve');
    Route::post('loans/applications/{application}/reject', [LoanManagementController::class, 'rejectApplication'])->name('loans.applications.reject');

    // User profile management
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar.upload');

    // Reports
    Route::get('/reports', [FinancialReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/investments', [FinancialReportController::class, 'investments'])->name('reports.investments');
    Route::get('/reports/withdrawals', [FinancialReportController::class, 'withdrawals'])->name('reports.withdrawals');
    Route::get('/reports/generate', [FinancialReportController::class, 'generateReport'])->name('reports.generate');

    // Role and Permission management (Legacy - keeping for backward compatibility)
    Route::resource('roles', RoleController::class)->names('roles');
    Route::resource('permissions', PermissionController::class)->names('permissions');
    
    // New Role & Permission Management
    Route::prefix('role-management')->name('role-management.')->group(function () {
        // Roles
        Route::get('/roles', [\App\Http\Controllers\Admin\RolePermissionController::class, 'rolesIndex'])->name('roles.index');
        Route::post('/roles', [\App\Http\Controllers\Admin\RolePermissionController::class, 'createRole'])->name('roles.create');
        Route::get('/roles/{role}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'rolesShow'])->name('roles.show');
        Route::put('/roles/{role}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/{role}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'deleteRole'])->name('roles.delete');
        Route::post('/roles/{role}/permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'updateRolePermissions'])->name('roles.permissions.update');
        
        // Permissions
        Route::get('/permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'permissionsIndex'])->name('permissions.index');
        Route::post('/permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'createPermission'])->name('permissions.create');
        Route::put('/permissions/{permission}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'updatePermission'])->name('permissions.update');
        Route::delete('/permissions/{permission}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'deletePermission'])->name('permissions.delete');
        
        // User Role Assignment
        Route::get('/users', [\App\Http\Controllers\Admin\RolePermissionController::class, 'usersRoles'])->name('users.index');
        Route::post('/users/{user}/assign-role', [\App\Http\Controllers\Admin\RolePermissionController::class, 'assignRole'])->name('users.assign-role');
        Route::post('/users/{user}/remove-role', [\App\Http\Controllers\Admin\RolePermissionController::class, 'removeRole'])->name('users.remove-role');
    });
    
    // Analytics Routes
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/points', [\App\Http\Controllers\Admin\AnalyticsController::class, 'points'])->name('points');
        Route::get('/matrix', [\App\Http\Controllers\Admin\AnalyticsController::class, 'matrix'])->name('matrix');
        Route::get('/members', [\App\Http\Controllers\Admin\AnalyticsController::class, 'members'])->name('members');
        Route::get('/financial', [\App\Http\Controllers\Admin\AnalyticsController::class, 'financial'])->name('financial');
        Route::get('/system', [\App\Http\Controllers\Admin\AnalyticsController::class, 'system'])->name('system');
    });

    // Transaction management
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::patch('/transactions/{transaction}/update-status', [TransactionController::class, 'updateStatus'])->name('transactions.update-status');

    // Activity log
    Route::get('/activity', [ActivityLogController::class, 'index'])->name('activity.index');
    Route::post('/activity/filter', [ActivityLogController::class, 'filter'])->name('activity.filter');

    // Workshop Management
    Route::prefix('workshops')->name('workshops.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\WorkshopController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\WorkshopController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\WorkshopController::class, 'store'])->name('store');
        Route::get('/registrations', [\App\Http\Controllers\Admin\WorkshopController::class, 'registrations'])->name('registrations');
        Route::get('/{workshop}/edit', [\App\Http\Controllers\Admin\WorkshopController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{workshop}', [\App\Http\Controllers\Admin\WorkshopController::class, 'update'])->name('update');
        Route::patch('/{workshop}/status', [\App\Http\Controllers\Admin\WorkshopController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{workshop}', [\App\Http\Controllers\Admin\WorkshopController::class, 'destroy'])->name('destroy');
    });

    // Starter Kit Management
    Route::get('/starter-kits', [\App\Http\Controllers\Admin\StarterKitController::class, 'index'])->name('starter-kits.index');

    // Reward System Management
    
    // Matrix Management
    Route::prefix('matrix')->name('matrix.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MatrixController::class, 'index'])->name('index');
        Route::get('/{user}', [\App\Http\Controllers\Admin\MatrixController::class, 'show'])->name('show');
        Route::post('/reassign/{user}', [\App\Http\Controllers\Admin\MatrixController::class, 'reassignPosition'])->name('reassign');
        Route::post('/process-spillover', [\App\Http\Controllers\Admin\MatrixController::class, 'processSpillover'])->name('process-spillover');
        Route::get('/analytics', [\App\Http\Controllers\Admin\MatrixController::class, 'matrixAnalytics'])->name('analytics');
    });

    // Profit Distribution Management
    Route::prefix('profit-distribution')->name('profit-distribution.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ProfitDistributionController::class, 'index'])->name('index');
        Route::get('/{profitDistribution}', [\App\Http\Controllers\Admin\ProfitDistributionController::class, 'show'])->name('show');
        Route::post('/process-annual', [\App\Http\Controllers\Admin\ProfitDistributionController::class, 'processAnnualDistribution'])->name('process-annual');
        Route::post('/process-quarterly', [\App\Http\Controllers\Admin\ProfitDistributionController::class, 'processQuarterlyBonus'])->name('process-quarterly');
        Route::post('/preview', [\App\Http\Controllers\Admin\ProfitDistributionController::class, 'previewDistribution'])->name('preview');
        Route::post('/bulk-approve', [\App\Http\Controllers\Admin\ProfitDistributionController::class, 'bulkApprove'])->name('bulk-approve');
        Route::get('/analytics', [\App\Http\Controllers\Admin\ProfitDistributionController::class, 'analytics'])->name('analytics');
        Route::get('/export', [\App\Http\Controllers\Admin\ProfitDistributionController::class, 'exportDistributions'])->name('export');
    });

    // Reward System Analytics
    Route::prefix('reward-analytics')->name('reward-analytics.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RewardAnalyticsController::class, 'index'])->name('index');
        Route::get('/referrals', [\App\Http\Controllers\Admin\RewardAnalyticsController::class, 'referralAnalytics'])->name('referrals');
        Route::get('/matrix', [\App\Http\Controllers\Admin\RewardAnalyticsController::class, 'matrixAnalytics'])->name('matrix');
        Route::get('/profits', [\App\Http\Controllers\Admin\RewardAnalyticsController::class, 'profitAnalytics'])->name('profits');
        Route::get('/tiers', [\App\Http\Controllers\Admin\RewardAnalyticsController::class, 'tierAnalytics'])->name('tiers');
        Route::get('/export', [\App\Http\Controllers\Admin\RewardAnalyticsController::class, 'exportAnalytics'])->name('export');
    });

    // Enhanced Referral Management (existing route enhanced)
    Route::prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReferralController::class, 'index'])->name('index');
        Route::get('/analytics', [\App\Http\Controllers\Admin\ReferralController::class, 'analytics'])->name('analytics');
        Route::post('/process-pending', [\App\Http\Controllers\Admin\ReferralController::class, 'processPendingCommissions'])->name('process-pending');
        Route::get('/export', [\App\Http\Controllers\Admin\ReferralController::class, 'exportReferrals'])->name('export');
    });

    // Employee Management (admin-prefixed aliases to existing modules)
    // Add a test route first to verify the group works
    Route::get('/test-employees', function() {
        return response()->json(['message' => 'Admin employee test route works', 'route_name' => 'admin.test-employees']);
    })->name('test-employees');

    // Employee Management Routes
    Route::prefix('employees')->name('employees.')->middleware(['permission:view-employees'])->group(function () {
        Route::get('/', [App\Http\Controllers\Employee\EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Employee\EmployeeController::class, 'create'])
            ->middleware('permission:create-employees')->name('create');
        Route::post('/', [App\Http\Controllers\Employee\EmployeeController::class, 'store'])
            ->middleware('permission:create-employees')->name('store');
        Route::get('/{employee}', [App\Http\Controllers\Employee\EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [App\Http\Controllers\Employee\EmployeeController::class, 'edit'])
            ->middleware('permission:edit-employees')->name('edit');
        Route::put('/{employee}', [App\Http\Controllers\Employee\EmployeeController::class, 'update'])
            ->middleware('permission:edit-employees')->name('update');
        Route::patch('/{employee}', [App\Http\Controllers\Employee\EmployeeController::class, 'update'])
            ->middleware('permission:edit-employees');
        Route::delete('/{employee}', [App\Http\Controllers\Employee\EmployeeController::class, 'destroy'])
            ->middleware('permission:delete-employees')->name('destroy');
        
        // Employee invitation routes
        Route::post('/{employee}/invite', [App\Http\Controllers\Employee\EmployeeController::class, 'inviteToPortal'])
            ->middleware('permission:invite-employees')->name('invite');
    });

    // MLM Administration Routes
    Route::prefix('mlm')->name('mlm.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\MLMAdministrationController::class, 'index'])->name('dashboard');
        Route::get('/commissions', [App\Http\Controllers\Admin\MLMAdministrationController::class, 'commissions'])->name('commissions');
        Route::get('/network-analysis', [App\Http\Controllers\Admin\MLMAdministrationController::class, 'networkAnalysis'])->name('network-analysis');
        Route::get('/performance-monitoring', [App\Http\Controllers\Admin\MLMAdministrationController::class, 'performanceMonitoring'])->name('performance-monitoring');
        
        // AJAX endpoints
        Route::post('/adjust-commission', [App\Http\Controllers\Admin\MLMAdministrationController::class, 'adjustCommission'])->name('adjust-commission');
        Route::post('/process-pending-commissions', [App\Http\Controllers\Admin\MLMAdministrationController::class, 'processPendingCommissions'])->name('process-pending-commissions');
        Route::post('/recalculate-network', [App\Http\Controllers\Admin\MLMAdministrationController::class, 'recalculateNetwork'])->name('recalculate-network');
        Route::get('/commission-details/{commission}', [App\Http\Controllers\Admin\MLMAdministrationController::class, 'getCommissionDetails'])->name('commission-details');
        Route::get('/dashboard-data', [App\Http\Controllers\Admin\MLMAdministrationController::class, 'getDashboardData'])->name('dashboard-data');
        
        // Export endpoints
        Route::get('/export-commissions', [App\Http\Controllers\Admin\MLMAdministrationController::class, 'exportCommissions'])->name('export-commissions');
        Route::get('/export-network-analysis', [App\Http\Controllers\Admin\MLMAdministrationController::class, 'exportNetworkAnalysis'])->name('export-network-analysis');
    });

    // Asset Management Administration Routes
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\AssetManagementController::class, 'index'])->name('dashboard');
        Route::get('/inventory', [App\Http\Controllers\Admin\AssetManagementController::class, 'inventory'])->name('inventory');
        Route::get('/allocations', [App\Http\Controllers\Admin\AssetManagementController::class, 'allocations'])->name('allocations');
        Route::get('/maintenance', [App\Http\Controllers\Admin\AssetManagementController::class, 'maintenance'])->name('maintenance');
        
        // Asset management endpoints
        Route::post('/create-asset', [App\Http\Controllers\Admin\AssetManagementController::class, 'createAsset'])->name('create-asset');
        Route::put('/update-asset/{asset}', [App\Http\Controllers\Admin\AssetManagementController::class, 'updateAsset'])->name('update-asset');
        Route::post('/allocate-asset', [App\Http\Controllers\Admin\AssetManagementController::class, 'allocateAsset'])->name('allocate-asset');
        Route::post('/transfer-ownership/{allocation}', [App\Http\Controllers\Admin\AssetManagementController::class, 'transferOwnership'])->name('transfer-ownership');
        Route::post('/handle-violation/{allocation}', [App\Http\Controllers\Admin\AssetManagementController::class, 'handleViolation'])->name('handle-violation');
        Route::post('/bulk-update-assets', [App\Http\Controllers\Admin\AssetManagementController::class, 'bulkUpdateAssets'])->name('bulk-update-assets');
        
        // Data endpoints
        Route::get('/asset-details/{asset}', [App\Http\Controllers\Admin\AssetManagementController::class, 'getAssetDetails'])->name('asset-details');
        Route::get('/allocation-details/{allocation}', [App\Http\Controllers\Admin\AssetManagementController::class, 'getAllocationDetails'])->name('allocation-details');
        Route::get('/dashboard-data', [App\Http\Controllers\Admin\AssetManagementController::class, 'getDashboardData'])->name('dashboard-data');
        Route::post('/check-user-eligibility', [App\Http\Controllers\Admin\AssetManagementController::class, 'checkUserEligibility'])->name('check-user-eligibility');
        Route::post('/generate-performance-report', [App\Http\Controllers\Admin\AssetManagementController::class, 'generatePerformanceReport'])->name('generate-performance-report');
        
        // Export endpoints
        Route::get('/export-inventory', [App\Http\Controllers\Admin\AssetManagementController::class, 'exportInventory'])->name('export-inventory');
        Route::get('/export-allocations', [App\Http\Controllers\Admin\AssetManagementController::class, 'exportAllocations'])->name('export-allocations');
        Route::get('/export-maintenance-report', [App\Http\Controllers\Admin\AssetManagementController::class, 'exportMaintenanceReport'])->name('export-maintenance-report');
    });

    // Financial Reporting and Analytics Routes
    Route::prefix('financial')->name('financial.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\FinancialReportingController::class, 'index'])->name('dashboard');
        Route::get('/reports', [App\Http\Controllers\Admin\FinancialReportingController::class, 'reports'])->name('reports');
        Route::get('/sustainability', [App\Http\Controllers\Admin\FinancialReportingController::class, 'sustainability'])->name('sustainability');
        Route::get('/compliance', [App\Http\Controllers\Admin\FinancialReportingController::class, 'compliance'])->name('compliance');
        
        // Report generation endpoints
        Route::post('/generate-custom-report', [App\Http\Controllers\Admin\FinancialReportingController::class, 'generateCustomReport'])->name('generate-custom-report');
        Route::post('/generate-compliance-report', [App\Http\Controllers\Admin\FinancialReportingController::class, 'generateComplianceReport'])->name('generate-compliance-report');
        Route::post('/schedule-report', [App\Http\Controllers\Admin\FinancialReportingController::class, 'scheduleReport'])->name('schedule-report');
        
        // Data endpoints
        Route::get('/commission-cap-data', [App\Http\Controllers\Admin\FinancialReportingController::class, 'getCommissionCapData'])->name('commission-cap-data');
        Route::post('/update-commission-cap', [App\Http\Controllers\Admin\FinancialReportingController::class, 'updateCommissionCap'])->name('update-commission-cap');
        Route::post('/get-projections', [App\Http\Controllers\Admin\FinancialReportingController::class, 'getProjections'])->name('get-projections');
        Route::get('/real-time-metrics', [App\Http\Controllers\Admin\FinancialReportingController::class, 'getRealTimeMetrics'])->name('real-time-metrics');
        Route::get('/financial-health-score', [App\Http\Controllers\Admin\FinancialReportingController::class, 'getFinancialHealthScore'])->name('financial-health-score');
        Route::get('/revenue-breakdown', [App\Http\Controllers\Admin\FinancialReportingController::class, 'getRevenueBreakdown'])->name('revenue-breakdown');
        Route::get('/commission-distribution', [App\Http\Controllers\Admin\FinancialReportingController::class, 'getCommissionDistribution'])->name('commission-distribution');
        Route::get('/cost-analysis', [App\Http\Controllers\Admin\FinancialReportingController::class, 'getCostAnalysis'])->name('cost-analysis');
        
        // Export endpoints
        Route::get('/export-report', [App\Http\Controllers\Admin\FinancialReportingController::class, 'exportReport'])->name('export-report');
    });

    // Job Applications Management Routes
    Route::prefix('applications')->name('applications.')->middleware(['permission:review-applications'])->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ApplicationsController::class, 'index'])->name('index');
        Route::get('/{application}', [App\Http\Controllers\Admin\ApplicationsController::class, 'show'])->name('show');
        Route::get('/{application}/resume', [App\Http\Controllers\Admin\ApplicationsController::class, 'downloadResume'])->name('resume');
        Route::patch('/{application}/status', [App\Http\Controllers\Admin\ApplicationsController::class, 'updateStatus'])->name('update-status');
        Route::post('/{application}/hire', [App\Http\Controllers\Admin\ApplicationsController::class, 'hire'])
            ->middleware('permission:hire-applicants')->name('hire');
        Route::post('/bulk-update', [App\Http\Controllers\Admin\ApplicationsController::class, 'bulkUpdate'])->name('bulk-update');
    });

    // Job Postings Management Routes (Commented out - Controller not implemented)
    /*
    Route::prefix('job-postings')->name('job-postings.')->middleware(['permission:manage-job-postings'])->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\JobPostingsController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\JobPostingsController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\JobPostingsController::class, 'store'])->name('store');
        Route::get('/{jobPosting}', [App\Http\Controllers\Admin\JobPostingsController::class, 'show'])->name('show');
        Route::get('/{jobPosting}/edit', [App\Http\Controllers\Admin\JobPostingsController::class, 'edit'])->name('edit');
        Route::put('/{jobPosting}', [App\Http\Controllers\Admin\JobPostingsController::class, 'update'])->name('update');
        Route::delete('/{jobPosting}', [App\Http\Controllers\Admin\JobPostingsController::class, 'destroy'])->name('destroy');
        Route::patch('/{jobPosting}/toggle-status', [App\Http\Controllers\Admin\JobPostingsController::class, 'toggleStatus'])->name('toggle-status');
    });
    */

    Route::get('/employees', [\App\Http\Controllers\Employee\EmployeeController::class, 'index'])
        ->name('employees.index');
    Route::get('/employees/create', [\App\Http\Controllers\Employee\EmployeeController::class, 'create'])
        ->name('employees.create');
    Route::post('/employees', [\App\Http\Controllers\Employee\EmployeeController::class, 'store'])
        ->name('employees.store');
    Route::get('/employees/{employee}', [\App\Http\Controllers\Employee\EmployeeController::class, 'show'])
        ->name('employees.show');
    Route::get('/employees/{employee}/edit', [\App\Http\Controllers\Employee\EmployeeController::class, 'edit'])
        ->name('employees.edit');
    Route::put('/employees/{employee}', [\App\Http\Controllers\Employee\EmployeeController::class, 'update'])
        ->name('employees.update');

    Route::get('/departments', [\App\Http\Controllers\Employee\DepartmentController::class, 'index'])
        ->name('departments.index');
    Route::get('/departments/create', [\App\Http\Controllers\Employee\DepartmentController::class, 'create'])
        ->name('departments.create');
    Route::post('/departments', [\App\Http\Controllers\Employee\DepartmentController::class, 'store'])
        ->name('departments.store');
    Route::get('/departments/{department}', [\App\Http\Controllers\Employee\DepartmentController::class, 'show'])
        ->name('departments.show');
    Route::get('/departments/{department}/edit', [\App\Http\Controllers\Employee\DepartmentController::class, 'edit'])
        ->name('departments.edit');
    Route::put('/departments/{department}', [\App\Http\Controllers\Employee\DepartmentController::class, 'update'])
        ->name('departments.update');

    Route::get('/positions', [\App\Http\Controllers\Employee\PositionController::class, 'index'])
        ->name('positions.index');
    Route::get('/positions/create', [\App\Http\Controllers\Employee\PositionController::class, 'create'])
        ->name('positions.create');
    Route::post('/positions', [\App\Http\Controllers\Employee\PositionController::class, 'store'])
        ->name('positions.store');
    Route::get('/positions/{position}', [\App\Http\Controllers\Employee\PositionController::class, 'show'])
        ->name('positions.show');
    Route::get('/positions/{position}/edit', [\App\Http\Controllers\Employee\PositionController::class, 'edit'])
        ->name('positions.edit');
    Route::put('/positions/{position}', [\App\Http\Controllers\Employee\PositionController::class, 'update'])
        ->name('positions.update');

    Route::get('/performance', [\App\Http\Controllers\Employee\PerformanceController::class, 'index'])
        ->name('performance.index');

    Route::get('/commissions', [\App\Http\Controllers\Employee\CommissionController::class, 'index'])
        ->name('commissions.index');

    // Points System Settings (LP & BP)
    Route::prefix('settings/bonus-points')->name('settings.bp.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BonusPointSettingsController::class, 'index'])->name('index');
        
        // Life Points (LP)
        Route::post('/update-lp-activity', [\App\Http\Controllers\Admin\BonusPointSettingsController::class, 'updateLPActivity'])->name('update-lp-activity');
        Route::post('/toggle-lp-activity', [\App\Http\Controllers\Admin\BonusPointSettingsController::class, 'toggleLPActivity'])->name('toggle-lp-activity');
        
        // Bonus Points (BP)
        Route::post('/update-activity', [\App\Http\Controllers\Admin\BonusPointSettingsController::class, 'updateActivity'])->name('update-activity');
        Route::post('/toggle-activity', [\App\Http\Controllers\Admin\BonusPointSettingsController::class, 'toggleActivity'])->name('toggle-activity');
        Route::post('/update-rate', [\App\Http\Controllers\Admin\BonusPointSettingsController::class, 'updateRate'])->name('update-rate');
        
        // Level Requirements
        Route::post('/update-level-requirement', [\App\Http\Controllers\Admin\BonusPointSettingsController::class, 'updateLevelRequirement'])->name('update-level-requirement');
    });

    // Package Management
    Route::prefix('packages')->name('packages.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PackageController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\PackageController::class, 'store'])->name('store');
        Route::put('/{package}', [\App\Http\Controllers\Admin\PackageController::class, 'update'])->name('update');
        Route::delete('/{package}', [\App\Http\Controllers\Admin\PackageController::class, 'destroy'])->name('destroy');
        Route::patch('/{package}/toggle-status', [\App\Http\Controllers\Admin\PackageController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Subscription Management
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('index');
        Route::post('/create', [\App\Http\Controllers\Admin\SubscriptionController::class, 'createSubscription'])->name('create');
        Route::get('/{user}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'show'])->name('show');
        Route::post('/{user}/update-status', [\App\Http\Controllers\Admin\SubscriptionController::class, 'updateStatus'])->name('update-status');
        Route::post('/{user}/extend', [\App\Http\Controllers\Admin\SubscriptionController::class, 'extendSubscription'])->name('extend');
        Route::post('/{user}/force-upgrade', [\App\Http\Controllers\Admin\SubscriptionController::class, 'forceUpgrade'])->name('force-upgrade');
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\SubscriptionController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/export', [\App\Http\Controllers\Admin\SubscriptionController::class, 'export'])->name('export');
    });

    // Organizational Structure Management
    Route::prefix('organization')->name('organization.')->group(function () {
        // Organizational Chart
        Route::get('/', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'index'])->name('index');
        Route::get('/positions/{position}', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'positionDetails'])->name('positions.show');
        
        // KPI Management
        Route::get('/kpis', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'kpis'])->name('kpis.index');
        Route::post('/kpis', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'storeKpi'])->name('kpis.store');
        Route::patch('/kpis/{kpi}', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'updateKpi'])->name('kpis.update');
        
        // Employee KPI Tracking
        Route::get('/employees/{employee}/kpis', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'employeeKpis'])->name('employees.kpis');
        Route::post('/employees/{employee}/kpis', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'recordEmployeeKpi'])->name('employees.kpis.record');
        
        // Hiring Roadmap
        Route::get('/hiring-roadmap', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'hiringRoadmap'])->name('hiring.index');
        Route::post('/hiring-roadmap', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'storeHiringRoadmap'])->name('hiring.store');
        Route::patch('/hiring-roadmap/{roadmap}', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'updateHiringRoadmap'])->name('hiring.update');
        
        // Position Responsibilities
        Route::post('/positions/{position}/responsibilities', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'storeResponsibility'])->name('responsibilities.store');
        Route::patch('/responsibilities/{responsibility}', [\App\Http\Controllers\Admin\OrganizationalStructureController::class, 'updateResponsibility'])->name('responsibilities.update');
    });

});
