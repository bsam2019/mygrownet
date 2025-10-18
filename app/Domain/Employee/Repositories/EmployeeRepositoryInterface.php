<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\EmploymentStatus;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

interface EmployeeRepositoryInterface
{
    // Core CRUD operations
    public function findById(EmployeeId $id): ?Employee;
    public function findByEmail(Email $email): ?Employee;
    public function findByEmployeeNumber(string $employeeNumber): ?Employee;
    public function findByUserId(int $userId): ?Employee;
    public function save(Employee $employee): bool;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    
    // Query methods
    public function findAll(array $filters = []): Collection;
    public function findByDepartment(int $departmentId): Collection;
    public function findByPosition(int $positionId): Collection;
    public function findByEmploymentStatus(string $status): Collection;
    public function findByManager(int $managerId): Collection;
    public function findByHireDateRange(Carbon $startDate, Carbon $endDate): Collection;
    public function search(string $query): Collection;
    
    // Specialized queries
    public function findEligibleForPerformanceReview(): Collection;
    public function findFieldAgentsWithClients(): Collection;
    public function findCommissionEligibleEmployees(): Collection;
    public function findDepartmentHeads(): Collection;
    public function findForPayrollProcessing(Carbon $payrollDate): Collection;
    public function findWithPerformanceMetrics(Carbon $startDate, Carbon $endDate): Collection;
    public function findUpcomingPerformanceReviews(int $daysAhead = 30): Collection;
    public function findByYearsOfService(int $minYears, int $maxYears = null): Collection;
    public function findTopPerformers(int $limit = 10, Carbon $startDate = null, Carbon $endDate = null): Collection;
    public function findByQualifications(array $qualifications): Collection;
    public function findRequiringTraining(): Collection;
    public function getEmployeeHierarchy(int $managerId = null): Collection;
    public function findWithClientConflicts(): Collection;
    
    // Statistics and reporting
    public function getActiveEmployeesCount(): int;
    public function getEmployeeStatistics(): array;
    public function employeeNumberExists(string $employeeNumber): bool;
    public function emailExists(string $email, ?int $excludeEmployeeId = null): bool;
    public function findLastEmployeeForYear(int $year): ?Employee;
}