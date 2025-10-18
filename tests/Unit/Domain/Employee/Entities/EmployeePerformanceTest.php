<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Entities;

use App\Domain\Employee\Entities\Department;
use App\Domain\Employee\Entities\Employee;
use App\Domain\Employee\Entities\EmployeePerformance;
use App\Domain\Employee\Entities\Position;
use App\Domain\Employee\Exceptions\EmployeePerformanceException;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\PerformanceMetrics;
use App\Domain\Employee\ValueObjects\Salary;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class EmployeePerformanceTest extends TestCase
{
    private Employee $employee;
    private Employee $reviewer;
    private PerformanceMetrics $metrics;
    private DateTimeImmutable $periodStart;
    private DateTimeImmutable $periodEnd;

    protected function setUp(): void
    {
        $department = Department::create('Engineering', 'Software development department');
        $position = Position::create(
            'Developer',
            'Software development',
            $department,
            Salary::fromKwacha(5000),
            Salary::fromKwacha(10000)
        );

        $this->employee = Employee::create(
            'EMP001',
            'John',
            'Doe',
            Email::fromString('john.doe@example.com'),
            new DateTimeImmutable('2023-01-01'),
            $department,
            $position,
            Salary::fromKwacha(7500)
        );

        $this->reviewer = Employee::create(
            'MGR001',
            'Jane',
            'Manager',
            Email::fromString('jane.manager@example.com'),
            new DateTimeImmutable('2022-01-01'),
            $department,
            $position,
            Salary::fromKwacha(9000)
        );

        $this->periodStart = new DateTimeImmutable('2023-01-01');
        $this->periodEnd = new DateTimeImmutable('2023-03-31');

        $this->metrics = PerformanceMetrics::create(
            10.0,
            85.5,
            5000.0,
            5,
            90.0,
            $this->periodStart,
            $this->periodEnd
        );
    }

    public function test_can_create_employee_performance(): void
    {
        $performance = EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $this->periodEnd,
            $this->metrics,
            $this->reviewer,
            'Good performance this quarter'
        );

        $this->assertEquals($this->employee, $performance->getEmployee());
        $this->assertEquals($this->periodStart, $performance->getEvaluationPeriodStart());
        $this->assertEquals($this->periodEnd, $performance->getEvaluationPeriodEnd());
        $this->assertEquals($this->metrics, $performance->getMetrics());
        $this->assertEquals($this->reviewer, $performance->getReviewer());
        $this->assertEquals('Good performance this quarter', $performance->getReviewNotes());
        $this->assertEmpty($performance->getGoalsNextPeriod());
    }

    public function test_can_update_metrics(): void
    {
        $performance = EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $this->periodEnd,
            $this->metrics,
            $this->reviewer
        );

        $newMetrics = PerformanceMetrics::create(
            12.0,
            90.0,
            6000.0,
            7,
            95.0,
            $this->periodStart,
            $this->periodEnd
        );

        $performance->updateMetrics($newMetrics);

        $this->assertEquals($newMetrics, $performance->getMetrics());
    }

    public function test_can_update_review_notes(): void
    {
        $performance = EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $this->periodEnd,
            $this->metrics,
            $this->reviewer
        );

        $performance->updateReviewNotes('Updated review notes');

        $this->assertEquals('Updated review notes', $performance->getReviewNotes());
    }

    public function test_can_add_goal_for_next_period(): void
    {
        $performance = EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $this->periodEnd,
            $this->metrics,
            $this->reviewer
        );

        $performance->addGoalForNextPeriod('Improve client retention to 95%');
        $performance->addGoalForNextPeriod('Complete advanced training course');

        $goals = $performance->getGoalsNextPeriod();
        $this->assertCount(2, $goals);
        $this->assertContains('Improve client retention to 95%', $goals);
        $this->assertContains('Complete advanced training course', $goals);
    }

    public function test_cannot_add_empty_goal(): void
    {
        $performance = EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $this->periodEnd,
            $this->metrics,
            $this->reviewer
        );

        $this->expectException(EmployeePerformanceException::class);
        $this->expectExceptionMessage('Invalid goal');

        $performance->addGoalForNextPeriod('');
    }

    public function test_cannot_add_duplicate_goal(): void
    {
        $performance = EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $this->periodEnd,
            $this->metrics,
            $this->reviewer
        );

        $performance->addGoalForNextPeriod('Improve client retention');
        $performance->addGoalForNextPeriod('Improve client retention'); // Duplicate

        $this->assertCount(1, $performance->getGoalsNextPeriod());
    }

    public function test_can_remove_goal_for_next_period(): void
    {
        $performance = EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $this->periodEnd,
            $this->metrics,
            $this->reviewer
        );

        $performance->addGoalForNextPeriod('Goal 1');
        $performance->addGoalForNextPeriod('Goal 2');
        $performance->removeGoalForNextPeriod('Goal 1');

        $goals = $performance->getGoalsNextPeriod();
        $this->assertCount(1, $goals);
        $this->assertNotContains('Goal 1', $goals);
        $this->assertContains('Goal 2', $goals);
    }

    public function test_can_set_goals_for_next_period(): void
    {
        $performance = EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $this->periodEnd,
            $this->metrics,
            $this->reviewer
        );

        $goals = [
            'Improve client retention to 95%',
            'Complete training course',
            'Mentor junior developers'
        ];

        $performance->setGoalsForNextPeriod($goals);

        $this->assertEquals($goals, $performance->getGoalsNextPeriod());
    }

    public function test_can_compare_with_other_performance(): void
    {
        $performance1 = EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $this->periodEnd,
            $this->metrics,
            $this->reviewer
        );

        $newMetrics = PerformanceMetrics::create(
            12.0, // +2 investments
            90.0, // +4.5% retention
            6000.0, // +1000 commission
            7, // +2 new clients
            95.0, // +5% goal achievement
            $this->periodStart,
            $this->periodEnd
        );

        $performance2 = EmployeePerformance::create(
            $this->employee,
            new DateTimeImmutable('2023-04-01'),
            new DateTimeImmutable('2023-06-30'),
            $newMetrics,
            $this->reviewer
        );

        $comparison = $performance2->compareWith($performance1);

        $this->assertEquals(2.0, $comparison['investments_facilitated_change']);
        $this->assertEquals(4.5, $comparison['client_retention_change']);
        $this->assertEquals(1000.0, $comparison['commission_change']);
        $this->assertEquals(2, $comparison['new_clients_change']);
        $this->assertEquals(5.0, $comparison['goal_achievement_change']);
    }

    public function test_performance_ratings(): void
    {
        // Outstanding performance (score >= 9.0)
        // Using high values that should result in score >= 9.0 based on normalization thresholds
        $outstandingMetrics = PerformanceMetrics::create(
            45000.0, // Close to 50k threshold for excellent investment score
            95.0,    // 95% retention rate
            4500.0,  // Close to 5k threshold for excellent commission
            18,      // Close to 20 threshold for excellent acquisitions
            95.0,    // 95% goal achievement
            $this->periodStart, $this->periodEnd
        );
        $outstandingPerformance = EmployeePerformance::create(
            $this->employee, $this->periodStart, $this->periodEnd,
            $outstandingMetrics, $this->reviewer
        );

        $this->assertTrue($outstandingPerformance->isOutstanding());
        $this->assertFalse($outstandingPerformance->meetsExpectations());
        $this->assertFalse($outstandingPerformance->needsImprovement());
        $this->assertEquals('Outstanding', $outstandingPerformance->getPerformanceRating());

        // Meets expectations (6.0 <= score < 9.0)
        $meetsExpectationsMetrics = PerformanceMetrics::create(
            25000.0, // Mid-range investment amount
            80.0,    // 80% retention rate
            2500.0,  // Mid-range commission
            10,      // Mid-range acquisitions
            75.0,    // 75% goal achievement
            $this->periodStart, $this->periodEnd
        );
        $meetsExpectationsPerformance = EmployeePerformance::create(
            $this->employee, $this->periodStart, $this->periodEnd,
            $meetsExpectationsMetrics, $this->reviewer
        );

        $this->assertFalse($meetsExpectationsPerformance->isOutstanding());
        $this->assertTrue($meetsExpectationsPerformance->meetsExpectations());
        $this->assertFalse($meetsExpectationsPerformance->needsImprovement());
        $this->assertEquals('Meets Expectations', $meetsExpectationsPerformance->getPerformanceRating());

        // Needs improvement (score < 6.0)
        $needsImprovementMetrics = PerformanceMetrics::create(
            5000.0,  // Low investment amount
            60.0,    // 60% retention rate
            500.0,   // Low commission
            2,       // Low acquisitions
            50.0,    // 50% goal achievement
            $this->periodStart, $this->periodEnd
        );
        $needsImprovementPerformance = EmployeePerformance::create(
            $this->employee, $this->periodStart, $this->periodEnd,
            $needsImprovementMetrics, $this->reviewer
        );

        $this->assertFalse($needsImprovementPerformance->isOutstanding());
        $this->assertFalse($needsImprovementPerformance->meetsExpectations());
        $this->assertTrue($needsImprovementPerformance->needsImprovement());
        $this->assertEquals('Needs Improvement', $needsImprovementPerformance->getPerformanceRating());
    }

    public function test_period_duration_and_review_types(): void
    {
        // Quarterly review (approximately 90 days)
        $quarterlyStart = new DateTimeImmutable('2023-01-01');
        $quarterlyEnd = new DateTimeImmutable('2023-03-31');
        $quarterlyPerformance = EmployeePerformance::create(
            $this->employee, $quarterlyStart, $quarterlyEnd,
            $this->metrics, $this->reviewer
        );

        $this->assertEquals(89, $quarterlyPerformance->getPeriodDurationInDays());
        $this->assertTrue($quarterlyPerformance->isQuarterlyReview());
        $this->assertFalse($quarterlyPerformance->isAnnualReview());

        // Annual review (approximately 365 days)
        $annualStart = new DateTimeImmutable('2023-01-01');
        $annualEnd = new DateTimeImmutable('2023-12-31');
        $annualPerformance = EmployeePerformance::create(
            $this->employee, $annualStart, $annualEnd,
            $this->metrics, $this->reviewer
        );

        $this->assertEquals(364, $annualPerformance->getPeriodDurationInDays());
        $this->assertFalse($annualPerformance->isQuarterlyReview());
        $this->assertTrue($annualPerformance->isAnnualReview());
    }

    public function test_trend_analysis_with_no_previous_data(): void
    {
        $performance = EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $this->periodEnd,
            $this->metrics,
            $this->reviewer
        );

        $analysis = $performance->calculateTrendAnalysis([]);

        $this->assertEquals('no_data', $analysis['trend']);
        $this->assertEquals($this->metrics->calculateOverallScore(), $analysis['average_score']);
        $this->assertEquals(0.0, $analysis['score_variance']);
        $this->assertIsArray($analysis['improvement_areas']);
        $this->assertIsArray($analysis['strengths']);
    }

    public function test_trend_analysis_with_previous_data(): void
    {
        // Create previous performances with improving trend
        $previousMetrics1 = PerformanceMetrics::create(
            6.0, 70.0, 3000.0, 3, 70.0,
            new DateTimeImmutable('2022-10-01'),
            new DateTimeImmutable('2022-12-31')
        );
        $previousPerformance1 = EmployeePerformance::create(
            $this->employee,
            new DateTimeImmutable('2022-10-01'),
            new DateTimeImmutable('2022-12-31'),
            $previousMetrics1,
            $this->reviewer
        );

        $previousMetrics2 = PerformanceMetrics::create(
            8.0, 75.0, 4000.0, 4, 80.0,
            new DateTimeImmutable('2023-01-01'),
            new DateTimeImmutable('2023-03-31')
        );
        $previousPerformance2 = EmployeePerformance::create(
            $this->employee,
            new DateTimeImmutable('2023-01-01'),
            new DateTimeImmutable('2023-03-31'),
            $previousMetrics2,
            $this->reviewer
        );

        $currentMetrics = PerformanceMetrics::create(
            10.0, 85.0, 5000.0, 5, 90.0,
            new DateTimeImmutable('2023-04-01'),
            new DateTimeImmutable('2023-06-30')
        );
        $currentPerformance = EmployeePerformance::create(
            $this->employee,
            new DateTimeImmutable('2023-04-01'),
            new DateTimeImmutable('2023-06-30'),
            $currentMetrics,
            $this->reviewer
        );

        $analysis = $currentPerformance->calculateTrendAnalysis([
            $previousPerformance1,
            $previousPerformance2
        ]);

        $this->assertEquals('improving', $analysis['trend']);
        $this->assertGreaterThan(0, $analysis['score_variance']);
        $this->assertIsArray($analysis['improvement_areas']);
        $this->assertIsArray($analysis['strengths']);
    }

    public function test_cannot_create_performance_with_invalid_period(): void
    {
        $this->expectException(EmployeePerformanceException::class);
        $this->expectExceptionMessage('Invalid evaluation period');

        EmployeePerformance::create(
            $this->employee,
            $this->periodEnd, // End as start
            $this->periodStart, // Start as end - invalid
            $this->metrics,
            $this->reviewer
        );
    }

    public function test_cannot_create_performance_with_future_end_date(): void
    {
        $futureDate = new DateTimeImmutable('+1 month');

        $this->expectException(EmployeePerformanceException::class);
        $this->expectExceptionMessage('Evaluation period cannot end in the future');

        EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $futureDate,
            $this->metrics,
            $this->reviewer
        );
    }

    public function test_cannot_create_self_review(): void
    {
        $this->expectException(EmployeePerformanceException::class);
        $this->expectExceptionMessage('cannot review themselves');

        EmployeePerformance::create(
            $this->employee,
            $this->periodStart,
            $this->periodEnd,
            $this->metrics,
            $this->employee // Same as employee - self review
        );
    }
}