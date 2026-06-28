<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Repositories;

use App\Domain\GrowBiz\Entities\Employee;
use App\Domain\GrowBiz\ValueObjects\EmployeeId;
use App\Domain\GrowBiz\ValueObjects\EmployeeStatus;

interface EmployeeRepositoryInterface
{
    public function findById(EmployeeId $id): ?Employee;
    
    public function findByManagerId(int $managerId): array;
    
    public function findActiveByManagerId(int $managerId): array;
    
    public function findByManagerIdAndStatus(int $managerId, EmployeeStatus $status): array;
    
    public function findByOwnerWithFilters(int $ownerId, array $filters): array;
    
    public function getDistinctDepartments(int $ownerId): array;
    
    /**
     * Find employee by email within a manager's team
     */
    public function findByEmail(int $managerId, string $email): ?Employee;
    
    public function save(Employee $employee): Employee;
    
    public function delete(EmployeeId $id): void;
}
