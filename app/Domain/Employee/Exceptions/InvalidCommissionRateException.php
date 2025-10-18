<?php

namespace App\Domain\Employee\Exceptions;

/**
 * Exception thrown when an invalid commission rate is used
 */
class InvalidCommissionRateException extends EmployeeDomainException
{
    public static function outOfRange(float $rate): self
    {
        return new self(
            "Commission rate {$rate}% is out of valid range (0-100%)",
            ['rate' => $rate]
        );
    }

    public static function negativeRate(float $rate): self
    {
        return new self(
            "Commission rate cannot be negative: {$rate}%",
            ['rate' => $rate]
        );
    }

    public static function exceedsMaximum(float $rate, float $maximum): self
    {
        return new self(
            "Commission rate {$rate}% exceeds maximum allowed rate of {$maximum}%",
            [
                'rate' => $rate,
                'maximum' => $maximum
            ]
        );
    }

    public static function invalidForPosition(float $rate, string $position): self
    {
        return new self(
            "Commission rate {$rate}% is not valid for position: {$position}",
            [
                'rate' => $rate,
                'position' => $position
            ]
        );
    }

    public static function zeroCommissionRate(string $employeeId): self
    {
        return new self(
            "Employee {$employeeId} has zero commission rate",
            ['employee_id' => $employeeId]
        );
    }
}