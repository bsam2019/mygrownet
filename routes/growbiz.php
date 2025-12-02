<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GrowBiz\DashboardController;
use App\Http\Controllers\GrowBiz\EmployeeController;
use App\Http\Controllers\GrowBiz\TaskController;
use App\Http\Controllers\GrowBiz\ReportsController;
use App\Http\Controllers\GrowBiz\InvitationController;
use App\Http\Controllers\GrowBiz\SetupController;
use App\Http\Controllers\GrowBiz\SettingsController;
use App\Http\Controllers\GrowBiz\NotificationController;
use App\Http\Controllers\GrowBiz\MessageController;

/*
|--------------------------------------------------------------------------
| GrowBiz Routes
|--------------------------------------------------------------------------
|
| Routes for GrowBiz - SME Task & Employee Management Tools
| Mobile-first PWA with standalone app support
|
*/

// Public invitation routes (no auth required for viewing)
Route::prefix('growbiz/invitation')->name('growbiz.invitation.')->group(function () {
    Route::get('/accept/{token}', [InvitationController::class, 'showAcceptPage'])->name('accept');
    Route::get('/code', [InvitationController::class, 'showCodeEntry'])->name('code');
});

// Public invitation submission routes (auth handled in controller)
Route::prefix('growbiz/invitation')->name('growbiz.invitation.')->group(function () {
    Route::post('/code', [InvitationController::class, 'acceptByCode'])->name('code.submit');
});

// Authenticated invitation routes
Route::middleware(['auth', 'verified'])
    ->prefix('growbiz/invitation')
    ->name('growbiz.invitation.')
    ->group(function () {
        Route::post('/accept/{token}', [InvitationController::class, 'acceptByToken'])->name('accept.submit');
        Route::get('/pending', [InvitationController::class, 'handlePendingInvitation'])->name('pending');
    });

Route::middleware(['auth', 'verified'])
    ->prefix('growbiz')
    ->name('growbiz.')
    ->group(function () {
    // Setup Wizard
    Route::get('/setup', [SetupController::class, 'index'])->name('setup');
    Route::post('/setup/business', [SetupController::class, 'setupAsBusiness'])->name('setup.business');
    
    // Dashboard (with setup check)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Employee Management
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/{id}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [EmployeeController::class, 'updateStatus'])->name('status');
        Route::get('/{id}/tasks', [EmployeeController::class, 'tasks'])->name('tasks');
        
        // Employee Invitations
        Route::post('/{id}/invite/email', [EmployeeController::class, 'sendInvitation'])->name('invite.email');
        Route::post('/{id}/invite/code', [EmployeeController::class, 'generateInvitationCode'])->name('invite.code');
        Route::get('/{id}/invitation', [EmployeeController::class, 'getInvitation'])->name('invitation');
        Route::delete('/{id}/invitation/{invitationId}', [EmployeeController::class, 'revokeInvitation'])->name('invitation.revoke');
    });

    // Task Management
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/create', [TaskController::class, 'create'])->name('create');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{id}', [TaskController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{id}', [TaskController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [TaskController::class, 'updateStatus'])->name('status');
        Route::patch('/{id}/progress', [TaskController::class, 'updateProgress'])->name('progress');
        Route::post('/{id}/time', [TaskController::class, 'logTime'])->name('time');
        Route::post('/{id}/notes', [TaskController::class, 'addNote'])->name('notes.store');
        Route::get('/{id}/updates', [TaskController::class, 'getUpdates'])->name('updates');
        Route::post('/{id}/comments', [TaskController::class, 'addComment'])->name('comments.store');
        Route::delete('/{id}/comments/{commentId}', [TaskController::class, 'deleteComment'])->name('comments.destroy');
    });

    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/analytics', [ReportsController::class, 'analytics'])->name('analytics');
        Route::get('/performance', [ReportsController::class, 'performance'])->name('performance');
        Route::get('/summaries', [ReportsController::class, 'summaries'])->name('summaries');
        
        // Summary API endpoints
        Route::get('/daily-summary', [ReportsController::class, 'dailySummary'])->name('daily-summary');
        Route::get('/weekly-summary', [ReportsController::class, 'weeklySummary'])->name('weekly-summary');
        
        // Export endpoints
        Route::get('/export/tasks', [ReportsController::class, 'exportTasks'])->name('export.tasks');
        Route::get('/export/employees', [ReportsController::class, 'exportEmployees'])->name('export.employees');
        Route::get('/export/weekly-summary', [ReportsController::class, 'exportWeeklySummary'])->name('export.weekly-summary');
        Route::get('/export/performance', [ReportsController::class, 'exportPerformance'])->name('export.performance');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::get('/security', [SettingsController::class, 'security'])->name('security');
        Route::put('/security/password', [SettingsController::class, 'updatePassword'])->name('security.password');
        Route::get('/business', [SettingsController::class, 'business'])->name('business');
        Route::post('/business', [SettingsController::class, 'updateBusiness'])->name('business.update');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    });

    // Messages
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/{id}', [MessageController::class, 'show'])->name('show');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::post('/{id}/reply', [MessageController::class, 'reply'])->name('reply');
    });
});
