<?php

namespace App\Domain\PrimeEdge\ValueObjects;

use InvalidArgumentException;

final class PhoneNumber
{
    private function __construct(private string $value)
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $value);
        if (strlen($cleaned) < 8 || strlen($cleaned) > 15) {
            throw new InvalidArgumentException('Invalid phone number');
        }
    }

    public static function fromString(string $value): self
    {
        return new self(trim($value));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
