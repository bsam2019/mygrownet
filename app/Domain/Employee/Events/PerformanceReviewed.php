<?php

declare(strict_types=1);

namespace App\Domain\Employee\Events;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\PerformanceMetrics;
use DateTimeImmutable;

/**
 * Domain event fired when an employee's performance is reviewed
 */
final readonly class PerformanceReviewed
{
    public function __construct(
        public EmployeeId $employeeId,
        public EmployeeId $reviewerId,
        public PerformanceMetrics $performanceMetrics,
        public DateTimeImmutable $evaluationPeriodStart,
        public DateTimeImmutable $evaluationPeriodEnd,
        public float $overallScore,
        public array $goals,
        public ?string $reviewNotes = null,
        public ?array $goalsNextPeriod = null,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}

    /**
     * Get event data as array for serialization
     */
    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId->toString(),
            'reviewer_id' => $this->reviewerId->toString(),
            'evaluation_period_start' => $this->evaluationPeriodStart->format('Y-m-d'),
            'evaluation_period_end' => $this->evaluationPeriodEnd->format('Y-m-d'),
            'investments_facilitated_amount' => $this->performanceMetrics->getInvestmentsFacilitated(),
            'client_retention_rate' => $this->performanceMetrics->getClientRetentionRate(),
            'commission_generated' => $this->performanceMetrics->getCommissionGenerated(),
            'new_client_acquisitions' => $this->performanceMetrics->getNewClientAcquisitions(),
            'goal_achievement_rate' => $this->performanceMetrics->getGoalAchievementRate(),
            'overall_score' => $this->overallScore,
            'goals' => $this->goals,
            'review_notes' => $this->reviewNotes,
            'goals_next_period' => $this->goalsNextPeriod,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Get event name for logging/tracking
     */
    public function getEventName(): string
    {
        return 'employee.performance_reviewed';
    }

    /**
     * Check if performance improved from previous review
     */
    public function isPerformanceImprovement(float $previousScore): bool
    {
        return $this->overallScore > $previousScore;
    }

    /**
     * Check if performance meets expectations (score >= 7.0)
     */
    public function meetsExpectations(): bool
    {
        return $this->overallScore >= 7.0;
    }

    /**
     * Check if performance is exceptional (score >= 9.0)
     */
    public function isExceptional(): bool
    {
        return $this->overallScore >= 9.0;
    }

    /**
     * Validate event data integrity
     */
    public function isValid(): bool
    {
        return $this->evaluationPeriodStart < $this->evaluationPeriodEnd
            && $this->evaluationPeriodEnd <= $this->occurredAt
            && $this->overallScore >= 0 && $this->overallScore <= 10
            && !empty($this->goals)
            && !$this->employeeId->equals($this->reviewerId); // Employee cannot review themselves
    }
}