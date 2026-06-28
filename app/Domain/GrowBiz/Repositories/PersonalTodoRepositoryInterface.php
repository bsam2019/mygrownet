<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Repositories;

use App\Domain\GrowBiz\Entities\PersonalTodo;
use App\Domain\GrowBiz\ValueObjects\TodoId;
use App\Domain\GrowBiz\ValueObjects\TodoStatus;

interface PersonalTodoRepositoryInterface
{
    public function findById(TodoId $id): ?PersonalTodo;
    
    public function findByUserId(int $userId): array;
    
    public function findByUserIdAndStatus(int $userId, TodoStatus $status): array;
    
    public function findByUserIdWithFilters(int $userId, array $filters): array;
    
    public function findTodayTodos(int $userId): array;
    
    public function findUpcomingTodos(int $userId, int $days = 7): array;
    
    public function findOverdueTodos(int $userId): array;
    
    public function findCompletedTodos(int $userId, int $limit = 50): array;
    
    public function findByCategory(int $userId, string $category): array;
    
    public function findSubtasks(int $parentId): array;
    
    public function getCategories(int $userId): array;
    
    public function getStatistics(int $userId): array;
    
    public function save(PersonalTodo $todo): PersonalTodo;
    
    public function delete(TodoId $id): void;
    
    public function updateSortOrder(int $userId, array $sortedIds): void;
}
