<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Employee\Entities\Task;
use App\Domain\Employee\ValueObjects\TaskId;
use App\Domain\Employee\ValueObjects\TaskPriority;
use App\Domain\Employee\ValueObjects\TaskStatus;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Repositories\TaskRepositoryInterface;
use App\Models\EmployeeTask;
use DateTimeImmutable;
use Illuminate\Support\Collection;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function findById(TaskId $id): ?Task
    {
        $model = EmployeeTask::find($id->toInt());
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        $query = EmployeeTask::forEmployee($employeeId->toInt())
            ->with(['assigner', 'department']);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->orderByRaw("FIELD(status, 'in_progress', 'todo', 'review', 'completed', 'cancelled')")
            ->orderBy('due_date')
            ->get();
    }

    public function findByStatus(EmployeeId $employeeId, TaskStatus $status): Collection
    {
        return EmployeeTask::forEmployee($employeeId->toInt())
            ->byStatus($status->getValue())
            ->orderBy('due_date')
            ->get();
    }

    public function findOverdue(EmployeeId $employeeId): Collection
    {
        return EmployeeTask::forEmployee($employeeId->toInt())
            ->overdue()
            ->orderBy('due_date')
            ->get();
    }

    public function findUpcoming(EmployeeId $employeeId, int $days = 7): Collection
    {
        return EmployeeTask::forEmployee($employeeId->toInt())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays($days))
            ->orderBy('due_date')
            ->get();
    }

    public function save(Task $task): bool
    {
        $data = [
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'assigned_to' => $task->getAssignedTo()->toInt(),
            'assigned_by' => $task->getAssignedBy()?->toInt(),
            'department_id' => $task->getDepartmentId(),
            'priority' => $task->getPriority()->getValue(),
            'status' => $task->getStatus()->getValue(),
            'due_date' => $task->getDueDate()?->format('Y-m-d'),
            'started_at' => $task->getStartedAt()?->format('Y-m-d H:i:s'),
            'completed_at' => $task->getCompletedAt()?->format('Y-m-d H:i:s'),
            'estimated_hours' => $task->getEstimatedHours(),
            'actual_hours' => $task->getActualHours(),
            'tags' => $task->getTags(),
            'notes' => $task->getNotes(),
        ];

        $id = $task->getId()->toInt();
        
        if ($id && EmployeeTask::find($id)) {
            return EmployeeTask::where('id', $id)->update($data) > 0;
        }

        $model = EmployeeTask::create($data);
        return $model->exists;
    }

    public function delete(TaskId $id): bool
    {
        return EmployeeTask::destroy($id->toInt()) > 0;
    }

    public function getStatusCounts(EmployeeId $employeeId): array
    {
        $empId = $employeeId->toInt();
        
        $total = EmployeeTask::forEmployee($empId)->count();
        
        return [
            'total' => $total,
            'all' => $total, // Alias for frontend compatibility
            'todo' => EmployeeTask::forEmployee($empId)->byStatus('todo')->count(),
            'in_progress' => EmployeeTask::forEmployee($empId)->byStatus('in_progress')->count(),
            'review' => EmployeeTask::forEmployee($empId)->byStatus('review')->count(),
            'completed' => EmployeeTask::forEmployee($empId)->byStatus('completed')->count(),
            'overdue' => EmployeeTask::forEmployee($empId)->overdue()->count(),
        ];
    }

    public function getTasksByStatusGrouped(EmployeeId $employeeId): array
    {
        return EmployeeTask::forEmployee($employeeId->toInt())
            ->with(['assigner'])
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->groupBy('status')
            ->toArray();
    }

    public function findAssignedByEmployee(EmployeeId $assignerId): Collection
    {
        return EmployeeTask::where('assigned_by', $assignerId->toInt())
            ->with(['assignee', 'department'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCompletionStats(EmployeeId $employeeId, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        $query = EmployeeTask::forEmployee($employeeId->toInt());

        if ($from) {
            $query->where('created_at', '>=', $from->format('Y-m-d'));
        }

        if ($to) {
            $query->where('created_at', '<=', $to->format('Y-m-d'));
        }

        $total = $query->count();
        $completed = (clone $query)->byStatus('completed')->count();
        
        $completedOnTime = (clone $query)
            ->byStatus('completed')
            ->whereColumn('completed_at', '<=', 'due_date')
            ->count();

        $avgCompletionTime = (float) ((clone $query)
            ->byStatus('completed')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as avg_hours')
            ->value('avg_hours') ?? 0);

        return [
            'total' => $total,
            'completed' => $completed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'on_time_rate' => $completed > 0 ? round(($completedOnTime / $completed) * 100, 2) : 0,
            'average_completion_time' => round($avgCompletionTime, 2),
        ];
    }

    private function toDomainEntity(EmployeeTask $model): Task
    {
        // Note: This is a simplified conversion. In a full implementation,
        // you'd reconstruct the full domain entity with all its state.
        return Task::create(
            $model->title,
            EmployeeId::fromInt($model->assigned_to),
            TaskPriority::fromString($model->priority),
            $model->description,
            $model->assigned_by ? EmployeeId::fromInt($model->assigned_by) : null,
            $model->department_id,
            $model->due_date ? new DateTimeImmutable($model->due_date->format('Y-m-d')) : null,
            $model->estimated_hours,
            $model->tags ?? []
        );
    }
}
