<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

final class EmployeeAlreadyExistsException extends EmployeeException
{
    public static function withEmail(string $email): self
    {
        return new self("Employee with email '{$email}' already exists.");
    }

    public static function withEmployeeNumber(string $employeeNumber): self
    {
        return new self("Employee with number '{$employeeNumber}' already exists.");
    }

    public static function withUserId(int $userId): self
    {
        return new self("Employee with user ID '{$userId}' already exists.");
    }
}