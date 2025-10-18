<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

class InvalidEmployeeIdException extends EmployeeDomainException
{
    public static function empty(): self
    {
        return new self('Employee ID cannot be empty');
    }

    public static function invalidFormat(string $id): self
    {
        return new self("Invalid Employee ID format: {$id}");
    }
}