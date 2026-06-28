<?php

namespace App\Domain\Employee\Exceptions;

/**
 * Exception thrown when a performance review cannot be found
 */
class PerformanceReviewNotFoundException extends EmployeeDomainException
{
    public static function byId(int $reviewId): self
    {
        return new self(
            "Performance review with ID {$reviewId} not found",
            ['review_id' => $reviewId]
        );
    }

    public static function forEmployee(int $employeeId, string $period): self
    {
        return new self(
            "Performance review for employee {$employeeId} in period {$period} not found",
            [
                'employee_id' => $employeeId,
                'period' => $period
            ]
        );
    }

    public static function forPeriod(string $startDate, string $endDate): self
    {
        return new self(
            "No performance reviews found for period {$startDate} to {$endDate}",
            [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        );
    }
}