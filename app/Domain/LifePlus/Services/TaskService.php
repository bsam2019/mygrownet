<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusTaskModel;
use Carbon\Carbon;

class TaskService
{
    public function getTasks(int $userId, array $filters = []): array
    {
        $query = LifePlusTaskModel::where('user_id', $userId);

        if (isset($filters['is_completed'])) {
            $query->where('is_completed', $filters['is_completed']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['due_date'])) {
            $query->whereDate('due_date', $filters['due_date']);
        }

        if (!empty($filters['today'])) {
            $query->whereDate('due_date', now()->toDateString());
        }

        if (!empty($filters['upcoming'])) {
            $query->where('due_date', '>', now())
                  ->where('due_date', '<=', now()->addDays(7));
        }

        if (!empty($filters['overdue'])) {
            $query->where('is_completed', false)
                  ->whereDate('due_date', '<', now()->toDateString());
        }

        return $query->orderBy('is_completed')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->limit($filters['limit'] ?? 100)
            ->get()
            ->map(fn($t) => $this->mapTask($t))
            ->toArray();
    }

    public function getTodayTasks(int $userId): array
    {
        return $this->getTasks($userId, ['today' => true, 'is_completed' => false]);
    }

    public function createTask(int $userId, array $data): array
    {
        $task = LifePlusTaskModel::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => $data['due_date'] ?? null,
            'due_time' => $data['due_time'] ?? null,
            'is_completed' => false,
            'is_synced' => $data['is_synced'] ?? true,
            'local_id' => $data['local_id'] ?? null,
        ]);

        return $this->mapTask($task);
    }

    public function updateTask(int $id, int $userId, array $data): ?array
    {
        $task = LifePlusTaskModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$task) return null;

        $task->update($data);
        return $this->mapTask($task->fresh());
    }

    public function toggleTask(int $id, int $userId): ?array
    {
        $task = LifePlusTaskModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$task) return null;

        $task->update([
            'is_completed' => !$task->is_completed,
            'completed_at' => !$task->is_completed ? now() : null,
        ]);

        return $this->mapTask($task->fresh());
    }

    public function deleteTask(int $id, int $userId): bool
    {
        return LifePlusTaskModel::where('id', $id)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function syncTasks(int $userId, array $tasks): array
    {
        $synced = [];
        foreach ($tasks as $task) {
            if (!empty($task['local_id'])) {
                $existing = LifePlusTaskModel::where('user_id', $userId)
                    ->where('local_id', $task['local_id'])
                    ->first();

                if ($existing) {
                    $existing->update($task);
                    $synced[] = $this->mapTask($existing->fresh());
                } else {
                    $synced[] = $this->createTask($userId, $task);
                }
            } else {
                $synced[] = $this->createTask($userId, $task);
            }
        }
        return $synced;
    }

    public function getStats(int $userId): array
    {
        $today = now()->toDateString();
        
        $total = LifePlusTaskModel::where('user_id', $userId)->count();
        $completed = LifePlusTaskModel::where('user_id', $userId)->where('is_completed', true)->count();
        $pending = $total - $completed;
        $todayTasks = LifePlusTaskModel::where('user_id', $userId)
            ->whereDate('due_date', $today)
            ->where('is_completed', false)
            ->count();
        $overdue = LifePlusTaskModel::where('user_id', $userId)
            ->where('is_completed', false)
            ->whereDate('due_date', '<', $today)
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'today' => $todayTasks,
            'overdue' => $overdue,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    public function getTasksForMonth(int $userId, string $month): array
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return LifePlusTaskModel::where('user_id', $userId)
            ->whereBetween('due_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->get()
            ->map(fn($t) => $this->mapTask($t))
            ->toArray();
    }

    private function mapTask($task): array
    {
        $isOverdue = !$task->is_completed && $task->due_date && $task->due_date->isPast();

        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'priority_color' => match($task->priority) {
                'high' => '#dc2626',
                'medium' => '#f59e0b',
                default => '#10b981',
            },
            'due_date' => $task->due_date?->format('Y-m-d'),
            'due_time' => $task->due_time,
            'formatted_due' => $task->due_date?->format('M d'),
            'is_completed' => $task->is_completed,
            'completed_at' => $task->completed_at?->toISOString(),
            'is_overdue' => $isOverdue,
            'is_today' => $task->due_date?->isToday() ?? false,
            'is_synced' => $task->is_synced,
            'local_id' => $task->local_id,
        ];
    }
}
