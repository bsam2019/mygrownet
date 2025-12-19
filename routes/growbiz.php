<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GrowBiz\DashboardController;
use App\Http\Controllers\GrowBiz\EmployeeController;
use App\Http\Controllers\GrowBiz\TaskController;
use App\Http\Controllers\GrowBiz\PersonalTodoController;
use App\Http\Controllers\GrowBiz\InventoryController;
use App\Http\Controllers\GrowBiz\AppointmentController;
use App\Http\Controllers\GrowBiz\ReportsController;
use App\Http\Controllers\GrowBiz\InvitationController;
use App\Http\Controllers\GrowBiz\SetupController;
use App\Http\Controllers\GrowBiz\SettingsController;
use App\Http\Controllers\GrowBiz\NotificationController;
use App\Http\Controllers\GrowBiz\MessageController;
use App\Http\Controllers\GrowBiz\SubscriptionController;
use App\Http\Controllers\GrowBiz\ProjectController;
use App\Http\Controllers\GrowBiz\POSController;

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
    
    // Dashboard (use /dashboard path to avoid conflict with public welcome page at /growbiz)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    // Project Management (Kanban & Gantt)
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{id}', [ProjectController::class, 'show'])->name('show');
        Route::put('/{id}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('destroy');
        
        // Kanban Board
        Route::get('/{id}/kanban', [ProjectController::class, 'kanban'])->name('kanban');
        Route::post('/{id}/kanban/move', [ProjectController::class, 'moveTask'])->name('kanban.move');
        
        // Gantt Chart
        Route::get('/{id}/gantt', [ProjectController::class, 'gantt'])->name('gantt');
        
        // Columns
        Route::post('/{id}/columns', [ProjectController::class, 'storeColumn'])->name('columns.store');
        Route::put('/{id}/columns/{columnId}', [ProjectController::class, 'updateColumn'])->name('columns.update');
        Route::delete('/{id}/columns/{columnId}', [ProjectController::class, 'destroyColumn'])->name('columns.destroy');
        Route::post('/{id}/columns/reorder', [ProjectController::class, 'reorderColumns'])->name('columns.reorder');
        
        // Milestones
        Route::post('/{id}/milestones', [ProjectController::class, 'storeMilestone'])->name('milestones.store');
        Route::put('/{id}/milestones/{milestoneId}', [ProjectController::class, 'updateMilestone'])->name('milestones.update');
        Route::delete('/{id}/milestones/{milestoneId}', [ProjectController::class, 'destroyMilestone'])->name('milestones.destroy');
        
        // Dependencies
        Route::post('/{id}/dependencies', [ProjectController::class, 'addDependency'])->name('dependencies.add');
        Route::delete('/{id}/dependencies', [ProjectController::class, 'removeDependency'])->name('dependencies.remove');
        
        // Statistics
        Route::get('/{id}/statistics', [ProjectController::class, 'statistics'])->name('statistics');
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

    // Personal To-Do List
    Route::prefix('todos')->name('todos.')->group(function () {
        Route::get('/', [PersonalTodoController::class, 'index'])->name('index');
        Route::get('/today', [PersonalTodoController::class, 'today'])->name('today');
        Route::get('/upcoming', [PersonalTodoController::class, 'upcoming'])->name('upcoming');
        Route::get('/completed', [PersonalTodoController::class, 'completed'])->name('completed');
        Route::post('/', [PersonalTodoController::class, 'store'])->name('store');
        Route::put('/{id}', [PersonalTodoController::class, 'update'])->name('update');
        Route::post('/{id}/toggle', [PersonalTodoController::class, 'toggle'])->name('toggle');
        Route::delete('/{id}', [PersonalTodoController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [PersonalTodoController::class, 'reorder'])->name('reorder');
        Route::get('/stats', [PersonalTodoController::class, 'stats'])->name('stats');
    });

    // Inventory Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
        Route::get('/search', [InventoryController::class, 'search'])->name('search');
        Route::get('/stats', [InventoryController::class, 'stats'])->name('stats');
        Route::get('/movements', [InventoryController::class, 'recentMovements'])->name('movements');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/{id}', [InventoryController::class, 'show'])->name('show');
        Route::put('/{id}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [InventoryController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/adjust', [InventoryController::class, 'adjustStock'])->name('adjust');
        Route::get('/{id}/movements', [InventoryController::class, 'movements'])->name('item.movements');
        
        // Categories
        Route::get('/categories/list', [InventoryController::class, 'categories'])->name('categories');
        Route::post('/categories', [InventoryController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{id}', [InventoryController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{id}', [InventoryController::class, 'destroyCategory'])->name('categories.destroy');
    });

    // Appointment Booking System
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('calendar');
        Route::get('/calendar/events', [AppointmentController::class, 'calendarEvents'])->name('calendar.events');
        Route::get('/today', [AppointmentController::class, 'today'])->name('today');
        Route::get('/upcoming', [AppointmentController::class, 'upcoming'])->name('upcoming');
        Route::get('/available-slots', [AppointmentController::class, 'availableSlots'])->name('available-slots');
        Route::get('/statistics', [AppointmentController::class, 'statistics'])->name('statistics');
        Route::post('/', [AppointmentController::class, 'store'])->name('store');
        Route::get('/{id}', [AppointmentController::class, 'show'])->name('show');
        Route::put('/{id}', [AppointmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [AppointmentController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [AppointmentController::class, 'updateStatus'])->name('status');
        Route::post('/{id}/reschedule', [AppointmentController::class, 'reschedule'])->name('reschedule');

        // Services
        Route::get('/manage/services', [AppointmentController::class, 'services'])->name('services');
        Route::post('/manage/services', [AppointmentController::class, 'storeService'])->name('services.store');
        Route::put('/manage/services/{id}', [AppointmentController::class, 'updateService'])->name('services.update');
        Route::delete('/manage/services/{id}', [AppointmentController::class, 'destroyService'])->name('services.destroy');

        // Providers (Staff)
        Route::get('/manage/providers', [AppointmentController::class, 'providers'])->name('providers');
        Route::post('/manage/providers', [AppointmentController::class, 'storeProvider'])->name('providers.store');
        Route::put('/manage/providers/{id}', [AppointmentController::class, 'updateProvider'])->name('providers.update');
        Route::delete('/manage/providers/{id}', [AppointmentController::class, 'destroyProvider'])->name('providers.destroy');

        // Customers
        Route::get('/manage/customers', [AppointmentController::class, 'customers'])->name('customers');
        Route::get('/manage/customers/search', [AppointmentController::class, 'searchCustomers'])->name('customers.search');
        Route::get('/manage/customers/{id}', [AppointmentController::class, 'showCustomer'])->name('customers.show');
        Route::post('/manage/customers', [AppointmentController::class, 'storeCustomer'])->name('customers.store');
        Route::put('/manage/customers/{id}', [AppointmentController::class, 'updateCustomer'])->name('customers.update');
        Route::delete('/manage/customers/{id}', [AppointmentController::class, 'destroyCustomer'])->name('customers.destroy');

        // Availability
        Route::get('/manage/availability', [AppointmentController::class, 'availability'])->name('availability');
        Route::post('/manage/availability/schedule', [AppointmentController::class, 'saveSchedule'])->name('availability.schedule');
        Route::post('/manage/availability/exceptions', [AppointmentController::class, 'storeException'])->name('availability.exceptions.store');
        Route::delete('/manage/availability/exceptions/{id}', [AppointmentController::class, 'destroyException'])->name('availability.exceptions.destroy');

        // Settings
        Route::get('/manage/settings', [AppointmentController::class, 'settings'])->name('settings');
        Route::post('/manage/settings', [AppointmentController::class, 'saveSettings'])->name('settings.save');
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
        Route::get('/subscription', [SubscriptionController::class, 'settings'])->name('subscription');
    });

    // Subscription Checkout (wallet-based)
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::post('/purchase', [SubscriptionController::class, 'purchase'])->name('purchase');
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

    // Point of Sale (POS)
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/sales', [POSController::class, 'sales'])->name('sales');
        Route::post('/sales', [POSController::class, 'storeSale'])->name('sales.store');
        Route::get('/sales/{id}', [POSController::class, 'showSale'])->name('sales.show');
        Route::post('/sales/{id}/void', [POSController::class, 'voidSale'])->name('sales.void');
        
        // Shifts
        Route::get('/shifts', [POSController::class, 'shifts'])->name('shifts');
        Route::post('/shifts/open', [POSController::class, 'openShift'])->name('shifts.open');
        Route::post('/shifts/{id}/close', [POSController::class, 'closeShift'])->name('shifts.close');
        
        // Products
        Route::get('/products/search', [POSController::class, 'searchProducts'])->name('products.search');
        Route::get('/quick-products', [POSController::class, 'quickProducts'])->name('quick-products');
        Route::post('/quick-products', [POSController::class, 'storeQuickProduct'])->name('quick-products.store');
        
        // Settings & Reports
        Route::get('/settings', [POSController::class, 'settings'])->name('settings');
        Route::post('/settings', [POSController::class, 'saveSettings'])->name('settings.save');
        Route::get('/daily-report', [POSController::class, 'dailyReport'])->name('daily-report');
    });

    // Subscription & Billing
    Route::get('/upgrade', [SubscriptionController::class, 'upgrade'])->name('upgrade');
    Route::get('/checkout', [SubscriptionController::class, 'checkout'])->name('checkout');
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
    Route::get('/usage', [SubscriptionController::class, 'usage'])->name('usage');
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
});
