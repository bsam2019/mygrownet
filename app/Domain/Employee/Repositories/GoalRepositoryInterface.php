<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use App\Domain\Employee\Entities\Goal;
use App\Domain\Employee\ValueObjects\EmployeeId;
use Illuminate\Support\Collection;

interface GoalRepositoryInterface
{
    public function findById(int $id): ?Goal;
    
    public function findByEmployee(EmployeeId $employeeId, array $filters = []): Collection;
    
    public function findActiveByEmployee(EmployeeId $employeeId): Collection;
    
    public function findOverdue(EmployeeId $employeeId): Collection;
    
    public function save(Goal $goal): bool;
    
    public function delete(int $id): bool;
    
    public function getProgressStats(EmployeeId $employeeId): array;
    
    public function findByCategory(EmployeeId $employeeId, string $category): Collection;
    
    public function getCompletionRate(EmployeeId $employeeId, ?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): float;
}
