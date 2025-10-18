<?php

declare(strict_types=1);

namespace App\Services;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Repositories\OptimizedEmployeeRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Employee Cache Service
 * 
 * Provides caching functionality for frequently accessed employee data
 * including department structures, employee hierarchies, and performance metrics.
 * 
 * Features:
 * - Redis-based caching with configurable TTL
 * - Cache invalidation strategies for data updates
 * - Cache warming for critical data
 * - Performance monitoring and metrics
 */
class EmployeeCacheService
{
    private const CACHE_PREFIX = 'employee:';
    private const DEFAULT_TTL = 3600; // 1 hour
    private const LONG_TTL = 86400; // 24 hours
    private const SHORT_TTL = 300; // 5 minutes

    public function __construct(
        private OptimizedEmployeeRepository $employeeRepository
    ) {}

    /**
     * Get cached department hierarchy
     */
    public function getDepartmentHierarchy(bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX . 'department_hierarchy';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::LONG_TTL, function () {
            Log::info('Cache miss: department_hierarchy - fetching from database');
            
            $departments = $this->employeeRepository->getDepartmentHierarchyOptimized();
            
            // Build hierarchical structure
            $hierarchy = $this->buildDepartmentTree($departments);
            
            Log::info('Cached department hierarchy', ['count' => count($hierarchy)]);
            
            return $hierarchy;
        });
    }

    /**
     * Get cached organizational chart
     */
    public function getOrganizationalChart(bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX . 'organizational_chart';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::DEFAULT_TTL, function () {
            Log::info('Cache miss: organizational_chart - fetching from database');
            
            $chart = $this->employeeRepository->getOrganizationalChart();
            
            Log::info('Cached organizational chart', ['nodes' => count($chart)]);
            
            return $chart;
        });
    }

    /**
     * Get cached employee statistics
     */
    public function getEmployeeStatistics(bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX . 'statistics';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::SHORT_TTL, function () {
            Log::info('Cache miss: employee_statistics - fetching from database');
            
            $stats = $this->employeeRepository->getEmployeeStatistics();
            
            Log::info('Cached employee statistics', ['total_employees' => $stats['total_employees']]);
            
            return $stats;
        });
    }

    /**
     * Get cached performance analytics
     */
    public function getPerformanceAnalytics(Carbon $startDate, Carbon $endDate, bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX . 'performance_analytics:' . $startDate->format('Y-m-d') . ':' . $endDate->format('Y-m-d');

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::DEFAULT_TTL, function () use ($startDate, $endDate) {
            Log::info('Cache miss: performance_analytics - fetching from database', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ]);
            
            $analytics = $this->employeeRepository->getPerformanceAnalytics($startDate, $endDate);
            
            Log::info('Cached performance analytics', ['total_reviews' => $analytics['total_reviews']]);
            
            return $analytics;
        });
    }

    /**
     * Get cached commission analytics
     */
    public function getCommissionAnalytics(Carbon $startDate, Carbon $endDate, bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX . 'commission_analytics:' . $startDate->format('Y-m-d') . ':' . $endDate->format('Y-m-d');

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::DEFAULT_TTL, function () use ($startDate, $endDate) {
            Log::info('Cache miss: commission_analytics - fetching from database', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ]);
            
            $analytics = $this->employeeRepository->getCommissionAnalytics($startDate, $endDate);
            
            Log::info('Cached commission analytics', ['total_commissions' => $analytics['total_commissions']]);
            
            return $analytics;
        });
    }

    /**
     * Get cached employee by ID
     */
    public function getEmployee(int $employeeId, bool $forceRefresh = false): ?array
    {
        $cacheKey = self::CACHE_PREFIX . 'employee:' . $employeeId;

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::DEFAULT_TTL, function () use ($employeeId) {
            Log::info('Cache miss: employee - fetching from database', ['employee_id' => $employeeId]);
            
            $employee = EmployeeModel::with(['department', 'position', 'manager', 'lastPerformanceReview'])
                ->find($employeeId);
            
            if (!$employee) {
                return null;
            }
            
            $data = $employee->toArray();
            
            Log::info('Cached employee data', ['employee_id' => $employeeId]);
            
            return $data;
        });
    }

    /**
     * Get cached department employees
     */
    public function getDepartmentEmployees(int $departmentId, bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX . 'department_employees:' . $departmentId;

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::DEFAULT_TTL, function () use ($departmentId) {
            Log::info('Cache miss: department_employees - fetching from database', ['department_id' => $departmentId]);
            
            $employees = EmployeeModel::where('department_id', $departmentId)
                ->where('employment_status', 'active')
                ->with(['position', 'manager'])
                ->orderBy('last_name')
                ->get()
                ->toArray();
            
            Log::info('Cached department employees', [
                'department_id' => $departmentId,
                'count' => count($employees)
            ]);
            
            return $employees;
        });
    }

    /**
     * Get cached payroll data
     */
    public function getPayrollData(Carbon $payrollDate, bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX . 'payroll:' . $payrollDate->format('Y-m-d');

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::SHORT_TTL, function () use ($payrollDate) {
            Log::info('Cache miss: payroll_data - fetching from database', ['payroll_date' => $payrollDate->format('Y-m-d')]);
            
            $payrollData = $this->employeeRepository->getPayrollData($payrollDate)->toArray();
            
            Log::info('Cached payroll data', [
                'payroll_date' => $payrollDate->format('Y-m-d'),
                'employee_count' => count($payrollData)
            ]);
            
            return $payrollData;
        });
    }

    /**
     * Invalidate employee-related caches
     */
    public function invalidateEmployeeCache(int $employeeId): void
    {
        $employee = EmployeeModel::find($employeeId);
        
        if (!$employee) {
            return;
        }

        $keysToInvalidate = [
            self::CACHE_PREFIX . 'employee:' . $employeeId,
            self::CACHE_PREFIX . 'department_employees:' . $employee->department_id,
            self::CACHE_PREFIX . 'organizational_chart',
            self::CACHE_PREFIX . 'statistics',
        ];

        foreach ($keysToInvalidate as $key) {
            Cache::forget($key);
        }

        Log::info('Invalidated employee cache', [
            'employee_id' => $employeeId,
            'keys_invalidated' => count($keysToInvalidate)
        ]);
    }

    /**
     * Invalidate department-related caches
     */
    public function invalidateDepartmentCache(int $departmentId): void
    {
        $keysToInvalidate = [
            self::CACHE_PREFIX . 'department_hierarchy',
            self::CACHE_PREFIX . 'department_employees:' . $departmentId,
            self::CACHE_PREFIX . 'organizational_chart',
            self::CACHE_PREFIX . 'statistics',
        ];

        foreach ($keysToInvalidate as $key) {
            Cache::forget($key);
        }

        Log::info('Invalidated department cache', [
            'department_id' => $departmentId,
            'keys_invalidated' => count($keysToInvalidate)
        ]);
    }

    /**
     * Invalidate performance-related caches
     */
    public function invalidatePerformanceCache(): void
    {
        $pattern = self::CACHE_PREFIX . 'performance_analytics:*';
        $this->invalidateCacheByPattern($pattern);

        Cache::forget(self::CACHE_PREFIX . 'statistics');

        Log::info('Invalidated performance cache');
    }

    /**
     * Invalidate commission-related caches
     */
    public function invalidateCommissionCache(): void
    {
        $pattern = self::CACHE_PREFIX . 'commission_analytics:*';
        $this->invalidateCacheByPattern($pattern);

        $pattern = self::CACHE_PREFIX . 'payroll:*';
        $this->invalidateCacheByPattern($pattern);

        Log::info('Invalidated commission cache');
    }

    /**
     * Warm up critical caches
     */
    public function warmUpCaches(): void
    {
        Log::info('Starting cache warm-up');

        // Warm up department hierarchy
        $this->getDepartmentHierarchy(true);

        // Warm up organizational chart
        $this->getOrganizationalChart(true);

        // Warm up employee statistics
        $this->getEmployeeStatistics(true);

        // Warm up recent performance analytics
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subMonths(3);
        $this->getPerformanceAnalytics($startDate, $endDate, true);

        // Warm up recent commission analytics
        $this->getCommissionAnalytics($startDate, $endDate, true);

        // Warm up current payroll data
        $this->getPayrollData(Carbon::now(), true);

        Log::info('Cache warm-up completed');
    }

    /**
     * Clear all employee caches
     */
    public function clearAllCaches(): void
    {
        $pattern = self::CACHE_PREFIX . '*';
        $this->invalidateCacheByPattern($pattern);

        Log::info('Cleared all employee caches');
    }

    /**
     * Get cache statistics
     */
    public function getCacheStatistics(): array
    {
        // This would require Redis-specific commands to get actual cache statistics
        // For now, return basic information
        return [
            'cache_prefix' => self::CACHE_PREFIX,
            'default_ttl' => self::DEFAULT_TTL,
            'long_ttl' => self::LONG_TTL,
            'short_ttl' => self::SHORT_TTL,
            'cache_driver' => config('cache.default'),
        ];
    }

    /**
     * Build department tree structure
     */
    private function buildDepartmentTree($departments): array
    {
        $departmentMap = $departments->keyBy('id');
        $tree = [];

        foreach ($departments as $department) {
            if (!$department->parent_department_id) {
                $tree[] = $this->buildDepartmentNode($department, $departmentMap);
            }
        }

        return $tree;
    }

    /**
     * Build department node with children
     */
    private function buildDepartmentNode($department, $departmentMap): array
    {
        $node = [
            'id' => $department->id,
            'name' => $department->name,
            'description' => $department->description,
            'head_name' => $department->head_name,
            'employee_count' => $department->employee_count,
            'avg_salary' => $department->avg_salary,
            'children' => []
        ];

        // Find child departments
        foreach ($departmentMap as $dept) {
            if ($dept->parent_department_id == $department->id) {
                $node['children'][] = $this->buildDepartmentNode($dept, $departmentMap);
            }
        }

        return $node;
    }

    /**
     * Invalidate cache by pattern (Redis-specific)
     */
    private function invalidateCacheByPattern(string $pattern): void
    {
        try {
            // This requires Redis and the predis package
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);
                
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to invalidate cache by pattern', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
        }
    }
}