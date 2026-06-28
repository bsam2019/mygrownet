<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use App\Domain\Employee\Entities\TimeOffRequest;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\TimeOffType;
use Illuminate\Support\Collection;

interface TimeOffRepositoryInterface
{
    public function findById(int $id): ?TimeOffRequest;
    
    public function findByEmployee(EmployeeId $employeeId, array $filters = []): Collection;
    
    public function findPendingByEmployee(EmployeeId $employeeId): Collection;
    
    public function findApprovedByEmployee(EmployeeId $employeeId): Collection;
    
    public function findUpcoming(EmployeeId $employeeId): Collection;
    
    public function save(TimeOffRequest $request): bool;
    
    public function delete(int $id): bool;
    
    public function getUsedDays(EmployeeId $employeeId, TimeOffType $type, int $year): float;
    
    public function getBalance(EmployeeId $employeeId, TimeOffType $type, int $year): array;
    
    public function findPendingForManager(EmployeeId $managerId): Collection;
    
    public function hasOverlappingRequest(EmployeeId $employeeId, \DateTimeImmutable $start, \DateTimeImmutable $end, ?int $excludeId = null): bool;
}
