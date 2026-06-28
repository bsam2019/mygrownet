<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Repositories;

use App\Domain\GrowBiz\Entities\Task;
use App\Domain\GrowBiz\ValueObjects\TaskId;
use App\Domain\GrowBiz\ValueObjects\TaskStatus;

interface TaskRepositoryInterface
{
    public function findById(TaskId $id): ?Task;
    
    public function findByManagerId(int $managerId): array;
    
    public function findByManagerIdAndStatus(int $managerId, TaskStatus $status): array;
    
    public function findOverdueTasks(int $managerId): array;
    
    public function findDueToday(int $managerId): array;
    
    public function findByOwnerWithFilters(int $ownerId, array $filters): array;
    
    public function findRecentByOwner(int $ownerId, int $limit): array;
    
    public function findUpcomingDue(int $ownerId, int $limit): array;
    
    public function findByEmployeeId(int $employeeId): array;
    
    public function findByAssignedEmployee(int $employeeId): array;
    
    public function getTaskStatsByEmployee(int $employeeId): array;
    
    public function assignEmployees(TaskId $taskId, array $employeeIds): void;
    
    public function syncEmployees(TaskId $taskId, array $employeeIds): void;
    
    public function addComment(int $taskId, int $userId, string $content): void;
    
    public function getComments(int $taskId): array;
    
    public function deleteComment(int $commentId, int $userId): void;
    
    public function save(Task $task): Task;
    
    public function delete(TaskId $id): void;

    // Progress tracking methods
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
    ): void;

    public function getTaskUpdates(int $taskId, ?string $updateType = null): array;

    public function getTotalTimeLogged(int $taskId): float;

    /**
     * Get User models for all assignees of a task (for notifications)
     * @return \App\Models\User[]
     */
    public function getTaskAssigneeUsers(int $taskId): array;
}
