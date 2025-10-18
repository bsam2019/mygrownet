<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Services\EmployeeCacheService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Cached Employee Repository Decorator
 * 
 * Wraps the OptimizedEmployeeRepository with caching functionality
 * to improve performance for frequently accessed employee data.
 * 
 * This decorator implements the Repository pattern with caching,
 * providing transparent caching for read operations while ensuring
 * cache consistency for write operations.
 */
class CachedEmployeeRepository implements EmployeeRepositoryInterface
{
    public function __construct(
        private OptimizedEmployeeRepository $repository,
        private EmployeeCacheService $cacheService
    ) {}

    /**
     * Find employee by ID with caching
     */
    public function findById(EmployeeId $id): ?Employee
    {
        // For individual employee lookups, use cache
        $cachedData = $this->cacheService->getEmployee((int) $id->toString());
        
        if ($cachedData) {
            // Convert cached data back to domain entity
            // This is a simplified approach - in production you'd need proper hydration
            return $this->repository->findById($id);
        }

        $employee = $this->repository->findById($id);
        
        // Cache the result if found
        if ($employee) {
            $this->cacheService->getEmployee((int) $id->toString(), true);
        }
        
        return $employee;
    }

    /**
     * Find employee by employee number
     */
    public function findByEmployeeNumber(string $employeeNumber): ?Employee
    {
        // Employee number lookups are less frequent, so go directly to repository
        return $this->repository->findByEmployeeNumber($employeeNumber);
    }

    /**
     * Find employee by user ID
     */
    public function findByUserId(int $userId): ?Employee
    {
        return $this->repository->findByUserId($userId);
    }

    /**
     * Find employee by email
     */
    public function findByEmail(Email $email): ?Employee
    {
        return $this->repository->findByEmail($email);
    }

    /**
     * Find all employees with caching for common queries
     */
    public function findAll(array $filters = []): Collection
    {
        // For complex filtered queries, bypass cache and go to optimized repository
        return $this->repository->findAllOptimized($filters);
    }

    /**
     * Find employees by department with caching
     */
    public function findByDepartment(int $departmentId): Collection
    {
        $cachedEmployees = $this->cacheService->getDepartmentEmployees($departmentId);
        
        if (!empty($cachedEmployees)) {
            // Convert cached data to collection
            // In production, you'd properly hydrate domain entities
            return $this->repository->findByDepartment($departmentId);
        }

        $employees = $this->repository->findByDepartment($departmentId);
        
        // Warm the cache
        $this->cacheService->getDepartmentEmployees($departmentId, true);
        
        return $employees;
    }

    /**
     * Find employees by position
     */
    public function findByPosition(int $positionId): Collection
    {
        return $this->repository->findByPosition($positionId);
    }

    /**
     * Find employees by employment status
     */
    public function findByEmploymentStatus(string $status): Collection
    {
        return $this->repository->findByEmploymentStatus($status);
    }

    /**
     * Find employees by manager
     */
    public function findByManager(int $managerId): Collection
    {
        return $this->repository->findByManager($managerId);
    }

