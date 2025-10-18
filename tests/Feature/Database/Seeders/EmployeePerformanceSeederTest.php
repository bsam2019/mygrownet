<?php

namespace Tests\Feature\Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\EmployeePerformanceSeeder;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\PositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePerformanceSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed dependencies
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        $this->artisan('db:seed', ['--class' => DepartmentSeeder::class]);
        $this->artisan('db:seed', ['--class' => PositionSeeder::class]);
        $this->artisan('db:seed', ['--class' => EmployeeSeeder::class]);
    }

    public function test_performance_seeder_creates_reviews(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeePerformanceSeeder::class]);

        $this->assertGreaterThan(0, EmployeePerformanceModel::count());
        
        // Should have at least one review per active employee
        $activeEmployees = EmployeeModel::where('employment_status', 'active')->count();
        $this->assertGreaterThanOrEqual($activeEmployees, EmployeePerformanceModel::count());
    }

    public function test_performance_reviews_have_valid_scores(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeePerformanceSeeder::class]);

        $reviews = EmployeePerformanceModel::all();

        foreach ($reviews as $review) {
            // Overall score should be between 1 and 5
            $this->assertGreaterThanOrEqual(1, $review->overall_score);
            $this->assertLessThanOrEqual(5, $review->overall_score);
            
            // Individual metrics should be between 1 and 5
            if ($review->quality_score) {
                $this->assertGreaterThanOrEqual(1, $review->quality_score);
                $this->assertLessThanOrEqual(5, $review->quality_score);
            }
            
            if ($review->productivity_score) {
                $this->assertGreaterThanOrEqual(1, $review->productivity_score);
                $this->assertLessThanOrEqual(5, $review->productivity_score);
            }
            
            if ($review->teamwork_score) {
                $this->assertGreaterThanOrEqual(1, $review->teamwork_score);
                $this->assertLessThanOrEqual(5, $review->teamwork_score);
            }
        }
    }

    public function test_performance_reviews_have_valid_periods(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeePerformanceSeeder::class]);

        $reviews = EmployeePerformanceModel::all();

        foreach ($reviews as $review) {
            $this->assertNotNull($review->review_period_start);
            $this->assertNotNull($review->review_period_end);
            
            // End date should be after start date
            $this->assertTrue($review->review_period_start < $review->review_period_end);
            
            // Review period should not be in the future
            $this->assertLessThanOrEqual(now(), $review->review_period_end);
            
            // Review period should be reasonable (not more than 1 year)
            $periodLength = $review->review_period_start->diffInDays($review->review_period_end);
            $this->assertLessThanOrEqual(365, $periodLength);
        }
    }

    public function test_performance_reviews_have_valid_employee_reviewer_relationships(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeePerformanceSeeder::class]);

        $reviews = EmployeePerformanceModel::with(['employee', 'reviewer'])->get();

        foreach ($reviews as $review) {
            // Employee must exist
            $this->assertNotNull($review->employee);
            
            // Reviewer must exist
            $this->assertNotNull($review->reviewer);
            
            // Employee and reviewer should be different
            $this->assertNotEquals($review->employee_id, $review->reviewer_id);
            
            // Reviewer should be manager or in management position
            $reviewer = $review->reviewer;
            $employee = $review->employee;
            
            $this->assertTrue(
                $reviewer->id === $employee->manager_id ||
                $reviewer->position->title === 'HR Manager' ||
                $reviewer->position->title === 'Investment Director'
            );
        }
    }

    public function test_performance_reviews_have_goals_and_achievements(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeePerformanceSeeder::class]);

        $reviews = EmployeePerformanceModel::all();

        foreach ($reviews as $review) {
            if ($review->goals) {
                $this->assertIsString($review->goals);
                $this->assertNotEmpty(trim($review->goals));
            }
            
            if ($review->achievements) {
                $this->assertIsString($review->achievements);
                $this->assertNotEmpty(trim($review->achievements));
            }
            
            if ($review->areas_for_improvement) {
                $this->assertIsString($review->areas_for_improvement);
                $this->assertNotEmpty(trim($review->areas_for_improvement));
            }
        }
    }

    public function test_performance_reviews_have_valid_status(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeePerformanceSeeder::class]);

        $reviews = EmployeePerformanceModel::all();
        $validStatuses = ['draft', 'completed', 'approved', 'acknowledged'];

        foreach ($reviews as $review) {
            $this->assertContains($review->status, $validStatuses);
        }
    }

    public function test_performance_reviews_are_distributed_across_time(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeePerformanceSeeder::class]);

        $reviews = EmployeePerformanceModel::all();
        $reviewDates = $reviews->pluck('review_period_end')->map(function ($date) {
            return $date->format('Y-m');
        })->unique();

        // Should have reviews across multiple months
        $this->assertGreaterThan(1, $reviewDates->count());
    }

    public function test_employees_have_multiple_reviews_over_time(): void
    {
        $this->artisan('db:seed', ['--class' => EmployeePerformanceSeeder::class]);

        $employeeReviewCounts = EmployeePerformanceModel::groupBy('employee_id')
            ->selectRaw('employee_id, count(*) as review_count')
            ->pluck('review_count', 'employee_id');

        // Some employees should have multiple reviews
        $employeesWithMultipleReviews = $employeeReviewCounts->filter(function ($count) {
            return $count > 1;
        });

        $this->assertGreaterThan(0, $employeesWithMultipleReviews->count());
    }
}