<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

class InvalidSalaryException extends EmployeeDomainException
{
    public static function negativeAmount(): self
    {
        return new self('Salary amount cannot be negative');
    }

    public static function emptyCurrency(): self
    {
        return new self('Currency cannot be empty');
    }

    public static function invalidCurrencyLength(): self
    {
        return new self('Currency must be a 3-letter ISO code');
    }

    public static function negativeAfterSubtraction(): self
    {
        return new self('Salary cannot be negative after subtraction');
    }

    public static function negativeMultiplier(): self
    {
        return new self('Salary multiplier cannot be negative');
    }

    public static function invalidPercentage(): self
    {
        return new self('Percentage must be between 0 and 100');
    }

    public static function differentCurrencies(string $currency1, string $currency2): self
    {
        return new self("Cannot perform operation on different currencies: {$currency1} and {$currency2}");
    }
}