<?php

namespace App\Domain\CMS\Operations\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\TaskModel;
use App\Infrastructure\Persistence\Eloquent\CMS\UserProductivityMetricModel;
use App\Infrastructure\Persistence\Eloquent\CMS\TaskCompletionTrendModel;
use App\Infrastructure\Persistence\Eloquent\CMS\TaskTimeEntryModel;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get productivity metrics for a user
     */
    public function getUserProductivityMetrics(int $companyId, int $userId, string $startDate, string $endDate): array
    {
        $metrics = UserProductivityMetricModel::where('company_id', $companyId)
            ->where('user_id', $userId)
            ->whereBetween('metric_date', [$startDate, $endDate])
            ->orderBy('metric_date')
            ->get();

        $summary = [
            'total_tasks_completed' => $metrics->sum('tasks_completed'),
            'total_hours_logged' => $metrics->sum('hours_logged'),
            'average_efficiency' => $metrics->avg('efficiency_percentage'),
            'on_time_rate' => $metrics->sum('on_time_completions') / max($metrics->sum('tasks_completed'), 1) * 100,
        ];

        return [
            'daily_metrics' => $metrics->toArray(),
            'summary' => $summary,
        ];
    }

    /**
     * Get task completion trends
     */
    public function getCompletionTrends(int $companyId, string $periodType, string $startDate, string $endDate): array
    {
        $trends = TaskCompletionTrendModel::where('company_id', $companyId)
            ->where('period_type', $periodType)
            ->whereBetween('trend_date', [$startDate, $endDate])
            ->orderBy('trend_date')
            ->get();

        return [
            'trends' => $trends->toArray(),
            'summary' => [
                'total_created' => $trends->sum('tasks_created'),
                'total_completed' => $trends->sum('tasks_completed'),
                'total_overdue' => $trends->sum('tasks_overdue'),
                'average_completion_rate' => $trends->avg('completion_rate_percentage'),
            ],
        ];
    }

    /**
     * Calculate and store daily productivity metrics
     */
    public function calculateDailyProductivityMetrics(int $companyId, string $date): void
    {
        $users = \App\Models\User::whereHas('cmsUsers', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->get();

        foreach ($users as $user) {
            $tasksCompleted = TaskModel::where('company_id', $companyId)
                ->where('assigned_to', $user->id)
                ->whereDate('completed_at', $date)
                ->get();

            $tasksStarted = TaskModel::where('company_id', $companyId)
                ->where('assigned_to', $user->id)
                ->whereDate('started_at', $date)
                ->count();

            $timeEntries = TaskTimeEntryModel::whereHas('task', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->where('user_id', $user->id)
                ->whereDate('started_at', $date)
                ->get();

            $hoursLogged = $timeEntries->sum('hours') ?? 0;
            $estimatedHours = $tasksCompleted->sum('estimated_hours') ?? 0;
            $efficiency = $estimatedHours > 0 ? ($estimatedHours / $hoursLogged) * 100 : null;

            $onTimeCompletions = $tasksCompleted->filter(function ($task) {
                return $task->due_date && $task->completed_at->lte($task->due_date);
            })->count();

            $lateCompletions = $tasksCompleted->count() - $onTimeCompletions;

            $avgDuration = $tasksCompleted->count() > 0
                ? $tasksCompleted->avg(fn($t) => $t->started_at && $t->completed_at ? $t->started_at->diffInHours($t->completed_at) : 0)
                : null;

            UserProductivityMetricModel::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'metric_date' => $date,
                ],
                [
                    'tasks_completed' => $tasksCompleted->count(),
                    'tasks_started' => $tasksStarted,
                    'hours_logged' => $hoursLogged,
                    'estimated_hours' => $estimatedHours,
                    'efficiency_percentage' => $efficiency,
                    'on_time_completions' => $onTimeCompletions,
                    'late_completions' => $lateCompletions,
                    'average_task_duration_hours' => $avgDuration,
                ]
            );
        }
    }

    /**
     * Calculate and store completion trends
     */
    public function calculateCompletionTrends(int $companyId, string $date, string $periodType): void
    {
        $tasksCreated = TaskModel::where('company_id', $companyId)
            ->whereDate('created_at', $date)
            ->count();

        $tasksCompleted = TaskModel::where('company_id', $companyId)
            ->whereDate('completed_at', $date)
            ->count();

        $tasksOverdue = TaskModel::where('company_id', $companyId)
            ->where('status', '!=', 'completed')
            ->whereDate('due_date', '<', $date)
            ->count();

        $completedTasks = TaskModel::where('company_id', $companyId)
            ->whereDate('completed_at', $date)
            ->get();

        $avgCompletionTime = $completedTasks->count() > 0
            ? $completedTasks->avg(fn($t) => $t->started_at && $t->completed_at ? $t->started_at->diffInHours($t->completed_at) : 0)
            : null;

        $completionRate = $tasksCreated > 0 ? ($tasksCompleted / $tasksCreated) * 100 : null;

        TaskCompletionTrendModel::updateOrCreate(
            [
                'company_id' => $companyId,
                'trend_date' => $date,
                'period_type' => $periodType,
            ],
            [
                'tasks_created' => $tasksCreated,
                'tasks_completed' => $tasksCompleted,
                'tasks_overdue' => $tasksOverdue,
                'average_completion_time_hours' => $avgCompletionTime,
                'completion_rate_percentage' => $completionRate,
            ]
        );
    }

    /**
     * Get Gantt chart data
     */
    public function getGanttChartData(int $companyId, ?int $workflowId = null): array
    {
        $query = TaskModel::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'in_progress', 'completed'])
            ->with(['assignedUser', 'workflowStage', 'dependencies']);

        if ($workflowId) {
            $query->where('workflow_id', $workflowId);
        }

        $tasks = $query->get();

        return $tasks->map(function ($task) {
            $start = $task->scheduled_start ?? $task->started_at ?? $task->created_at;
            $end = $task->scheduled_end ?? $task->completed_at ?? $task->due_date;

            return [
                'id' => $task->id,
                'title' => $task->title,
                'start' => $start?->toDateTimeString(),
                'end' => $end?->toDateTimeString(),
                'progress' => $task->progress_percentage ?? 0,
                'assigned_to' => $task->assignedUser?->name,
                'status' => $task->status,
                'dependencies' => $task->dependencies->map(fn($d) => [
                    'task_id' => $d->depends_on_task_id,
                    'type' => $d->dependency_type,
                    'lag_days' => $d->lag_days,
                ])->toArray(),
            ];
        })->toArray();
    }

    /**
     * Get time estimation accuracy
     */
    public function getTimeEstimationAccuracy(int $companyId, int $userId = null): array
    {
        $query = TaskModel::where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereNotNull('estimated_hours')
            ->whereNotNull('actual_hours');

        if ($userId) {
            $query->where('assigned_to', $userId);
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            return [
                'accuracy_percentage' => null,
                'average_variance_hours' => null,
                'tasks_analyzed' => 0,
            ];
        }

        $totalVariance = 0;
        $accurateCount = 0;

        foreach ($tasks as $task) {
            $variance = abs($task->actual_hours - $task->estimated_hours);
            $totalVariance += $variance;

            // Consider accurate if within 20% of estimate
            $threshold = $task->estimated_hours * 0.2;
            if ($variance <= $threshold) {
                $accurateCount++;
            }
        }

        return [
            'accuracy_percentage' => round(($accurateCount / $tasks->count()) * 100, 2),
            'average_variance_hours' => round($totalVariance / $tasks->count(), 2),
            'tasks_analyzed' => $tasks->count(),
            'underestimated' => $tasks->filter(fn($t) => $t->actual_hours > $t->estimated_hours)->count(),
            'overestimated' => $tasks->filter(fn($t) => $t->actual_hours < $t->estimated_hours)->count(),
        ];
    }

    /**
     * Get team performance comparison
     */
    public function getTeamPerformanceComparison(int $companyId, string $startDate, string $endDate): array
    {
        $users = \App\Models\User::whereHas('cmsUsers', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->get();

        $performance = [];

        foreach ($users as $user) {
            $completedTasks = TaskModel::where('company_id', $companyId)
                ->where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->get();

            $timeEntries = TaskTimeEntryModel::whereHas('task', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->where('user_id', $user->id)
                ->whereBetween('started_at', [$startDate, $endDate])
                ->get();

            $onTimeCount = $completedTasks->filter(function ($task) {
                return $task->due_date && $task->completed_at->lte($task->due_date);
            })->count();

            $performance[] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'tasks_completed' => $completedTasks->count(),
                'hours_logged' => $timeEntries->sum('hours'),
                'on_time_rate' => $completedTasks->count() > 0 ? round(($onTimeCount / $completedTasks->count()) * 100, 2) : 0,
                'average_task_duration' => $completedTasks->count() > 0
                    ? round($completedTasks->avg(fn($t) => $t->started_at && $t->completed_at ? $t->started_at->diffInHours($t->completed_at) : 0), 2)
                    : 0,
            ];
        }

        // Sort by tasks completed
        usort($performance, fn($a, $b) => $b['tasks_completed'] <=> $a['tasks_completed']);

        return $performance;
    }
}
