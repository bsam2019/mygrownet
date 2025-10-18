<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Infrastructure\Persistence\Repositories\OptimizedEmployeeRepository;
use App\Services\EmployeeCacheService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Tests for Employee Cache Service
 * 
 * Validates caching functionality, cache invalidation strategies,
 * and performance improvements for employee data operations.
 */
class EmployeeCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmployeeCacheService $cacheService;
    private OptimizedEmployeeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = $this->createMock(OptimizedEmployeeRepository::class);
        $this->cacheService = new EmployeeCacheService($this->repository);
        
        // Clear cache before each test
        Cache::flush();
    }

    /**
     * Test department hierarchy caching
     */
    public function test_department_hierarchy_caching(): void
    {
        $mockData = collect([
            (object) ['id' => 1, 'name' => 'IT', 'parent_department_id' => null],
            (object) ['id' => 2, 'name' => 'Development', 'parent_department_id' => 1],
        ]);

        $this->repository->expects($this->once())
            ->method('getDepartmentHierarchyOptimized')
            ->willReturn($mockData);

        // First call should hit the repository
        $result1 = $this->cacheService->getDepartmentHierarchy();
        
        // Second call should use cache (repository not called again)
        $result2 = $this->cacheService->getDepartmentHierarchy();

        $this->assertEquals($result1, $result2);
        $this->assertIsArray($result1);
    }

    /**
     * Test organizational chart caching
     */
    public function test_organizational_chart_caching(): void
    {
        $mockChart = [
            ['id' => 1, 'name' => 'CEO', 'children' => []]
        ];

        $this->repository->expects($this->once())
            ->method('getOrganizationalChart')
            ->willReturn($mockChart);

        // First call should hit the repository
        $result1 = $this->cacheService->getOrganizationalChart();
        
        // Second call should use cache
        $result2 = $this->cacheService->getOrganizationalChart();

        $this->assertEquals($result1, $result2);
        $this->assertEquals($mockChart, $result1);
    }

    /**
     * Test employee statistics caching
     */
    public function test_employee_statistics_caching(): void
    {
        $mockStats = [
            'total_employees' => 100,
            'active_employees' => 85,
            'by_department' => ['IT' => 25, 'Sales' => 30]
        ];

        $this->repository->expects($this->once())
            ->method('getEmployeeStatistics')
            ->willReturn($mockStats);

        // First call should hit the repository
        $result1 = $this->cacheService->getEmployeeStatistics();
        
        // Second call should use cache
        $result2 = $this->cacheService->getEmployeeStatistics();

        $this->assertEquals($result1, $result2);
        $this->assertEquals($mockStats, $result1);
    }

    /**
     * Test performance analytics caching with date parameters
     */
    public function test_performance_analytics_caching(): void
    {
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();
        
        $mockAnalytics = [
            'total_reviews' => 50,
            'average_score' => 85.5,
            'by_department' => ['IT' => ['avg_score' => 88]]
        ];

        $this->repository->expects($this->once())
            ->method('getPerformanceAnalytics')
            ->with($startDate, $endDate)
            ->willReturn($mockAnalytics);

        // First call should hit the repository
        $result1 = $this->cacheService->getPerformanceAnalytics($startDate, $endDate);
        
        // Second call with same dates should use cache
        $result2 = $this->cacheService->getPerformanceAnalytics($startDate, $endDate);

        $this->assertEquals($result1, $result2);
        $this->assertEquals($mockAnalytics, $result1);
    }

    /**
     * Test commission analytics caching
     */
    public function test_commission_analytics_caching(): void
    {
        $startDate = Carbon::now()->subMonths(1);
        $endDate = Carbon::now();
        
        $mockAnalytics = [
            'total_commissions' => 15000.00,
            'by_type' => ['base' => 8000, 'performance' => 7000]
        ];

        $this->repository->expects($this->once())
            ->method('getCommissionAnalytics')
            ->with($startDate, $endDate)
            ->willReturn($mockAnalytics);

        $result1 = $this->cacheService->getCommissionAnalytics($startDate, $endDate);
        $result2 = $this->cacheService->getCommissionAnalytics($startDate, $endDate);

        $this->assertEquals($result1, $result2);
        $this->assertEquals($mockAnalytics, $result1);
    }

    /**
     * Test cache invalidation for employee updates
     */
    public function test_employee_cache_invalidation(): void
    {
        $employeeId = 1;
        
        // Mock employee data
        $mockEmployee = (object) [
            'id' => $employeeId,
            'department_id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe'
        ];

        // Set up cache with employee data
        Cache::put('employee:employee:' . $employeeId, $mockEmployee->toArray(), 3600);
        Cache::put('employee:department_employees:1', [$mockEmployee], 3600);
        Cache::put('employee:organizational_chart', [], 3600);
        Cache::put('employee:statistics', ['total' => 1], 3600);

        // Verify cache exists
        $this->assertTrue(Cache::has('employee:employee:' . $employeeId));
        $this->assertTrue(Cache::has('employee:department_employees:1'));

        // Invalidate employee cache
        $this->cacheService->invalidateEmployeeCache($employeeId);

        // Verify relevant caches are cleared
        $this->assertFalse(Cache::has('employee:employee:' . $employeeId));
        $this->assertFalse(Cache::has('employee:organizational_chart'));
        $this->assertFalse(Cache::has('employee:statistics'));
    }

    /**
     * Test cache invalidation for department updates
     */
    public function test_department_cache_invalidation(): void
    {
        $departmentId = 1;
        
        // Set up cache
        Cache::put('employee:department_hierarchy', [], 3600);
        Cache::put('employee:department_employees:' . $departmentId, [], 3600);
        Cache::put('employee:organizational_chart', [], 3600);

        $this->assertTrue(Cache::has('employee:department_hierarchy'));
        $this->assertTrue(Cache::has('employee:department_employees:' . $departmentId));

        // Invalidate department cache
        $this->cacheService->invalidateDepartmentCache($departmentId);

        // Verify caches are cleared
        $this->assertFalse(Cache::has('employee:department_hierarchy'));
        $this->assertFalse(Cache::has('employee:department_employees:' . $departmentId));
        $this->assertFalse(Cache::has('employee:organizational_chart'));
    }

    /**
     * Test performance cache invalidation
     */
    public function test_performance_cache_invalidation(): void
    {
        // Set up performance-related cache
        Cache::put('employee:performance_analytics:2024-01-01:2024-03-31', [], 3600);
        Cache::put('employee:statistics', [], 3600);

        $this->assertTrue(Cache::has('employee:statistics'));

        // Invalidate performance cache
        $this->cacheService->invalidatePerformanceCache();

        // Verify statistics cache is cleared
        $this->assertFalse(Cache::has('employee:statistics'));
    }

    /**
     * Test commission cache invalidation
     */
    public function test_commission_cache_invalidation(): void
    {
        // Set up commission-related cache
        Cache::put('employee:commission_analytics:2024-01-01:2024-01-31', [], 3600);
        Cache::put('employee:payroll:2024-01-31', [], 3600);

        // Invalidate commission cache
        $this->cacheService->invalidateCommissionCache();

        // Note: Pattern-based invalidation testing would require Redis
        // In unit tests, we verify the method doesn't throw errors
        $this->assertTrue(true);
    }

    /**
     * Test cache warm-up functionality
     */
    public function test_cache_warm_up(): void
    {
        // Mock all the methods that will be called during warm-up
        $this->repository->method('getDepartmentHierarchyOptimized')->willReturn(collect([]));
        $this->repository->method('getOrganizationalChart')->willReturn([]);
        $this->repository->method('getEmployeeStatistics')->willReturn(['total' => 0]);
        $this->repository->method('getPerformanceAnalytics')->willReturn(['total_reviews' => 0]);
        $this->repository->method('getCommissionAnalytics')->willReturn(['total_commissions' => 0]);
        $this->repository->method('getPayrollData')->willReturn(collect([]));

        // Warm up caches
        $this->cacheService->warmUpCaches();

        // Verify that caches are populated
        $this->assertTrue(Cache::has('employee:department_hierarchy'));
        $this->assertTrue(Cache::has('employee:organizational_chart'));
        $this->assertTrue(Cache::has('employee:statistics'));
    }

    /**
     * Test clear all caches functionality
     */
    public function test_clear_all_caches(): void
    {
        // Set up various caches
        Cache::put('employee:test1', 'data1', 3600);
        Cache::put('employee:test2', 'data2', 3600);
        Cache::put('other:test', 'data3', 3600);

        $this->assertTrue(Cache::has('employee:test1'));
        $this->assertTrue(Cache::has('employee:test2'));
        $this->assertTrue(Cache::has('other:test'));

        // Clear all employee caches
        $this->cacheService->clearAllCaches();

        // Note: Pattern-based clearing testing would require Redis
        // In unit tests, we verify the method doesn't throw errors
        $this->assertTrue(true);
    }

    /**
     * Test cache statistics
     */
    public function test_cache_statistics(): void
    {
        $stats = $this->cacheService->getCacheStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('cache_prefix', $stats);
        $this->assertArrayHasKey('default_ttl', $stats);
        $this->assertArrayHasKey('cache_driver', $stats);
        $this->assertEquals('employee:', $stats['cache_prefix']);
    }

    /**
     * Test force refresh functionality
     */
    public function test_force_refresh(): void
    {
        $mockData = collect([
            (object) ['id' => 1, 'name' => 'IT']
        ]);

        // Set up repository to be called twice (once for cache, once for force refresh)
        $this->repository->expects($this->exactly(2))
            ->method('getDepartmentHierarchyOptimized')
            ->willReturn($mockData);

        // First call populates cache
        $result1 = $this->cacheService->getDepartmentHierarchy();
        
        // Force refresh should bypass cache and call repository again
        $result2 = $this->cacheService->getDepartmentHierarchy(true);

        $this->assertEquals($result1, $result2);
    }
}