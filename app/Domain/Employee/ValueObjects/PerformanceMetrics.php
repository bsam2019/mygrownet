<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObjects;

use App\Domain\Employee\Exceptions\InvalidPerformanceMetricsException;
use DateTimeImmutable;

final class PerformanceMetrics
{
    private const MIN_SCORE = 0.0;
    private const MAX_SCORE = 10.0;
    private const MIN_PERCENTAGE = 0.0;
    private const MAX_PERCENTAGE = 100.0;

    private float $investmentsFacilitated;
    private float $clientRetentionRate;
    private float $commissionGenerated;
    private int $newClientAcquisitions;
    private float $goalAchievementRate;
    private DateTimeImmutable $evaluationPeriodStart;
    private DateTimeImmutable $evaluationPeriodEnd;

    public function __construct(
        float $investmentsFacilitated,
        float $clientRetentionRate,
        float $commissionGenerated,
        int $newClientAcquisitions,
        float $goalAchievementRate,
        DateTimeImmutable $evaluationPeriodStart,
        DateTimeImmutable $evaluationPeriodEnd
    ) {
        $this->validateInvestmentsFacilitated($investmentsFacilitated);
        $this->validateClientRetentionRate($clientRetentionRate);
        $this->validateCommissionGenerated($commissionGenerated);
        $this->validateNewClientAcquisitions($newClientAcquisitions);
        $this->validateGoalAchievementRate($goalAchievementRate);
        $this->validateEvaluationPeriod($evaluationPeriodStart, $evaluationPeriodEnd);

        $this->investmentsFacilitated = $investmentsFacilitated;
        $this->clientRetentionRate = $clientRetentionRate;
        $this->commissionGenerated = $commissionGenerated;
        $this->newClientAcquisitions = $newClientAcquisitions;
        $this->goalAchievementRate = $goalAchievementRate;
        $this->evaluationPeriodStart = $evaluationPeriodStart;
        $this->evaluationPeriodEnd = $evaluationPeriodEnd;
    }

    public static function create(
        float $investmentsFacilitated,
        float $clientRetentionRate,
        float $commissionGenerated,
        int $newClientAcquisitions,
        float $goalAchievementRate,
        DateTimeImmutable $evaluationPeriodStart,
        DateTimeImmutable $evaluationPeriodEnd
    ): self {
        return new self(
            $investmentsFacilitated,
            $clientRetentionRate,
            $commissionGenerated,
            $newClientAcquisitions,
            $goalAchievementRate,
            $evaluationPeriodStart,
            $evaluationPeriodEnd
        );
    }

    public function calculateOverallScore(): float
    {
        // Weighted scoring system
        $weights = [
            'investments' => 0.30,      // 30% weight for investments facilitated
            'retention' => 0.25,        // 25% weight for client retention
            'commission' => 0.20,       // 20% weight for commission generated
            'acquisition' => 0.15,      // 15% weight for new client acquisitions
            'goals' => 0.10,           // 10% weight for goal achievement
        ];

        // Normalize metrics to 0-10 scale
        $investmentScore = $this->normalizeInvestmentScore($this->investmentsFacilitated);
        $retentionScore = $this->normalizePercentageScore($this->clientRetentionRate);
        $commissionScore = $this->normalizeCommissionScore($this->commissionGenerated);
        $acquisitionScore = $this->normalizeAcquisitionScore($this->newClientAcquisitions);
        $goalScore = $this->normalizePercentageScore($this->goalAchievementRate);

        $overallScore = (
            ($investmentScore * $weights['investments']) +
            ($retentionScore * $weights['retention']) +
            ($commissionScore * $weights['commission']) +
            ($acquisitionScore * $weights['acquisition']) +
            ($goalScore * $weights['goals'])
        );

        return round($overallScore, 2);
    }

    public function compareWith(PerformanceMetrics $other): array
    {
        return [
            'investments_facilitated' => [
                'current' => $this->investmentsFacilitated,
                'previous' => $other->investmentsFacilitated,
                'change' => $this->investmentsFacilitated - $other->investmentsFacilitated,
                'change_percentage' => $other->investmentsFacilitated > 0 
                    ? (($this->investmentsFacilitated - $other->investmentsFacilitated) / $other->investmentsFacilitated) * 100
                    : 0,
            ],
            'client_retention_rate' => [
                'current' => $this->clientRetentionRate,
                'previous' => $other->clientRetentionRate,
                'change' => $this->clientRetentionRate - $other->clientRetentionRate,
                'change_percentage' => $other->clientRetentionRate > 0
                    ? (($this->clientRetentionRate - $other->clientRetentionRate) / $other->clientRetentionRate) * 100
                    : 0,
            ],
            'commission_generated' => [
                'current' => $this->commissionGenerated,
                'previous' => $other->commissionGenerated,
                'change' => $this->commissionGenerated - $other->commissionGenerated,
                'change_percentage' => $other->commissionGenerated > 0
                    ? (($this->commissionGenerated - $other->commissionGenerated) / $other->commissionGenerated) * 100
                    : 0,
            ],
            'new_client_acquisitions' => [
                'current' => $this->newClientAcquisitions,
                'previous' => $other->newClientAcquisitions,
                'change' => $this->newClientAcquisitions - $other->newClientAcquisitions,
                'change_percentage' => $other->newClientAcquisitions > 0
                    ? (($this->newClientAcquisitions - $other->newClientAcquisitions) / $other->newClientAcquisitions) * 100
                    : 0,
            ],
            'goal_achievement_rate' => [
                'current' => $this->goalAchievementRate,
                'previous' => $other->goalAchievementRate,
                'change' => $this->goalAchievementRate - $other->goalAchievementRate,
                'change_percentage' => $other->goalAchievementRate > 0
                    ? (($this->goalAchievementRate - $other->goalAchievementRate) / $other->goalAchievementRate) * 100
                    : 0,
            ],
            'overall_score' => [
                'current' => $this->calculateOverallScore(),
                'previous' => $other->calculateOverallScore(),
                'change' => $this->calculateOverallScore() - $other->calculateOverallScore(),
            ],
        ];
    }

