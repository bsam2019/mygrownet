<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Referral\ReferralController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Investment\InvestmentController;
use App\Http\Controllers\DashboardStatsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\InvestmentMetricsController;
use App\Http\Controllers\Admin\AdminDashboardController;
// InvestmentTierController removed - using Venture Builder
use App\Http\Controllers\Admin\AdminInvestmentController;
use App\Http\Controllers\Manager\ManagerDashboardController;
use App\Http\Controllers\EarningsProjectionController;
use App\Http\Controllers\ComplianceController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/features', fn() => Inertia::render('Features'))->name('features');
Route::get('/faq', fn() => Inertia::render('FAQ'))->name('faq');
Route::get('/policies', fn() => Inertia::render('Policies'))->name('policies');
Route::get('/privacy', fn() => Inertia::render('Privacy'))->name('privacy');
Route::get('/terms', fn() => Inertia::render('Terms'))->name('terms');
Route::get('/wallet/policy', fn() => Inertia::render('Wallet/Policy'))->name('wallet.policy');
Route::get('/loyalty-reward/policy', fn() => Inertia::render('LoyaltyReward/Policy'))->name('loyalty-reward.policy');
Route::get('/investment', [HomeController::class, 'investment'])->name('investment');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');

// Public Careers routes
Route::prefix('careers')->name('careers.')->group(function () {
    Route::get('/', [App\Http\Controllers\CareersController::class, 'index'])->name('index');
    Route::get('/{jobPosting}', [App\Http\Controllers\CareersController::class, 'show'])->name('show');
    Route::get('/{jobPosting}/apply', [App\Http\Controllers\CareersController::class, 'apply'])->name('apply');
    Route::post('/{jobPosting}/apply', [App\Http\Controllers\CareersController::class, 'storeApplication'])->name('store-application');
    Route::get('/application/success', [App\Http\Controllers\CareersController::class, 'success'])->name('success');
});

// Public Compliance Information
Route::get('/compliance', [ComplianceController::class, 'information'])->name('compliance.information');

// Public Membership Information
Route::get('/membership', function () {
    return Inertia::render('Membership/Index');
})->name('membership.index');

