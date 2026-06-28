<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

final class PositionException extends EmployeeDomainException
{
    public static function invalidTitle(string $title): self
    {
        return new self("Invalid position title: {$title}");
    }

    public static function invalidSalaryRange(int $min, int $max): self
    {
        return new self("Invalid salary range: minimum ({$min}) cannot be greater than maximum ({$max})");
    }

    public static function invalidCommissionRate(float $rate): self
    {
        return new self("Invalid commission rate: {$rate}. Rate must be between 0 and 100");
    }

    public static function invalidResponsibility(string $responsibility): self
    {
        return new self("Invalid responsibility: {$responsibility}");
    }

    public static function invalidPermission(string $permission): self
    {
        return new self("Invalid permission: {$permission}");
    }
}