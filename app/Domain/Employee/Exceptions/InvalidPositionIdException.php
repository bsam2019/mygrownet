<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

final class InvalidPositionIdException extends EmployeeDomainException
{
    public static function empty(): self
    {
        return new self('Position ID cannot be empty');
    }

    public static function invalidFormat(string $id): self
    {
        return new self("Invalid position ID format: {$id}");
    }
}