<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Domain\Employee\Services\TaskManagementService;
use App\Domain\Employee\Services\GoalTrackingService;
use App\Domain\Employee\Services\TimeOffService;
use App\Domain\Employee\Services\AttendanceService;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\TaskId;
use App\Models\Employee;
use App\Models\EmployeeTask;
use App\Models\EmployeeDocument;
use App\Models\EmployeeNotification;
use App\Models\EmployeePayslip;
use App\Models\EmployeeAnnouncement;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use DateTimeImmutable;

class PortalController extends Controller
{
    public function __construct(
        private TaskManagementService $taskService,
        private GoalTrackingService $goalService,
        private TimeOffService $timeOffService,
        private AttendanceService $attendanceService
    ) {}

    protected function getEmployee(): Employee
    {
        return Employee::where('user_id', Auth::id())
            ->with(['department', 'position', 'manager'])
            ->firstOrFail();
    }

    protected function getEmployeeId(): EmployeeId
    {
        return EmployeeId::fromInt($this->getEmployee()->id);
    }

    // ==================== DASHBOARD ====================

    public function dashboard()
    {
        $employee = $this->getEmployee();
        $employeeId = EmployeeId::fromInt($employee->id);

        $taskStats = $this->taskService->getStatusCounts($employeeId);
        $goalsSummary = $this->goalService->getGoalsSummary($employeeId);
        $attendanceSummary = $this->attendanceService->getAttendanceSummary($employeeId);
        $timeOffSummary = $this->timeOffService->getTimeOffSummary($employeeId, (int) date('Y'));
        
        // Get time off balances for the modal
        $timeOffBalances = $this->timeOffService->getAllBalances($employeeId, (int) date('Y'));

        $recentTasks = $this->taskService->getUpcomingTasks($employeeId, 7);
        $activeGoals = $this->goalService->getActiveGoals($employeeId);

        $unreadNotifications = EmployeeNotification::where('employee_id', $employee->id)
            ->unread()
            ->count();

        // Get assigned support tickets for this employee (assigned_to references user_id, not employee_id)
        $assignedTickets = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::query()
            ->where('assigned_to', $employee->user_id)
            ->whereIn('status', ['open', 'in_progress', 'pending'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($ticket) => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number ?? ($ticket->source === 'investor' ? 'INV-' : 'MEM-') . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'priority' => $ticket->priority ?? 'medium',
                'source' => $ticket->source ?? 'member',
                'user_name' => $ticket->user?->name ?? 'Unknown',
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
            ]);

        // Support ticket stats for this employee (assigned_to references user_id)
        $supportStats = [
            'assigned_to_me' => \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::where('assigned_to', $employee->user_id)
                ->whereIn('status', ['open', 'in_progress', 'pending'])
                ->count(),
            'total_open' => \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::whereIn('status', ['open', 'in_progress', 'pending'])->count(),
        ];

        return Inertia::render('Employee/Portal/Dashboard', [
            'employee' => $employee,
            'taskStats' => $taskStats,
            'goalsSummary' => $goalsSummary,
            'attendanceSummary' => $attendanceSummary,
            'timeOffSummary' => $timeOffSummary,
            'timeOffBalances' => $timeOffBalances,
            'recentTasks' => $recentTasks,
            'activeGoals' => $activeGoals,
            'unreadNotifications' => $unreadNotifications,
            'assignedTickets' => $assignedTickets,
            'supportStats' => $supportStats,
        ]);
    }

    // ==================== TASKS ====================

    public function tasks(Request $request)
    {
        $employeeId = $this->getEmployeeId();

        $tasks = $this->taskService->getTasksForEmployee($employeeId, [
            'status' => $request->status,
            'priority' => $request->priority,
        ]);

        $statusCounts = $this->taskService->getStatusCounts($employeeId);

        return Inertia::render('Employee/Portal/Tasks/Index', [
            'tasks' => $tasks,
            'statusCounts' => $statusCounts,
            'filters' => $request->only(['status', 'priority']),
        ]);
    }

    public function taskShow(EmployeeTask $task)
    {
        $employee = $this->getEmployee();

        if ($task->assigned_to !== $employee->id) {
            abort(403);
        }

        $task->load(['assigner', 'department', 'comments.employee', 'attachments']);

        return Inertia::render('Employee/Portal/Tasks/Show', [
            'task' => $task,
        ]);
    }

    public function taskUpdateStatus(Request $request, EmployeeTask $task)
    {
        $employeeId = $this->getEmployeeId();

        $request->validate([
            'status' => 'required|in:todo,in_progress,review,completed',
        ]);

        $this->taskService->updateTaskStatus(
            TaskId::fromInt($task->id),
            $employeeId,
            $request->status
        );

        return back()->with('success', 'Task status updated.');
    }

    public function taskKanban()
    {
        $employeeId = $this->getEmployeeId();

        $tasksByStatus = $this->taskService->getTasksGroupedByStatus($employeeId);

        return Inertia::render('Employee/Portal/Tasks/Kanban', [
            'tasksByStatus' => $tasksByStatus,
        ]);
    }

