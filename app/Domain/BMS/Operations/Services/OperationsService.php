<?php

namespace App\Domain\BMS\Operations\Services;

use App\Infrastructure\Persistence\Eloquent\BMS\TaskModel;
use App\Infrastructure\Persistence\Eloquent\BMS\TaskLogModel;
use App\Infrastructure\Persistence\Eloquent\BMS\WorkflowModel;
use App\Infrastructure\Persistence\Eloquent\BMS\WorkflowStageModel;
use Illuminate\Support\Facades\DB;

class OperationsService
{
    public function __construct(
        private TaskNotificationService $notificationService
    ) {}

    public function createTask(int $companyId, array $data): TaskModel
    {
        return DB::transaction(function () use ($companyId, $data) {
            // Generate task number
            $data['task_number'] = $this->generateTaskNumber($companyId);
            $data['company_id'] = $companyId;
            $data['created_by'] = auth()->id();

            // If workflow is specified, set to first stage
            if (isset($data['workflow_id'])) {
                $firstStage = WorkflowStageModel::where('workflow_id', $data['workflow_id'])
                    ->orderBy('sequence_order')
                    ->first();
                
                if ($firstStage) {
                    $data['workflow_stage_id'] = $firstStage->id;
                }
            }

            $task = TaskModel::create($data);

            // Log creation
            $task->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'note' => 'Task created',
            ]);

            // Send notification if assigned
            if (isset($data['assigned_to'])) {
                $this->notificationService->notifyTaskAssigned($task);
            }

            return $task->load(['workflow', 'workflowStage', 'assignedUser', 'creator']);
        });
    }

    public function updateTask(int $taskId, array $data): TaskModel
    {
        return DB::transaction(function () use ($taskId, $data) {
            $task = TaskModel::findOrFail($taskId);
            $oldData = $task->toArray();

            $task->update($data);

            // Log the update
            $changes = array_diff_assoc($data, $oldData);
            if (!empty($changes)) {
                $task->logs()->create([
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'note' => 'Task updated',
                    'changes' => $changes,
                ]);
            }

            return $task->fresh(['workflow', 'workflowStage', 'assignedUser']);
        });
    }

    public function startTask(int $taskId): TaskModel
    {
        return DB::transaction(function () use ($taskId) {
            $task = TaskModel::findOrFail($taskId);

            $task->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            $task->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'started',
                'note' => 'Task started',
            ]);

            return $task->fresh();
        });
    }

    public function completeTask(int $taskId, ?float $actualHours = null): TaskModel
    {
        return DB::transaction(function () use ($taskId, $actualHours) {
            $task = TaskModel::findOrFail($taskId);

            $updateData = [
                'status' => 'completed',
                'completed_at' => now(),
            ];

            if ($actualHours !== null) {
                $updateData['actual_hours'] = $actualHours;
            }

            $task->update($updateData);

            $task->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'completed',
                'note' => 'Task completed',
            ]);

            // Send notification
            $this->notificationService->notifyTaskCompleted($task);

            return $task->fresh();
        });
    }

    public function blockTask(int $taskId, string $reason): TaskModel
    {
        return DB::transaction(function () use ($taskId, $reason) {
            $task = TaskModel::findOrFail($taskId);

            $task->update(['status' => 'blocked']);

            $task->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'blocked',
                'note' => $reason,
            ]);

            // Send notification
            $this->notificationService->notifyTaskBlocked($task, $reason);

            return $task->fresh();
        });
    }

    public function unblockTask(int $taskId): TaskModel
    {
        return DB::transaction(function () use ($taskId) {
            $task = TaskModel::findOrFail($taskId);

            $task->update(['status' => 'in_progress']);

            $task->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'unblocked',
                'note' => 'Task unblocked',
            ]);

            return $task->fresh();
        });
    }

    public function reassignTask(int $taskId, int $newAssigneeId): TaskModel
    {
        return DB::transaction(function () use ($taskId, $newAssigneeId) {
            $task = TaskModel::findOrFail($taskId);
            $oldAssignee = $task->assignedUser;
            $oldAssigneeName = $oldAssignee?->name ?? 'Unassigned';

            $task->update(['assigned_to' => $newAssigneeId]);

            $newAssignee = $task->fresh()->assignedUser->name;

            $task->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'reassigned',
                'note' => "Reassigned from {$oldAssigneeName} to {$newAssignee}",
                'changes' => [
                    'old_assignee' => $oldAssigneeName,
                    'new_assignee' => $newAssignee,
                ],
            ]);

            // Send notifications
            if ($oldAssignee) {
                $this->notificationService->notifyTaskReassigned($task, $oldAssignee);
            } else {
                $this->notificationService->notifyTaskAssigned($task);
            }

            return $task->fresh(['assignedUser']);
        });
    }

    public function getTaskStatistics(int $companyId): array
    {
        $tasks = TaskModel::where('company_id', $companyId);

        return [
            'total' => $tasks->count(),
            'active' => $tasks->clone()->active()->count(),
            'completed' => $tasks->clone()->where('status', 'completed')->count(),
            'overdue' => $tasks->clone()->overdue()->count(),
            'due_today' => $tasks->clone()->dueToday()->count(),
            'blocked' => $tasks->clone()->where('status', 'blocked')->count(),
            'by_priority' => [
                'urgent' => $tasks->clone()->where('priority', 'urgent')->active()->count(),
                'high' => $tasks->clone()->where('priority', 'high')->active()->count(),
                'medium' => $tasks->clone()->where('priority', 'medium')->active()->count(),
                'low' => $tasks->clone()->where('priority', 'low')->active()->count(),
            ],
            'completion_rate' => $this->calculateCompletionRate($companyId),
        ];
    }

    public function getWorkloadByUser(int $companyId): array
    {
        return TaskModel::where('company_id', $companyId)
            ->active()
            ->with('assignedUser')
            ->get()
            ->groupBy('assigned_to')
            ->map(function ($tasks, $userId) {
                $user = $tasks->first()->assignedUser;
                return [
                    'user_id' => $userId,
                    'user_name' => $user?->name ?? 'Unassigned',
                    'task_count' => $tasks->count(),
                    'total_hours' => $tasks->sum('estimated_hours'),
                    'overdue_count' => $tasks->filter->isOverdue()->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function detectBottlenecks(int $companyId): array
    {
        $bottlenecks = [];

        $workflows = WorkflowModel::where('company_id', $companyId)
            ->where('is_active', true)
            ->with('stages')
            ->get();

        foreach ($workflows as $workflow) {
            foreach ($workflow->stages as $stage) {
                $tasksInStage = TaskModel::where('workflow_stage_id', $stage->id)
                    ->where('status', 'in_progress')
                    ->get();

                if ($tasksInStage->count() > 5) { // Threshold
                    $avgDuration = $this->calculateAverageStageDuration($stage->id);
                    
                    $bottlenecks[] = [
                        'workflow_name' => $workflow->name,
                        'stage_name' => $stage->name,
                        'tasks_count' => $tasksInStage->count(),
                        'avg_duration_days' => $avgDuration,
                        'severity' => $this->calculateBottleneckSeverity($tasksInStage->count(), $avgDuration),
                    ];
                }
            }
        }

        return $bottlenecks;
    }

    private function generateTaskNumber(int $companyId): string
    {
        $prefix = 'TSK';
        $date = now()->format('Ymd');
        
        $lastTask = TaskModel::where('company_id', $companyId)
            ->where('task_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTask) {
            $lastNumber = (int) substr($lastTask->task_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $newNumber);
    }

    private function calculateCompletionRate(int $companyId): float
    {
        $total = TaskModel::where('company_id', $companyId)->count();
        
        if ($total === 0) {
            return 0;
        }

        $completed = TaskModel::where('company_id', $companyId)
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    private function calculateAverageStageDuration(int $stageId): float
    {
        $logs = TaskLogModel::whereHas('task', function ($query) use ($stageId) {
                $query->where('workflow_stage_id', $stageId);
            })
            ->where('action', 'stage_changed')
            ->get();

        if ($logs->isEmpty()) {
            return 0;
        }

        // Simplified calculation - in production, track stage entry/exit times
        return 3.5; // Placeholder
    }

    private function calculateBottleneckSeverity(int $taskCount, float $avgDuration): string
    {
        if ($taskCount > 15 || $avgDuration > 7) {
            return 'critical';
        } elseif ($taskCount > 10 || $avgDuration > 5) {
            return 'high';
        } elseif ($taskCount > 5 || $avgDuration > 3) {
            return 'medium';
        }
        
        return 'low';
    }

    public function enableOperationsModule(int $companyId): void
    {
        DB::transaction(function () use ($companyId) {
            // Create default workflows for the company
            $this->createDefaultWorkflowsForCompany($companyId);
        });
    }

    private function createDefaultWorkflowsForCompany(int $companyId): void
    {
        $workflows = [
            [
                'name' => 'General Task Workflow',
                'description' => 'Standard workflow for general tasks',
                'workflow_type' => 'general',
                'is_default' => true,
                'stages' => [
                    ['name' => 'To Do', 'color' => '#6b7280'],
                    ['name' => 'In Progress', 'color' => '#3b82f6'],
                    ['name' => 'Review', 'color' => '#f59e0b', 'requires_approval' => true],
                    ['name' => 'Done', 'color' => '#10b981'],
                ],
            ],
        ];

        foreach ($workflows as $workflowData) {
            $stages = $workflowData['stages'];
            unset($workflowData['stages']);
            
            $workflowData['company_id'] = $companyId;
            $workflow = WorkflowModel::create($workflowData);
            
            foreach ($stages as $index => $stageData) {
                $stageData['workflow_id'] = $workflow->id;
                $stageData['sequence_order'] = $index + 1;
                $stageData['requires_approval'] = $stageData['requires_approval'] ?? false;
                WorkflowStageModel::create($stageData);
            }
        }
    }
}
