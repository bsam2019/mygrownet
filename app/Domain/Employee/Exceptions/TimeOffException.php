<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

class TimeOffException extends EmployeeDomainException
{
    public static function invalidDateRange(): self
    {
        return new self('End date must be after start date');
    }

    public static function cannotRequestPastDates(): self
    {
        return new self('Cannot request time off for past dates');
    }

    public static function invalidDaysRequested(): self
    {
        return new self('Days requested must be greater than zero');
    }

    public static function daysExceedRange(float $requested, int $max): self
    {
        return new self("Days requested ({$requested}) exceeds date range ({$max} days)");
    }

    public static function cannotApproveNonPendingRequest(int $requestId): self
    {
        return new self("Cannot approve non-pending request: {$requestId}");
    }

    public static function cannotRejectNonPendingRequest(int $requestId): self
    {
        return new self("Cannot reject non-pending request: {$requestId}");
    }

    public static function cannotCancelNonPendingRequest(int $requestId): self
    {
        return new self("Cannot cancel non-pending request: {$requestId}");
    }

    public static function insufficientBalance(string $type, float $available, float $requested): self
    {
        return new self("Insufficient {$type} leave balance. Available: {$available}, Requested: {$requested}");
    }

    public static function requestNotFound(int $requestId): self
    {
        return new self("Time off request not found: {$requestId}");
    }
}
