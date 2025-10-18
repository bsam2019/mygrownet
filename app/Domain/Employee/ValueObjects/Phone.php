<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObjects;

use App\Domain\Employee\Exceptions\InvalidPhoneException;

final class Phone
{
    private string $value;

    private function __construct(string $value)
    {
        $this->validatePhone($value);
        $this->value = $value;
    }

    public static function fromString(string $phone): self
    {
        return new self($phone);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function equals(Phone $other): bool
    {
        return $this->value === $other->value;
    }

    private function validatePhone(string $phone): void
    {
        $trimmedPhone = trim($phone);
        
        if (empty($trimmedPhone)) {
            throw InvalidPhoneException::empty();
        }

        // Basic phone validation - allows digits, spaces, hyphens, parentheses, and plus sign
        if (!preg_match('/^[\+]?[0-9\s\-\(\)]{7,20}$/', $trimmedPhone)) {
            throw InvalidPhoneException::invalidFormat($phone);
        }
    }
}