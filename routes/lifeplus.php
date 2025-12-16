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

/*
|--------------------------------------------------------------------------
| LifePlus Routes
|--------------------------------------------------------------------------
|
| Routes for MyGrowNet Life+ - Daily Life Companion App
| Mobile-first PWA with offline support
|
*/

Route::middleware(['auth', 'verified'])
    ->prefix('lifeplus')
    ->name('lifeplus.')
    ->group(function () {
        
        // Home / Dashboard
        Route::get('/', [HomeController::class, 'index'])->name('home');
        
        // Onboarding
        Route::get('/onboarding', [HomeController::class, 'onboarding'])->name('onboarding');
        Route::post('/onboarding/complete', [HomeController::class, 'completeOnboarding'])->name('onboarding.complete');

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
