<?php

namespace App\Domain\VentureBuilder\ValueObjects;

use InvalidArgumentException;

final class DividendStatus
{
    private const DECLARED = 'declared';
    private const PAID = 'paid';

    private const VALID = [self::DECLARED, self::PAID];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID, true)) {
            throw new InvalidArgumentException("Invalid dividend status: {$value}");
        }
    }

    public static function declared(): self { return new self(self::DECLARED); }
    public static function paid(): self { return new self(self::PAID); }
    public static function fromString(string $value): self { return new self($value); }
    public function value(): string { return $this->value; }
    public function isDeclared(): bool { return $this->value === self::DECLARED; }
    public function isPaid(): bool { return $this->value === self::PAID; }
}