// Investor routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - Role-based routing
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Points System Routes
    Route::prefix('points')->name('points.')->group(function () {
        Route::get('/', [App\Http\Controllers\PointsController::class, 'index'])->name('index');
        Route::get('/transactions', [App\Http\Controllers\PointsController::class, 'transactions'])->name('transactions');
        Route::get('/level-progress', [App\Http\Controllers\PointsController::class, 'levelProgress'])->name('level-progress');
        Route::get('/qualification', [App\Http\Controllers\PointsController::class, 'qualificationStatus'])->name('qualification');
        Route::post('/daily-login', [App\Http\Controllers\PointsController::class, 'dailyLogin'])->name('daily-login');
        Route::get('/leaderboard', [App\Http\Controllers\PointsController::class, 'leaderboard'])->name('leaderboard');
        Route::get('/badges', [App\Http\Controllers\PointsController::class, 'badges'])->name('badges');
    });

    // Admin Payment Approval Routes
    Route::middleware(['admin'])->prefix('admin/payments')->name('admin.payments.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PaymentApprovalController::class, 'index'])->name('index');
        Route::post('/{id}/verify', [App\Http\Controllers\Admin\PaymentApprovalController::class, 'verify'])->name('verify');
        Route::post('/{id}/reject', [App\Http\Controllers\Admin\PaymentApprovalController::class, 'reject'])->name('reject');
        Route::post('/{id}/reset', [App\Http\Controllers\Admin\PaymentApprovalController::class, 'reset'])->name('reset');
    });

    // Admin Profit Sharing Routes
    Route::middleware(['admin'])->prefix('admin/profit-sharing')->name('admin.profit-sharing.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ProfitSharingController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\ProfitSharingController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\ProfitSharingController::class, 'store'])->name('store');
        Route::post('/{id}/approve', [App\Http\Controllers\Admin\ProfitSharingController::class, 'approve'])->name('approve');
        Route::post('/{id}/distribute', [App\Http\Controllers\Admin\ProfitSharingController::class, 'distribute'])->name('distribute');
    });

    // Member Profit Sharing Routes
    Route::middleware(['auth'])->prefix('mygrownet')->name('mygrownet.')->group(function () {
        Route::get('/profit-shares', [App\Http\Controllers\MyGrowNet\ProfitShareController::class, 'index'])->name('profit-shares');
    });

    // Admin Points Management Routes
    Route::middleware(['admin'])->prefix('admin/points')->name('admin.points.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminPointsController::class, 'index'])->name('index');
        Route::get('/users', [App\Http\Controllers\Admin\AdminPointsController::class, 'users'])->name('users');
        Route::get('/users/{user}', [App\Http\Controllers\Admin\AdminPointsController::class, 'show'])->name('show');
        Route::post('/users/{user}/award', [App\Http\Controllers\Admin\AdminPointsController::class, 'awardPoints'])->name('award');
        Route::post('/users/{user}/deduct', [App\Http\Controllers\Admin\AdminPointsController::class, 'deductPoints'])->name('deduct');
        Route::post('/users/{user}/set', [App\Http\Controllers\Admin\AdminPointsController::class, 'setPoints'])->name('set');
        Route::post('/users/{user}/advance-level', [App\Http\Controllers\Admin\AdminPointsController::class, 'advanceLevel'])->name('advance-level');
        Route::post('/users/{user}/reset-monthly', [App\Http\Controllers\Admin\AdminPointsController::class, 'resetMonthlyPoints'])->name('reset-monthly');
        Route::post('/bulk-award', [App\Http\Controllers\Admin\AdminPointsController::class, 'bulkAwardPoints'])->name('bulk-award');
        Route::get('/statistics', [App\Http\Controllers\Admin\AdminPointsController::class, 'statistics'])->name('statistics');
    });

    // Admin Impersonation Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::post('/impersonate/{user}', [App\Http\Controllers\Admin\ImpersonateController::class, 'impersonate'])->name('impersonate');
    });

    // Admin Starter Kit Management Routes
    Route::middleware(['admin'])->prefix('admin/starter-kit')->name('admin.starter-kit.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\StarterKitAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/purchases', [App\Http\Controllers\Admin\StarterKitAdminController::class, 'purchases'])->name('purchases');
        Route::put('/purchases/{purchase}/status', [App\Http\Controllers\Admin\StarterKitAdminController::class, 'updatePurchaseStatus'])->name('purchases.update-status');
        Route::get('/members', [App\Http\Controllers\Admin\StarterKitAdminController::class, 'members'])->name('members');
        Route::get('/analytics', [App\Http\Controllers\Admin\StarterKitAdminController::class, 'analytics'])->name('analytics');
        
        // Content Management
        Route::get('/content', [App\Http\Controllers\Admin\StarterKitContentController::class, 'index'])->name('content.index');
        Route::post('/content', [App\Http\Controllers\Admin\StarterKitContentController::class, 'store'])->name('content.store');
        Route::put('/content/{item}', [App\Http\Controllers\Admin\StarterKitContentController::class, 'update'])->name('content.update');
        Route::delete('/content/{item}', [App\Http\Controllers\Admin\StarterKitContentController::class, 'destroy'])->name('content.destroy');
        Route::post('/content/reorder', [App\Http\Controllers\Admin\StarterKitContentController::class, 'reorder'])->name('content.reorder');
    });
    
    // Admin Library Management Routes
    Route::middleware(['admin'])->prefix('admin/library')->name('admin.library.')->group(function () {
        Route::get('/resources', [App\Http\Controllers\Admin\LibraryResourceController::class, 'index'])->name('resources.index');
        Route::post('/resources', [App\Http\Controllers\Admin\LibraryResourceController::class, 'store'])->name('resources.store');
        Route::put('/resources/{resource}', [App\Http\Controllers\Admin\LibraryResourceController::class, 'update'])->name('resources.update');
        Route::delete('/resources/{resource}', [App\Http\Controllers\Admin\LibraryResourceController::class, 'destroy'])->name('resources.destroy');
    });
    
    // Admin LGR Management Routes
    Route::middleware(['admin'])->prefix('admin/lgr')->name('admin.lgr.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\LgrAdminController::class, 'index'])->name('index');
        Route::get('/cycles', [App\Http\Controllers\Admin\LgrAdminController::class, 'cycles'])->name('cycles');
        Route::get('/qualifications', [App\Http\Controllers\Admin\LgrAdminController::class, 'qualifications'])->name('qualifications');
        Route::get('/pool', [App\Http\Controllers\Admin\LgrAdminController::class, 'pool'])->name('pool');
        Route::get('/activities', [App\Http\Controllers\Admin\LgrAdminController::class, 'activities'])->name('activities');
        Route::get('/settings', [App\Http\Controllers\Admin\LgrAdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [App\Http\Controllers\Admin\LgrAdminController::class, 'updateSettings'])->name('settings.update');
        
        // Manual Award Routes
        Route::get('/awards', [App\Http\Controllers\Admin\LgrManualAwardController::class, 'index'])->name('awards.index');
        Route::get('/awards/create', [App\Http\Controllers\Admin\LgrManualAwardController::class, 'create'])->name('awards.create');
        Route::post('/awards', [App\Http\Controllers\Admin\LgrManualAwardController::class, 'store'])->name('awards.store');
        Route::get('/awards/{award}', [App\Http\Controllers\Admin\LgrManualAwardController::class, 'show'])->name('awards.show');
        
        // Settings Routes
        Route::get('/settings', [App\Http\Controllers\Admin\LgrSettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [App\Http\Controllers\Admin\LgrSettingsController::class, 'update'])->name('settings.update');
    });
    
    // Leave impersonation - no admin middleware needed (user is impersonated)
    Route::post('/admin/leave-impersonation', [App\Http\Controllers\Admin\ImpersonateController::class, 'leave'])->name('admin.leave-impersonation');
    Route::get('/investor/dashboard', [DashboardController::class, 'investorDashboard'])->name('investor.dashboard');
    Route::get('/dashboard/stats', [DashboardStatsController::class, 'index'])->name('dashboard.stats');
    
    // Dashboard API Endpoints (MyGrowNet)
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/metrics', [DashboardController::class, 'dashboardMetrics'])->name('metrics');
        Route::get('/notifications-activity', [DashboardController::class, 'notificationsAndActivity'])->name('notifications-activity');
        Route::get('/matrix-data', [DashboardController::class, 'matrixData'])->name('matrix-data');
    });

    // VBIF Investment routes removed - now using Venture Builder system

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::post('/transactions/withdraw', [TransactionController::class, 'withdraw'])->name('transactions.withdraw');

    // Referrals - Enhanced VBIF System
    // My Team Routes (formerly referrals)
    Route::prefix('my-team')->name('my-team.')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
        Route::get('/tree', [ReferralController::class, 'tree'])->name('tree');
        Route::get('/statistics', [ReferralController::class, 'statistics'])->name('statistics');
        Route::get('/commissions', [ReferralController::class, 'commissions'])->name('commissions');
        Route::get('/matrix-position', [ReferralController::class, 'matrixPosition'])->name('matrix-position');
        Route::get('/matrix-genealogy', [ReferralController::class, 'matrixGenealogy'])->name('matrix-genealogy');
        Route::get('/by-level', [ReferralController::class, 'referralsByLevel'])->name('by-level');
        Route::get('/performance-report', [ReferralController::class, 'performanceReport'])->name('performance-report');
        Route::post('/generate-code', [ReferralController::class, 'generateReferralCode'])->name('generate-code');
        Route::post('/validate-code', [ReferralController::class, 'validateReferralCode'])->name('validate-code');
        Route::post('/calculate-commission', [ReferralController::class, 'calculateCommission'])->name('calculate-commission');
        Route::post('/export', [ReferralController::class, 'export'])->name('export');
    });
    
    // Legacy referrals routes (redirect to my-team)
    Route::redirect('/referrals', '/my-team');
    Route::redirect('/referrals/{any}', '/my-team/{any}')->where('any', '.*');

    // Withdrawals
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::get('/withdrawals/{withdrawal}', [WithdrawalController::class, 'show'])->name('withdrawals.show');
    Route::patch('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::patch('/withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');

    // Reports
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    Route::get('/reports/investments', [DashboardController::class, 'investmentReports'])->name('reports.investments');
    Route::get('/reports/transactions', [DashboardController::class, 'transactionReports'])->name('reports.transactions');
    Route::get('/reports/referrals', [DashboardController::class, 'referralReports'])->name('reports.referrals');
    
    // Earnings Projection Calculator
    Route::prefix('earnings-projection')->name('earnings-projection.')->middleware('compliance.check')->group(function () {
        Route::get('/', [EarningsProjectionController::class, 'index'])->name('index');
        Route::post('/calculate', [EarningsProjectionController::class, 'calculate'])->name('calculate');
        Route::post('/scenarios', [EarningsProjectionController::class, 'scenarios'])->name('scenarios');
        Route::post('/breakdown', [EarningsProjectionController::class, 'breakdown'])->name('breakdown');
    });
    
    // Compliance and Legal System (Authenticated routes)
    Route::prefix('compliance')->name('compliance.')->group(function () {
        Route::get('/dashboard', [ComplianceController::class, 'index'])->name('dashboard');
        Route::get('/business-structure', [ComplianceController::class, 'businessStructure'])->name('business-structure');
        Route::get('/legal-disclaimers', [ComplianceController::class, 'legalDisclaimers'])->name('legal-disclaimers');
        Route::get('/sustainability-metrics', [ComplianceController::class, 'sustainabilityMetrics'])->name('sustainability-metrics');
        Route::get('/commission-cap-compliance', [ComplianceController::class, 'commissionCapCompliance'])->name('commission-cap-compliance');
        Route::post('/enforce-commission-caps', [ComplianceController::class, 'enforceCommissionCaps'])->name('enforce-commission-caps');
        Route::get('/regulatory-compliance', [ComplianceController::class, 'regulatoryCompliance'])->name('regulatory-compliance');
        Route::post('/validate-earnings', [ComplianceController::class, 'validateEarnings'])->name('validate-earnings');
        Route::get('/generate-report', [ComplianceController::class, 'generateReport'])->name('generate-report');
    });
    
    // OTP Verification Routes
    Route::prefix('otp')->name('otp.')->group(function () {
        Route::post('/generate', [App\Http\Controllers\OtpController::class, 'generate'])->name('generate');
        Route::post('/verify', [App\Http\Controllers\OtpController::class, 'verify'])->name('verify');
        Route::post('/resend', [App\Http\Controllers\OtpController::class, 'resend'])->name('resend');
        Route::get('/status', [App\Http\Controllers\OtpController::class, 'status'])->name('status');
        Route::get('/stats', [App\Http\Controllers\OtpController::class, 'stats'])->name('stats');
    });

    // Settings Routes
    Route::redirect('settings', '/settings/profile');
    Route::get('settings/profile', [\App\Http\Controllers\Settings\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [\App\Http\Controllers\Settings\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [\App\Http\Controllers\Settings\ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('settings/password', [\App\Http\Controllers\Settings\PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [\App\Http\Controllers\Settings\PasswordController::class, 'update'])->name('password.update');
    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    // Compensation Plan Route
    Route::get('/compensation-plan', [App\Http\Controllers\CompensationPlanController::class, 'show'])->name('compensation-plan.show');

    // Debug route to check member payments
    Route::get('/debug/check-member-status/{userId}', function($userId) {
        $user = \App\Models\User::with('memberPayments')->findOrFail($userId);
        
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'subscription_status' => $user->subscription_status,
            'subscription_end_date' => $user->subscription_end_date,
            'has_active_subscription' => $user->hasActiveSubscription(),
            'member_payments' => $user->memberPayments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_type' => $payment->payment_type,
                    'status' => $payment->status,
                    'payment_method' => $payment->payment_method,
                    'verified_at' => $payment->verified_at,
                    'created_at' => $payment->created_at,
                ];
            }),
            'verified_subscription_payments' => $user->memberPayments()
                ->where('status', 'verified')
                ->where('payment_type', 'subscription')
                ->get()
                ->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'status' => $payment->status,
                        'payment_type' => $payment->payment_type,
                    ];
                }),
        ]);
    })->middleware('auth');

    // Employee Management Routes
    Route::prefix('employees')->name('employees.')->middleware(['can:view-employees', \App\Http\Middleware\EmployeeOperationLogger::class])->group(function () {
        Route::get('/', [App\Http\Controllers\Employee\EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Employee\EmployeeController::class, 'create'])
            ->middleware('can:create-employees')->name('create');
        Route::post('/', [App\Http\Controllers\Employee\EmployeeController::class, 'store'])
            ->middleware('can:create-employees')->name('store');
        Route::get('/{employee}', [App\Http\Controllers\Employee\EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [App\Http\Controllers\Employee\EmployeeController::class, 'edit'])
            ->middleware('can:edit-employees')->name('edit');
        Route::put('/{employee}', [App\Http\Controllers\Employee\EmployeeController::class, 'update'])
            ->middleware('can:edit-employees')->name('update');
        Route::patch('/{employee}', [App\Http\Controllers\Employee\EmployeeController::class, 'update'])
            ->middleware('can:edit-employees');
        Route::delete('/{employee}', [App\Http\Controllers\Employee\EmployeeController::class, 'destroy'])
            ->middleware('can:delete-employees')->name('destroy');
    });
    
    // Department Management Routes
    Route::prefix('departments')->name('departments.')->middleware(['can:view-departments'])->group(function () {
        Route::get('/', [App\Http\Controllers\Employee\DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Employee\DepartmentController::class, 'create'])
            ->middleware('can:create-departments')->name('create');
        Route::post('/', [App\Http\Controllers\Employee\DepartmentController::class, 'store'])
            ->middleware('can:create-departments')->name('store');
        Route::get('/{department}', [App\Http\Controllers\Employee\DepartmentController::class, 'show'])->name('show');
        Route::get('/{department}/edit', [App\Http\Controllers\Employee\DepartmentController::class, 'edit'])
            ->middleware('can:edit-departments')->name('edit');
        Route::put('/{department}', [App\Http\Controllers\Employee\DepartmentController::class, 'update'])
            ->middleware('can:edit-departments')->name('update');
        Route::patch('/{department}', [App\Http\Controllers\Employee\DepartmentController::class, 'update'])
            ->middleware('can:edit-departments');
        Route::delete('/{department}', [App\Http\Controllers\Employee\DepartmentController::class, 'destroy'])
            ->middleware('can:delete-departments')->name('destroy');
        
        // Additional department routes
        Route::get('/hierarchy/tree', [App\Http\Controllers\Employee\DepartmentController::class, 'hierarchy'])->name('hierarchy');
        Route::post('/{department}/assign-head', [App\Http\Controllers\Employee\DepartmentController::class, 'assignHead'])
            ->middleware('can:manage-department-heads')->name('assign-head');
        Route::delete('/{department}/remove-head', [App\Http\Controllers\Employee\DepartmentController::class, 'removeHead'])
            ->middleware('can:manage-department-heads')->name('remove-head');
    });
    
    // Position Management Routes
    Route::prefix('positions')->name('positions.')->middleware(['can:view-positions'])->group(function () {
        Route::get('/', [App\Http\Controllers\Employee\PositionController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Employee\PositionController::class, 'create'])
            ->middleware('can:create-positions')->name('create');
        Route::post('/', [App\Http\Controllers\Employee\PositionController::class, 'store'])
            ->middleware('can:create-positions')->name('store');
        Route::get('/{position}', [App\Http\Controllers\Employee\PositionController::class, 'show'])->name('show');
        Route::get('/{position}/edit', [App\Http\Controllers\Employee\PositionController::class, 'edit'])
            ->middleware('can:edit-positions')->name('edit');
        Route::put('/{position}', [App\Http\Controllers\Employee\PositionController::class, 'update'])
            ->middleware('can:edit-positions')->name('update');
        Route::patch('/{position}', [App\Http\Controllers\Employee\PositionController::class, 'update'])
            ->middleware('can:edit-positions');
        Route::delete('/{position}', [App\Http\Controllers\Employee\PositionController::class, 'destroy'])
            ->middleware('can:delete-positions')->name('destroy');
        
        // Additional position routes
        Route::get('/department/{department}', [App\Http\Controllers\Employee\PositionController::class, 'byDepartment'])->name('by-department');
        Route::get('/salary-ranges/analysis', [App\Http\Controllers\Employee\PositionController::class, 'salaryRanges'])->name('salary-ranges');
        Route::get('/commission-eligible/list', [App\Http\Controllers\Employee\PositionController::class, 'commissionEligible'])->name('commission-eligible');
    });
    
    // Performance Management Routes
    Route::prefix('performance')->name('performance.')->middleware(['can:view-performance'])->group(function () {
        Route::get('/', [App\Http\Controllers\Employee\PerformanceController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Employee\PerformanceController::class, 'create'])
            ->middleware('can:create-performance-reviews')->name('create');
        Route::post('/', [App\Http\Controllers\Employee\PerformanceController::class, 'store'])
            ->middleware('can:create-performance-reviews')->name('store');
        Route::get('/{performance}', [App\Http\Controllers\Employee\PerformanceController::class, 'show'])->name('show');
        Route::get('/{performance}/edit', [App\Http\Controllers\Employee\PerformanceController::class, 'edit'])
            ->middleware('can:edit-performance-reviews')->name('edit');
        Route::put('/{performance}', [App\Http\Controllers\Employee\PerformanceController::class, 'update'])
            ->middleware('can:edit-performance-reviews')->name('update');
        Route::patch('/{performance}', [App\Http\Controllers\Employee\PerformanceController::class, 'update'])
            ->middleware('can:edit-performance-reviews');
        Route::delete('/{performance}', [App\Http\Controllers\Employee\PerformanceController::class, 'destroy'])
            ->middleware('can:delete-performance-reviews')->name('destroy');
        
        // Additional performance routes
        Route::get('/analytics/dashboard', [App\Http\Controllers\Employee\PerformanceController::class, 'analytics'])->name('analytics');
        Route::post('/goals/set', [App\Http\Controllers\Employee\PerformanceController::class, 'setGoals'])
            ->middleware('can:manage-performance-goals')->name('goals.set');
        Route::get('/goals/track/{employee}', [App\Http\Controllers\Employee\PerformanceController::class, 'trackGoals'])->name('goals.track');
    });
    
    // Commission Management Routes
    Route::prefix('commissions')->name('commissions.')->middleware(['can:view-commissions'])->group(function () {
        Route::get('/', [App\Http\Controllers\Employee\CommissionController::class, 'index'])->name('index');
        Route::post('/calculate', [App\Http\Controllers\Employee\CommissionController::class, 'calculate'])
            ->middleware('can:calculate-commissions')->name('calculate');
        Route::post('/', [App\Http\Controllers\Employee\CommissionController::class, 'store'])
            ->middleware('can:create-commissions')->name('store');
        Route::patch('/{commission}/approve', [App\Http\Controllers\Employee\CommissionController::class, 'approve'])
            ->middleware('can:approve-commissions')->name('approve');
        Route::patch('/{commission}/mark-paid', [App\Http\Controllers\Employee\CommissionController::class, 'markPaid'])
            ->middleware('can:process-commission-payments')->name('mark-paid');
        
        // Commission reporting routes
        Route::get('/reports/monthly', [App\Http\Controllers\Employee\CommissionController::class, 'monthlyReport'])
            ->middleware('can:view-commission-reports')->name('reports.monthly');
        Route::get('/reports/quarterly', [App\Http\Controllers\Employee\CommissionController::class, 'quarterlyReport'])
            ->middleware('can:view-commission-reports')->name('reports.quarterly');
        Route::get('/analytics', [App\Http\Controllers\Employee\CommissionController::class, 'analytics'])
            ->middleware('can:view-commission-analytics')->name('analytics');
    });

    // Note: investments.create and investments.store are already defined by Route::resource('investments') above
    
    // Receipts
    Route::prefix('receipts')->name('receipts.')->group(function () {
        Route::get('/', [App\Http\Controllers\ReceiptController::class, 'index'])->name('index');
        Route::get('/{receipt}/download', [App\Http\Controllers\ReceiptController::class, 'download'])->name('download');
        Route::get('/{receipt}/view', [App\Http\Controllers\ReceiptController::class, 'view'])->name('view');
    });
    
    // VBIF-specific routes
    // Matrix Management
    Route::get('/matrix', [App\Http\Controllers\MatrixController::class, 'index'])->name('matrix.index');
    Route::post('/matrix/generate-code', [App\Http\Controllers\MatrixController::class, 'generateReferralCode'])->name('matrix.generate-code');
    Route::get('/matrix/data', [App\Http\Controllers\MatrixController::class, 'getMatrixData'])->name('matrix.data');
    
    // Debug route
    Route::get('/matrix/debug', function() {
        $user = auth()->user();
        return response()->json([
            'user_id' => $user->id,
            'referral_code' => $user->referral_code,
            'matrix_structure' => $user->buildMatrixStructure(3),
            'downline_counts' => $user->getMatrixDownlineCount(),
            'referral_stats' => $user->getReferralStats(),
        ]);
    })->name('matrix.debug');
    
    // Tier Management
    Route::get('/tiers', [App\Http\Controllers\TierController::class, 'index'])->name('tiers.index');
    Route::get('/tiers/compare', [App\Http\Controllers\TierController::class, 'compare'])->name('tiers.compare');
    Route::get('/tiers/{tier}', [App\Http\Controllers\TierController::class, 'show'])->name('tiers.show');
    
    // MyGrowNet Dashboard Routes
    Route::prefix('mygrownet')->name('mygrownet.')->middleware(['auth'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'index'])->name('dashboard');
        
        // Member Membership Routes
        Route::get('/my-membership', [App\Http\Controllers\MyGrowNet\MembershipController::class, 'show'])->name('membership.show');
        Route::get('/professional-levels', [App\Http\Controllers\MyGrowNet\MembershipController::class, 'levels'])->name('levels.index');
        
        // Starter Kit Routes
        Route::get('/my-starter-kit', [App\Http\Controllers\MyGrowNet\StarterKitController::class, 'show'])->name('starter-kit.show');
        Route::get('/my-starter-kit/purchase', [App\Http\Controllers\MyGrowNet\StarterKitController::class, 'purchase'])->name('starter-kit.purchase');
        Route::post('/my-starter-kit/purchase', [App\Http\Controllers\MyGrowNet\StarterKitController::class, 'storePurchase'])->name('starter-kit.store');
        Route::get('/my-starter-kit/upgrade', [App\Http\Controllers\MyGrowNet\StarterKitController::class, 'showUpgrade'])->name('starter-kit.upgrade');
        Route::post('/my-starter-kit/upgrade', [App\Http\Controllers\MyGrowNet\StarterKitController::class, 'processUpgrade'])->name('starter-kit.upgrade.process');
        
        // Library Routes (requires starter kit)
        Route::get('/library', [App\Http\Controllers\MyGrowNet\LibraryController::class, 'index'])->name('library.index');
        Route::get('/library/{resource}', [App\Http\Controllers\MyGrowNet\LibraryController::class, 'show'])->name('library.show');
        Route::post('/library/{resource}/complete', [App\Http\Controllers\MyGrowNet\LibraryController::class, 'markCompleted'])->name('library.complete');
        
        // Notification Routes
        Route::get('/notifications', [App\Http\Controllers\MyGrowNet\NotificationController::class, 'center'])->name('notifications.center');
        Route::get('/notifications/count', [App\Http\Controllers\MyGrowNet\NotificationController::class, 'count'])->name('notifications.count');
        Route::get('/notifications/list', [App\Http\Controllers\MyGrowNet\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [App\Http\Controllers\MyGrowNet\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [App\Http\Controllers\MyGrowNet\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        
        // Loyalty Growth Reward (LGR) Routes
        Route::prefix('loyalty-reward')->name('loyalty-reward.')->group(function () {
            Route::get('/', [App\Http\Controllers\MyGrowNet\LoyaltyRewardController::class, 'index'])->name('index');
            Route::get('/qualification', [App\Http\Controllers\MyGrowNet\LoyaltyRewardController::class, 'qualification'])->name('qualification');
            Route::get('/activities', [App\Http\Controllers\MyGrowNet\LoyaltyRewardController::class, 'activities'])->name('activities');
            Route::post('/start-cycle', [App\Http\Controllers\MyGrowNet\LoyaltyRewardController::class, 'startCycle'])->name('start-cycle');
            Route::post('/record-activity', [App\Http\Controllers\MyGrowNet\LoyaltyRewardController::class, 'recordActivity'])->name('record-activity');
        });
        
        // Finance Routes
        Route::get('/wallet', [App\Http\Controllers\MyGrowNet\WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/accept-policy', [App\Http\Controllers\MyGrowNet\WalletController::class, 'acceptPolicy'])->name('wallet.accept-policy');
        Route::post('/wallet/check-withdrawal-limit', [App\Http\Controllers\MyGrowNet\WalletController::class, 'checkWithdrawalLimit'])->name('wallet.check-withdrawal-limit');
        Route::post('/wallet/lgr-transfer', function(Illuminate\Http\Request $request) {
            \Log::info('LGR Transfer Request', $request->all());
            
            $user = auth()->user();
            \Log::info('User LGR Balance', ['user_id' => $user->id, 'balance' => $user->loyalty_points]);
            
            $validated = $request->validate(['amount' => 'required|numeric|min:10']);
            $amount = $validated['amount'];
            
            \Log::info('Validated amount', ['amount' => $amount]);
            
            if ($user->loyalty_points < $amount) {
                \Log::warning('Insufficient balance', ['has' => $user->loyalty_points, 'needs' => $amount]);
                return back()->withErrors(['amount' => 'Insufficient LGR balance']);
            }

            DB::beginTransaction();
            try {
                $user->decrement('loyalty_points', $amount);
                $user->increment('wallet_balance', $amount);
                
                $reference = 'LGR-TRANSFER-' . strtoupper(uniqid());
                
                DB::table('transactions')->insert([
                    'user_id' => $user->id,
                    'transaction_type' => 'lgr_transfer',
                    'amount' => $amount,
                    'reference_number' => $reference,
                    'description' => "Transfer from LGR to Wallet",
                    'status' => 'completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                DB::commit();
                \Log::info('LGR Transfer Success', ['amount' => $amount, 'reference' => $reference]);
                
                return redirect()->route('mygrownet.wallet.index')
                    ->with('success', "Successfully transferred K{$amount} to your wallet");
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('LGR Transfer Failed', ['error' => $e->getMessage()]);
                return back()->withErrors(['error' => 'Transfer failed: ' . $e->getMessage()]);
            }
        })->name('wallet.lgr-transfer');
        
        // Earnings Hub - Central page for all earnings
        Route::get('/my-earnings', [App\Http\Controllers\MyGrowNet\EarningsController::class, 'hub'])->name('earnings.hub');
        Route::get('/earnings', [App\Http\Controllers\MyGrowNet\EarningsController::class, 'index'])->name('earnings.index');
        Route::get('/profit-sharing', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('profit-sharing'))->name('profit-sharing.index');
        
        // Member Payment Routes (subscriptions, workshops, products, coaching)
        Route::get('/payments', [App\Http\Controllers\MyGrowNet\MemberPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [App\Http\Controllers\MyGrowNet\MemberPaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [App\Http\Controllers\MyGrowNet\MemberPaymentController::class, 'store'])->name('payments.store');
        
        // Workshop Routes
        Route::get('/workshops', [App\Http\Controllers\MyGrowNet\WorkshopController::class, 'index'])->name('workshops.index');
        Route::get('/workshops/my-workshops', [App\Http\Controllers\MyGrowNet\WorkshopController::class, 'myWorkshops'])->name('workshops.my-workshops');
        Route::get('/workshops/{slug}', [App\Http\Controllers\MyGrowNet\WorkshopController::class, 'show'])->name('workshops.show');
        Route::post('/workshops/{id}/register', [App\Http\Controllers\MyGrowNet\WorkshopController::class, 'register'])->name('workshops.register');
        
        // Profit Sharing Routes
        Route::get('/profit-shares', [App\Http\Controllers\MyGrowNet\ProfitShareController::class, 'index'])->name('profit-shares');
        
        // Network & Analytics Routes
        Route::get('/network', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('network'))->name('network.index');
        Route::get('/network/analytics', [App\Http\Controllers\MyGrowNet\NetworkAnalyticsController::class, 'index'])->name('network.analytics');
        Route::get('/commissions', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('commissions'))->name('commissions.index');
        Route::get('/membership/upgrade', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('membership-upgrade'))->name('membership.upgrade');
        Route::get('/projects', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('projects'))->name('projects.index');
        Route::get('/assets', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('assets'))->name('assets.index');
        Route::get('/rewards', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('rewards'))->name('rewards.index');
        Route::get('/referrals', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('referrals'))->name('referrals.index');
        Route::get('/learning', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('learning'))->name('learning.index');
        
        // API Routes for Dashboard Data
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/dashboard-stats', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'getDashboardStats'])->name('dashboard-stats');
            Route::get('/network-data', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'getNetworkData'])->name('network-data');
            Route::get('/commission-summary', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'getCommissionSummary'])->name('commission-summary');
            Route::get('/team-volume-data', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'getTeamVolumeData'])->name('team-volume-data');
            Route::get('/five-level-commission-data', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'getFiveLevelCommissionData'])->name('five-level-commission-data');
            Route::get('/network-structure', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'getNetworkStructure'])->name('network-structure');
            Route::get('/asset-tracking-data', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'getAssetTrackingDataApi'])->name('asset-tracking-data');
            Route::get('/asset-performance/{allocation}', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'getAssetPerformanceAnalytics'])->name('asset-performance');
            Route::post('/asset-income/{allocation}', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'recordAssetIncome'])->name('record-asset-income');
            Route::patch('/asset-maintenance/{allocation}', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'updateAssetMaintenance'])->name('update-asset-maintenance');
            Route::get('/community-project-data', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'getCommunityProjectDataApi'])->name('community-project-data');
            Route::post('/project-contribute/{project}', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'contributeToProject'])->name('project-contribute');
            Route::post('/project-vote/{project}', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'castProjectVote'])->name('project-vote');
            Route::get('/project-analytics/{project}', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'getProjectAnalytics'])->name('project-analytics');
        });
    });

    // Employee Self-Service Routes
    Route::prefix('employee')->name('employee.')->middleware(['auth'])->group(function () {
        Route::get('/dashboard', function () {
            $user = auth()->user();
            $employeeModel = \App\Infrastructure\Persistence\Eloquent\EmployeeModel::class;
            $employee = $employeeModel::where('user_id', $user->id)->first();
            
            if (!$employee) {
                return redirect()->route('dashboard')->with('error', 'Employee profile not found.');
            }
            
            return redirect()->route('dashboard'); // Employee data is integrated into main dashboard
        })->name('dashboard');
        
        Route::get('/profile/edit', function () {
            return Inertia::render('Employee/SelfService/EditProfile');
        })->name('profile.edit');
        
        Route::get('/time-off/request', function () {
            return Inertia::render('Employee/SelfService/TimeOffRequest');
        })->name('time-off.request');
        
        Route::get('/expenses/create', function () {
            return Inertia::render('Employee/SelfService/ExpenseForm');
        })->name('expenses.create');
        
        Route::get('/documents', function () {
            return Inertia::render('Employee/SelfService/Documents');
        })->name('documents');
        
        Route::get('/feedback/create', function () {
            return Inertia::render('Employee/SelfService/FeedbackForm');
        })->name('feedback.create');
        
        Route::get('/activities', function () {
            return Inertia::render('Employee/SelfService/Activities');
        })->name('activities');
        
        Route::post('/goals', function () {
            // Handle goal creation
            return back()->with('success', 'Goal created successfully');
        })->name('goals.store');
    });

    // Employee Widget API Routes
    Route::prefix('api/employee')->name('api.employee.')->group(function () {
        Route::get('/{employee}/profile', [App\Http\Controllers\Employee\EmployeeController::class, 'getProfile'])->name('profile');
        Route::get('/{employee}/performance-summary', [App\Http\Controllers\Employee\PerformanceController::class, 'getPerformanceSummary'])->name('performance-summary');
        Route::get('/{employee}/client-portfolio', [App\Http\Controllers\Employee\CommissionController::class, 'getClientPortfolio'])->name('client-portfolio');
    });

    // Admin Employee Management API Routes
    Route::prefix('api/admin')->name('api.admin.')->middleware(['auth'])->group(function () {
        Route::get('/employee-management-summary', [App\Http\Controllers\Admin\AdminDashboardController::class, 'employeeManagementSummary'])->name('employee-management-summary');
        Route::get('/department-overview', [App\Http\Controllers\Admin\AdminDashboardController::class, 'departmentOverview'])->name('department-overview');
        Route::get('/performance-stats', [App\Http\Controllers\Admin\AdminDashboardController::class, 'performanceStats'])->name('performance-stats');
    });

    // Enhanced Employee Management Routes
    Route::prefix('admin/employees')->name('admin.employees.')->middleware(['auth', 'can:view-employees'])->group(function () {
        Route::get('/analytics', function () {
            return Inertia::render('Admin/Employees/Analytics');
        })->name('analytics');
        
        Route::get('/bulk', function () {
            return Inertia::render('Admin/Employees/BulkOperations');
        })->name('bulk');
        
        Route::post('/bulk/status', [App\Http\Controllers\Employee\BulkOperationsController::class, 'updateStatus'])
            ->middleware('can:edit-employees')->name('bulk.status');
        
        Route::post('/bulk/transfer', [App\Http\Controllers\Employee\BulkOperationsController::class, 'transferDepartment'])
            ->middleware('can:edit-employees')->name('bulk.transfer');
        
        Route::post('/bulk/salary', [App\Http\Controllers\Employee\BulkOperationsController::class, 'adjustSalary'])
            ->middleware('can:edit-employees')->name('bulk.salary');
        
        Route::get('/export', [App\Http\Controllers\Employee\EmployeeController::class, 'export'])
            ->name('export');
        
        Route::post('/search/save', [App\Http\Controllers\Employee\EmployeeController::class, 'saveSearch'])
            ->name('search.save');
    });

    // Real-time Notification Routes
    Route::prefix('api/notifications')->name('api.notifications.')->middleware(['auth'])->group(function () {
        Route::get('/employee', [App\Http\Controllers\Employee\NotificationController::class, 'getEmployeeNotifications'])
            ->name('employee');
        
        Route::post('/mark-read', [App\Http\Controllers\Employee\NotificationController::class, 'markAsRead'])
            ->name('mark-read');
        
        Route::get('/dashboard-alerts', [App\Http\Controllers\Admin\AdminDashboardController::class, 'getDashboardAlerts'])
            ->middleware('can:view-admin-dashboard')
            ->name('dashboard-alerts');
    });
});


