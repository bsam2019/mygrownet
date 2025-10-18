<?php

declare(strict_types=1);

namespace App\Listeners\Employee;

use App\Domain\Employee\Events\EmployeeHired;
use App\Domain\Employee\Events\EmployeePromoted;
use App\Domain\Employee\Events\EmployeeTerminated;
use App\Domain\Employee\Events\PerformanceReviewed;
use App\Domain\Employee\Events\CommissionCalculated;
use App\Domain\Employee\Events\PayrollProcessed;
use App\Services\EmployeeCacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Cache Invalidation Listener for Employee Events
 * 
 * Automatically invalidates relevant caches when employee-related
 * domain events are fired to ensure cache consistency.
 */
class InvalidateEmployeeCacheListener implements ShouldQueue
{
    private ?EmployeeCacheService $cacheService = null;

    public function __construct()
    {
        // Lazy load the cache service to avoid circular dependencies during bootstrap
    }

    /**
     * Get cache service instance (lazy loaded)
     */
    private function getCacheService(): EmployeeCacheService
    {
        if ($this->cacheService === null) {
            $this->cacheService = app(EmployeeCacheService::class);
        }
        return $this->cacheService;
    }

    /**
     * Handle employee hired event
     */
    public function handleEmployeeHired(EmployeeHired $event): void
    {
        Log::info('Handling EmployeeHired event for cache invalidation', [
            'employee_id' => $event->employeeId->toString()
        ]);

        // Invalidate caches that would be affected by a new employee
        $this->getCacheService()->invalidateEmployeeCache((int) $event->employeeId->toString());
        $this->getCacheService()->invalidateDepartmentCache((int) $event->departmentId->toInt());
    }

    /**
     * Handle employee promoted event
     */
    public function handleEmployeePromoted(EmployeePromoted $event): void
    {
        Log::info('Handling EmployeePromoted event for cache invalidation', [
            'employee_id' => $event->employeeId->toString()
        ]);

        // Invalidate employee and organizational caches
        $this->getCacheService()->invalidateEmployeeCache((int) $event->employeeId->toString());
        
        // If department changed, invalidate both old and new department caches
        if (isset($event->newDepartmentId)) {
            $this->getCacheService()->invalidateDepartmentCache((int) $event->newDepartmentId->toInt());
        }
    }

    /**
     * Handle employee terminated event
     */
    public function handleEmployeeTerminated(EmployeeTerminated $event): void
    {
        Log::info('Handling EmployeeTerminated event for cache invalidation', [
            'employee_id' => $event->employeeId->toString()
        ]);

        // Invalidate all employee-related caches
        $this->getCacheService()->invalidateEmployeeCache((int) $event->employeeId->toString());
    }

    /**
     * Handle performance reviewed event
     */
    public function handlePerformanceReviewed(PerformanceReviewed $event): void
    {
        Log::info('Handling PerformanceReviewed event for cache invalidation', [
            'employee_id' => $event->employeeId->toString()
        ]);

        // Invalidate performance-related caches
        $this->getCacheService()->invalidatePerformanceCache();
        $this->getCacheService()->invalidateEmployeeCache((int) $event->employeeId->toString());
    }

    /**
     * Handle commission calculated event
     */
    public function handleCommissionCalculated(CommissionCalculated $event): void
    {
        Log::info('Handling CommissionCalculated event for cache invalidation', [
            'employee_id' => $event->employeeId->toString()
        ]);

        // Invalidate commission and payroll caches
        $this->getCacheService()->invalidateCommissionCache();
        $this->getCacheService()->invalidateEmployeeCache((int) $event->employeeId->toString());
    }

    /**
     * Handle payroll processed event
     */
    public function handlePayrollProcessed(PayrollProcessed $event): void
    {
        Log::info('Handling PayrollProcessed event for cache invalidation');

        // Invalidate all payroll and commission caches
        $this->getCacheService()->invalidateCommissionCache();
    }

    /**
     * Register event listeners
     */
    public function subscribe($events): void
    {
        $events->listen(EmployeeHired::class, [self::class, 'handleEmployeeHired']);
        $events->listen(EmployeePromoted::class, [self::class, 'handleEmployeePromoted']);
        $events->listen(EmployeeTerminated::class, [self::class, 'handleEmployeeTerminated']);
        $events->listen(PerformanceReviewed::class, [self::class, 'handlePerformanceReviewed']);
        $events->listen(CommissionCalculated::class, [self::class, 'handleCommissionCalculated']);
        $events->listen(PayrollProcessed::class, [self::class, 'handlePayrollProcessed']);
    }
}