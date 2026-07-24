<?php

namespace App\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusTask;
use App\Domain\LifePlus\Exceptions\LifePlusException;
use App\Domain\LifePlus\Repositories\TaskRepositoryInterface;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepo,
    ) {}

    public function getTasks(int $userId, array $filters = []): array
    {
        $tasks = $this->taskRepo->findByUser($userId, $filters);
        return array_map(fn($t) => $this->mapTask($t), $tasks);
    }

    public function getTodayTasks(int $userId): array
    {
        return $this->getTasks($userId, ['today' => true, 'is_completed' => false]);
    }

    public function createTask(int $userId, array $data): array
    {
        $task = LifePlusTask::reconstitute([
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

        return $this->mapTask($this->taskRepo->save($task));
    }

    public function updateTask(int $id, int $userId, array $data): ?array
    {
        $task = $this->taskRepo->findById($id);
        if (!$task || $task->userId !== $userId) return null;

        $merged = array_merge($task->toArray(), $data);
        $saved = $this->taskRepo->save(LifePlusTask::reconstitute($merged));
        return $this->mapTask($saved);
    }

    public function toggleTask(int $id, int $userId): ?array
    {
        $task = $this->taskRepo->findById($id);
        if (!$task || $task->userId !== $userId) return null;

        $merged = $task->toArray();
        $merged['is_completed'] = !$task->isCompleted;
        $merged['completed_at'] = $merged['is_completed'] ? now()->toDateTimeString() : null;

        $saved = $this->taskRepo->save(LifePlusTask::reconstitute($merged));
        return $this->mapTask($saved);
    }

    public function deleteTask(int $id, int $userId): bool
    {
        $task = $this->taskRepo->findById($id);
        if (!$task || $task->userId !== $userId) return false;
        return $this->taskRepo->delete($id);
    }

    public function syncTasks(int $userId, array $tasks): array
    {
        $synced = [];
        foreach ($tasks as $task) {
            if (!empty($task['local_id'])) {
                $existing = $this->taskRepo->findByLocalId($userId, $task['local_id']);
                if ($existing) {
                    $synced[] = $this->updateTask($existing->id, $userId, $task);
                } else {
                    $synced[] = $this->createTask($userId, $task);
                }
            } else {
                $synced[] = $this->createTask($userId, $task);
            }
        }
        return array_filter($synced);
    }

    public function getStats(int $userId): array
    {
        return $this->taskRepo->getStats($userId);
    }

    public function getTasksForMonth(int $userId, string $month): array
    {
        return array_map(fn($t) => $this->mapTask($t), $this->taskRepo->getForMonth($userId, $month));
    }

    private function mapTask(LifePlusTask $task): array
    {
        $now = new \DateTimeImmutable();
        $isOverdue = !$task->isCompleted && $task->dueDate && $task->dueDate < $now;

        $priorityColor = match($task->priority) {
            'high' => '#dc2626',
            'medium' => '#f59e0b',
            default => '#10b981',
        };

        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'priority_color' => $priorityColor,
            'due_date' => $task->dueDate?->format('Y-m-d'),
            'due_time' => $task->dueTime,
            'formatted_due' => $task->dueDate?->format('M d'),
            'is_completed' => $task->isCompleted,
            'completed_at' => $task->completedAt?->format('Y-m-d\TH:i:s\Z'),
            'is_overdue' => $isOverdue,
            'is_today' => $task->dueDate?->format('Y-m-d') === $now->format('Y-m-d'),
            'is_synced' => $task->isSynced,
            'local_id' => $task->localId,
        ];
    }
}
