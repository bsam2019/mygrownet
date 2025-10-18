<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

use DateTimeImmutable;

final class EmployeePerformanceException extends EmployeeDomainException
{
    public static function invalidEvaluationPeriod(DateTimeImmutable $start, DateTimeImmutable $end): self
    {
        return new self("Invalid evaluation period: start date ({$start->format('Y-m-d')}) must be before end date ({$end->format('Y-m-d')})");
    }

    public static function futureEvaluationPeriod(DateTimeImmutable $end): self
    {
        return new self("Evaluation period cannot end in the future: {$end->format('Y-m-d')}");
    }

    public static function cannotSelfReview(string $employeeId): self
    {
        return new self("Employee {$employeeId} cannot review themselves");
    }

    public static function invalidGoal(string $goal): self
    {
        return new self("Invalid goal: {$goal}");
    }
}