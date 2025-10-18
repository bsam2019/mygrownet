<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\Events;

use App\Domain\Employee\Events\PerformanceReviewed;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\PerformanceMetrics;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PerformanceReviewedTest extends TestCase
{
    private EmployeeId $employeeId;
    private EmployeeId $reviewerId;
    private PerformanceMetrics $performanceMetrics;
    private DateTimeImmutable $evaluationPeriodStart;
    private DateTimeImmutable $evaluationPeriodEnd;
    private array $goals;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employeeId = EmployeeId::generate();
        $this->reviewerId = EmployeeId::generate();
        $this->evaluationPeriodStart = new DateTimeImmutable('2024-01-01');
        $this->evaluationPeriodEnd = new DateTimeImmutable('2024-03-31');
        $this->performanceMetrics = PerformanceMetrics::create(
            investmentsFacilitated: 100000.0,
            clientRetentionRate: 85.5,
            commissionGenerated: 5000.0,
            newClientAcquisitions: 5,
            goalAchievementRate: 90.0,
            evaluationPeriodStart: $this->evaluationPeriodStart,
            evaluationPeriodEnd: $this->evaluationPeriodEnd
        );
        $this->goals = [
            'Facilitate 10 new investments',
            'Maintain 85% client retention rate',
            'Acquire 5 new clients'
        ];
    }

    public function test_can_create_performance_reviewed_event(): void
    {
        $event = new PerformanceReviewed(
            employeeId: $this->employeeId,
            reviewerId: $this->reviewerId,
            performanceMetrics: $this->performanceMetrics,
            evaluationPeriodStart: $this->evaluationPeriodStart,
            evaluationPeriodEnd: $this->evaluationPeriodEnd,
            overallScore: 8.5,
            goals: $this->goals,
            reviewNotes: 'Excellent performance this quarter',
            goalsNextPeriod: ['Increase client base by 20%']
        );

        $this->assertTrue($this->employeeId->equals($event->employeeId));
        $this->assertTrue($this->reviewerId->equals($event->reviewerId));
        $this->assertEquals($this->performanceMetrics, $event->performanceMetrics);
        $this->assertEquals($this->evaluationPeriodStart, $event->evaluationPeriodStart);
        $this->assertEquals($this->evaluationPeriodEnd, $event->evaluationPeriodEnd);
        $this->assertEquals(8.5, $event->overallScore);
        $this->assertEquals($this->goals, $event->goals);
        $this->assertEquals('Excellent performance this quarter', $event->reviewNotes);
        $this->assertEquals(['Increase client base by 20%'], $event->goalsNextPeriod);
    }  
  public function test_to_array_returns_correct_data(): void
    {
        $event = new PerformanceReviewed(
            employeeId: $this->employeeId,
            reviewerId: $this->reviewerId,
            performanceMetrics: $this->performanceMetrics,
            evaluationPeriodStart: $this->evaluationPeriodStart,
            evaluationPeriodEnd: $this->evaluationPeriodEnd,
            overallScore: 8.5,
            goals: $this->goals,
            reviewNotes: 'Great work'
        );

        $array = $event->toArray();

        $this->assertEquals($this->employeeId->toString(), $array['employee_id']);
        $this->assertEquals($this->reviewerId->toString(), $array['reviewer_id']);
        $this->assertEquals('2024-01-01', $array['evaluation_period_start']);
        $this->assertEquals('2024-03-31', $array['evaluation_period_end']);
        $this->assertEquals(100000.0, $array['investments_facilitated_amount']);
        $this->assertEquals(85.5, $array['client_retention_rate']);
        $this->assertEquals(5000.0, $array['commission_generated']);
        $this->assertEquals(5, $array['new_client_acquisitions']);
        $this->assertEquals(90.0, $array['goal_achievement_rate']);
        $this->assertEquals(8.5, $array['overall_score']);
        $this->assertEquals($this->goals, $array['goals']);
        $this->assertEquals('Great work', $array['review_notes']);
        $this->assertArrayHasKey('occurred_at', $array);
    }

    public function test_get_event_name_returns_correct_name(): void
    {
        $event = new PerformanceReviewed(
            employeeId: $this->employeeId,
            reviewerId: $this->reviewerId,
            performanceMetrics: $this->performanceMetrics,
            evaluationPeriodStart: $this->evaluationPeriodStart,
            evaluationPeriodEnd: $this->evaluationPeriodEnd,
            overallScore: 8.5,
            goals: $this->goals
        );

        $this->assertEquals('employee.performance_reviewed', $event->getEventName());
    }

    public function test_is_performance_improvement_returns_true_when_score_improved(): void
    {
        $event = new PerformanceReviewed(
            employeeId: $this->employeeId,
            reviewerId: $this->reviewerId,
            performanceMetrics: $this->performanceMetrics,
            evaluationPeriodStart: $this->evaluationPeriodStart,
            evaluationPeriodEnd: $this->evaluationPeriodEnd,
            overallScore: 8.5,
            goals: $this->goals
        );

        $this->assertTrue($event->isPerformanceImprovement(7.0));
        $this->assertFalse($event->isPerformanceImprovement(9.0));
    }

    public function test_meets_expectations_returns_true_for_score_above_seven(): void
    {
        $event = new PerformanceReviewed(
            employeeId: $this->employeeId,
            reviewerId: $this->reviewerId,
            performanceMetrics: $this->performanceMetrics,
            evaluationPeriodStart: $this->evaluationPeriodStart,
            evaluationPeriodEnd: $this->evaluationPeriodEnd,
            overallScore: 8.5,
            goals: $this->goals
        );

        $this->assertTrue($event->meetsExpectations());
    }

    public function test_is_exceptional_returns_true_for_score_above_nine(): void
    {
        $event = new PerformanceReviewed(
            employeeId: $this->employeeId,
            reviewerId: $this->reviewerId,
            performanceMetrics: $this->performanceMetrics,
            evaluationPeriodStart: $this->evaluationPeriodStart,
            evaluationPeriodEnd: $this->evaluationPeriodEnd,
            overallScore: 9.5,
            goals: $this->goals
        );

        $this->assertTrue($event->isExceptional());
    }

    public function test_is_valid_returns_true_for_valid_event(): void
    {
        $event = new PerformanceReviewed(
            employeeId: $this->employeeId,
            reviewerId: $this->reviewerId,
            performanceMetrics: $this->performanceMetrics,
            evaluationPeriodStart: $this->evaluationPeriodStart,
            evaluationPeriodEnd: $this->evaluationPeriodEnd,
            overallScore: 8.5,
            goals: $this->goals
        );

        $this->assertTrue($event->isValid());
    }

    public function test_is_valid_returns_false_when_employee_reviews_themselves(): void
    {
        $event = new PerformanceReviewed(
            employeeId: $this->employeeId,
            reviewerId: $this->employeeId, // Same as employee
            performanceMetrics: $this->performanceMetrics,
            evaluationPeriodStart: $this->evaluationPeriodStart,
            evaluationPeriodEnd: $this->evaluationPeriodEnd,
            overallScore: 8.5,
            goals: $this->goals
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_invalid_evaluation_period(): void
    {
        $event = new PerformanceReviewed(
            employeeId: $this->employeeId,
            reviewerId: $this->reviewerId,
            performanceMetrics: $this->performanceMetrics,
            evaluationPeriodStart: $this->evaluationPeriodEnd, // Start after end
            evaluationPeriodEnd: $this->evaluationPeriodStart,
            overallScore: 8.5,
            goals: $this->goals
        );

        $this->assertFalse($event->isValid());
    }

    public function test_is_valid_returns_false_for_empty_goals(): void
    {
        $event = new PerformanceReviewed(
            employeeId: $this->employeeId,
            reviewerId: $this->reviewerId,
            performanceMetrics: $this->performanceMetrics,
            evaluationPeriodStart: $this->evaluationPeriodStart,
            evaluationPeriodEnd: $this->evaluationPeriodEnd,
            overallScore: 8.5,
            goals: [] // Empty goals
        );

        $this->assertFalse($event->isValid());
    }
}