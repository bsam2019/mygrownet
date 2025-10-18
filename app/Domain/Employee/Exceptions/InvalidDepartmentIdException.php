<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

final class InvalidDepartmentIdException extends EmployeeDomainException
{
    public static function empty(): self
    {
        return new self('Department ID cannot be empty');
    }

    public static function invalidFormat(string $id): self
    {
        return new self("Invalid department ID format: {$id}");
    }
}