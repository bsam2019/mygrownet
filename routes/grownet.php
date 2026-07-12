<?php

use App\Http\Controllers\GrowNet\GuestController;
use App\Http\Controllers\MyGrowNet\DashboardController;
use App\Http\Controllers\MyGrowNet\MembershipController;
use App\Http\Controllers\MyGrowNet\StarterKitController;
use App\Http\Controllers\MyGrowNet\GiftController;
use App\Http\Controllers\MyGrowNet\LibraryController;
use App\Http\Controllers\MyGrowNet\ToolsController;
use App\Http\Controllers\MyGrowNet\BusinessPlanController;
use App\Http\Controllers\MyGrowNet\NotificationController;
use App\Http\Controllers\MyGrowNet\MessageController;
use App\Http\Controllers\MyGrowNet\SupportTicketController;
use App\Http\Controllers\MyGrowNet\LoyaltyRewardController;
use App\Http\Controllers\MyGrowNet\LgrPackageController;
use App\Http\Controllers\MyGrowNet\AnalyticsController;
use App\Http\Controllers\MyGrowNet\WalletController;
use App\Http\Controllers\MyGrowNet\LgrTransferController;
use App\Http\Controllers\MyGrowNet\LoanApplicationController;
use App\Http\Controllers\MyGrowNet\EarningsController;
use App\Http\Controllers\MyGrowNet\MemberPaymentController;
use App\Http\Controllers\MyGrowNet\WorkshopController;
use App\Http\Controllers\MyGrowNet\ProfitShareController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Helper register all authenticated GrowNet subdomain routes ──
$registerGrowNetSubdomainRoutes = function (string $prefix, string $namePrefix) {
    Route::prefix($prefix)->name($namePrefix)->middleware(['auth'])->group(function () {

        Route::get('/profit-shares', [ProfitShareController::class, 'index'])->name('profit-shares');

        Route::get('/my-membership', [MembershipController::class, 'show'])->name('membership.show');
        Route::get('/professional-levels', [MembershipController::class, 'levels'])->name('levels.index');

        Route::get('/my-starter-kit', [StarterKitController::class, 'show'])->name('starter-kit.show');
        Route::get('/my-starter-kit/purchase', [StarterKitController::class, 'purchase'])->name('starter-kit.purchase');
        Route::post('/my-starter-kit/purchase', [StarterKitController::class, 'storePurchase'])->name('starter-kit.store');
        Route::get('/my-starter-kit/upgrade', [StarterKitController::class, 'showUpgrade'])->name('starter-kit.upgrade');
        Route::post('/my-starter-kit/upgrade', [StarterKitController::class, 'processUpgrade'])->name('starter-kit.upgrade.process');

        Route::post('/gifts/starter-kit', [GiftController::class, 'giftStarterKit'])->name('gifts.starter-kit');
        Route::get('/gifts/limits', [GiftController::class, 'getLimits'])->name('gifts.limits');
        Route::get('/gifts/history', [GiftController::class, 'getHistory'])->name('gifts.history');
        Route::get('/network/level/{level}/members', [GiftController::class, 'getLevelMembers'])->name('network.level.members');

        Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
        Route::get('/library/{resource}', [LibraryController::class, 'show'])->name('library.show');
        Route::post('/library/{resource}/complete', [LibraryController::class, 'markCompleted'])->name('library.complete');

        Route::get('/benefits', fn() => inertia('GrowNet/Benefits'))->name('benefits.index');

        Route::prefix('content')->name('content.')->group(function () {
            Route::get('/', [\App\Http\Controllers\MyGrowNet\StarterKitContentController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\MyGrowNet\StarterKitContentController::class, 'show'])->name('show');
            Route::get('/{id}/download', [\App\Http\Controllers\MyGrowNet\StarterKitContentController::class, 'download'])->name('download');
            Route::get('/{id}/stream', [\App\Http\Controllers\MyGrowNet\StarterKitContentController::class, 'stream'])->name('stream');
        });

        Route::prefix('tools')->name('tools.')->group(function () {
            Route::get('/', [ToolsController::class, 'index'])->name('index');
            Route::get('/commission-calculator', [ToolsController::class, 'commissionCalculator'])->name('commission-calculator');
            Route::get('/goal-tracker', [ToolsController::class, 'goalTracker'])->name('goal-tracker');
            Route::post('/goals', [ToolsController::class, 'storeGoal'])->name('goals.store');
            Route::patch('/goals/{goalId}', [ToolsController::class, 'updateGoalProgress'])->name('goals.update');
            Route::get('/network-visualizer', [ToolsController::class, 'networkVisualizer'])->name('network-visualizer');
        });

        Route::get('/business-plan-generator', [BusinessPlanController::class, 'index'])->name('business-plan-generator');
        Route::post('/business-plan/save', [BusinessPlanController::class, 'save'])->name('business-plan.save');
        Route::post('/business-plan/complete', [BusinessPlanController::class, 'complete'])->name('business-plan.complete');
        Route::post('/business-plan/generate-ai', [BusinessPlanController::class, 'generateAI'])->name('business-plan.generate-ai');
        Route::get('/business-plan/export', [BusinessPlanController::class, 'export'])->name('business-plan.export');
        Route::get('/business-plan/download/{exportId}', [BusinessPlanController::class, 'download'])->name('business-plan.download');
        Route::get('/business-plans', [BusinessPlanController::class, 'list'])->name('business-plans.list');
        Route::get('/business-plans/api', [BusinessPlanController::class, 'apiList'])->name('business-plans.api');
        Route::get('/business-plan/{planId}', [BusinessPlanController::class, 'view'])->name('business-plan.view');
        Route::delete('/business-plan/{planId}', [BusinessPlanController::class, 'delete'])->name('business-plan.delete');
        Route::get('/roi-calculator', [ToolsController::class, 'roiCalculator'])->name('roi-calculator');

        Route::get('/notifications', [NotificationController::class, 'center'])->name('notifications.center');
        Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
        Route::get('/notifications/list', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{id}', [MessageController::class, 'show'])->name('messages.show');
        Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
        Route::post('/messages/{id}/read', [MessageController::class, 'markAsRead'])->name('messages.read');

        Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
        Route::get('/support/create', [SupportTicketController::class, 'create'])->name('support.create');
        Route::post('/support', [SupportTicketController::class, 'store'])->name('support.store');
        Route::get('/support/{id}', [SupportTicketController::class, 'show'])->name('support.show');
        Route::post('/support/{id}/comment', [SupportTicketController::class, 'addComment'])->name('support.comment');
        Route::get('/api/support/tickets/{id}/comments', [SupportTicketController::class, 'getComments'])->name('support.comments');
        Route::post('/support/quick-chat', [SupportTicketController::class, 'quickChat'])->name('support.quick-chat');
        Route::post('/support/{id}/chat', [SupportTicketController::class, 'chat'])->name('support.chat');
        Route::get('/api/support/tickets', [SupportTicketController::class, 'listJson'])->name('support.list-json');
        Route::get('/api/support/tickets/{id}', [SupportTicketController::class, 'showJson'])->name('support.show-json');
        Route::post('/support/{id}/rate', [SupportTicketController::class, 'rate'])->name('support.rate');

        Route::prefix('loyalty-rewards')->name('loyalty-rewards.')->group(function () {
            Route::get('/', [LoyaltyRewardController::class, 'index'])->name('index');
            Route::get('/qualification', [LoyaltyRewardController::class, 'qualification'])->name('qualification');
            Route::get('/activities', [LoyaltyRewardController::class, 'activities'])->name('activities');
            Route::post('/start-cycle', [LoyaltyRewardController::class, 'startCycle'])->name('start-cycle');
            Route::post('/record-activity', [LoyaltyRewardController::class, 'recordActivity'])->name('record-activity');
        });

        Route::prefix('lgr')->name('lgr.')->group(function () {
            Route::get('/packages', [LgrPackageController::class, 'packages'])->name('packages');
            Route::get('/packages/{package}', [LgrPackageController::class, 'show'])->name('packages.show');
            Route::post('/packages/{package}/purchase', [LgrPackageController::class, 'purchase'])->name('packages.purchase');
        });

        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('/performance', [AnalyticsController::class, 'performance'])->name('performance');
            Route::get('/recommendations', [AnalyticsController::class, 'recommendations'])->name('recommendations');
            Route::post('/recommendations/{id}/dismiss', [AnalyticsController::class, 'dismissRecommendation'])->name('recommendations.dismiss');
            Route::get('/predictions', [AnalyticsController::class, 'predictions'])->name('predictions');
            Route::get('/growth-potential', [AnalyticsController::class, 'growthPotential'])->name('growth-potential');
            Route::get('/churn-risk', [AnalyticsController::class, 'churnRisk'])->name('churn-risk');
        });

        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/accept-policy', [WalletController::class, 'acceptPolicy'])->name('wallet.accept-policy');
        Route::post('/wallet/check-withdrawal-limit', [WalletController::class, 'checkWithdrawalLimit'])->name('wallet.check-withdrawal-limit');
        Route::post('/wallet/lgr-transfer', [LgrTransferController::class, 'store'])->name('wallet.lgr-transfer');

        Route::get('/loans', [LoanApplicationController::class, 'index'])->name('loans.index');
        Route::post('/loans/apply', [LoanApplicationController::class, 'store'])->name('loans.apply');

        Route::get('/my-earnings', [EarningsController::class, 'hub'])->name('earnings.hub');
        Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings.index');
        Route::get('/profit-sharing', fn() => app(\App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('profit-sharing'))->name('profit-sharing.index');

        Route::get('/payments', [MemberPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [MemberPaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [MemberPaymentController::class, 'store'])->name('payments.store');

        Route::get('/workshops', [WorkshopController::class, 'index'])->name('workshops.index');
        Route::get('/workshops/my-workshops', [WorkshopController::class, 'myWorkshops'])->name('workshops.my-workshops');
        Route::get('/workshops/{workshop}', [WorkshopController::class, 'show'])->name('workshops.show');
        Route::post('/workshops/{workshop}/register', [WorkshopController::class, 'register'])->name('workshops.register');
        Route::get('/workshops/{workshop}/certificate', [WorkshopController::class, 'certificate'])->name('workshops.certificate');
    });
};

// ============================================================
// SUBDOMAIN ROUTES (grownet.mygrownet.com/)
// ============================================================
Route::domain('grownet.mygrownet.com')->group(function () use ($registerGrowNetSubdomainRoutes) {

    // Public welcome page at root
    Route::get('/', function () {
        return Inertia::render('GrowNet/Welcome');
    })->name('grownet.sub.welcome');

    // GrowNet SPA dashboard
    Route::get('/dashboard', [DashboardController::class, 'mobileIndex'])
        ->middleware(['auth'])
        ->name('grownet.sub.dashboard');

    // Authenticated routes (served at root, no prefix)
    $registerGrowNetSubdomainRoutes('', 'grownet.sub.');

    // Guest-only auth routes
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', [GuestController::class, 'login'])->name('grownet.sub.login');
        Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
        Route::get('/register', [GuestController::class, 'register'])->name('grownet.sub.register');
        Route::post('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);

        Route::get('/forgot-password', [GuestController::class, 'forgotPassword'])->name('grownet.sub.password.request');
        Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->name('grownet.sub.password.email');
        Route::get('/reset-password/{token}', [GuestController::class, 'resetPassword'])->name('grownet.sub.password.reset');
        Route::post('/reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->name('grownet.sub.password.update');

        Route::get('/auth/google', [\App\Http\Controllers\Auth\SocialiteController::class, 'redirectToGoogle'])->name('grownet.sub.auth.google');
        Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\SocialiteController::class, 'handleGoogleCallback'])->name('grownet.sub.auth.google.callback');
    });
});
