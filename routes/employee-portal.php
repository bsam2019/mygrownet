<?php

use App\Http\Controllers\Employee\PortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Employee Portal Routes
|--------------------------------------------------------------------------
|
| These routes are for the employee self-service portal.
| All routes require authentication and employee role.
|
*/

Route::middleware(['auth', 'verified', 'employee'])->prefix('employee/portal')->name('employee.portal.')->group(function () {
    
    // Dashboard
    Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
    
    // Tasks
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [PortalController::class, 'tasks'])->name('index');
        Route::get('/kanban', [PortalController::class, 'taskKanban'])->name('kanban');
        Route::get('/{task}', [PortalController::class, 'taskShow'])->name('show');
        Route::patch('/{task}/status', [PortalController::class, 'taskUpdateStatus'])->name('update-status');
        Route::post('/{task}/comments', [PortalController::class, 'taskAddComment'])->name('add-comment');
    });
    
    // Goals
    Route::prefix('goals')->name('goals.')->group(function () {
        Route::get('/', [PortalController::class, 'goals'])->name('index');
        Route::patch('/{goal}/progress', [PortalController::class, 'goalUpdateProgress'])->name('update-progress');
        Route::patch('/{goal}/milestones/{milestone}', [PortalController::class, 'goalUpdateMilestone'])->name('update-milestone');
    });
    
    // Time Off
    Route::prefix('time-off')->name('time-off.')->group(function () {
        Route::get('/', [PortalController::class, 'timeOff'])->name('index');
        Route::get('/create', [PortalController::class, 'timeOffCreate'])->name('create');
        Route::post('/', [PortalController::class, 'timeOffStore'])->name('store');
        Route::delete('/{request}', [PortalController::class, 'timeOffCancel'])->name('cancel');
    });
    
    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [PortalController::class, 'attendance'])->name('index');
        Route::post('/clock-in', [PortalController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out', [PortalController::class, 'clockOut'])->name('clock-out');
        Route::post('/break/start', [PortalController::class, 'startBreak'])->name('break-start');
        Route::post('/break/end', [PortalController::class, 'endBreak'])->name('break-end');
    });
    
    // Documents
    Route::get('/documents', [PortalController::class, 'documents'])->name('documents');
    
    // Team
    Route::get('/team', [PortalController::class, 'team'])->name('team');
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [PortalController::class, 'notifications'])->name('index');
        Route::patch('/{notification}/read', [PortalController::class, 'markNotificationRead'])->name('mark-read');
        Route::post('/mark-all-read', [PortalController::class, 'markAllNotificationsRead'])->name('mark-all-read');
    });
    
    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [PortalController::class, 'profile'])->name('index');
        Route::patch('/', [PortalController::class, 'updateProfile'])->name('update');
    });

    // Payslips & Compensation
    Route::prefix('payslips')->name('payslips.')->group(function () {
        Route::get('/', [PortalController::class, 'payslips'])->name('index');
        Route::get('/{payslip}', [PortalController::class, 'payslipShow'])->name('show');
        Route::get('/{payslip}/download', [PortalController::class, 'payslipDownload'])->name('download');
    });

    // Announcements
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [PortalController::class, 'announcements'])->name('index');
        Route::get('/{announcement}', [PortalController::class, 'announcementShow'])->name('show');
        Route::post('/{announcement}/read', [PortalController::class, 'markAnnouncementRead'])->name('mark-read');
    });

    // Company Directory
    Route::prefix('directory')->name('directory.')->group(function () {
        Route::get('/', [PortalController::class, 'directory'])->name('index');
        Route::get('/org-chart', [PortalController::class, 'orgChart'])->name('org-chart');
    });

    // Performance Reviews
    Route::prefix('performance')->name('performance.')->group(function () {
        Route::get('/', [PortalController::class, 'performanceReviews'])->name('index');
        Route::get('/{review}', [PortalController::class, 'performanceReviewShow'])->name('show');
        Route::post('/{review}/submit', [PortalController::class, 'submitSelfAssessment'])->name('submit');
    });

    // Training & Learning
    Route::prefix('training')->name('training.')->group(function () {
        Route::get('/', [PortalController::class, 'training'])->name('index');
        Route::get('/courses', [PortalController::class, 'availableCourses'])->name('courses');
        Route::get('/certifications', [PortalController::class, 'certifications'])->name('certifications');
        Route::patch('/enrollments/{enrollment}/progress', [PortalController::class, 'updateCourseProgress'])->name('update-progress');
    });

    // Expenses
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [PortalController::class, 'expenses'])->name('index');
        Route::get('/create', [PortalController::class, 'expenseCreate'])->name('create');
        Route::post('/', [PortalController::class, 'expenseStore'])->name('store');
        Route::get('/{expense}', [PortalController::class, 'expenseShow'])->name('show');
        Route::post('/{expense}/submit', [PortalController::class, 'expenseSubmit'])->name('submit');
        Route::delete('/{expense}', [PortalController::class, 'expenseCancel'])->name('cancel');
    });

    // Help Desk / Support Tickets
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [PortalController::class, 'supportTickets'])->name('index');
        Route::get('/create', [PortalController::class, 'supportTicketCreate'])->name('create');
        Route::post('/', [PortalController::class, 'supportTicketStore'])->name('store');
        Route::get('/{ticket}', [PortalController::class, 'supportTicketShow'])->name('show');
        Route::post('/{ticket}/comments', [PortalController::class, 'supportTicketAddComment'])->name('add-comment');
        Route::post('/{ticket}/comment', [PortalController::class, 'supportTicketAddComment'])->name('comment'); // Alias for live chat
        Route::post('/{ticket}/chat', [PortalController::class, 'supportTicketChat'])->name('chat');
        Route::post('/{ticket}/rate', [PortalController::class, 'supportTicketRate'])->name('rate');
        Route::post('/quick-chat', [PortalController::class, 'supportQuickChat'])->name('quick-chat');
    });

    // Calendar
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [PortalController::class, 'calendar'])->name('index');
        Route::get('/events', [PortalController::class, 'calendarEvents'])->name('events');
        Route::post('/events', [PortalController::class, 'calendarEventStore'])->name('events.store');
        Route::patch('/events/{event}', [PortalController::class, 'calendarEventUpdate'])->name('events.update');
        Route::delete('/events/{event}', [PortalController::class, 'calendarEventCancel'])->name('events.cancel');
    });

    // Support Agent Dashboard (for employees who handle member/investor support)
    // Note: Add 'can:handle-support-tickets' middleware when permission is set up
    Route::prefix('support-agent')->name('support-agent.')->group(function () {
        Route::get('/', [PortalController::class, 'supportAgentDashboard'])->name('dashboard');
        Route::get('/tickets', [PortalController::class, 'supportAgentTickets'])->name('tickets');
        Route::get('/tickets/{ticket}', [PortalController::class, 'supportAgentTicketShow'])->name('show');
        Route::post('/tickets/{ticket}/reply', [PortalController::class, 'supportAgentReply'])->name('reply');
        Route::patch('/tickets/{ticket}/status', [PortalController::class, 'supportAgentUpdateStatus'])->name('update-status');
        Route::patch('/tickets/{ticket}/assign', [PortalController::class, 'supportAgentAssign'])->name('assign');
        Route::get('/stats', [PortalController::class, 'supportAgentStats'])->name('stats');
    });
});
