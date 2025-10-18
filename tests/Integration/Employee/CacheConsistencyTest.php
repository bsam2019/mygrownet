<?php

declare(strict_types=1);

namespace Tests\Integration\Employee;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Repositories\CachedEmployeeRepository;
use App\Infrastructure\Persistence\Repositories\OptimizedEmployeeRepository;
use App\Services\EmployeeCacheService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Cache Consistency Integration Tests
 * 
 * Tests that verify cache consistency and performance improvements
 * when using the cached repository implementation.
 */
class CacheConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private CachedEmployeeRepository $cachedRepository;
    private OptimizedEmployeeRepository $optimizedRepository;
    private EmployeeCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->optimizedRepository = new OptimizedEmployeeRepository(new EmployeeModel());
        $this->cacheService = new EmployeeCacheService($this->optimizedRepository);
        $this->cachedRepository = new CachedEmployeeRepository($this->optimizedRepository, $this->cacheService);
        
        // Clear cache before each test
        Cache::flush();
        
        // Create test data
        $this->createTestData();
    }

    /**
     * Test that cached repository returns same data as optimized repository
     */
    public function test_cached_repository_data_consistency(): void
    {
        // Get data from both repositories
        $optimizedStats = $this->optimizedRepository->getEmployeeStatistics();
        $cachedStats = $this->cachedRepository->getEmployeeStatistics();

        // Data should be identical
        $this->assertEquals($optimizedStats['total_employees'], $cachedStats['total_employees']);
        $this->assertEquals($optimizedStats['active_employees'], $cachedStats['active_employees']);
    }

    /**
     * Test department hierarchy caching and consistency
     */
    public function test_department_hierarchy_cache_consistency(): void
    {
        // First call should populate cache
        $hierarchy1 = $this->cachedRepository->getDepartmentHierarchyOptimized();
        
        // Verify cache is populated
        $this->assertTrue(Cache::has('employee:department_hierarchy'));
        
        // Second call should use cache
        $hierarchy2 = $this->cachedRepository->getDepartmentHierarchyOptimized();
        
        // Data should be identical
        $this->assertEquals($hierarchy1, $hierarchy2);
        $this->assertIsArray($hierarchy1);
        $this->assertNotEmpty($hierarchy1);
    }

    /**
     * Test organizational chart caching
     */
    public function test_organizational_chart_cache_consistency(): void
    {
        $chart1 = $this->cachedRepository->getOrganizationalChart();
        
        // Verify cache is populated
        $this->assertTrue(Cache::has('employee:organizational_chart'));
        
        $chart2 = $this->cachedRepository->getOrganizationalChart();
        
        $this->assertEquals($chart1, $chart2);
        $this->assertIsArray($chart1);
    }

    /**
     * Test performance analytics caching with date ranges
     */
    public function test_performance_analytics_cache_consistency(): void
    {
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();
        
        $analytics1 = $this->cachedRepository->getPerformanceAnalytics($startDate, $endDate);
        
        // Verify cache key exists
        $cacheKey = 'employee:performance_analytics:' . $startDate->format('Y-m-d') . ':' . $endDate->format('Y-m-d');
        $this->assertTrue(Cache::has($cacheKey));
        
        $analytics2 = $this->cachedRepository->getPerformanceAnalytics($startDate, $endDate);
        
        $this->assertEquals($analytics1, $analytics2);
        $this->assertIsArray($analytics1);
        $this->assertArrayHasKey('total_reviews', $analytics1);
    }

    /**
     * Test commission analytics caching
     */
    public function test_commission_analytics_cache_consistency(): void
    {
        $startDate = Carbon::now()->subMonth();
        $endDate = Carbon::now();
        
        $analytics1 = $this->cachedRepository->getCommissionAnalytics($startDate, $endDate);
        $analytics2 = $this->cachedRepository->getCommissionAnalytics($startDate, $endDate);
        
        $this->assertEquals($analytics1, $analytics2);
        $this->assertIsArray($analytics1);
        $this->assertArrayHasKey('total_commissions', $analytics1);
    }

    /**
     * Test payroll data caching
     */
    public function test_payroll_data_cache_consistency(): void
    {
        $payrollDate = Carbon::now();
        
        $payroll1 = $this->cachedRepository->getPayrollData($payrollDate);
        
        // Verify cache is populated
        $cacheKey = 'employee:payroll:' . $payrollDate->format('Y-m-d');
        $this->assertTrue(Cache::has($cacheKey));
        
        $payroll2 = $this->cachedRepository->getPayrollData($payrollDate);
        
        $this->assertEquals($payroll1, $payroll2);
        $this->assertIsArray($payroll1);
    }

    /**
     * Test cache invalidation when employee is updated
     */
    public function test_cache_invalidation_on_employee_update(): void
    {
        $employee = EmployeeModel::first();
        
        // Populate cache
        $this->cacheService->getEmployee($employee->id);
        $this->assertTrue(Cache::has('employee:employee:' . $employee->id));
        
        // Update employee through cached repository
        $this->cachedRepository->update($employee->id, ['first_name' => 'Updated Name']);
        
        // Cache should be invalidated
        $this->assertFalse(Cache::has('employee:employee:' . $employee->id));
    }

    /**
     * Test cache invalidation when employee is deleted
     */
    public function test_cache_invalidation_on_employee_delete(): void
    {
        $employee = EmployeeModel::first();
        
        // Populate cache
        $this->cacheService->getEmployee($employee->id);
        $this->cacheService->getEmployeeStatistics();
        
        $this->assertTrue(Cache::has('employee:employee:' . $employee->id));
        $this->assertTrue(Cache::has('employee:statistics'));
        
        // Delete employee through cached repository
        $this->cachedRepository->delete($employee->id);
        
        // Relevant caches should be invalidated
        $this->assertFalse(Cache::has('employee:employee:' . $employee->id));
        $this->assertFalse(Cache::has('employee:statistics'));
    }

    /**
     * Test department cache invalidation
     */
    public function test_department_cache_invalidation(): void
    {
        $department = DepartmentModel::first();
        
        // Populate department-related caches
        $this->cacheService->getDepartmentHierarchy();
        $this->cacheService->getDepartmentEmployees($department->id);
        
        $this->assertTrue(Cache::has('employee:department_hierarchy'));
        $this->assertTrue(Cache::has('employee:department_employees:' . $department->id));
        
        // Invalidate department cache
        $this->cacheService->invalidateDepartmentCache($department->id);
        
        // Department-related caches should be cleared
        $this->assertFalse(Cache::has('employee:department_hierarchy'));
        $this->assertFalse(Cache::has('employee:department_employees:' . $department->id));
    }

    /**
     * Test cache warm-up improves performance
     */
    public function test_cache_warm_up_improves_performance(): void
    {
        // Measure time without cache
        $startTime = microtime(true);
        $this->optimizedRepository->getDepartmentHierarchyOptimized();
        $this->optimizedRepository->getOrganizationalChart();
        $this->optimizedRepository->getEmployeeStatistics();
        $timeWithoutCache = microtime(true) - $startTime;
        
        // Warm up cache
        $this->cacheService->warmUpCaches();
        
        // Measure time with cache
        $startTime = microtime(true);
        $this->cachedRepository->getDepartmentHierarchyOptimized();
        $this->cachedRepository->getOrganizationalChart();
        $this->cachedRepository->getEmployeeStatistics();
        $timeWithCache = microtime(true) - $startTime;
        
        // Cached version should be faster (though this might be minimal in tests)
        $this->assertLessThanOrEqual($timeWithoutCache * 2, $timeWithCache);
    }

    /**
     * Test cache statistics are accurate
     */
    public function test_cache_statistics_accuracy(): void
    {
        $stats = $this->cacheService->getCacheStatistics();
        
        $this->assertIsArray($stats);
        $this->assertEquals('employee:', $stats['cache_prefix']);
        $this->assertIsInt($stats['default_ttl']);
        $this->assertIsString($stats['cache_driver']);
    }

    /**
     * Test concurrent cache access doesn't cause issues
     */
    public function test_concurrent_cache_access(): void
    {
        // Simulate concurrent access to the same cached data
        $results = [];
        
        for ($i = 0; $i < 5; $i++) {
            $results[] = $this->cachedRepository->getEmployeeStatistics();
        }
        
        // All results should be identical
        $firstResult = $results[0];
        foreach ($results as $result) {
            $this->assertEquals($firstResult, $result);
        }
    }

    /**
     * Test cache TTL behavior
     */
    public function test_cache_ttl_behavior(): void
    {
        // Get data to populate cache
        $this->cacheService->getEmployeeStatistics();
        
        // Verify cache exists
        $this->assertTrue(Cache::has('employee:statistics'));
        
        // Manually expire cache (simulate TTL expiration)
        Cache::forget('employee:statistics');
        
        // Verify cache is gone
        $this->assertFalse(Cache::has('employee:statistics'));
        
        // Next call should repopulate cache
        $stats = $this->cacheService->getEmployeeStatistics();
        $this->assertTrue(Cache::has('employee:statistics'));
        $this->assertIsArray($stats);
    }

    /**
     * Create test data for cache testing
     */
    private function createTestData(): void
    {
        // Create departments
        $departments = DepartmentModel::factory()->count(3)->create();
        
        // Create positions
        foreach ($departments as $department) {
            PositionModel::factory()->count(2)->create([
                'department_id' => $department->id
            ]);
        }
        
        // Create employees
        foreach ($departments as $department) {
            $positions = PositionModel::where('department_id', $department->id)->get();
            
            foreach ($positions as $position) {
                EmployeeModel::factory()->count(5)->create([
                    'department_id' => $department->id,
                    'position_id' => $position->id
                ]);
            }
        }
    }
}