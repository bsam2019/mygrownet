<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LifePlus\HomeController;
use App\Http\Controllers\LifePlus\ExpenseController;
use App\Http\Controllers\LifePlus\BudgetController;
use App\Http\Controllers\LifePlus\TaskController;
use App\Http\Controllers\LifePlus\HabitController;
use App\Http\Controllers\LifePlus\NoteController;
use App\Http\Controllers\LifePlus\GigController;
use App\Http\Controllers\LifePlus\CommunityController;
use App\Http\Controllers\LifePlus\KnowledgeController;
use App\Http\Controllers\LifePlus\ProfileController;
use App\Http\Controllers\LifePlus\AnalyticsController;
use App\Http\Controllers\LifePlus\ChilimbaController;
use App\Http\Controllers\LifePlus\NotificationController;
use App\Http\Controllers\ModuleSubscriptionCheckoutController;

/*
|--------------------------------------------------------------------------
| LifePlus Routes
|--------------------------------------------------------------------------
|
| Routes for MyGrowNet Life+ - Daily Life Companion App
| Mobile-first PWA with offline support
|
*/

Route::middleware(['auth', 'verified', \App\Http\Middleware\InjectLifePlusAccess::class])
    ->prefix('lifeplus')
    ->name('lifeplus.')
    ->group(function () {
        
        // Home / Dashboard
        Route::get('/', [HomeController::class, 'index'])->name('home');
        
        // Onboarding
        Route::get('/onboarding', [HomeController::class, 'onboarding'])->name('onboarding');
        Route::post('/onboarding/complete', [HomeController::class, 'completeOnboarding'])->name('onboarding.complete');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

        // Money Management
        Route::prefix('money')->name('money.')->group(function () {
            Route::get('/', [ExpenseController::class, 'index'])->name('index');
            Route::get('/summary', [ExpenseController::class, 'summary'])->name('summary');
            Route::get('/categories', [ExpenseController::class, 'categories'])->name('categories');
            Route::post('/categories', [ExpenseController::class, 'storeCategory'])->name('categories.store');
            Route::post('/sync', [ExpenseController::class, 'sync'])->name('sync');
            
            // Expenses
            Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
            Route::put('/expenses/{id}', [ExpenseController::class, 'update'])->name('expenses.update');
            Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
            
            // Budgets
            Route::get('/budget', [BudgetController::class, 'index'])->name('budget');
            Route::post('/budget', [BudgetController::class, 'store'])->name('budget.store');
            Route::put('/budget/{id}', [BudgetController::class, 'update'])->name('budget.update');
            Route::delete('/budget/{id}', [BudgetController::class, 'destroy'])->name('budget.destroy');
            
            // Savings Goals
            Route::get('/savings', [BudgetController::class, 'savingsGoals'])->name('savings');
            Route::post('/savings', [BudgetController::class, 'storeSavingsGoal'])->name('savings.store');
            Route::put('/savings/{id}', [BudgetController::class, 'updateSavingsGoal'])->name('savings.update');
            Route::post('/savings/{id}/contribute', [BudgetController::class, 'contributeSavings'])->name('savings.contribute');
            Route::delete('/savings/{id}', [BudgetController::class, 'destroySavingsGoal'])->name('savings.destroy');
        });

        // Chilimba (Village Banking)
        Route::prefix('chilimba')->name('chilimba.')->group(function () {
            Route::get('/', [ChilimbaController::class, 'index'])->name('index');
            Route::post('/', [ChilimbaController::class, 'store'])->name('store');
            Route::get('/{id}', [ChilimbaController::class, 'show'])->name('show');
            Route::put('/{id}', [ChilimbaController::class, 'update'])->name('update');
            Route::delete('/{id}', [ChilimbaController::class, 'destroy'])->name('destroy');
            
            // Members
            Route::post('/{groupId}/members', [ChilimbaController::class, 'storeMembers'])->name('members.store');
            Route::put('/members/{memberId}', [ChilimbaController::class, 'updateMember'])->name('members.update');
            Route::delete('/members/{memberId}', [ChilimbaController::class, 'destroyMember'])->name('members.destroy');
            
            // Contributions
            Route::post('/{groupId}/contributions', [ChilimbaController::class, 'storeContribution'])->name('contributions.store');
            Route::post('/{groupId}/contributions/bulk', [ChilimbaController::class, 'storeBulkContributions'])->name('contributions.bulk');
            Route::put('/contributions/{id}', [ChilimbaController::class, 'updateContribution'])->name('contributions.update');
            Route::delete('/contributions/{id}', [ChilimbaController::class, 'destroyContribution'])->name('contributions.destroy');
            Route::get('/{groupId}/contributions/summary', [ChilimbaController::class, 'contributionSummary'])->name('contributions.summary');
            
            // Payouts
            Route::post('/{groupId}/payouts', [ChilimbaController::class, 'storePayout'])->name('payouts.store');
            
            // Loans
            Route::post('/{groupId}/loans', [ChilimbaController::class, 'storeLoan'])->name('loans.store');
            Route::post('/loans/{loanId}/approve', [ChilimbaController::class, 'approveLoan'])->name('loans.approve');
            Route::post('/loans/{loanId}/payments', [ChilimbaController::class, 'storeLoanPayment'])->name('loans.payment');
            
            // Special Contributions
            Route::get('/{groupId}/contribution-types', [ChilimbaController::class, 'getContributionTypes'])->name('types.index');
            Route::post('/{groupId}/contribution-types', [ChilimbaController::class, 'storeContributionType'])->name('types.store');
            Route::post('/{groupId}/special-contributions', [ChilimbaController::class, 'storeSpecialContribution'])->name('special.store');
            
            // Meetings
            Route::post('/{groupId}/meetings', [ChilimbaController::class, 'storeMeeting'])->name('meetings.store');
        });

        // Tasks & Productivity
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [TaskController::class, 'index'])->name('index');
            Route::get('/today', [TaskController::class, 'today'])->name('today');
            Route::post('/', [TaskController::class, 'store'])->name('store');
            Route::put('/{id}', [TaskController::class, 'update'])->name('update');
            Route::post('/{id}/toggle', [TaskController::class, 'toggle'])->name('toggle');
            Route::delete('/{id}', [TaskController::class, 'destroy'])->name('destroy');
            Route::post('/sync', [TaskController::class, 'sync'])->name('sync');
        });

        // Habits
        Route::prefix('habits')->name('habits.')->group(function () {
            Route::get('/', [HabitController::class, 'index'])->name('index');
            Route::get('/week', [HabitController::class, 'weekProgress'])->name('week');
            Route::post('/', [HabitController::class, 'store'])->name('store');
            Route::put('/{id}', [HabitController::class, 'update'])->name('update');
            Route::post('/{id}/log', [HabitController::class, 'log'])->name('log');
            Route::delete('/{id}', [HabitController::class, 'destroy'])->name('destroy');
        });

        // Notes
        Route::prefix('notes')->name('notes.')->group(function () {
            Route::get('/', [NoteController::class, 'index'])->name('index');
            Route::get('/{id}', [NoteController::class, 'show'])->name('show');
            Route::post('/', [NoteController::class, 'store'])->name('store');
            Route::put('/{id}', [NoteController::class, 'update'])->name('update');
            Route::post('/{id}/pin', [NoteController::class, 'togglePin'])->name('pin');
            Route::delete('/{id}', [NoteController::class, 'destroy'])->name('destroy');
            Route::post('/sync', [NoteController::class, 'sync'])->name('sync');
        });

        // Community
        Route::prefix('community')->name('community.')->group(function () {
            Route::get('/', [CommunityController::class, 'index'])->name('index');
            Route::get('/notices', [CommunityController::class, 'notices'])->name('notices');
            Route::get('/events', [CommunityController::class, 'events'])->name('events');
            Route::get('/lost-found', [CommunityController::class, 'lostFound'])->name('lost-found');
            Route::get('/posts/{id}', [CommunityController::class, 'show'])->name('posts.show');
            Route::post('/posts', [CommunityController::class, 'store'])->name('posts.store');
            Route::put('/posts/{id}', [CommunityController::class, 'update'])->name('posts.update');
            Route::delete('/posts/{id}', [CommunityController::class, 'destroy'])->name('posts.destroy');
        });

        // Gigs
        Route::prefix('gigs')->name('gigs.')->group(function () {
            Route::get('/', [GigController::class, 'index'])->name('index');
            Route::get('/create', [GigController::class, 'create'])->name('create');
            Route::get('/my-gigs', [GigController::class, 'myGigs'])->name('my-gigs');
            Route::get('/{id}', [GigController::class, 'show'])->name('show');
            Route::post('/', [GigController::class, 'store'])->name('store');
            Route::put('/{id}', [GigController::class, 'update'])->name('update');
            Route::delete('/{id}', [GigController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/apply', [GigController::class, 'apply'])->name('apply');
            Route::post('/{id}/assign', [GigController::class, 'assign'])->name('assign');
            Route::post('/{id}/complete', [GigController::class, 'complete'])->name('complete');
        });

        // Knowledge Center
        Route::prefix('knowledge')->name('knowledge.')->group(function () {
            Route::get('/', [KnowledgeController::class, 'index'])->name('index');
            Route::get('/daily-tip', [KnowledgeController::class, 'dailyTip'])->name('daily-tip');
            Route::get('/downloads', [KnowledgeController::class, 'downloads'])->name('downloads');
            Route::get('/{id}', [KnowledgeController::class, 'show'])->name('show');
            Route::post('/{id}/download', [KnowledgeController::class, 'download'])->name('download');
        });

        // Profile
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'index'])->name('index');
            Route::put('/', [ProfileController::class, 'update'])->name('update');
            Route::get('/skills', [ProfileController::class, 'skills'])->name('skills');
            Route::put('/skills', [ProfileController::class, 'updateSkills'])->name('skills.update');
            Route::get('/stats', [ProfileController::class, 'stats'])->name('stats');
            Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
            Route::get('/subscription', [ProfileController::class, 'subscription'])->name('subscription');
        });

        // Subscription Checkout
        Route::prefix('subscription')->name('subscription.')->group(function () {
            Route::post('/purchase', [ModuleSubscriptionCheckoutController::class, 'purchase'])->name('purchase');
            Route::post('/trial', [ModuleSubscriptionCheckoutController::class, 'startTrial'])->name('trial');
            Route::post('/upgrade', [ModuleSubscriptionCheckoutController::class, 'upgrade'])->name('upgrade');
            Route::post('/cancel', [ModuleSubscriptionCheckoutController::class, 'cancel'])->name('cancel');
        });

        // Analytics & Reports
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('/expenses', [AnalyticsController::class, 'expenses'])->name('expenses');
            Route::get('/tasks', [AnalyticsController::class, 'tasks'])->name('tasks');
            Route::get('/habits', [AnalyticsController::class, 'habits'])->name('habits');
        });

        // Data Export
        Route::prefix('export')->name('export.')->group(function () {
            Route::post('/all', [AnalyticsController::class, 'exportAll'])->name('all');
            Route::post('/expenses', [AnalyticsController::class, 'exportExpenses'])->name('expenses');
            Route::post('/tasks', [AnalyticsController::class, 'exportTasks'])->name('tasks');
            Route::post('/notes', [AnalyticsController::class, 'exportNotes'])->name('notes');
        });

        // Tasks Calendar View
        Route::get('/tasks/calendar', [TaskController::class, 'calendar'])->name('tasks.calendar');
    });
