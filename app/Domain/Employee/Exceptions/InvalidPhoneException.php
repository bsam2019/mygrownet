<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

final class InvalidPhoneException extends EmployeeDomainException
{
    public static function empty(): self
    {
        return new self('Phone number cannot be empty');
    }

    public static function invalidFormat(string $phone): self
    {
        return new self("Invalid phone number format: {$phone}");
    }
}