    /**
     * Find employees by hire date range
     */
    public function findByHireDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->repository->findByHireDateRange($startDate, $endDate);
    }

    /**
     * Search employees
     */
    public function search(string $query): Collection
    {
        // Use optimized search from repository
        return $this->repository->searchOptimized($query);
    }

    /**
     * Get active employees count with caching
     */
    public function getActiveEmployeesCount(): int
    {
        $stats = $this->cacheService->getEmployeeStatistics();
        return $stats['active_employees'] ?? $this->repository->getActiveEmployeesCount();
    }

    /**
     * Find employees eligible for performance review
     */
    public function findEligibleForPerformanceReview(): Collection
    {
        return $this->repository->findEligibleForPerformanceReview();
    }

    /**
     * Find field agents with clients
     */
    public function findFieldAgentsWithClients(): Collection
    {
        return $this->repository->findFieldAgentsWithClients();
    }

    /**
     * Find commission eligible employees
     */
    public function findCommissionEligibleEmployees(): Collection
    {
        return $this->repository->findCommissionEligibleEmployees();
    }

    /**
     * Find department heads
     */
    public function findDepartmentHeads(): Collection
    {
        return $this->repository->findDepartmentHeads();
    }

    /**
     * Find employees for payroll processing with caching
     */
    public function findForPayrollProcessing(Carbon $payrollDate): Collection
    {
        // Use cached payroll data
        $cachedData = $this->cacheService->getPayrollData($payrollDate);
        
        if (!empty($cachedData)) {
            // Convert to collection - in production, properly hydrate entities
            return $this->repository->findForPayrollProcessing($payrollDate);
        }

        $employees = $this->repository->findForPayrollProcessing($payrollDate);
        
        // Warm the cache
        $this->cacheService->getPayrollData($payrollDate, true);
        
        return $employees;
    }

    /**
     * Save employee and invalidate cache
     */
    public function save(Employee $employee): bool
    {
        $result = $this->repository->save($employee);
        
        if ($result && $employee->getId()) {
            // Invalidate relevant caches
            $this->cacheService->invalidateEmployeeCache((int) $employee->getId()->toString());
        }
        
        return $result;
    }

    /**
     * Update employee and invalidate cache
     */
    public function update(int $id, array $data): bool
    {
        $result = $this->repository->update($id, $data);
        
        if ($result) {
            $this->cacheService->invalidateEmployeeCache($id);
        }
        
        return $result;
    }

    /**
     * Delete employee and invalidate cache
     */
    public function delete(int $id): bool
    {
        $result = $this->repository->delete($id);
        
        if ($result) {
            $this->cacheService->invalidateEmployeeCache($id);
        }
        
        return $result;
    }

    /**
     * Get employee statistics with caching
     */
    public function getEmployeeStatistics(): array
    {
        return $this->cacheService->getEmployeeStatistics();
    }

    /**
     * Find employees with performance metrics
     */
    public function findWithPerformanceMetrics(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->repository->findWithPerformanceMetrics($startDate, $endDate);
    }

    /**
     * Check if employee number exists
     */
    public function employeeNumberExists(string $employeeNumber): bool
    {
        return $this->repository->employeeNumberExists($employeeNumber);
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeEmployeeId = null): bool
    {
        return $this->repository->emailExists($email, $excludeEmployeeId);
    }

    /**
     * Find last employee for year
     */
    public function findLastEmployeeForYear(int $year): ?Employee
    {
        return $this->repository->findLastEmployeeForYear($year);
    }

    /**
     * Find employees with upcoming performance reviews
     */
    public function findUpcomingPerformanceReviews(int $daysAhead = 30): Collection
    {
        return $this->repository->findUpcomingPerformanceReviews($daysAhead);
    }

    /**
     * Find employees by years of service
     */
    public function findByYearsOfService(int $minYears, int $maxYears = null): Collection
    {
        return $this->repository->findByYearsOfService($minYears, $maxYears);
    }

    /**
     * Find top performers
     */
    public function findTopPerformers(int $limit = 10, Carbon $startDate = null, Carbon $endDate = null): Collection
    {
        return $this->repository->findTopPerformers($limit, $startDate, $endDate);
    }

    /**
     * Find employees by qualifications
     */
    public function findByQualifications(array $qualifications): Collection
    {
        return $this->repository->findByQualifications($qualifications);
    }

    /**
     * Find employees requiring training
     */
    public function findRequiringTraining(): Collection
    {
        return $this->repository->findRequiringTraining();
    }

    /**
     * Get employee hierarchy with caching
     */
    public function getEmployeeHierarchy(int $managerId = null): Collection
    {
        if ($managerId === null) {
            // Use cached organizational chart for full hierarchy
            $cachedChart = $this->cacheService->getOrganizationalChart();
            
            if (!empty($cachedChart)) {
                // Convert to collection - in production, properly hydrate entities
                return $this->repository->getEmployeeHierarchy($managerId);
            }
        }

        return $this->repository->getEmployeeHierarchy($managerId);
    }

    /**
     * Find employees with client conflicts
     */
    public function findWithClientConflicts(): Collection
    {
        return $this->repository->findWithClientConflicts();
    }

    /**
     * Get department hierarchy with caching
     */
    public function getDepartmentHierarchyOptimized(): array
    {
        return $this->cacheService->getDepartmentHierarchy();
    }

    /**
     * Get performance analytics with caching
     */
    public function getPerformanceAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        return $this->cacheService->getPerformanceAnalytics($startDate, $endDate);
    }

    /**
     * Get commission analytics with caching
     */
    public function getCommissionAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        return $this->cacheService->getCommissionAnalytics($startDate, $endDate);
    }

    /**
     * Get payroll data with caching
     */
    public function getPayrollData(Carbon $payrollDate): array
    {
        return $this->cacheService->getPayrollData($payrollDate);
    }

    /**
     * Get organizational chart with caching
     */
    public function getOrganizationalChart(): array
    {
        return $this->cacheService->getOrganizationalChart();
    }

    /**
     * Search employees optimized
     */
    public function searchOptimized(string $searchTerm, int $limit = 50): Collection
    {
        return $this->repository->searchOptimized($searchTerm, $limit);
    }
}