<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\GrowBiz\Entities\Task;
use App\Domain\GrowBiz\Repositories\TaskRepositoryInterface;
use App\Domain\GrowBiz\ValueObjects\TaskId;
use App\Domain\GrowBiz\ValueObjects\TaskPriority;
use App\Domain\GrowBiz\ValueObjects\TaskStatus;
use App\Infrastructure\Persistence\Eloquent\GrowBizTaskModel;
use DateTimeImmutable;

class GrowBizTaskRepository implements TaskRepositoryInterface
{
    public function findById(TaskId $id): ?Task
    {
        $model = GrowBizTaskModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByManagerId(int $managerId): array
    {
        return GrowBizTaskModel::forManager($managerId)
            ->orderBy('due_date')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByManagerIdAndStatus(int $managerId, TaskStatus $status): array
    {
        return GrowBizTaskModel::forManager($managerId)
            ->withStatus($status->getValue())
            ->orderBy('due_date')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findOverdueTasks(int $managerId): array
    {
        return GrowBizTaskModel::forManager($managerId)
            ->overdue()
            ->orderBy('due_date')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findDueToday(int $managerId): array
    {
        return GrowBizTaskModel::forManager($managerId)
            ->dueToday()
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }


    public function save(Task $task): Task
    {
        $data = [
            'manager_id' => $task->getManagerId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'due_date' => $task->getDueDate()?->format('Y-m-d'),
            'priority' => $task->getPriority()->getValue(),
            'status' => $task->getStatus()->getValue(),
            'category' => $task->getCategory(),
            'progress_percentage' => $task->getProgressPercentage(),
            'estimated_hours' => $task->getEstimatedHours(),
            'actual_hours' => $task->getActualHours(),
            'started_at' => $task->getStartedAt()?->format('Y-m-d H:i:s'),
            'completed_at' => $task->getCompletedAt()?->format('Y-m-d H:i:s'),
        ];

        if ($task->getId()->toInt() === 0) {
            $model = GrowBizTaskModel::create($data);
        } else {
            $model = GrowBizTaskModel::find($task->getId()->toInt());
            $model->update($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findByOwnerWithFilters(int $ownerId, array $filters): array
    {
        $query = GrowBizTaskModel::forManager($ownerId);

        if (!empty($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->whereHas('assignments', function ($q) use ($filters) {
                $q->where('employee_id', $filters['assigned_to']);
            });
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('due_date')
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findRecentByOwner(int $ownerId, int $limit): array
    {
        return GrowBizTaskModel::forManager($ownerId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findUpcomingDue(int $ownerId, int $limit): array
    {
        return GrowBizTaskModel::forManager($ownerId)
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('due_date')
            ->limit($limit)
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByEmployeeId(int $employeeId): array
    {
        return GrowBizTaskModel::whereHas('assignments', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })
            ->orderBy('due_date')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByAssignedEmployee(int $employeeId): array
    {
        return $this->findByEmployeeId($employeeId);
    }

    public function getTaskStatsByEmployee(int $employeeId): array
    {
        $tasks = GrowBizTaskModel::whereHas('assignments', function ($q) use ($employeeId) {
            $q->where('employee_id', $employeeId);
        })->get();

        $total = $tasks->count();
        $completed = $tasks->where('status', 'completed')->count();
        $inProgress = $tasks->where('status', 'in_progress')->count();
        $pending = $tasks->where('status', 'pending')->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'pending' => $pending,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    public function assignEmployees(TaskId $taskId, array $employeeIds): void
    {
        $task = GrowBizTaskModel::find($taskId->toInt());
        if ($task) {
            foreach ($employeeIds as $employeeId) {
                $task->assignments()->firstOrCreate([
                    'employee_id' => $employeeId,
                ], [
                    'assigned_at' => now(),
                ]);
            }
        }
    }

    public function syncEmployees(TaskId $taskId, array $employeeIds): void
    {
        $task = GrowBizTaskModel::find($taskId->toInt());
        if ($task) {
            $task->assignments()->whereNotIn('employee_id', $employeeIds)->delete();
            
            foreach ($employeeIds as $employeeId) {
                $task->assignments()->firstOrCreate([
                    'employee_id' => $employeeId,
                ], [
                    'assigned_at' => now(),
                ]);
            }
        }
    }

    public function addComment(int $taskId, int $userId, string $content): void
    {
        $task = GrowBizTaskModel::find($taskId);
        if ($task) {
            $task->comments()->create([
                'user_id' => $userId,
                'content' => $content,
            ]);
        }
    }

    public function getComments(int $taskId): array
    {
        $task = GrowBizTaskModel::find($taskId);
        if (!$task) {
            return [];
        }

        return $task->comments()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => $comment->user ? [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ] : null,
                    'created_at' => $comment->created_at->toISOString(),
                ];
            })
            ->toArray();
    }

    public function deleteComment(int $commentId, int $userId): void
    {
        \App\Infrastructure\Persistence\Eloquent\GrowBizTaskCommentModel::where('id', $commentId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function delete(TaskId $id): void
    {
        GrowBizTaskModel::destroy($id->toInt());
    }

    public function logTaskUpdate(
        int $taskId,
        int $userId,
        string $updateType,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?int $oldProgress = null,
        ?int $newProgress = null,
        ?float $hoursLogged = null,
        ?string $notes = null,
        ?int $employeeId = null
    ): void {
        $task = GrowBizTaskModel::find($taskId);
        if ($task) {
            $task->updates()->create([
                'user_id' => $userId,
                'employee_id' => $employeeId,
                'update_type' => $updateType,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_progress' => $oldProgress,
                'new_progress' => $newProgress,
                'hours_logged' => $hoursLogged,
                'notes' => $notes,
            ]);
        }
    }

    public function getTaskUpdates(int $taskId, ?string $updateType = null): array
    {
        $task = GrowBizTaskModel::find($taskId);
        if (!$task) {
            return [];
        }

        $query = $task->updates()->with(['user', 'employee'])->orderBy('created_at', 'desc');
        
        if ($updateType) {
            $query->where('update_type', $updateType);
        }

        return $query->get()->map(function ($update) {
            return [
                'id' => $update->id,
                'update_type' => $update->update_type,
                'formatted_type' => $update->formatted_type,
                'old_status' => $update->old_status,
                'new_status' => $update->new_status,
                'old_progress' => $update->old_progress,
                'new_progress' => $update->new_progress,
                'hours_logged' => $update->hours_logged,
                'notes' => $update->notes,
                'user' => $update->user ? [
                    'id' => $update->user->id,
                    'name' => $update->user->name,
                ] : null,
                'employee' => $update->employee ? [
                    'id' => $update->employee->id,
                    'name' => $update->employee->first_name . ' ' . $update->employee->last_name,
                ] : null,
                'created_at' => $update->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    public function getTotalTimeLogged(int $taskId): float
    {
        $task = GrowBizTaskModel::find($taskId);
        if (!$task) {
            return 0.0;
        }

        return (float) $task->updates()
            ->where('update_type', 'time_log')
            ->sum('hours_logged');
    }

    public function getTaskAssigneeUsers(int $taskId): array
    {
        $task = GrowBizTaskModel::find($taskId);
        if (!$task) {
            return [];
        }

        // Get employee IDs assigned to this task
        $employeeIds = $task->assignments()->pluck('employee_id')->toArray();
        
        if (empty($employeeIds)) {
            return [];
        }

        // Get user IDs from employees
        $userIds = \App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeModel::whereIn('id', $employeeIds)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->toArray();

        // Also include the task manager
        $userIds[] = $task->manager_id;
        $userIds = array_unique($userIds);

        return \App\Models\User::whereIn('id', $userIds)->get()->all();
    }

    private function toDomainEntity(GrowBizTaskModel $model): Task
    {
        return Task::reconstitute(
            id: TaskId::fromInt($model->id),
            managerId: $model->manager_id,
            title: $model->title,
            description: $model->description,
            dueDate: $model->due_date ? new DateTimeImmutable($model->due_date->toDateTimeString()) : null,
            priority: TaskPriority::fromString($model->priority),
            status: TaskStatus::fromString($model->status),
            category: $model->category,
            progressPercentage: $model->progress_percentage ?? 0,
            estimatedHours: $model->estimated_hours ? (float) $model->estimated_hours : null,
            actualHours: (float) ($model->actual_hours ?? 0),
            startedAt: $model->started_at ? new DateTimeImmutable($model->started_at->toDateTimeString()) : null,
            completedAt: $model->completed_at ? new DateTimeImmutable($model->completed_at->toDateTimeString()) : null,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString())
        );
    }
}
