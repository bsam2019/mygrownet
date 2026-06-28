<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

final class EmployeeNotFoundException extends EmployeeException
{
    public static function withId(string $id): self
    {
        return new self("Employee with ID '{$id}' not found.");
    }

    public static function withEmployeeNumber(string $employeeNumber): self
    {
        return new self("Employee with number '{$employeeNumber}' not found.");
    }

    public static function withEmail(string $email): self
    {
        return new self("Employee with email '{$email}' not found.");
    }

    public static function withUserId(int $userId): self
    {
        return new self("Employee with user ID '{$userId}' not found.");
    }
}