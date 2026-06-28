<?php

declare(strict_types=1);

namespace App\Domain\MLM\ValueObjects;

use InvalidArgumentException;

final class CommissionAmount
{
    private function __construct(private float $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Commission amount cannot be negative');
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public static function zero(): self
    {
        return new self(0.0);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function isPositive(): bool
    {
        return $this->value > 0;
    }

    public function isZero(): bool
    {
        return $this->value === 0.0;
    }

    public function add(CommissionAmount $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(CommissionAmount $other): self
    {
        $result = $this->value - $other->value;
        if ($result < 0) {
            throw new InvalidArgumentException('Cannot subtract to negative amount');
        }
        return new self($result);
    }

    public function multiply(float $multiplier): self
    {
        if ($multiplier < 0) {
            throw new InvalidArgumentException('Multiplier cannot be negative');
        }
        return new self($this->value * $multiplier);
    }

    public function equals(CommissionAmount $other): bool
    {
        return abs($this->value - $other->value) < 0.01; // Account for floating point precision
    }

    public function toFormattedString(): string
    {
        return 'K' . number_format($this->value, 2);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}