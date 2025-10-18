<?php

namespace App\Domain\Employee\Exceptions;

/**
 * Exception thrown when performance metrics are invalid
 */
class InvalidPerformanceMetricsException extends EmployeeDomainException
{
    public static function invalidScore(float $score): self
    {
        return new self(
            "Invalid performance score: {$score}. Score must be between 0 and 10",
            ['score' => $score]
        );
    }

    public static function invalidRate(float $rate, string $metric): self
    {
        return new self(
            "Invalid {$metric} rate: {$rate}. Rate must be between 0 and 100",
            [
                'rate' => $rate,
                'metric' => $metric
            ]
        );
    }

    public static function invalidPeriod(string $startDate, string $endDate): self
    {
        return new self(
            "Invalid evaluation period: start date {$startDate} must be before end date {$endDate}",
            [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        );
    }

    public static function missingMetrics(array $requiredMetrics): self
    {
        return new self(
            "Missing required performance metrics: " . implode(', ', $requiredMetrics),
            ['required_metrics' => $requiredMetrics]
        );
    }

    public static function invalidInvestmentAmount(float $amount): self
    {
        return new self(
            "Invalid investment amount: {$amount}. Amount cannot be negative",
            ['amount' => $amount]
        );
    }

    public static function invalidPercentage(string $metric, float $percentage): self
    {
        return new self(
            "Invalid {$metric}: {$percentage}. Percentage must be between 0 and 100",
            [
                'metric' => $metric,
                'percentage' => $percentage
            ]
        );
    }

    public static function invalidCommissionAmount(float $amount): self
    {
        return new self(
            "Invalid commission amount: {$amount}. Amount cannot be negative",
            ['amount' => $amount]
        );
    }

    public static function invalidClientCount(int $count): self
    {
        return new self(
            "Invalid client count: {$count}. Count cannot be negative",
            ['count' => $count]
        );
    }

    public static function invalidEvaluationPeriod(\DateTimeImmutable $start, \DateTimeImmutable $end): self
    {
        return new self(
            "Invalid evaluation period: start date {$start->format('Y-m-d')} must be before end date {$end->format('Y-m-d')}",
            [
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d')
            ]
        );
    }

    public static function noPerformanceMetricsFound(string $employeeId): self
    {
        return new self(
            "No performance metrics found for employee {$employeeId}",
            ['employee_id' => $employeeId]
        );
    }

    public static function insufficientDataForComparison(): self
    {
        return new self("Insufficient performance data for comparison");
    }

    public static function invalidRetentionRate(float $rate): self
    {
        return new self(
            "Invalid client retention rate: {$rate}. Rate must be between 0 and 100",
            ['rate' => $rate]
        );
    }

    public static function invalidGoalAchievementRate(float $rate): self
    {
        return new self(
            "Invalid goal achievement rate: {$rate}. Rate must be between 0 and 100",
            ['rate' => $rate]
        );
    }

    public static function invalidInvestmentCount(float $count): self
    {
        return new self(
            "Invalid investment count: {$count}. Count cannot be negative",
            ['count' => $count]
        );
    }

    public static function invalidClientAcquisitionCount(int $count): self
    {
        return new self(
            "Invalid client acquisition count: {$count}. Count cannot be negative",
            ['count' => $count]
        );
    }

    public static function invalidGoalDescription(): self
    {
        return new self("Goal description cannot be empty");
    }

    public static function invalidGoalTarget(): self
    {
        return new self("Goal target must be a valid number");
    }

    public static function invalidGoalDeadline(): self
    {
        return new self("Goal deadline must be a valid date");
    }
}