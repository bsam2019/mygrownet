<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Eloquent\EmployeePerformanceModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePerformanceModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_performance_has_fillable_attributes(): void
    {
        $fillable = [
            'employee_id',
            'reviewer_id',
            'evaluation_period',
            'period_start',
            'period_end',
            'metrics',
            'overall_score',
            'rating',
            'strengths',
            'areas_for_improvement',
            'goals_next_period',
            'reviewer_comments',
            'employee_comments',
            'status',
            'submitted_at',
            'approved_at',
        ];

        $performance = new EmployeePerformanceModel();
        
        $this->assertEquals($fillable, $performance->getFillable());
    }

    public function test_employee_performance_casts_attributes_correctly(): void
    {
        $performance = EmployeePerformanceModel::factory()->create([
            'period_start' => '2023-01-01',
            'period_end' => '2023-03-31',
            'metrics' => [
                'investments_facilitated_count' => 25,
                'investments_facilitated_amount' => 150000.00,
                'client_retention_rate' => 85.50,
                'commission_generated' => 15000.00,
                'new_client_acquisitions' => 10,
                'goal_achievement_rate' => 95.75,
            ],
            'overall_score' => '8.50',
            'rating' => 'good',
            'goals_next_period' => 'Goal 1, Goal 2',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $performance->period_start);
        $this->assertInstanceOf(\Carbon\Carbon::class, $performance->period_end);
        $this->assertIsArray($performance->metrics);
        $this->assertIsFloat($performance->overall_score);
        $this->assertIsString($performance->rating);
        $this->assertIsString($performance->goals_next_period);
    }

    public function test_employee_performance_belongs_to_employee(): void
    {
        $employee = EmployeeModel::factory()->create();
        $performance = EmployeePerformanceModel::factory()->create([
            'employee_id' => $employee->id,
        ]);

        $this->assertInstanceOf(EmployeeModel::class, $performance->employee);
        $this->assertEquals($employee->id, $performance->employee->id);
    }

    public function test_employee_performance_belongs_to_reviewer(): void
    {
        $reviewer = EmployeeModel::factory()->create();
        $performance = EmployeePerformanceModel::factory()->create([
            'reviewer_id' => $reviewer->id,
        ]);

        $this->assertInstanceOf(EmployeeModel::class, $performance->reviewer);
        $this->assertEquals($reviewer->id, $performance->reviewer->id);
    }

    public function test_for_employee_scope_filters_by_employee(): void
    {
        $employee1 = EmployeeModel::factory()->create();
        $employee2 = EmployeeModel::factory()->create();
        
        EmployeePerformanceModel::factory()->count(3)->create(['employee_id' => $employee1->id]);
        EmployeePerformanceModel::factory()->count(2)->create(['employee_id' => $employee2->id]);

        $performanceForEmployee1 = EmployeePerformanceModel::forEmployee($employee1->id)->get();

        $this->assertCount(3, $performanceForEmployee1);
        
        foreach ($performanceForEmployee1 as $performance) {
            $this->assertEquals($employee1->id, $performance->employee_id);
        }
    }

    public function test_for_period_scope_filters_by_evaluation_period(): void
    {
        EmployeePerformanceModel::factory()->create([
            'period_start' => '2023-01-01',
            'period_end' => '2023-03-31',
        ]);
        EmployeePerformanceModel::factory()->create([
            'period_start' => '2023-04-01',
            'period_end' => '2023-06-30',
        ]);
        EmployeePerformanceModel::factory()->create([
            'period_start' => '2023-07-01',
            'period_end' => '2023-09-30',
        ]);

        $performanceInPeriod = EmployeePerformanceModel::forPeriod('2023-01-01', '2023-06-30')->get();

        $this->assertCount(2, $performanceInPeriod);
    }

    public function test_by_reviewer_scope_filters_by_reviewer(): void
    {
        $reviewer1 = EmployeeModel::factory()->create();
        $reviewer2 = EmployeeModel::factory()->create();
        
        EmployeePerformanceModel::factory()->count(2)->create(['reviewer_id' => $reviewer1->id]);
        EmployeePerformanceModel::factory()->count(3)->create(['reviewer_id' => $reviewer2->id]);

        $performanceByReviewer1 = EmployeePerformanceModel::byReviewer($reviewer1->id)->get();

        $this->assertCount(2, $performanceByReviewer1);
        
        foreach ($performanceByReviewer1 as $performance) {
            $this->assertEquals($reviewer1->id, $performance->reviewer_id);
        }
    }

    public function test_with_min_score_scope_filters_by_minimum_score(): void
    {
        EmployeePerformanceModel::factory()->create(['overall_score' => 9.5]);
        EmployeePerformanceModel::factory()->create(['overall_score' => 8.0]);
        EmployeePerformanceModel::factory()->create(['overall_score' => 6.5]);
        EmployeePerformanceModel::factory()->create(['overall_score' => 7.8]);

        $highPerformance = EmployeePerformanceModel::withMinScore(8.0)->get();

        $this->assertCount(2, $highPerformance);
        
        foreach ($highPerformance as $performance) {
            $this->assertGreaterThanOrEqual(8.0, $performance->overall_score);
        }
    }

    public function test_recent_scope_filters_by_recent_evaluations(): void
    {
        EmployeePerformanceModel::factory()->create([
            'period_end' => now()->subMonths(6),
        ]);
        EmployeePerformanceModel::factory()->create([
            'period_end' => now()->subMonths(18),
        ]);
        EmployeePerformanceModel::factory()->create([
            'period_end' => now()->subMonths(3),
        ]);

        $recentPerformance = EmployeePerformanceModel::recent(12)->get();

        $this->assertCount(2, $recentPerformance);
    }

    public function test_order_by_score_scope_orders_by_score(): void
    {
        EmployeePerformanceModel::factory()->create(['overall_score' => 7.5]);
        EmployeePerformanceModel::factory()->create(['overall_score' => 9.2]);
        EmployeePerformanceModel::factory()->create(['overall_score' => 6.8]);

        $performanceDesc = EmployeePerformanceModel::orderByScore('desc')->get();
        $performanceAsc = EmployeePerformanceModel::orderByScore('asc')->get();

        $this->assertEquals(9.2, $performanceDesc->first()->overall_score);
        $this->assertEquals(6.8, $performanceDesc->last()->overall_score);
        $this->assertEquals(6.8, $performanceAsc->first()->overall_score);
        $this->assertEquals(9.2, $performanceAsc->last()->overall_score);
    }

    public function test_evaluation_period_accessor(): void
    {
        $performance = EmployeePerformanceModel::factory()->create([
            'period_start' => '2023-01-01',
            'period_end' => '2023-03-31',
        ]);

        $this->assertEquals('Jan 2023 - Mar 2023', $performance->evaluation_period);
    }

    public function test_performance_grade_accessor(): void
    {
        $excellent = EmployeePerformanceModel::factory()->create(['rating' => 'excellent']);
        $good = EmployeePerformanceModel::factory()->create(['rating' => 'good']);
        $satisfactory = EmployeePerformanceModel::factory()->create(['rating' => 'satisfactory']);
        $needsImprovement = EmployeePerformanceModel::factory()->create(['rating' => 'needs_improvement']);
        $unsatisfactory = EmployeePerformanceModel::factory()->create(['rating' => 'unsatisfactory']);

        $this->assertEquals('Excellent', $excellent->performance_grade);
        $this->assertEquals('Good', $good->performance_grade);
        $this->assertEquals('Satisfactory', $satisfactory->performance_grade);
        $this->assertEquals('Needs_improvement', $needsImprovement->performance_grade);
        $this->assertEquals('Unsatisfactory', $unsatisfactory->performance_grade);
    }

    public function test_commission_per_investment_accessor(): void
    {
        $performance = EmployeePerformanceModel::factory()->create([
            'metrics' => [
                'investments_facilitated_count' => 10,
                'commission_generated' => 5000,
            ],
        ]);

        $this->assertEquals(500.0, $performance->commission_per_investment);
    }

    public function test_commission_per_investment_accessor_with_zero_investments(): void
    {
        $performance = EmployeePerformanceModel::factory()->create([
            'metrics' => [
                'investments_facilitated_count' => 0,
                'commission_generated' => 0,
            ],
        ]);

        $this->assertEquals(0, $performance->commission_per_investment);
    }

    public function test_employee_performance_factory_states_work_correctly(): void
    {
        $excellent = EmployeePerformanceModel::factory()->excellent()->create();
        $good = EmployeePerformanceModel::factory()->good()->create();
        $needsImprovement = EmployeePerformanceModel::factory()->needsImprovement()->create();

        $this->assertGreaterThanOrEqual(90.0, $excellent->overall_score);
        $this->assertEquals('excellent', $excellent->rating);
        $this->assertGreaterThanOrEqual(70.0, $good->overall_score);
        $this->assertLessThan(90.0, $good->overall_score);
        $this->assertEquals('good', $good->rating);
        $this->assertGreaterThanOrEqual(40.0, $needsImprovement->overall_score);
        $this->assertLessThan(70.0, $needsImprovement->overall_score);
        $this->assertEquals('needs_improvement', $needsImprovement->rating);
    }

    public function test_employee_performance_can_be_created_with_factory(): void
    {
        $performance = EmployeePerformanceModel::factory()->create();

        $this->assertInstanceOf(EmployeePerformanceModel::class, $performance);
        $this->assertNotNull($performance->employee_id);
        $this->assertNotNull($performance->reviewer_id);
        $this->assertNotNull($performance->period_start);
        $this->assertNotNull($performance->period_end);
        $this->assertIsFloat($performance->overall_score);
    }
}