    public function getInvestmentsFacilitated(): float
    {
        return $this->investmentsFacilitated;
    }

    public function getClientRetentionRate(): float
    {
        return $this->clientRetentionRate;
    }

    public function getCommissionGenerated(): float
    {
        return $this->commissionGenerated;
    }

    public function getNewClientAcquisitions(): int
    {
        return $this->newClientAcquisitions;
    }

    public function getGoalAchievementRate(): float
    {
        return $this->goalAchievementRate;
    }

    public function getEvaluationPeriodStart(): DateTimeImmutable
    {
        return $this->evaluationPeriodStart;
    }

    public function getEvaluationPeriodEnd(): DateTimeImmutable
    {
        return $this->evaluationPeriodEnd;
    }

    public function getEvaluationPeriodDays(): int
    {
        return $this->evaluationPeriodStart->diff($this->evaluationPeriodEnd)->days;
    }

    public function equals(PerformanceMetrics $other): bool
    {
        return $this->investmentsFacilitated === $other->investmentsFacilitated
            && $this->clientRetentionRate === $other->clientRetentionRate
            && $this->commissionGenerated === $other->commissionGenerated
            && $this->newClientAcquisitions === $other->newClientAcquisitions
            && $this->goalAchievementRate === $other->goalAchievementRate
            && $this->evaluationPeriodStart->format('Y-m-d') === $other->evaluationPeriodStart->format('Y-m-d')
            && $this->evaluationPeriodEnd->format('Y-m-d') === $other->evaluationPeriodEnd->format('Y-m-d');
    }

    private function validateInvestmentsFacilitated(float $amount): void
    {
        if ($amount < 0) {
            throw InvalidPerformanceMetricsException::invalidInvestmentAmount($amount);
        }
    }

    private function validateClientRetentionRate(float $rate): void
    {
        if ($rate < self::MIN_PERCENTAGE || $rate > self::MAX_PERCENTAGE) {
            throw InvalidPerformanceMetricsException::invalidPercentage('client retention rate', $rate);
        }
    }

    private function validateCommissionGenerated(float $amount): void
    {
        if ($amount < 0) {
            throw InvalidPerformanceMetricsException::invalidCommissionAmount($amount);
        }
    }

    private function validateNewClientAcquisitions(int $count): void
    {
        if ($count < 0) {
            throw InvalidPerformanceMetricsException::invalidClientCount($count);
        }
    }

    private function validateGoalAchievementRate(float $rate): void
    {
        if ($rate < self::MIN_PERCENTAGE || $rate > self::MAX_PERCENTAGE) {
            throw InvalidPerformanceMetricsException::invalidPercentage('goal achievement rate', $rate);
        }
    }

    private function validateEvaluationPeriod(DateTimeImmutable $start, DateTimeImmutable $end): void
    {
        if ($start >= $end) {
            throw InvalidPerformanceMetricsException::invalidEvaluationPeriod($start, $end);
        }
    }

    private function normalizeInvestmentScore(float $amount): float
    {
        // Normalize investment amount to 0-10 scale
        // Assuming K50,000 is excellent performance (score 10)
        $excellentThreshold = 50000.0;
        $score = ($amount / $excellentThreshold) * self::MAX_SCORE;
        return min($score, self::MAX_SCORE);
    }

    private function normalizePercentageScore(float $percentage): float
    {
        // Convert percentage (0-100) to score (0-10)
        return ($percentage / 100) * self::MAX_SCORE;
    }

    private function normalizeCommissionScore(float $amount): float
    {
        // Normalize commission amount to 0-10 scale
        // Assuming K5,000 is excellent commission performance (score 10)
        $excellentThreshold = 5000.0;
        $score = ($amount / $excellentThreshold) * self::MAX_SCORE;
        return min($score, self::MAX_SCORE);
    }

    private function normalizeAcquisitionScore(int $count): float
    {
        // Normalize new client acquisitions to 0-10 scale
        // Assuming 20 new clients is excellent performance (score 10)
        $excellentThreshold = 20;
        $score = ($count / $excellentThreshold) * self::MAX_SCORE;
        return min($score, self::MAX_SCORE);
    }
}