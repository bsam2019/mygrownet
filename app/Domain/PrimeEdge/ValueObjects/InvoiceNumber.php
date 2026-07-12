<?php

namespace App\Domain\PrimeEdge\ValueObjects;

use InvalidArgumentException;

final class InvoiceNumber
{
    private const PREFIX = 'PRIME';

    private function __construct(private string $value)
    {
        if (!preg_match('/^PRIME-\d{4}-\d{4,}$/', $value)) {
            throw new InvalidArgumentException('Invalid invoice number format (expected PRIME-YYYY-NNNN)');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(int $year, int $sequence): self
    {
        return new self(sprintf('%s-%04d-%04d', self::PREFIX, $year, $sequence));
    }

    public static function prefix(): string
    {
        return self::PREFIX;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
