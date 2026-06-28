<?php

namespace App\Domain\Employee\Exceptions;

/**
 * Exception thrown when an invalid employment status is used or transition is not allowed
 */
class InvalidEmploymentStatusException extends EmployeeDomainException
{
    public static function invalidStatus(string $status): self
    {
        return new self(
            "Invalid employment status: {$status}",
            ['status' => $status]
        );
    }

    public static function invalidTransition(string $fromStatus, string $toStatus): self
    {
        return new self(
            "Invalid employment status transition from {$fromStatus} to {$toStatus}",
            [
                'from_status' => $fromStatus,
                'to_status' => $toStatus
            ]
        );
    }

    public static function missingReason(string $status): self
    {
        return new self(
            "Employment status {$status} requires a reason",
            ['status' => $status]
        );
    }
}