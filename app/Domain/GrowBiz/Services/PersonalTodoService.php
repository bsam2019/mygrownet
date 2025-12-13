<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Services;

use App\Domain\GrowBiz\Entities\PersonalTodo;
use App\Domain\GrowBiz\Exceptions\OperationFailedException;
use App\Domain\GrowBiz\Repositories\PersonalTodoRepositoryInterface;
use App\Domain\GrowBiz\ValueObjects\TodoId;
use App\Domain\GrowBiz\ValueObjects\TodoPriority;
use App\Domain\GrowBiz\ValueObjects\TodoStatus;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

class PersonalTodoService
{
    public function __construct(
        private PersonalTodoRepositoryInterface $todoRepository
    ) {}

    public function createTodo(
        int $userId,
        string $title,
        ?string $description = null,
        ?string $dueDate = null,
        ?string $dueTime = null,
        string $priority = 'medium',
        ?string $category = null,
        array $tags = [],
        bool $isRecurring = false,
        ?string $recurrencePattern = null,
        ?int $parentId = null
    ): PersonalTodo {
        try {
            $todo = PersonalTodo::create(
                userId: $userId,
                title: $title,
                description: $description,
                dueDate: $dueDate ? new DateTimeImmutable($dueDate) : null,
                dueTime: $dueTime,
                priority: TodoPriority::fromString($priority),
                category: $category,
                tags: $tags,
                isRecurring: $isRecurring,
                recurrencePattern: $recurrencePattern,
                parentId: $parentId
            );

            $savedTodo = $this->todoRepository->save($todo);

            Log::info('Personal todo created', [
                'todo_id' => $savedTodo->id(),
                'user_id' => $userId,
            ]);

            return $savedTodo;
        } catch (Throwable $e) {
            Log::error('Failed to create personal todo', [
                'user_id' => $userId,
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('create todo', $e->getMessage());
        }
    }

    public function updateTodo(int $todoId, int $userId, array $data): PersonalTodo
    {
        try {
            $todo = $this->todoRepository->findById(TodoId::fromInt($todoId));
            
            if (!$todo || $todo->getUserId() !== $userId) {
                throw new \RuntimeException('Todo not found or access denied');
            }

            $todo->update(
                title: $data['title'] ?? $todo->getTitle(),
                description: $data['description'] ?? $todo->getDescription(),
                dueDate: isset($data['due_date']) ? ($data['due_date'] ? new DateTimeImmutable($data['due_date']) : null) : $todo->getDueDate(),
                dueTime: $data['due_time'] ?? $todo->getDueTime(),
                priority: isset($data['priority']) ? TodoPriority::fromString($data['priority']) : $todo->getPriority(),
                category: $data['category'] ?? $todo->getCategory(),
                tags: $data['tags'] ?? $todo->getTags()
            );

            if (isset($data['status'])) {
                $todo->updateStatus(TodoStatus::fromString($data['status']));
            }

            $savedTodo = $this->todoRepository->save($todo);

            Log::info('Personal todo updated', ['todo_id' => $todoId]);

            return $savedTodo;
        } catch (Throwable $e) {
            Log::error('Failed to update personal todo', [
                'todo_id' => $todoId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('update todo', $e->getMessage());
        }
    }

    public function toggleComplete(int $todoId, int $userId): PersonalTodo
    {
        try {
            $todo = $this->todoRepository->findById(TodoId::fromInt($todoId));
            
            if (!$todo || $todo->getUserId() !== $userId) {
                throw new \RuntimeException('Todo not found or access denied');
            }

            if ($todo->getStatus()->isCompleted()) {
                $todo->markAsPending();
            } else {
                $todo->markAsCompleted();
            }

            return $this->todoRepository->save($todo);
        } catch (Throwable $e) {
            Log::error('Failed to toggle todo completion', [
                'todo_id' => $todoId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('toggle todo', $e->getMessage());
        }
    }

    public function deleteTodo(int $todoId, int $userId): void
    {
        try {
            $todo = $this->todoRepository->findById(TodoId::fromInt($todoId));
            
            if (!$todo || $todo->getUserId() !== $userId) {
                throw new \RuntimeException('Todo not found or access denied');
            }

            $this->todoRepository->delete(TodoId::fromInt($todoId));

            Log::info('Personal todo deleted', ['todo_id' => $todoId]);
        } catch (Throwable $e) {
            Log::error('Failed to delete personal todo', [
                'todo_id' => $todoId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('delete todo', $e->getMessage());
        }
    }

    public function getTodoById(int $todoId, int $userId): ?PersonalTodo
    {
        $todo = $this->todoRepository->findById(TodoId::fromInt($todoId));
        
        if (!$todo || $todo->getUserId() !== $userId) {
            return null;
        }

        return $todo;
    }

    public function getTodosForUser(int $userId, array $filters = []): array
    {
        return $this->todoRepository->findByUserIdWithFilters($userId, $filters);
    }

    public function getTodayTodos(int $userId): array
    {
        return $this->todoRepository->findTodayTodos($userId);
    }

    public function getUpcomingTodos(int $userId, int $days = 7): array
    {
        return $this->todoRepository->findUpcomingTodos($userId, $days);
    }

    public function getOverdueTodos(int $userId): array
    {
        return $this->todoRepository->findOverdueTodos($userId);
    }

    public function getCompletedTodos(int $userId, int $limit = 50): array
    {
        return $this->todoRepository->findCompletedTodos($userId, $limit);
    }

    public function getStatistics(int $userId): array
    {
        return $this->todoRepository->getStatistics($userId);
    }

    public function getCategories(int $userId): array
    {
        return $this->todoRepository->getCategories($userId);
    }

    public function getSubtasks(int $parentId): array
    {
        return $this->todoRepository->findSubtasks($parentId);
    }

    public function updateSortOrder(int $userId, array $sortedIds): void
    {
        try {
            $this->todoRepository->updateSortOrder($userId, $sortedIds);
        } catch (Throwable $e) {
            Log::error('Failed to update sort order', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw new OperationFailedException('update sort order', $e->getMessage());
        }
    }
}
