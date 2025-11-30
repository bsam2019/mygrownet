<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use App\Domain\Employee\ValueObjects\EmployeeId;
use Illuminate\Support\Collection;

interface EmployeeRepositoryInterface
{
    public function findById(EmployeeId $id): ?object;
    
    public function findByUserId(int $userId): ?object;
    
    public function findByDepartment(int $departmentId): Collection;
    
    public function findByManager(EmployeeId $managerId): Collection;
    
    public function getActiveEmployees(): Collection;
    
    public function save(object $employee): void;
}
