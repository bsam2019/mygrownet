<?php

declare(strict_types=1);

namespace App\Domain\Employee\Entities;

use App\Domain\Employee\Exceptions\EmployeePerformanceException;
use App\Domain\Employee\ValueObjects\PerformanceMetrics;
use DateTimeImmutable;

final class EmployeePerformance
{
    private int $id;
    private Employee $employee;
    private DateTimeImmutable $evaluationPeriodStart;
    private DateTimeImmutable $evaluationPeriodEnd;
    private PerformanceMetrics $metrics;
    private Employee $reviewer;
    private ?string $reviewNotes;
    private array $goalsNextPeriod;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        Employee $employee,
        DateTimeImmutable $evaluationPeriodStart,
        DateTimeImmutable $evaluationPeriodEnd,
        PerformanceMetrics $metrics,
        Employee $reviewer,
        ?string $reviewNotes = null,
        array $goalsNextPeriod = []
    ) {
        $this->validateEvaluationPeriod($evaluationPeriodStart, $evaluationPeriodEnd);
        $this->validateReviewer($reviewer, $employee);

        $this->employee = $employee;
        $this->evaluationPeriodStart = $evaluationPeriodStart;
        $this->evaluationPeriodEnd = $evaluationPeriodEnd;
        $this->metrics = $metrics;
        $this->reviewer = $reviewer;
        $this->reviewNotes = $reviewNotes;
        $this->goalsNextPeriod = $goalsNextPeriod;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(
        Employee $employee,
        DateTimeImmutable $evaluationPeriodStart,
        DateTimeImmutable $evaluationPeriodEnd,
        PerformanceMetrics $metrics,
        Employee $reviewer,
        ?string $reviewNotes = null
    ): self {
        return new self(
            $employee,
            $evaluationPeriodStart,
            $evaluationPeriodEnd,
            $metrics,
            $reviewer,
            $reviewNotes
        );
    }

    public function updateMetrics(PerformanceMetrics $newMetrics): void
    {
        $this->metrics = $newMetrics;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateReviewNotes(string $notes): void
    {
        $this->reviewNotes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addGoalForNextPeriod(string $goal): void
    {
        $trimmedGoal = trim($goal);
        if (empty($trimmedGoal)) {
            throw EmployeePerformanceException::invalidGoal($goal);
        }

        if (!in_array($trimmedGoal, $this->goalsNextPeriod, true)) {
            $this->goalsNextPeriod[] = $trimmedGoal;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function removeGoalForNextPeriod(string $goal): void
    {
        $this->goalsNextPeriod = array_filter($this->goalsNextPeriod, function ($g) use ($goal) {
            return $g !== $goal;
        });
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setGoalsForNextPeriod(array $goals): void
    {
        $validatedGoals = [];
        foreach ($goals as $goal) {
            $trimmedGoal = trim($goal);
            if (empty($trimmedGoal)) {
                throw EmployeePerformanceException::invalidGoal($goal);
            }
            $validatedGoals[] = $trimmedGoal;
        }

        $this->goalsNextPeriod = array_unique($validatedGoals);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function compareWith(EmployeePerformance $other): array
    {
        return [
            'overall_score_change' => $this->metrics->calculateOverallScore() - $other->metrics->calculateOverallScore(),
            'investments_facilitated_change' => $this->metrics->getInvestmentsFacilitated() - $other->metrics->getInvestmentsFacilitated(),
            'client_retention_change' => $this->metrics->getClientRetentionRate() - $other->metrics->getClientRetentionRate(),
            'commission_change' => $this->metrics->getCommissionGenerated() - $other->metrics->getCommissionGenerated(),
            'new_clients_change' => $this->metrics->getNewClientAcquisitions() - $other->metrics->getNewClientAcquisitions(),
            'goal_achievement_change' => $this->metrics->getGoalAchievementRate() - $other->metrics->getGoalAchievementRate(),
        ];
    }

    public function calculateTrendAnalysis(array $previousPerformances): array
    {
        if (empty($previousPerformances)) {
            return [
                'trend' => 'no_data',
                'average_score' => $this->metrics->calculateOverallScore(),
                'score_variance' => 0.0,
                'improvement_areas' => [],
                'strengths' => $this->identifyStrengths()
            ];
        }

        $scores = array_map(fn(EmployeePerformance $p) => $p->getMetrics()->calculateOverallScore(), $previousPerformances);
        $scores[] = $this->metrics->calculateOverallScore();

        $averageScore = array_sum($scores) / count($scores);
        $variance = $this->calculateVariance($scores, $averageScore);

        $trend = $this->determineTrend($scores);
        $improvementAreas = $this->identifyImprovementAreas($previousPerformances);
        $strengths = $this->identifyStrengths();

        return [
            'trend' => $trend,
            'average_score' => $averageScore,
            'score_variance' => $variance,
            'improvement_areas' => $improvementAreas,
            'strengths' => $strengths
        ];
    }

    public function isOutstanding(): bool
    {
        return $this->metrics->calculateOverallScore() >= 9.0;
    }

    public function needsImprovement(): bool
    {
        return $this->metrics->calculateOverallScore() < 6.0;
    }

    public function meetsExpectations(): bool
    {
        return $this->metrics->calculateOverallScore() >= 6.0 && $this->metrics->calculateOverallScore() < 9.0;
    }

    public function getPerformanceRating(): string
    {
        if ($this->isOutstanding()) {
            return 'Outstanding';
        }

        if ($this->needsImprovement()) {
            return 'Needs Improvement';
        }

        return 'Meets Expectations';
    }

    public function getPeriodDurationInDays(): int
    {
        return $this->evaluationPeriodStart->diff($this->evaluationPeriodEnd)->days;
    }

    public function isQuarterlyReview(): bool
    {
        $days = $this->getPeriodDurationInDays();
        return $days >= 85 && $days <= 95; // Approximately 3 months
    }

    public function isAnnualReview(): bool
    {
        $days = $this->getPeriodDurationInDays();
        return $days >= 360 && $days <= 370; // Approximately 1 year
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function getEvaluationPeriodStart(): DateTimeImmutable
    {
        return $this->evaluationPeriodStart;
    }

    public function getEvaluationPeriodEnd(): DateTimeImmutable
    {
        return $this->evaluationPeriodEnd;
    }

    public function getMetrics(): PerformanceMetrics
    {
        return $this->metrics;
    }

    public function getReviewer(): Employee
    {
        return $this->reviewer;
    }

    public function getReviewNotes(): ?string
    {
        return $this->reviewNotes;
    }

    public function getGoalsNextPeriod(): array
    {
        return $this->goalsNextPeriod;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Private helper methods
    private function validateEvaluationPeriod(DateTimeImmutable $start, DateTimeImmutable $end): void
    {
        if ($start >= $end) {
            throw EmployeePerformanceException::invalidEvaluationPeriod($start, $end);
        }

        $now = new DateTimeImmutable();
        if ($end > $now) {
            throw EmployeePerformanceException::futureEvaluationPeriod($end);
        }
    }

    private function validateReviewer(Employee $reviewer, Employee $employee): void
    {
        if ($reviewer->getId()->equals($employee->getId())) {
            throw EmployeePerformanceException::cannotSelfReview($employee->getId()->toString());
        }

        // In a real implementation, we might check if the reviewer has authority to review this employee
        // For now, we'll just ensure they're not the same person
    }

    private function calculateVariance(array $scores, float $average): float
    {
        $sumSquaredDifferences = array_sum(array_map(function ($score) use ($average) {
            return pow($score - $average, 2);
        }, $scores));

        return $sumSquaredDifferences / count($scores);
    }

    private function determineTrend(array $scores): string
    {
        if (count($scores) < 2) {
            return 'stable';
        }

        $recentScores = array_slice($scores, -3); // Last 3 scores
        $isImproving = true;
        $isDeclining = true;

        for ($i = 1; $i < count($recentScores); $i++) {
            if ($recentScores[$i] <= $recentScores[$i - 1]) {
                $isImproving = false;
            }
            if ($recentScores[$i] >= $recentScores[$i - 1]) {
                $isDeclining = false;
            }
        }

        if ($isImproving) {
            return 'improving';
        }

        if ($isDeclining) {
            return 'declining';
        }

        return 'stable';
    }

    private function identifyImprovementAreas(array $previousPerformances): array
    {
        $improvementAreas = [];

        // Compare current metrics with previous average
        if (!empty($previousPerformances)) {
            $avgPrevious = $this->calculateAverageMetrics($previousPerformances);

            if ($this->metrics->getClientRetentionRate() < $avgPrevious['client_retention']) {
                $improvementAreas[] = 'Client Retention';
            }

            if ($this->metrics->getGoalAchievementRate() < $avgPrevious['goal_achievement']) {
                $improvementAreas[] = 'Goal Achievement';
            }

            if ($this->metrics->getNewClientAcquisitions() < $avgPrevious['new_clients']) {
                $improvementAreas[] = 'New Client Acquisition';
            }
        }

        // Identify areas based on absolute thresholds
        if ($this->metrics->getClientRetentionRate() < 80.0) {
            $improvementAreas[] = 'Client Retention';
        }

        if ($this->metrics->getGoalAchievementRate() < 75.0) {
            $improvementAreas[] = 'Goal Achievement';
        }

        return array_unique($improvementAreas);
    }

    private function identifyStrengths(): array
    {
        $strengths = [];

        if ($this->metrics->getClientRetentionRate() >= 90.0) {
            $strengths[] = 'Excellent Client Retention';
        }

        if ($this->metrics->getGoalAchievementRate() >= 90.0) {
            $strengths[] = 'Outstanding Goal Achievement';
        }

        if ($this->metrics->getInvestmentsFacilitated() >= 15.0) {
            $strengths[] = 'High Investment Facilitation';
        }

        if ($this->metrics->getNewClientAcquisitions() >= 10) {
            $strengths[] = 'Strong New Client Acquisition';
        }

        return $strengths;
    }

    private function calculateAverageMetrics(array $performances): array
    {
        $totals = [
            'client_retention' => 0,
            'goal_achievement' => 0,
            'new_clients' => 0,
        ];

        foreach ($performances as $performance) {
            $metrics = $performance->getMetrics();
            $totals['client_retention'] += $metrics->getClientRetentionRate();
            $totals['goal_achievement'] += $metrics->getGoalAchievementRate();
            $totals['new_clients'] += $metrics->getNewClientAcquisitions();
        }

        $count = count($performances);
        return [
            'client_retention' => $totals['client_retention'] / $count,
            'goal_achievement' => $totals['goal_achievement'] / $count,
            'new_clients' => $totals['new_clients'] / $count,
        ];
    }
}