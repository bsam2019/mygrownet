<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

final class InvalidEmailException extends EmployeeDomainException
{
    public static function empty(): self
    {
        return new self('Email cannot be empty');
    }

    public static function invalidFormat(string $email): self
    {
        return new self("Invalid email format: {$email}");
    }
}