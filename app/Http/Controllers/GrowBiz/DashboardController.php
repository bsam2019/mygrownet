<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Domain\GrowBiz\Services\TaskManagementService;
use App\Domain\GrowBiz\Services\EmployeeManagementService;
use App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel;
use App\Infrastructure\Persistence\Eloquent\GrowBizBusinessProfileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        private TaskManagementService $taskService,
        private EmployeeManagementService $employeeService
    ) {}

    /**
     * GrowBiz Dashboard - Routes to owner or employee dashboard based on user role
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check if user needs setup (neither owner nor employee)
        if (SetupController::needsSetup($user->id)) {
            return redirect()->route('growbiz.setup');
        }

        // Check if user is an employee (linked via invitation)
        $employeeRecord = GrowBizEmployeeModel::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($employeeRecord) {
            return $this->employeeDashboard($user, $employeeRecord);
        }

        // Default: Owner/Manager dashboard
        return $this->ownerDashboard($user);
    }

    /**
     * Owner/Manager Dashboard - Full management view
     */
    private function ownerDashboard($user)
    {
        try {
            $taskStats = $this->taskService->getTaskStatistics($user->id);
            $employeeStats = $this->employeeService->getEmployeeStatistics($user->id);
            $recentTasks = $this->taskService->getRecentTasks($user->id, 5);
            $upcomingTasks = $this->taskService->getUpcomingDueTasks($user->id, 5);
            $overdueTasks = $this->taskService->getOverdueTasks($user->id);

            // Convert Task entities to arrays for the frontend
            $recentTasksArray = array_map(fn($task) => $task->toArray(), $recentTasks);
            $upcomingTasksArray = array_map(fn($task) => $task->toArray(), $upcomingTasks);
            $overdueTasksArray = array_map(fn($task) => $task->toArray(), $overdueTasks);

            // Get business profile
            $businessProfile = GrowBizBusinessProfileModel::where('user_id', $user->id)->first();

            return Inertia::render('GrowBiz/Dashboard', [
                'userRole' => 'owner',
                'businessProfile' => $businessProfile ? [
                    'business_name' => $businessProfile->business_name,
                    'industry' => $businessProfile->industry,
                    'team_size' => $businessProfile->team_size,
                    'owner_title' => $businessProfile->owner_title,
                ] : null,
                'taskStats' => $taskStats,
                'employeeStats' => $employeeStats,
                'recentTasks' => $recentTasksArray,
                'upcomingTasks' => $upcomingTasksArray,
                'overdueTasks' => $overdueTasksArray,
            ]);
        } catch (Throwable $e) {
            Log::error('Owner dashboard data fetch failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return Inertia::render('GrowBiz/Dashboard', [
                'userRole' => 'owner',
                'taskStats' => $this->emptyTaskStats(),
                'employeeStats' => $this->emptyEmployeeStats(),
                'recentTasks' => [],
                'upcomingTasks' => [],
                'overdueTasks' => [],
                'error' => 'Unable to load some dashboard data. Please refresh the page.',
            ]);
        }
    }

    /**
     * Employee Dashboard - Limited view of assigned tasks only
     */
    private function employeeDashboard($user, $employeeRecord)
    {
        try {
            // Get tasks assigned to this employee
            $assignedTasks = $this->taskService->getTasksAssignedToEmployee($employeeRecord->id);
            
            // Calculate employee-specific stats
            $taskStats = $this->calculateEmployeeTaskStats($assignedTasks);
            
            // Separate tasks by status
            $myTasks = array_filter($assignedTasks, fn($task) => 
                in_array($task->status()->value(), ['pending', 'in_progress'])
            );
            $completedTasks = array_filter($assignedTasks, fn($task) => 
                $task->status()->value() === 'completed'
            );
            $overdueTasks = array_filter($assignedTasks, fn($task) => 
                $task->isOverdue() && $task->status()->value() !== 'completed'
            );

            // Convert to arrays
            $myTasksArray = array_values(array_map(fn($task) => $task->toArray(), $myTasks));
            $completedTasksArray = array_values(array_map(fn($task) => $task->toArray(), array_slice($completedTasks, 0, 5)));
            $overdueTasksArray = array_values(array_map(fn($task) => $task->toArray(), $overdueTasks));

            return Inertia::render('GrowBiz/EmployeeDashboard', [
                'userRole' => 'employee',
                'employee' => [
                    'id' => $employeeRecord->id,
                    'name' => $employeeRecord->name,
                    'position' => $employeeRecord->position,
                    'department' => $employeeRecord->department,
                    'manager_name' => $employeeRecord->manager?->name ?? 'Unknown',
                ],
                'taskStats' => $taskStats,
                'myTasks' => $myTasksArray,
                'completedTasks' => $completedTasksArray,
                'overdueTasks' => $overdueTasksArray,
            ]);
        } catch (Throwable $e) {
            Log::error('Employee dashboard data fetch failed', [
                'user_id' => $user->id,
                'employee_id' => $employeeRecord->id,
                'error' => $e->getMessage(),
            ]);

            return Inertia::render('GrowBiz/EmployeeDashboard', [
                'userRole' => 'employee',
                'employee' => [
                    'id' => $employeeRecord->id,
                    'name' => $employeeRecord->name,
                    'position' => $employeeRecord->position,
                    'department' => $employeeRecord->department,
                    'manager_name' => $employeeRecord->manager?->name ?? 'Unknown',
                ],
                'taskStats' => $this->emptyTaskStats(),
                'myTasks' => [],
                'completedTasks' => [],
                'overdueTasks' => [],
                'error' => 'Unable to load some dashboard data. Please refresh the page.',
            ]);
        }
    }

    private function calculateEmployeeTaskStats(array $tasks): array
    {
        $total = count($tasks);
        $pending = 0;
        $inProgress = 0;
        $completed = 0;
        $overdue = 0;

        foreach ($tasks as $task) {
            $status = $task->status()->value();
            
            if ($status === 'pending') $pending++;
            elseif ($status === 'in_progress') $inProgress++;
            elseif ($status === 'completed') $completed++;
            
            if ($task->isOverdue() && $status !== 'completed') {
                $overdue++;
            }
        }

        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'overdue' => $overdue,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    private function emptyTaskStats(): array
    {
        return [
            'total' => 0,
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'overdue' => 0,
            'completion_rate' => 0,
        ];
    }

    private function emptyEmployeeStats(): array
    {
        return [
            'total' => 0,
            'active' => 0,
            'inactive' => 0,
            'on_leave' => 0,
        ];
    }
}