    public function taskAddComment(Request $request, EmployeeTask $task)
    {
        $employee = $this->getEmployee();

        $request->validate(['content' => 'required|string|max:1000']);

        $task->comments()->create([
            'employee_id' => $employee->id,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Comment added.');
    }

    // ==================== GOALS ====================

    public function goals(Request $request)
    {
        $employeeId = $this->getEmployeeId();

        $goals = $this->goalService->getGoalsForEmployee($employeeId, [
            'status' => $request->status,
            'category' => $request->category,
        ]);

        $stats = $this->goalService->getProgressStats($employeeId);

        return Inertia::render('Employee/Portal/Goals/Index', [
            'goals' => $goals,
            'stats' => $stats,
            'filters' => $request->only(['status', 'category']),
        ]);
    }

    public function goalUpdateProgress(Request $request, int $goalId)
    {
        $request->validate(['progress' => 'required|integer|min:0|max:100']);

        $this->goalService->updateProgress($goalId, $request->progress);

        return back()->with('success', 'Goal progress updated.');
    }

    public function goalUpdateMilestone(Request $request, int $goalId, int $milestoneIndex)
    {
        $request->validate(['completed' => 'required|boolean']);

        $this->goalService->updateMilestone($goalId, $milestoneIndex, $request->completed);

        return back()->with('success', 'Milestone updated.');
    }

    // ==================== TIME OFF ====================

    public function timeOff(Request $request)
    {
        $employeeId = $this->getEmployeeId();

        $requests = $this->timeOffService->getRequestsForEmployee($employeeId, [
            'status' => $request->status,
            'type' => $request->type,
            'year' => $request->year ?? date('Y'),
        ]);

        $balances = $this->timeOffService->getAllBalances($employeeId, (int) ($request->year ?? date('Y')));

        return Inertia::render('Employee/Portal/TimeOff/Index', [
            'requests' => $requests,
            'balances' => $balances,
            'filters' => $request->only(['status', 'type', 'year']),
        ]);
    }

    public function timeOffCreate()
    {
        $employeeId = $this->getEmployeeId();
        $balances = $this->timeOffService->getAllBalances($employeeId, (int) date('Y'));

        return Inertia::render('Employee/Portal/TimeOff/Create', [
            'balances' => $balances,
        ]);
    }

    public function timeOffStore(Request $request)
    {
        $employeeId = $this->getEmployeeId();

        $validated = $request->validate([
            'type' => 'required|string|in:annual,sick,personal,maternity,paternity,bereavement,unpaid,study',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days_requested' => 'required|numeric|min:0.5',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->timeOffService->createRequest(
                $employeeId,
                $validated['type'],
                new DateTimeImmutable($validated['start_date']),
                new DateTimeImmutable($validated['end_date']),
                $validated['days_requested'],
                $validated['reason'] ?? null
            );

            // Return back with success - works for both modal and full page
            return back()->with('success', 'Time off request submitted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function timeOffCancel(int $requestId)
    {
        $employeeId = $this->getEmployeeId();

        try {
            $this->timeOffService->cancelRequest($requestId, $employeeId);
            return back()->with('success', 'Time off request cancelled.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ==================== ATTENDANCE ====================

    public function attendance(Request $request)
    {
        $employeeId = $this->getEmployeeId();

        $year = $request->year ?? date('Y');
        $month = $request->month ?? date('m');

        $summary = $this->attendanceService->getAttendanceSummary($employeeId);
        $monthlyStats = $this->attendanceService->getMonthlyStats($employeeId, (int) $year, (int) $month);

        $startDate = new DateTimeImmutable("{$year}-{$month}-01");
        $endDate = new DateTimeImmutable($startDate->format('Y-m-t'));
        $history = $this->attendanceService->getAttendanceHistory($employeeId, $startDate, $endDate);

        return Inertia::render('Employee/Portal/Attendance/Index', [
            'summary' => $summary,
            'monthlyStats' => $monthlyStats,
            'history' => $history,
            'filters' => ['year' => $year, 'month' => $month],
        ]);
    }

    public function clockIn()
    {
        $employeeId = $this->getEmployeeId();

        try {
            $this->attendanceService->clockIn($employeeId);
            return back()->with('success', 'Clocked in successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function clockOut()
    {
        $employeeId = $this->getEmployeeId();

        try {
            $this->attendanceService->clockOut($employeeId);
            return back()->with('success', 'Clocked out successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function startBreak()
    {
        $employeeId = $this->getEmployeeId();

        try {
            $this->attendanceService->startBreak($employeeId);
            return back()->with('success', 'Break started.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function endBreak()
    {
        $employeeId = $this->getEmployeeId();

        try {
            $this->attendanceService->endBreak($employeeId);
            return back()->with('success', 'Break ended.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ==================== DOCUMENTS ====================

    public function documents(Request $request)
    {
        $employee = $this->getEmployee();

        $documents = EmployeeDocument::where('employee_id', $employee->id)
            ->when($request->category, fn($q) => $q->byCategory($request->category))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = EmployeeDocument::where('employee_id', $employee->id)
            ->distinct()
            ->pluck('category');

        return Inertia::render('Employee/Portal/Documents/Index', [
            'documents' => $documents,
            'categories' => $categories,
            'filters' => $request->only(['category']),
        ]);
    }

    // ==================== TEAM ====================

    public function team()
    {
        $employee = $this->getEmployee();

        $teamMembers = Employee::where('department_id', $employee->department_id)
            ->with(['position', 'user'])
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get()
            ->map(fn($member) => [
                'id' => $member->id,
                'name' => $member->full_name,
                'position' => $member->position?->title,
                'email' => $member->email,
                'phone' => $member->phone,
                'avatar' => $member->user?->avatar,
                'is_manager' => $member->id === $employee->manager_id,
                'is_self' => $member->id === $employee->id,
            ]);

        return Inertia::render('Employee/Portal/Team/Index', [
            'teamMembers' => $teamMembers,
            'department' => $employee->department,
        ]);
    }

    // ==================== NOTIFICATIONS ====================

    public function notifications()
    {
        $employee = $this->getEmployee();

        $notifications = EmployeeNotification::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Employee/Portal/Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markNotificationRead(EmployeeNotification $notification)
    {
        $employee = $this->getEmployee();

        if ($notification->employee_id !== $employee->id) {
            abort(403);
        }

        $notification->markAsRead();

        return back();
    }

    public function markAllNotificationsRead()
    {
        $employee = $this->getEmployee();

        EmployeeNotification::where('employee_id', $employee->id)
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    // ==================== PROFILE ====================

    public function profile()
    {
        $employee = $this->getEmployee();

        return Inertia::render('Employee/Portal/Profile/Index', [
            'employee' => $employee->load(['department', 'position', 'manager']),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $employee = $this->getEmployee();

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        $employee->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    // ==================== PAYSLIPS ====================

    public function payslips(Request $request)
    {
        $employee = $this->getEmployee();
        $year = $request->year ?? date('Y');

        $payslips = EmployeePayslip::where('employee_id', $employee->id)
            ->paid()
            ->forYear((int) $year)
            ->orderBy('payment_date', 'desc')
            ->get();

        // Calculate YTD totals
        $ytdTotals = [
            'gross_pay' => $payslips->sum('gross_pay'),
            'net_pay' => $payslips->sum('net_pay'),
            'tax' => $payslips->sum('tax'),
            'pension' => $payslips->sum('pension'),
        ];

        // Get available years
        $availableYears = EmployeePayslip::where('employee_id', $employee->id)
            ->paid()
            ->selectRaw('YEAR(payment_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return Inertia::render('Employee/Portal/Payslips/Index', [
            'payslips' => $payslips,
            'ytdTotals' => $ytdTotals,
            'availableYears' => $availableYears,
            'selectedYear' => (int) $year,
        ]);
    }

    public function payslipShow(EmployeePayslip $payslip)
    {
        $employee = $this->getEmployee();

        if ($payslip->employee_id !== $employee->id) {
            abort(403);
        }

        return Inertia::render('Employee/Portal/Payslips/Show', [
            'payslip' => $payslip->load('employee.department', 'employee.position'),
        ]);
    }

    public function payslipDownload(EmployeePayslip $payslip)
    {
        $employee = $this->getEmployee();

        if ($payslip->employee_id !== $employee->id) {
            abort(403);
        }

        if ($payslip->pdf_path && Storage::exists($payslip->pdf_path)) {
            return Storage::download($payslip->pdf_path, "payslip-{$payslip->payslip_number}.pdf");
        }

        // Generate PDF on the fly if not stored
        // For now, return a simple response
        return back()->with('error', 'PDF not available for this payslip.');
    }

    // ==================== ANNOUNCEMENTS ====================

    public function announcements(Request $request)
    {
        $employee = $this->getEmployee();

        $announcements = EmployeeAnnouncement::active()
            ->forDepartment($employee->department_id)
            ->with('author', 'department')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('publish_date', 'desc')
            ->paginate(10);

        // Add read status for each announcement
        $announcements->getCollection()->transform(function ($announcement) use ($employee) {
            $announcement->is_read = $announcement->isReadBy($employee->id);
            return $announcement;
        });

        // Count unread
        $unreadCount = EmployeeAnnouncement::active()
            ->forDepartment($employee->department_id)
            ->whereDoesntHave('readBy', fn($q) => $q->where('employee_id', $employee->id))
            ->count();

        return Inertia::render('Employee/Portal/Announcements/Index', [
            'announcements' => $announcements,
            'unreadCount' => $unreadCount,
            'filters' => $request->only(['type']),
        ]);
    }

    public function announcementShow(EmployeeAnnouncement $announcement)
    {
        $employee = $this->getEmployee();

        // Check if employee can view this announcement
        if ($announcement->department_id && $announcement->department_id !== $employee->department_id) {
            abort(403);
        }

        // Mark as read
        $announcement->markAsReadBy($employee->id);

        return Inertia::render('Employee/Portal/Announcements/Show', [
            'announcement' => $announcement->load('author', 'department'),
        ]);
    }

    public function markAnnouncementRead(EmployeeAnnouncement $announcement)
    {
        $employee = $this->getEmployee();
        $announcement->markAsReadBy($employee->id);

        return back();
    }

    // ==================== COMPANY DIRECTORY ====================

    public function directory(Request $request)
    {
        $employee = $this->getEmployee();

        $query = Employee::with(['department', 'position'])
            ->where('employment_status', 'active');

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->department) {
            $query->where('department_id', $request->department);
        }

        $employees = $query->orderBy('first_name')
            ->paginate(20)
            ->through(fn($emp) => [
                'id' => $emp->id,
                'name' => $emp->full_name,
                'email' => $emp->email,
                'phone' => $emp->phone,
                'department' => $emp->department?->name,
                'position' => $emp->position?->title,
                'is_self' => $emp->id === $employee->id,
            ]);

        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Employee/Portal/Directory/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $request->only(['search', 'department']),
        ]);
    }

    public function orgChart()
    {
        $departments = Department::with(['employees' => function ($q) {
            $q->where('employment_status', 'active')
                ->with('position')
                ->orderBy('first_name');
        }, 'headEmployee'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($dept) => [
                'id' => $dept->id,
                'name' => $dept->name,
                'head' => $dept->headEmployee ? [
                    'id' => $dept->headEmployee->id,
                    'name' => $dept->headEmployee->full_name,
                    'position' => $dept->headEmployee->position?->title,
                ] : null,
                'employees' => $dept->employees->map(fn($emp) => [
                    'id' => $emp->id,
                    'name' => $emp->full_name,
                    'position' => $emp->position?->title,
                ]),
                'employee_count' => $dept->employees->count(),
            ]);

        return Inertia::render('Employee/Portal/Directory/OrgChart', [
            'departments' => $departments,
        ]);
    }

    // ==================== PERFORMANCE REVIEWS ====================

    public function performanceReviews(Request $request)
    {
        $employeeId = $this->getEmployeeId();
        $reviewService = app(\App\Domain\Employee\Services\PerformanceReviewService::class);

        $reviews = $reviewService->getReviewsForEmployee($employeeId, [
            'status' => $request->status,
            'type' => $request->type,
        ]);

        $stats = $reviewService->getReviewStats($employeeId);
        $ratingTrends = $reviewService->getRatingTrends($employeeId);

        return Inertia::render('Employee/Portal/Performance/Index', [
            'reviews' => $reviews,
            'stats' => $stats,
            'ratingTrends' => $ratingTrends,
            'filters' => $request->only(['status', 'type']),
        ]);
    }

    public function performanceReviewShow(\App\Models\EmployeePerformanceReview $review)
    {
        $employee = $this->getEmployee();

        if ($review->employee_id !== $employee->id) {
            abort(403);
        }

        return Inertia::render('Employee/Portal/Performance/Show', [
            'review' => $review->load('reviewer'),
        ]);
    }

    public function submitSelfAssessment(Request $request, \App\Models\EmployeePerformanceReview $review)
    {
        $employee = $this->getEmployee();

        if ($review->employee_id !== $employee->id) {
            abort(403);
        }

        $validated = $request->validate([
            'ratings' => 'nullable|array',
            'strengths' => 'nullable|string|max:2000',
            'improvements' => 'nullable|string|max:2000',
            'employee_comments' => 'nullable|string|max:2000',
        ]);

        $reviewService = app(\App\Domain\Employee\Services\PerformanceReviewService::class);
        $reviewService->submitSelfAssessment($review->id, $validated);

        return back()->with('success', 'Self-assessment submitted successfully.');
    }

    // ==================== TRAINING & LEARNING ====================

    public function training(Request $request)
    {
        $employeeId = $this->getEmployeeId();
        $trainingService = app(\App\Domain\Employee\Services\TrainingService::class);

        $enrollments = $trainingService->getEnrollmentsForEmployee($employeeId, [
            'status' => $request->status,
        ]);

        $stats = $trainingService->getTrainingStats($employeeId);
        $learningPath = $trainingService->getLearningPath($employeeId);

        return Inertia::render('Employee/Portal/Training/Index', [
            'enrollments' => $enrollments,
            'stats' => $stats,
            'learningPath' => $learningPath,
            'filters' => $request->only(['status']),
        ]);
    }

    public function availableCourses()
    {
        $employeeId = $this->getEmployeeId();
        $trainingService = app(\App\Domain\Employee\Services\TrainingService::class);

        $courses = $trainingService->getAvailableCourses($employeeId);

        return Inertia::render('Employee/Portal/Training/Courses', [
            'courses' => $courses,
        ]);
    }

    public function certifications()
    {
        $employeeId = $this->getEmployeeId();
        $trainingService = app(\App\Domain\Employee\Services\TrainingService::class);

        $certifications = $trainingService->getCertifications($employeeId);

        return Inertia::render('Employee/Portal/Training/Certifications', [
            'certifications' => $certifications,
        ]);
    }

    public function updateCourseProgress(Request $request, \App\Models\EmployeeCourseEnrollment $enrollment)
    {
        $employee = $this->getEmployee();

        if ($enrollment->employee_id !== $employee->id) {
            abort(403);
        }

        $request->validate(['progress' => 'required|integer|min:0|max:100']);

        $trainingService = app(\App\Domain\Employee\Services\TrainingService::class);
        $trainingService->updateEnrollmentProgress($enrollment->id, $request->progress);

        return back()->with('success', 'Progress updated.');
    }

    // ==================== EXPENSES ====================

    public function expenses(Request $request)
    {
        $employeeId = $this->getEmployeeId();
        $expenseService = app(\App\Domain\Employee\Services\ExpenseService::class);

        $expenses = $expenseService->getExpensesForEmployee($employeeId, [
            'status' => $request->status,
            'category' => $request->category,
            'year' => $request->year ?? date('Y'),
        ]);

        $stats = $expenseService->getExpenseStats($employeeId, (int) ($request->year ?? date('Y')));
        $categories = $expenseService->getExpenseCategories();

        return Inertia::render('Employee/Portal/Expenses/Index', [
            'expenses' => $expenses,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => $request->only(['status', 'category', 'year']),
        ]);
    }

    public function expenseCreate()
    {
        $expenseService = app(\App\Domain\Employee\Services\ExpenseService::class);

        return Inertia::render('Employee/Portal/Expenses/Create', [
            'categories' => $expenseService->getExpenseCategories(),
        ]);
    }

    public function expenseStore(Request $request)
    {
        $employeeId = $this->getEmployeeId();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date|before_or_equal:today',
            'receipts' => 'nullable|array',
        ]);

        $expenseService = app(\App\Domain\Employee\Services\ExpenseService::class);
        $expenseService->createExpense($employeeId, $validated);

        return redirect()->route('employee.portal.expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    public function expenseShow(\App\Models\EmployeeExpense $expense)
    {
        $employee = $this->getEmployee();

        if ($expense->employee_id !== $employee->id) {
            abort(403);
        }

        return Inertia::render('Employee/Portal/Expenses/Show', [
            'expense' => $expense->load('approver'),
        ]);
    }

    public function expenseSubmit(\App\Models\EmployeeExpense $expense)
    {
        $employeeId = $this->getEmployeeId();

        $expenseService = app(\App\Domain\Employee\Services\ExpenseService::class);
        $expenseService->submitExpense($expense->id, $employeeId);

        return back()->with('success', 'Expense submitted for approval.');
    }

    public function expenseCancel(\App\Models\EmployeeExpense $expense)
    {
        $employeeId = $this->getEmployeeId();

        $expenseService = app(\App\Domain\Employee\Services\ExpenseService::class);
        $expenseService->cancelExpense($expense->id, $employeeId);

        return redirect()->route('employee.portal.expenses.index')
            ->with('success', 'Expense cancelled.');
    }

    // ==================== SUPPORT TICKETS ====================

    public function supportTickets(Request $request)
    {
        $employeeId = $this->getEmployeeId();
        $ticketService = app(\App\Domain\Employee\Services\SupportTicketService::class);

        $tickets = $ticketService->getTicketsForEmployee($employeeId, [
            'status' => $request->status,
            'category' => $request->category,
            'priority' => $request->priority,
        ]);

        $stats = $ticketService->getTicketStats($employeeId);
        $categories = $ticketService->getCategories();

        // Return JSON for AJAX requests (used by LiveChatWidget)
        if ($request->wantsJson()) {
            return response()->json([
                'tickets' => $tickets->map(fn($t) => [
                    'id' => $t->id,
                    'ticket_number' => $t->ticket_number,
                    'subject' => $t->subject,
                    'status' => $t->status,
                    'updated_at' => $t->updated_at->toISOString(),
                    'comments_count' => $t->comments()->count(),
                    'rating' => $t->rating ?? null,
                    'rating_feedback' => $t->rating_feedback ?? null,
                ]),
            ]);
        }

        return Inertia::render('Employee/Portal/Support/Index', [
            'tickets' => $tickets,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => $request->only(['status', 'category', 'priority']),
        ]);
    }

    public function supportTicketCreate()
    {
        $ticketService = app(\App\Domain\Employee\Services\SupportTicketService::class);

        return Inertia::render('Employee/Portal/Support/Create', [
            'categories' => $ticketService->getCategories(),
        ]);
    }

    public function supportTicketStore(Request $request)
    {
        $employeeId = $this->getEmployeeId();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'attachments' => 'nullable|array',
        ]);

        $ticketService = app(\App\Domain\Employee\Services\SupportTicketService::class);
        $ticketService->createTicket($employeeId, $validated);

        return redirect()->route('employee.portal.support.index')
            ->with('success', 'Support ticket created successfully.');
    }

    public function supportTicketShow(Request $request, \App\Models\EmployeeSupportTicket $ticket)
    {
        $employeeId = $this->getEmployeeId();
        $ticketService = app(\App\Domain\Employee\Services\SupportTicketService::class);

        $ticket = $ticketService->getTicketWithComments($ticket->id, $employeeId);
        $categories = $ticketService->getCategories();

        // Return JSON for AJAX requests (used by polling)
        if ($request->wantsJson()) {
            return response()->json([
                'ticket' => $ticket,
            ]);
        }

        return Inertia::render('Employee/Portal/Support/Show', [
            'ticket' => $ticket,
            'categories' => $categories,
        ]);
    }

    public function supportTicketAddComment(Request $request, \App\Models\EmployeeSupportTicket $ticket)
    {
        $employeeId = $this->getEmployeeId();

        $request->validate([
            'content' => 'required|string|max:2000',
            'attachments' => 'nullable|array',
        ]);

        $ticketService = app(\App\Domain\Employee\Services\SupportTicketService::class);
        $ticketService->addComment($ticket->id, $employeeId, $request->content, $request->attachments ?? []);

        return back()->with('success', 'Comment added.');
    }

    public function supportTicketChat(Request $request, \App\Models\EmployeeSupportTicket $ticket)
    {
        try {
            $employee = $this->getEmployee();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Employee record not found. Please contact your administrator.',
                'code' => 'EMPLOYEE_NOT_FOUND'
            ], 404);
        }

        if ($ticket->employee_id !== $employee->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        try {
            // Add comment to ticket (this also broadcasts the message)
            $ticketService = app(\App\Domain\Employee\Services\SupportTicketService::class);
            $ticketService->addComment($ticket->id, EmployeeId::fromInt($employee->id), $request->message, []);

            // Always return JSON for chat endpoint
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Failed to send chat message', [
                'ticket_id' => $ticket->id,
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to send message. Please try again.',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function supportQuickChat(Request $request)
    {
        try {
            $employee = $this->getEmployee();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Employee record not found. Please contact your administrator.',
                'code' => 'EMPLOYEE_NOT_FOUND'
            ], 404);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
            'category' => 'nullable|string|in:general,technical,billing,account,feedback,hr,it,payroll',
        ]);

        // Map category to subject for better context
        $categorySubjects = [
            'general' => 'General Inquiry',
            'technical' => 'Technical Support',
            'billing' => 'Billing & Payments',
            'account' => 'Account Help',
            'feedback' => 'Feedback & Suggestions',
            'hr' => 'HR Related',
            'it' => 'IT Support',
            'payroll' => 'Payroll Question',
        ];

        $category = $request->category ?? 'general';
        $subject = $categorySubjects[$category] ?? 'Support Request';

        try {
            // Create a new support ticket for quick chat
            $ticketService = app(\App\Domain\Employee\Services\SupportTicketService::class);
            $ticket = $ticketService->createTicket(EmployeeId::fromInt($employee->id), [
                'subject' => $subject,
                'description' => $request->message,
                'category' => $category,
                'priority' => 'medium',
            ]);

            return response()->json([
                'success' => true,
                'ticket_id' => $ticket->id ?? null,
                'ticket_number' => $ticket->ticket_number ?? null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create quick chat ticket', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to send message. Please try again.',
                'details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Rate a closed support ticket
     */
    public function supportTicketRate(Request $request, \App\Models\EmployeeSupportTicket $ticket)
    {
        $employeeId = $this->getEmployeeId();
        
        // Verify ownership
        if ($ticket->employee_id !== $employeeId) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        // Only allow rating closed/resolved tickets
        if (!in_array($ticket->status, ['closed', 'resolved'])) {
            return response()->json(['error' => 'Can only rate closed tickets'], 400);
        }

        // Check if already rated
        if ($ticket->satisfaction_rating) {
            return response()->json(['error' => 'Ticket already rated'], 400);
        }

        try {
            $ticket->update([
                'satisfaction_rating' => $request->rating,
                'rating_feedback' => $request->feedback,
                'rated_at' => now(),
            ]);

            \Log::info('Employee rated support ticket', [
                'ticket_id' => $ticket->id,
                'employee_id' => $employeeId,
                'rating' => $request->rating,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback!',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to rate ticket', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to submit rating. Please try again.',
            ], 500);
        }
    }

    // ==================== CALENDAR ====================

    public function calendar(Request $request)
    {
        $employeeId = $this->getEmployeeId();
        $calendarService = app(\App\Domain\Employee\Services\CalendarService::class);

        $summary = $calendarService->getCalendarSummary($employeeId);

        return Inertia::render('Employee/Portal/Calendar/Index', [
            'summary' => $summary,
        ]);
    }

    public function calendarEvents(Request $request)
    {
        $employeeId = $this->getEmployeeId();
        $calendarService = app(\App\Domain\Employee\Services\CalendarService::class);

        $start = new \DateTimeImmutable($request->start ?? 'first day of this month');
        $end = new \DateTimeImmutable($request->end ?? 'last day of this month');

        $events = $calendarService->getEventsForEmployee($employeeId, $start, $end);

        return response()->json(['events' => $events]);
    }

    public function calendarEventStore(Request $request)
    {
        $employeeId = $this->getEmployeeId();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'nullable|in:meeting,training,deadline,holiday,personal,team',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_all_day' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url|max:500',
        ]);

        $calendarService = app(\App\Domain\Employee\Services\CalendarService::class);
        $calendarService->createEvent($employeeId, $validated);

        return back()->with('success', 'Event created.');
    }

    public function calendarEventUpdate(Request $request, \App\Models\EmployeeCalendarEvent $event)
    {
        $employeeId = $this->getEmployeeId();

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'location' => 'nullable|string|max:255',
        ]);

        $calendarService = app(\App\Domain\Employee\Services\CalendarService::class);
        $calendarService->updateEvent($event->id, $employeeId, $validated);

        return back()->with('success', 'Event updated.');
    }

    public function calendarEventCancel(\App\Models\EmployeeCalendarEvent $event)
    {
        $employeeId = $this->getEmployeeId();

        $calendarService = app(\App\Domain\Employee\Services\CalendarService::class);
        $calendarService->cancelEvent($event->id, $employeeId);

        return back()->with('success', 'Event cancelled.');
    }

    // ==================== SUPPORT AGENT (Handle Member/Investor Tickets) ====================

    /**
     * Support Agent Dashboard - Overview of all member/investor tickets
     */
    public function supportAgentDashboard()
    {
        $employee = $this->getEmployee();
        
        // Get unified support tickets (members + investors)
        $memberTickets = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::query()
            ->whereIn('status', ['open', 'in_progress', 'pending'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $investorTickets = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::query()
            ->where('source', 'investor')
            ->whereIn('status', ['open', 'in_progress', 'pending'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Stats - all tickets are in SupportTicketModel with different sources (assigned_to references user_id)
        $stats = [
            'total_open' => \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::whereIn('status', ['open', 'in_progress', 'pending'])->count(),
            'assigned_to_me' => \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::where('assigned_to', $employee->user_id)->whereIn('status', ['open', 'in_progress'])->count(),
            'resolved_today' => \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::where('status', 'resolved')->whereDate('updated_at', today())->count(),
            'avg_response_time' => '< 2 hours', // Placeholder - calculate from actual data
        ];

        // Combine and sort tickets
        $allTickets = collect();
        
        foreach ($memberTickets as $ticket) {
            $allTickets->push([
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number ?? 'MEM-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'priority' => $ticket->priority ?? 'medium',
                'source' => 'member',
                'user_name' => $ticket->user?->name ?? 'Unknown',
                'user_email' => $ticket->user?->email ?? '',
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
                'assigned_to' => $ticket->assigned_to,
            ]);
        }

        foreach ($investorTickets as $ticket) {
            $allTickets->push([
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number ?? 'INV-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'priority' => $ticket->priority ?? 'medium',
                'source' => 'investor',
                'user_name' => $ticket->investorAccount?->name ?? $ticket->user?->name ?? 'Unknown',
                'user_email' => $ticket->investorAccount?->email ?? $ticket->user?->email ?? '',
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
                'assigned_to' => $ticket->assigned_to,
            ]);
        }

        $allTickets = $allTickets->sortByDesc('created_at')->values();

        return Inertia::render('Employee/Portal/SupportAgent/Dashboard', [
            'employee' => $employee,
            'tickets' => $allTickets,
            'stats' => $stats,
        ]);
    }

    /**
     * List all support tickets with filters
     */
    public function supportAgentTickets(Request $request)
    {
        $employee = $this->getEmployee();
        
        $status = $request->get('status', 'open');
        $source = $request->get('source', 'all');
        $assignedToMe = $request->boolean('assigned_to_me', false);

        $allTickets = collect();

        // Member tickets
        if ($source === 'all' || $source === 'member') {
            $memberQuery = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::query()
                ->with('user');
            
            if ($status !== 'all') {
                $memberQuery->where('status', $status);
            }
            if ($assignedToMe) {
                $memberQuery->where('assigned_to', $employee->user_id);
            }
            
            foreach ($memberQuery->orderBy('created_at', 'desc')->get() as $ticket) {
                $allTickets->push([
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number ?? 'MEM-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                    'subject' => $ticket->subject,
                    'description' => $ticket->description,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority ?? 'medium',
                    'category' => $ticket->category ?? 'general',
                    'source' => 'member',
                    'user_name' => $ticket->user?->name ?? 'Unknown',
                    'user_email' => $ticket->user?->email ?? '',
                    'created_at' => $ticket->created_at,
                    'updated_at' => $ticket->updated_at,
                    'assigned_to' => $ticket->assigned_to,
                ]);
            }
        }

        // Investor tickets (same model, different source)
        if ($source === 'all' || $source === 'investor') {
            $investorQuery = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::query()
                ->where('source', 'investor')
                ->with(['user', 'investorAccount']);
            
            if ($status !== 'all') {
                $investorQuery->where('status', $status);
            }
            if ($assignedToMe) {
                $investorQuery->where('assigned_to', $employee->user_id);
            }
            
            foreach ($investorQuery->orderBy('created_at', 'desc')->get() as $ticket) {
                $allTickets->push([
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number ?? 'INV-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT),
                    'subject' => $ticket->subject,
                    'description' => $ticket->description,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority ?? 'medium',
                    'category' => $ticket->category ?? 'general',
                    'source' => 'investor',
                    'user_name' => $ticket->investorAccount?->name ?? $ticket->user?->name ?? 'Unknown',
                    'user_email' => $ticket->investorAccount?->email ?? $ticket->user?->email ?? '',
                    'created_at' => $ticket->created_at,
                    'updated_at' => $ticket->updated_at,
                    'assigned_to' => $ticket->assigned_to,
                ]);
            }
        }

        $allTickets = $allTickets->sortByDesc('created_at')->values();

        return Inertia::render('Employee/Portal/SupportAgent/Tickets', [
            'employee' => $employee,
            'tickets' => $allTickets,
            'filters' => [
                'status' => $status,
                'source' => $source,
                'assigned_to_me' => $assignedToMe,
            ],
        ]);
    }

    /**
     * Show a single ticket with chat interface
     */
    public function supportAgentTicketShow(Request $request, int $ticket)
    {
        $employee = $this->getEmployee();
        $source = $request->get('source', 'member');

        if ($source === 'investor') {
            $ticketModel = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::with(['user', 'investorAccount', 'comments.user'])->findOrFail($ticket);
            $ticketData = [
                'id' => $ticketModel->id,
                'ticket_number' => $ticketModel->ticket_number ?? 'INV-' . str_pad($ticketModel->id, 6, '0', STR_PAD_LEFT),
                'subject' => $ticketModel->subject,
                'description' => $ticketModel->description,
                'status' => $ticketModel->status,
                'priority' => $ticketModel->priority ?? 'medium',
                'category' => $ticketModel->category ?? 'general',
                'source' => 'investor',
                'user_id' => $ticketModel->investor_account_id ?? $ticketModel->user_id,
                'user_name' => $ticketModel->investorAccount?->name ?? $ticketModel->user?->name ?? 'Unknown',
                'user_email' => $ticketModel->investorAccount?->email ?? $ticketModel->user?->email ?? '',
                'created_at' => $ticketModel->created_at,
                'updated_at' => $ticketModel->updated_at,
                'assigned_to' => $ticketModel->assigned_to,
                'comments' => $ticketModel->comments->map(fn($c) => [
                    'id' => $c->id,
                    'content' => $c->content ?? $c->comment,
                    'author_type' => $c->author_type ?? 'support',
                    'author_name' => $c->user?->name ?? $c->author_name ?? $c->display_author_name ?? 'Support',
                    'created_at' => $c->created_at,
                ]),
            ];
            $channelName = "investor.support.{$ticket}";
        } else {
            $ticketModel = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::with(['user', 'comments.user'])->findOrFail($ticket);
            $ticketData = [
                'id' => $ticketModel->id,
                'ticket_number' => $ticketModel->ticket_number ?? 'MEM-' . str_pad($ticketModel->id, 6, '0', STR_PAD_LEFT),
                'subject' => $ticketModel->subject,
                'description' => $ticketModel->description,
                'status' => $ticketModel->status,
                'priority' => $ticketModel->priority ?? 'medium',
                'category' => $ticketModel->category ?? 'general',
                'source' => 'member',
                'user_id' => $ticketModel->user_id,
                'user_name' => $ticketModel->user?->name ?? 'Unknown',
                'user_email' => $ticketModel->user?->email ?? '',
                'created_at' => $ticketModel->created_at,
                'updated_at' => $ticketModel->updated_at,
                'assigned_to' => $ticketModel->assigned_to,
                'comments' => $ticketModel->comments->map(fn($c) => [
                    'id' => $c->id,
                    'content' => $c->content ?? $c->comment,
                    'author_type' => $c->author_type ?? ($c->user_id === $ticketModel->user_id ? 'user' : 'support'),
                    'author_name' => $c->user?->name ?? $c->author_name ?? $c->display_author_name ?? 'Support',
                    'created_at' => $c->created_at,
                ]),
            ];
            $channelName = "member.support.{$ticket}";
        }

        // Return JSON for AJAX requests
        if ($request->wantsJson()) {
            return response()->json([
                'ticket' => $ticketData,
                'channel' => $channelName,
            ]);
        }

        return Inertia::render('Employee/Portal/SupportAgent/Show', [
            'employee' => $employee,
            'ticket' => $ticketData,
            'channel' => $channelName,
        ]);
    }

    /**
     * Reply to a ticket (send message)
     */
    public function supportAgentReply(Request $request, int $ticket)
    {
        $request->validate([
            'message' => 'required|string|min:1',
            'source' => 'required|in:member,investor',
        ]);

        $employee = $this->getEmployee();
        $source = $request->input('source');
        $message = $request->input('message');

        if ($source === 'investor') {
            $ticketModel = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::findOrFail($ticket);
            
            // Add comment
            $comment = \App\Infrastructure\Persistence\Eloquent\Support\TicketCommentModel::create([
                'ticket_id' => $ticket,
                'content' => $message,
                'author_id' => $employee->user_id,
                'author_type' => 'support',
                'display_author_name' => $employee->full_name,
            ]);

            // Update ticket status if needed
            if ($ticketModel->status === 'open') {
                $ticketModel->update(['status' => 'in_progress']);
            }

            // Broadcast message
            event(new \App\Events\Investor\InvestorSupportMessage(
                $ticket,
                $employee->user_id,
                $employee->full_name,
                'support',
                $message
            ));

            $channelName = "investor.support.{$ticket}";
        } else {
            $ticketModel = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::findOrFail($ticket);
            
            // Add comment
            $comment = \App\Infrastructure\Persistence\Eloquent\Support\TicketCommentModel::create([
                'ticket_id' => $ticket,
                'content' => $message,
                'author_id' => $employee->user_id,
                'author_type' => 'support',
                'display_author_name' => $employee->full_name,
            ]);

            // Update ticket status if needed
            if ($ticketModel->status === 'open') {
                $ticketModel->update(['status' => 'in_progress']);
            }

            // Broadcast message
            event(new \App\Events\Member\MemberSupportMessage(
                $ticket,
                $employee->user_id,
                $employee->full_name,
                'support',
                $message
            ));

            $channelName = "member.support.{$ticket}";
        }

        return response()->json([
            'success' => true,
            'comment_id' => $comment->id,
            'channel' => $channelName,
        ]);
    }

    /**
     * Update ticket status
     */
    public function supportAgentUpdateStatus(Request $request, int $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,pending,resolved,closed',
            'source' => 'required|in:member,investor',
        ]);

        $employee = $this->getEmployee();
        $source = $request->input('source');
        $status = $request->input('status');

        // All tickets use the same model now
        $ticketModel = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::findOrFail($ticket);

        $ticketModel->update([
            'status' => $status,
            'closed_by' => in_array($status, ['resolved', 'closed']) ? $employee->user_id : null,
            'closed_at' => in_array($status, ['resolved', 'closed']) ? now() : null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Assign ticket to self or another agent
     */
    public function supportAgentAssign(Request $request, int $ticket)
    {
        $request->validate([
            'source' => 'required|in:member,investor',
            'assigned_to' => 'nullable|integer', // This should be a user_id
        ]);

        $employee = $this->getEmployee();
        // assigned_to references users.id, not employees.id
        $assignedTo = $request->input('assigned_to', $employee->user_id);

        // All tickets use the same model now (SupportTicketModel with source field)
        $ticketModel = \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::findOrFail($ticket);
        $ticketModel->update(['assigned_to' => $assignedTo]);

        return response()->json(['success' => true]);
    }

    /**
     * Get support agent statistics
     */
    public function supportAgentStats()
    {
        $employee = $this->getEmployee();

        $stats = [
            'tickets_handled_today' => \App\Infrastructure\Persistence\Eloquent\Support\TicketCommentModel::where('author_id', $employee->user_id)
                ->where('author_type', 'support')
                ->whereDate('created_at', today())
                ->distinct('ticket_id')
                ->count('ticket_id'),
            'tickets_resolved_this_week' => \App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel::where('closed_by', $employee->user_id)
                ->whereBetween('closed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'avg_response_time' => '1.5 hours', // Placeholder
            'satisfaction_rating' => 4.5, // Placeholder
        ];

        return response()->json($stats);
    }
}
