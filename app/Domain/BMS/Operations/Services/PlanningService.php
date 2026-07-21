<?php

namespace App\Domain\BMS\Operations\Services;

use App\Infrastructure\Persistence\Eloquent\BMS\TaskModel;
use App\Infrastructure\Persistence\Eloquent\BMS\WorkflowStageModel;
use App\Infrastructure\Persistence\Eloquent\BMS\TaskResourceModel;
use App\Infrastructure\Persistence\Eloquent\BMS\ResourceAvailabilityModel;
use App\Infrastructure\Persistence\Eloquent\BMS\PlanningScenarioModel;
use Illuminate\Support\Facades\DB;

class PlanningService
{
    /**
     * Get workload balancing data for all users
     */
    public function getWorkloadBalance(int $companyId): array
    {
        $users = \App\Models\User::whereHas('cmsUsers', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->get();

        $workloadData = [];

        foreach ($users as $user) {
            $activeTasks = TaskModel::where('company_id', $companyId)
                ->where('assigned_to', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->get();

            $totalHours = $activeTasks->sum('estimated_hours') ?? 0;
            $taskCount = $activeTasks->count();
            $overdueTasks = $activeTasks->filter(fn($t) => $t->due_date && $t->due_date->isPast())->count();

            // Calculate capacity (assume 40 hours per week)
            $weeklyCapacity = 40;
            $utilization = $weeklyCapacity > 0 ? ($totalHours / $weeklyCapacity) * 100 : 0;

            $workloadData[] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'task_count' => $taskCount,
                'total_hours' => round($totalHours, 2),
                'weekly_capacity' => $weeklyCapacity,
                'utilization_percentage' => round($utilization, 2),
                'overdue_count' => $overdueTasks,
                'status' => $this->getWorkloadStatus($utilization),
                'tasks' => $activeTasks->map(fn($t) => [
                    'id' => $t->id,
                    'title' => $t->title,
                    'estimated_hours' => $t->estimated_hours,
                    'due_date' => $t->due_date?->toDateString(),
                ]),
            ];
        }

        // Sort by utilization (highest first)
        usort($workloadData, fn($a, $b) => $b['utilization_percentage'] <=> $a['utilization_percentage']);

        return [
            'users' => $workloadData,
            'summary' => [
                'total_users' => count($workloadData),
                'overloaded_users' => count(array_filter($workloadData, fn($u) => $u['status'] === 'overloaded')),
                'balanced_users' => count(array_filter($workloadData, fn($u) => $u['status'] === 'balanced')),
                'underutilized_users' => count(array_filter($workloadData, fn($u) => $u['status'] === 'underutilized')),
                'average_utilization' => round(array_sum(array_column($workloadData, 'utilization_percentage')) / max(count($workloadData), 1), 2),
            ],
        ];
    }

    private function getWorkloadStatus(float $utilization): string
    {
        if ($utilization > 100) return 'overloaded';
        if ($utilization >= 70) return 'balanced';
        return 'underutilized';
    }

    /**
     * Get capacity forecast for upcoming weeks
     */
    public function getCapacityForecast(int $companyId, int $weeksAhead = 4): array
    {
        $forecast = [];
        $startDate = now()->startOfWeek();

        for ($week = 0; $week < $weeksAhead; $week++) {
            $weekStart = $startDate->copy()->addWeeks($week);
            $weekEnd = $weekStart->copy()->endOfWeek();

            $tasksInWeek = TaskModel::where('company_id', $companyId)
                ->where(function ($q) use ($weekStart, $weekEnd) {
                    $q->whereBetween('due_date', [$weekStart, $weekEnd])
                        ->orWhereBetween('scheduled_start', [$weekStart, $weekEnd]);
                })
                ->whereIn('status', ['pending', 'in_progress'])
                ->get();

            $demandHours = $tasksInWeek->sum('estimated_hours') ?? 0;
            
            // Calculate available capacity (users * 40 hours)
            $userCount = \App\Models\User::whereHas('cmsUsers', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->count();
            
            $capacityHours = $userCount * 40;
            $utilizationRate = $capacityHours > 0 ? ($demandHours / $capacityHours) * 100 : 0;

            $forecast[] = [
                'week_number' => $week + 1,
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                'demand_hours' => round($demandHours, 2),
                'available_capacity' => $capacityHours,
                'capacity_gap' => round($demandHours - $capacityHours, 2),
                'utilization_rate' => round($utilizationRate, 2),
                'status' => $utilizationRate > 100 ? 'overbooked' : ($utilizationRate > 80 ? 'high' : 'normal'),
                'task_count' => $tasksInWeek->count(),
            ];
        }

        return $forecast;
    }

    /**
     * Bulk reassign tasks
     */
    public function bulkReassignTasks(int $companyId, array $taskIds, int $newAssigneeId, int $userId): array
    {
        DB::beginTransaction();
        try {
            $tasks = TaskModel::where('company_id', $companyId)
                ->whereIn('id', $taskIds)
                ->get();

            $affected = 0;
            foreach ($tasks as $task) {
                $oldAssignee = $task->assigned_to;
                $task->update(['assigned_to' => $newAssigneeId]);
                
                // Log activity
                $task->logs()->create([
                    'user_id' => $userId,
                    'action' => 'reassigned',
                    'note' => "Task reassigned from user {$oldAssignee} to user {$newAssigneeId}",
                ]);
                
                $affected++;
            }

            // Log bulk operation
            \App\Infrastructure\Persistence\Eloquent\BMS\BulkOperationModel::create([
                'company_id' => $companyId,
                'user_id' => $userId,
                'operation_type' => 'reassign',
                'tasks_affected' => $affected,
                'operation_data' => [
                    'new_assignee_id' => $newAssigneeId,
                ],
                'task_ids' => $taskIds,
                'executed_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'tasks_affected' => $affected,
                'message' => "{$affected} tasks reassigned successfully",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Bulk update task priority
     */
    public function bulkUpdatePriority(int $companyId, array $taskIds, string $priority, int $userId): array
    {
        DB::beginTransaction();
        try {
            $affected = TaskModel::where('company_id', $companyId)
                ->whereIn('id', $taskIds)
                ->update(['priority' => $priority]);

            // Log bulk operation
            \App\Infrastructure\Persistence\Eloquent\BMS\BulkOperationModel::create([
                'company_id' => $companyId,
                'user_id' => $userId,
                'operation_type' => 'change_priority',
                'tasks_affected' => $affected,
                'operation_data' => ['new_priority' => $priority],
                'task_ids' => $taskIds,
                'executed_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'tasks_affected' => $affected,
                'message' => "{$affected} tasks updated successfully",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Bulk reschedule tasks
     */
    public function bulkRescheduleTasks(int $companyId, array $taskIds, string $newDueDate, int $userId): array
    {
        DB::beginTransaction();
        try {
            $affected = TaskModel::where('company_id', $companyId)
                ->whereIn('id', $taskIds)
                ->update(['due_date' => $newDueDate]);

            \App\Infrastructure\Persistence\Eloquent\BMS\BulkOperationModel::create([
                'company_id' => $companyId,
                'user_id' => $userId,
                'operation_type' => 'reschedule',
                'tasks_affected' => $affected,
                'operation_data' => ['new_due_date' => $newDueDate],
                'task_ids' => $taskIds,
                'executed_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'tasks_affected' => $affected,
                'message' => "{$affected} tasks rescheduled successfully",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create what-if scenario
     */
    public function createScenario(int $companyId, int $userId, array $data): array
    {
        // Capture current state
        $currentState = $this->captureCurrentState($companyId);
        
        // Apply changes (in memory, not to database)
        $simulatedState = $this->simulateChanges($companyId, $data['changes']);
        
        // Calculate metrics
        $metricsBefore = $this->calculateMetrics($currentState);
        $metricsAfter = $this->calculateMetrics($simulatedState);

        $scenario = PlanningScenarioModel::create([
            'company_id' => $companyId,
            'scenario_name' => $data['name'],
            'description' => $data['description'] ?? null,
            'scenario_type' => $data['type'] ?? 'custom',
            'changes_json' => $data['changes'],
            'original_state' => $currentState,
            'metrics_before' => $metricsBefore,
            'metrics_after' => $metricsAfter,
            'impact_analysis_json' => $this->analyzeImpact($metricsBefore, $metricsAfter),
            'created_by' => $userId,
        ]);

        return [
            'scenario_id' => $scenario->id,
            'metrics_before' => $metricsBefore,
            'metrics_after' => $metricsAfter,
            'impact' => $scenario->impact_analysis_json,
        ];
    }

    private function captureCurrentState(int $companyId): array
    {
        return [
            'tasks' => TaskModel::where('company_id', $companyId)
                ->whereIn('status', ['pending', 'in_progress'])
                ->get(['id', 'assigned_to', 'priority', 'due_date', 'estimated_hours'])
                ->toArray(),
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    private function simulateChanges(int $companyId, array $changes): array
    {
        $tasks = TaskModel::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->get();

        // Apply changes to collection (not database)
        foreach ($changes as $change) {
            if ($change['type'] === 'reassign') {
                foreach ($change['task_ids'] as $taskId) {
                    $task = $tasks->firstWhere('id', $taskId);
                    if ($task) {
                        $task->assigned_to = $change['new_assignee_id'];
                    }
                }
            }
        }

        return [
            'tasks' => $tasks->toArray(),
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    private function calculateMetrics(array $state): array
    {
        $tasks = collect($state['tasks']);
        
        $userWorkload = $tasks->groupBy('assigned_to')->map(function ($userTasks) {
            return [
                'task_count' => $userTasks->count(),
                'total_hours' => $userTasks->sum('estimated_hours'),
            ];
        });

        return [
            'total_tasks' => $tasks->count(),
            'total_hours' => $tasks->sum('estimated_hours'),
            'user_workload' => $userWorkload->toArray(),
            'max_user_hours' => $userWorkload->max('total_hours') ?? 0,
            'min_user_hours' => $userWorkload->min('total_hours') ?? 0,
            'average_user_hours' => $userWorkload->avg('total_hours') ?? 0,
        ];
    }

    private function analyzeImpact(array $before, array $after): array
    {
        return [
            'workload_balance_improvement' => $this->calculateBalanceImprovement($before, $after),
            'max_workload_change' => $after['max_user_hours'] - $before['max_user_hours'],
            'min_workload_change' => $after['min_user_hours'] - $before['min_user_hours'],
            'recommendation' => $this->generateRecommendation($before, $after),
        ];
    }

    private function calculateBalanceImprovement(array $before, array $after): float
    {
        $beforeStdDev = $this->calculateStdDev(array_column($before['user_workload'], 'total_hours'));
        $afterStdDev = $this->calculateStdDev(array_column($after['user_workload'], 'total_hours'));
        
        return round((($beforeStdDev - $afterStdDev) / max($beforeStdDev, 1)) * 100, 2);
    }

    private function calculateStdDev(array $values): float
    {
        $count = count($values);
        if ($count === 0) return 0;
        
        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / $count;
        
        return sqrt($variance);
    }

    private function generateRecommendation(array $before, array $after): string
    {
        $improvement = $this->calculateBalanceImprovement($before, $after);
        
        if ($improvement > 20) {
            return 'Highly recommended: This change significantly improves workload balance.';
        } elseif ($improvement > 10) {
            return 'Recommended: This change improves workload balance.';
        } elseif ($improvement > 0) {
            return 'Slight improvement: This change has a minor positive effect.';
        } else {
            return 'Not recommended: This change does not improve workload balance.';
        }
    }
}