// Employee Health Check Routes
Route::prefix('health')->name('health.')->group(function () {
    Route::get('/employee-management', [\App\Http\Controllers\EmployeeHealthController::class, 'health'])->name('employee');
    Route::get('/employee-management/detailed', [\App\Http\Controllers\EmployeeHealthController::class, 'healthDetailed'])->name('employee.detailed');
    Route::get('/employee-management/metrics', [\App\Http\Controllers\EmployeeHealthController::class, 'metrics'])->name('employee.metrics');
    Route::get('/employee-management/alerts', [\App\Http\Controllers\EmployeeHealthController::class, 'alerts'])->name('employee.alerts');
    Route::post('/employee-management/check', [\App\Http\Controllers\EmployeeHealthController::class, 'checkHealth'])->name('employee.check');
    Route::delete('/employee-management/data', [\App\Http\Controllers\EmployeeHealthController::class, 'clearData'])->name('employee.clear')->middleware('auth');
});

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/venture.php';
require __DIR__.'/bgf.php';

    // MyGrow Shop Routes
    Route::prefix('shop')->name('shop.')->group(function () {
        // Public routes (guests can browse)
        Route::get('/', [App\Http\Controllers\ShopController::class, 'index'])->name('index');
        Route::get('/category/{category:slug}', [App\Http\Controllers\ShopController::class, 'category'])->name('category');
        Route::get('/product/{product:slug}', [App\Http\Controllers\ShopController::class, 'show'])->name('product');
        
        // Protected routes (require authentication)
        Route::middleware(['auth'])->group(function () {
            // Cart Routes
            Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart');
            Route::post('/cart/add', [App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
            Route::patch('/cart/update', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
            Route::delete('/cart/remove', [App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
            Route::post('/checkout', [App\Http\Controllers\CartController::class, 'checkout'])->name('checkout');
            
            // Orders Routes
            Route::get('/orders', [App\Http\Controllers\CartController::class, 'orders'])->name('orders');
            Route::get('/orders/{order}', [App\Http\Controllers\CartController::class, 'showOrder'])->name('orders.show');
        });
    });

    // Starter Kit Routes
    Route::prefix('starter-kit')->name('starter-kit.')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\StarterKitController::class, 'index'])->name('index');
        Route::get('/purchase', [App\Http\Controllers\StarterKitController::class, 'purchase'])->name('purchase');
        Route::post('/purchase', [App\Http\Controllers\StarterKitController::class, 'store'])->name('store');
        Route::get('/dashboard', [App\Http\Controllers\StarterKitController::class, 'dashboard'])->name('dashboard');
        Route::get('/library', [App\Http\Controllers\StarterKitController::class, 'library'])->name('library');
        Route::post('/track-access', [App\Http\Controllers\StarterKitController::class, 'trackAccess'])->name('track-access');
        Route::post('/update-progress', [App\Http\Controllers\StarterKitController::class, 'updateProgress'])->name('update-progress');
    });
