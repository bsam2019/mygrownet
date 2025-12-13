<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\GrowBiz\Entities\PersonalTodo;
use App\Domain\GrowBiz\Repositories\PersonalTodoRepositoryInterface;
use App\Domain\GrowBiz\ValueObjects\TodoId;
use App\Domain\GrowBiz\ValueObjects\TodoStatus;
use App\Domain\GrowBiz\ValueObjects\TodoPriority;
use App\Infrastructure\Persistence\Eloquent\PersonalTodoModel;
use DateTimeImmutable;

class EloquentPersonalTodoRepository implements PersonalTodoRepositoryInterface
{
    public function findById(TodoId $id): ?PersonalTodo
    {
        $model = PersonalTodoModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(int $userId): array
    {
        $models = PersonalTodoModel::forUser($userId)
            ->parentTodos()
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findByUserIdAndStatus(int $userId, TodoStatus $status): array
    {
        $models = PersonalTodoModel::forUser($userId)
            ->withStatus($status->value())
            ->parentTodos()
            ->orderBy('sort_order')
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findByUserIdWithFilters(int $userId, array $filters): array
    {
        $query = PersonalTodoModel::forUser($userId)->parentTodos();

        if (!empty($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['due_date'])) {
            match ($filters['due_date']) {
                'today' => $query->dueToday(),
                'week' => $query->dueThisWeek(),
                'overdue' => $query->overdue(),
                default => null,
            };
        }

        $models = $query->orderBy('sort_order')->orderBy('due_date')->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findTodayTodos(int $userId): array
    {
        $models = PersonalTodoModel::forUser($userId)
            ->dueToday()
            ->pending()
            ->parentTodos()
            ->orderBy('priority', 'desc')
            ->orderBy('due_time')
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findUpcomingTodos(int $userId, int $days = 7): array
    {
        $models = PersonalTodoModel::forUser($userId)
            ->whereBetween('due_date', [
                now()->addDay()->startOfDay(),
                now()->addDays($days)->endOfDay()
            ])
            ->pending()
            ->parentTodos()
            ->orderBy('due_date')
            ->orderBy('priority', 'desc')
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findOverdueTodos(int $userId): array
    {
        $models = PersonalTodoModel::forUser($userId)
            ->overdue()
            ->parentTodos()
            ->orderBy('due_date')
            ->orderBy('priority', 'desc')
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findCompletedTodos(int $userId, int $limit = 50): array
    {
        $models = PersonalTodoModel::forUser($userId)
            ->completed()
            ->parentTodos()
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findByCategory(int $userId, string $category): array
    {
        $models = PersonalTodoModel::forUser($userId)
            ->byCategory($category)
            ->parentTodos()
            ->orderBy('sort_order')
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findSubtasks(int $parentId): array
    {
        $models = PersonalTodoModel::where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function getCategories(int $userId): array
    {
        return PersonalTodoModel::forUser($userId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->all();
    }

    public function getStatistics(int $userId): array
    {
        $todos = PersonalTodoModel::forUser($userId)->parentTodos()->get();
        
        $total = $todos->count();
        $pending = $todos->where('status', 'pending')->count();
        $inProgress = $todos->where('status', 'in_progress')->count();
        $completed = $todos->where('status', 'completed')->count();
        $overdue = $todos->filter(function ($todo) {
            return $todo->due_date && 
                   $todo->due_date < now()->startOfDay() && 
                   $todo->status !== 'completed';
        })->count();
        $dueToday = $todos->filter(function ($todo) {
            return $todo->due_date && 
                   $todo->due_date->format('Y-m-d') === now()->format('Y-m-d') &&
                   $todo->status !== 'completed';
        })->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'overdue' => $overdue,
            'due_today' => $dueToday,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }

    public function save(PersonalTodo $todo): PersonalTodo
    {
        $data = [
            'user_id' => $todo->getUserId(),
            'title' => $todo->getTitle(),
            'description' => $todo->getDescription(),
            'due_date' => $todo->getDueDate()?->format('Y-m-d'),
            'due_time' => $todo->getDueTime(),
            'priority' => $todo->getPriority()->value(),
            'status' => $todo->getStatus()->value(),
            'category' => $todo->getCategory(),
            'tags' => $todo->getTags(),
            'is_recurring' => $todo->isRecurring(),
            'recurrence_pattern' => $todo->getRecurrencePattern(),
            'parent_id' => $todo->getParentId(),
            'sort_order' => $todo->getSortOrder(),
            'completed_at' => $todo->getCompletedAt()?->format('Y-m-d H:i:s'),
        ];

        if ($todo->id() === 0) {
            $model = PersonalTodoModel::create($data);
        } else {
            $model = PersonalTodoModel::find($todo->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(TodoId $id): void
    {
        PersonalTodoModel::destroy($id->toInt());
    }

    public function updateSortOrder(int $userId, array $sortedIds): void
    {
        foreach ($sortedIds as $index => $id) {
            PersonalTodoModel::where('id', $id)
                ->where('user_id', $userId)
                ->update(['sort_order' => $index]);
        }
    }

    private function toDomainEntity(PersonalTodoModel $model): PersonalTodo
    {
        return PersonalTodo::reconstitute(
            id: TodoId::fromInt($model->id),
            userId: $model->user_id,
            title: $model->title,
            description: $model->description,
            dueDate: $model->due_date ? new DateTimeImmutable($model->due_date->format('Y-m-d')) : null,
            dueTime: $model->due_time,
            priority: TodoPriority::fromString($model->priority),
            status: TodoStatus::fromString($model->status),
            category: $model->category,
            tags: $model->tags ?? [],
            isRecurring: $model->is_recurring,
            recurrencePattern: $model->recurrence_pattern,
            parentId: $model->parent_id,
            sortOrder: $model->sort_order,
            completedAt: $model->completed_at ? new DateTimeImmutable($model->completed_at->format('Y-m-d H:i:s')) : null,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s'))
        );
    }
}
