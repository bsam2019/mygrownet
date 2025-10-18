<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Employee\ValueObjects;

use App\Domain\Employee\Exceptions\InvalidPerformanceMetricsException;
use App\Domain\Employee\ValueObjects\PerformanceMetrics;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PerformanceMetricsTest extends TestCase
{
    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;

    protected function setUp(): void
    {
        $this->startDate = new DateTimeImmutable('2024-01-01');
        $this->endDate = new DateTimeImmutable('2024-03-31');
    }

    public function test_can_create_performance_metrics(): void
    {
        $metrics = PerformanceMetrics::create(
            25000.0,    // investments facilitated
            85.5,       // client retention rate
            2500.0,     // commission generated
            10,         // new client acquisitions
            90.0,       // goal achievement rate
            $this->startDate,
            $this->endDate
        );

        $this->assertEquals(25000.0, $metrics->getInvestmentsFacilitated());
        $this->assertEquals(85.5, $metrics->getClientRetentionRate());
        $this->assertEquals(2500.0, $metrics->getCommissionGenerated());
        $this->assertEquals(10, $metrics->getNewClientAcquisitions());
        $this->assertEquals(90.0, $metrics->getGoalAchievementRate());
        $this->assertEquals($this->startDate, $metrics->getEvaluationPeriodStart());
        $this->assertEquals($this->endDate, $metrics->getEvaluationPeriodEnd());
    }

    public function test_throws_exception_for_negative_investments(): void
    {
        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->expectExceptionMessage('Invalid investment amount: -1000. Amount cannot be negative');

        PerformanceMetrics::create(
            -1000.0,
            85.0,
            2500.0,
            10,
            90.0,
            $this->startDate,
            $this->endDate
        );
    }

    public function test_throws_exception_for_invalid_client_retention_rate(): void
    {
        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->expectExceptionMessage('Invalid client retention rate: 150. Percentage must be between 0 and 100');

        PerformanceMetrics::create(
            25000.0,
            150.0,  // Invalid percentage
            2500.0,
            10,
            90.0,
            $this->startDate,
            $this->endDate
        );
    }

    public function test_throws_exception_for_negative_commission(): void
    {
        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->expectExceptionMessage('Invalid commission amount: -500. Amount cannot be negative');

        PerformanceMetrics::create(
            25000.0,
            85.0,
            -500.0,  // Negative commission
            10,
            90.0,
            $this->startDate,
            $this->endDate
        );
    }

    public function test_throws_exception_for_negative_client_acquisitions(): void
    {
        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->expectExceptionMessage('Invalid client count: -5. Count cannot be negative');

        PerformanceMetrics::create(
            25000.0,
            85.0,
            2500.0,
            -5,  // Negative count
            90.0,
            $this->startDate,
            $this->endDate
        );
    }

    public function test_throws_exception_for_invalid_goal_achievement_rate(): void
    {
        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->expectExceptionMessage('Invalid goal achievement rate: -10. Percentage must be between 0 and 100');

        PerformanceMetrics::create(
            25000.0,
            85.0,
            2500.0,
            10,
            -10.0,  // Invalid percentage
            $this->startDate,
            $this->endDate
        );
    }

    public function test_throws_exception_for_invalid_evaluation_period(): void
    {
        $invalidEndDate = new DateTimeImmutable('2023-12-31'); // Before start date

        $this->expectException(InvalidPerformanceMetricsException::class);
        $this->expectExceptionMessage('Invalid evaluation period: start date 2024-01-01 must be before end date 2023-12-31');

        PerformanceMetrics::create(
            25000.0,
            85.0,
            2500.0,
            10,
            90.0,
            $this->startDate,
            $invalidEndDate
        );
    }

    public function test_calculates_overall_score(): void
    {
        $metrics = PerformanceMetrics::create(
            50000.0,    // Excellent investments (normalized to 10)
            100.0,      // Perfect retention (normalized to 10)
            5000.0,     // Excellent commission (normalized to 10)
            20,         // Excellent acquisitions (normalized to 10)
            100.0,      // Perfect goal achievement (normalized to 10)
            $this->startDate,
            $this->endDate
        );

        $overallScore = $metrics->calculateOverallScore();
        
        // With perfect scores and given weights, should be 10.0
        $this->assertEquals(10.0, $overallScore);
    }

    public function test_calculates_partial_overall_score(): void
    {
        $metrics = PerformanceMetrics::create(
            25000.0,    // Half of excellent (normalized to 5)
            50.0,       // Half retention (normalized to 5)
            2500.0,     // Half commission (normalized to 5)
            10,         // Half acquisitions (normalized to 5)
            50.0,       // Half goal achievement (normalized to 5)
            $this->startDate,
            $this->endDate
        );

        $overallScore = $metrics->calculateOverallScore();
        
        // All metrics at 50% should result in score of 5.0
        $this->assertEquals(5.0, $overallScore);
    }

    public function test_compares_with_other_metrics(): void
    {
        $currentMetrics = PerformanceMetrics::create(
            30000.0,
            90.0,
            3000.0,
            15,
            85.0,
            $this->startDate,
            $this->endDate
        );

        $previousMetrics = PerformanceMetrics::create(
            20000.0,
            80.0,
            2000.0,
            10,
            75.0,
            new DateTimeImmutable('2023-10-01'),
            new DateTimeImmutable('2023-12-31')
        );

        $comparison = $currentMetrics->compareWith($previousMetrics);

        $this->assertEquals(30000.0, $comparison['investments_facilitated']['current']);
        $this->assertEquals(20000.0, $comparison['investments_facilitated']['previous']);
        $this->assertEquals(10000.0, $comparison['investments_facilitated']['change']);
        $this->assertEquals(50.0, $comparison['investments_facilitated']['change_percentage']);

        $this->assertEquals(90.0, $comparison['client_retention_rate']['current']);
        $this->assertEquals(80.0, $comparison['client_retention_rate']['previous']);
        $this->assertEquals(10.0, $comparison['client_retention_rate']['change']);
        $this->assertEquals(12.5, $comparison['client_retention_rate']['change_percentage']);

        $this->assertArrayHasKey('overall_score', $comparison);
        $this->assertArrayHasKey('current', $comparison['overall_score']);
        $this->assertArrayHasKey('previous', $comparison['overall_score']);
        $this->assertArrayHasKey('change', $comparison['overall_score']);
    }

    public function test_handles_zero_previous_values_in_comparison(): void
    {
        $currentMetrics = PerformanceMetrics::create(
            30000.0,
            90.0,
            3000.0,
            15,
            85.0,
            $this->startDate,
            $this->endDate
        );

        $previousMetrics = PerformanceMetrics::create(
            0.0,    // Zero previous investments
            0.0,    // Zero previous retention
            0.0,    // Zero previous commission
            0,      // Zero previous acquisitions
            0.0,    // Zero previous goals
            new DateTimeImmutable('2023-10-01'),
            new DateTimeImmutable('2023-12-31')
        );

        $comparison = $currentMetrics->compareWith($previousMetrics);

        // Should handle division by zero gracefully
        $this->assertEquals(0.0, $comparison['investments_facilitated']['change_percentage']);
        $this->assertEquals(0.0, $comparison['client_retention_rate']['change_percentage']);
        $this->assertEquals(0.0, $comparison['commission_generated']['change_percentage']);
        $this->assertEquals(0.0, $comparison['new_client_acquisitions']['change_percentage']);
        $this->assertEquals(0.0, $comparison['goal_achievement_rate']['change_percentage']);
    }

    public function test_calculates_evaluation_period_days(): void
    {
        $metrics = PerformanceMetrics::create(
            25000.0,
            85.0,
            2500.0,
            10,
            90.0,
            $this->startDate,
            $this->endDate
        );

        $days = $metrics->getEvaluationPeriodDays();
        
        // From 2024-01-01 to 2024-03-31 is 90 days
        $this->assertEquals(90, $days);
    }

    public function test_equality_comparison(): void
    {
        $metrics1 = PerformanceMetrics::create(
            25000.0,
            85.0,
            2500.0,
            10,
            90.0,
            $this->startDate,
            $this->endDate
        );

        $metrics2 = PerformanceMetrics::create(
            25000.0,
            85.0,
            2500.0,
            10,
            90.0,
            $this->startDate,
            $this->endDate
        );

        $metrics3 = PerformanceMetrics::create(
            30000.0,  // Different investment amount
            85.0,
            2500.0,
            10,
            90.0,
            $this->startDate,
            $this->endDate
        );

        $this->assertTrue($metrics1->equals($metrics2));
        $this->assertFalse($metrics1->equals($metrics3));
    }

    public function test_boundary_values(): void
    {
        // Test with boundary values
        $metrics = PerformanceMetrics::create(
            0.0,        // Minimum investments
            0.0,        // Minimum retention
            0.0,        // Minimum commission
            0,          // Minimum acquisitions
            0.0,        // Minimum goals
            $this->startDate,
            $this->endDate
        );

        $this->assertEquals(0.0, $metrics->getInvestmentsFacilitated());
        $this->assertEquals(0.0, $metrics->getClientRetentionRate());
        $this->assertEquals(0.0, $metrics->getCommissionGenerated());
        $this->assertEquals(0, $metrics->getNewClientAcquisitions());
        $this->assertEquals(0.0, $metrics->getGoalAchievementRate());

        $overallScore = $metrics->calculateOverallScore();
        $this->assertEquals(0.0, $overallScore);
    }

    public function test_maximum_boundary_values(): void
    {
        // Test with maximum boundary values
        $metrics = PerformanceMetrics::create(
            100000.0,   // High investments
            100.0,      // Maximum retention
            10000.0,    // High commission
            50,         // High acquisitions
            100.0,      // Maximum goals
            $this->startDate,
            $this->endDate
        );

        $this->assertEquals(100000.0, $metrics->getInvestmentsFacilitated());
        $this->assertEquals(100.0, $metrics->getClientRetentionRate());
        $this->assertEquals(10000.0, $metrics->getCommissionGenerated());
        $this->assertEquals(50, $metrics->getNewClientAcquisitions());
        $this->assertEquals(100.0, $metrics->getGoalAchievementRate());

        $overallScore = $metrics->calculateOverallScore();
        $this->assertEquals(10.0, $overallScore); // Should be capped at 10.0
    }
}