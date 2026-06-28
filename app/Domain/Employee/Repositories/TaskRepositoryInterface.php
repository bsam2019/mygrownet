<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use App\Domain\Employee\Entities\Task;
use App\Domain\Employee\ValueObjects\TaskId;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\TaskStatus;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function findById(TaskId $id): ?Task;
    
    public function findByEmployee(EmployeeId $employeeId, array $filters = []): Collection;
    
    public function findByStatus(EmployeeId $employeeId, TaskStatus $status): Collection;
    
    public function findOverdue(EmployeeId $employeeId): Collection;
    
    public function findUpcoming(EmployeeId $employeeId, int $days = 7): Collection;
    
    public function save(Task $task): bool;
    
    public function delete(TaskId $id): bool;
    
    public function getStatusCounts(EmployeeId $employeeId): array;
    
    public function getTasksByStatusGrouped(EmployeeId $employeeId): array;
    
    public function findAssignedByEmployee(EmployeeId $assignerId): Collection;
    
    public function getCompletionStats(EmployeeId $employeeId, ?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): array;
}
