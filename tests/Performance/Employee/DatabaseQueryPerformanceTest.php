<?php

declare(strict_types=1);

namespace Tests\Performance\Employee;

use App\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Infrastructure\Persistence\Repositories\EloquentEmployeeRepository;
use App\Infrastructure\Persistence\Repositories\OptimizedEmployeeRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Performance tests for employee database queries
 * 
 * These tests validate that database optimizations improve query performance
 * and prevent N+1 query problems in employee management operations.
 */
class DatabaseQueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private EloquentEmployeeRepository $repository;
    private OptimizedEmployeeRepository $optimizedRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new EloquentEmployeeRepository(new EmployeeModel());
        $this->optimizedRepository = new OptimizedEmployeeRepository(new EmployeeModel());
        
        // Create test data
        $this->createTestData();
    }

    /**
     * Test that optimized queries reduce the number of database queries
     */
    public function test_optimized_find_all_reduces_query_count(): void
    {
        // Test standard repository
        DB::enableQueryLog();
        $standardResults = $this->repository->findAll(['limit' => 50]);
        $standardQueryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Test optimized repository
        DB::enableQueryLog();
        $optimizedResults = $this->optimizedRepository->findAllOptimized(['limit' => 50]);
        $optimizedQueryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Optimized version should use fewer queries
        $this->assertLessThan($standardQueryCount, $optimizedQueryCount);
        $this->assertEquals($standardResults->count(), $optimizedResults->count());
    }

    /**
     * Test department hierarchy query performance
     */
    public function test_department_hierarchy_query_performance(): void
    {
        $startTime = microtime(true);
        
        DB::enableQueryLog();
        $hierarchy = $this->optimizedRepository->getDepartmentHierarchyOptimized();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Should complete in reasonable time with minimal queries
        $this->assertLessThan(100, $executionTime, 'Department hierarchy query should complete in under 100ms');
        $this->assertLessThanOrEqual(1, $queryCount, 'Department hierarchy should use only 1 query');
        $this->assertGreaterThan(0, $hierarchy->count());
    }

    /**
     * Test performance analytics query performance
     */
    public function test_performance_analytics_query_performance(): void
    {
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();

        $startTime = microtime(true);
        
        DB::enableQueryLog();
        $analytics = $this->optimizedRepository->getPerformanceAnalytics($startDate, $endDate);
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Should complete efficiently with minimal queries
        $this->assertLessThan(200, $executionTime, 'Performance analytics should complete in under 200ms');
        $this->assertLessThanOrEqual(1, $queryCount, 'Performance analytics should use only 1 query');
        
        // Verify analytics structure
        $this->assertArrayHasKey('total_reviews', $analytics);
        $this->assertArrayHasKey('average_score', $analytics);
        $this->assertArrayHasKey('by_department', $analytics);
        $this->assertArrayHasKey('by_rating', $analytics);
    }

    /**
     * Test commission analytics query performance
     */
    public function test_commission_analytics_query_performance(): void
    {
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();

        $startTime = microtime(true);
        
        DB::enableQueryLog();
        $analytics = $this->optimizedRepository->getCommissionAnalytics($startDate, $endDate);
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Should complete efficiently
        $this->assertLessThan(200, $executionTime, 'Commission analytics should complete in under 200ms');
        $this->assertLessThanOrEqual(1, $queryCount, 'Commission analytics should use only 1 query');
        
        // Verify analytics structure
        $this->assertArrayHasKey('total_commissions', $analytics);
        $this->assertArrayHasKey('by_type', $analytics);
        $this->assertArrayHasKey('by_department', $analytics);
        $this->assertArrayHasKey('top_earners', $analytics);
    }

    /**
     * Test payroll data query performance
     */
    public function test_payroll_data_query_performance(): void
    {
        $payrollDate = Carbon::now();

        $startTime = microtime(true);
        
        DB::enableQueryLog();
        $payrollData = $this->optimizedRepository->getPayrollData($payrollDate);
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Should complete efficiently with single query
        $this->assertLessThan(150, $executionTime, 'Payroll data query should complete in under 150ms');
        $this->assertLessThanOrEqual(1, $queryCount, 'Payroll data should use only 1 query');
        $this->assertGreaterThan(0, $payrollData->count());
    }

    /**
     * Test organizational chart query performance
     */
    public function test_organizational_chart_query_performance(): void
    {
        $startTime = microtime(true);
        
        DB::enableQueryLog();
        $chart = $this->optimizedRepository->getOrganizationalChart();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Should complete efficiently
        $this->assertLessThan(100, $executionTime, 'Organizational chart should complete in under 100ms');
        $this->assertLessThanOrEqual(1, $queryCount, 'Organizational chart should use only 1 query');
        $this->assertIsArray($chart);
    }

    /**
     * Test search query performance
     */
    public function test_search_query_performance(): void
    {
        $searchTerm = 'John';

        $startTime = microtime(true);
        
        DB::enableQueryLog();
        $results = $this->optimizedRepository->searchOptimized($searchTerm, 20);
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Should complete quickly with single query
        $this->assertLessThan(50, $executionTime, 'Search should complete in under 50ms');
        $this->assertLessThanOrEqual(1, $queryCount, 'Search should use only 1 query');
    }

    /**
     * Test index effectiveness with EXPLAIN queries
     */
    public function test_index_effectiveness(): void
    {
        // Test employee status index
        $explain = DB::select('EXPLAIN SELECT * FROM employees WHERE employment_status = ? AND department_id = ?', ['active', 1]);
        $this->assertNotEmpty($explain);
        
        // Test performance review index
        $explain = DB::select('EXPLAIN SELECT * FROM employee_performance WHERE employee_id = ? AND period_end >= ?', [1, Carbon::now()->subMonths(6)]);
        $this->assertNotEmpty($explain);
        
        // Test commission index
        $explain = DB::select('EXPLAIN SELECT * FROM employee_commissions WHERE employee_id = ? AND earned_date BETWEEN ? AND ?', [1, Carbon::now()->subMonth(), Carbon::now()]);
        $this->assertNotEmpty($explain);
    }

    /**
     * Test large dataset performance
     */
    public function test_large_dataset_performance(): void
    {
        // Create additional test data for large dataset testing
        $this->createLargeTestDataset();

        $startTime = microtime(true);
        
        // Test complex query with filters
        $results = $this->optimizedRepository->findAllOptimized([
            'status' => 'active',
            'hire_date_from' => Carbon::now()->subYears(2),
            'salary_min' => 30000,
            'limit' => 100
        ]);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Should handle large datasets efficiently
        $this->assertLessThan(300, $executionTime, 'Large dataset query should complete in under 300ms');
        $this->assertLessThanOrEqual(100, $results->count());
    }

    /**
     * Create test data for performance testing
     */
    private function createTestData(): void
    {
        // Create departments
        $departments = DepartmentModel::factory()->count(5)->create();
        
        // Create positions
        $positions = [];
        foreach ($departments as $department) {
            $positions = array_merge($positions, PositionModel::factory()
                ->count(3)
                ->create(['department_id' => $department->id])
                ->toArray());
        }

        // Create employees
        foreach ($departments as $department) {
            $departmentPositions = array_filter($positions, fn($p) => $p['department_id'] == $department->id);
            
            EmployeeModel::factory()
                ->count(10)
                ->create([
                    'department_id' => $department->id,
                    'position_id' => collect($departmentPositions)->random()['id']
                ]);
        }
    }

    /**
     * Create large test dataset for performance testing
     */
    private function createLargeTestDataset(): void
    {
        // Create additional departments
        $departments = DepartmentModel::factory()->count(10)->create();
        
        // Create more positions
        foreach ($departments as $department) {
            $positions = PositionModel::factory()
                ->count(5)
                ->create(['department_id' => $department->id]);

            // Create more employees
            foreach ($positions as $position) {
                EmployeeModel::factory()
                    ->count(20)
                    ->create([
                        'department_id' => $department->id,
                        'position_id' => $position->id
                    ]);
            }
        }
    }
}