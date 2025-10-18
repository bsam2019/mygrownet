<?php

declare(strict_types=1);

namespace App\Domain\Community\ValueObjects;

use InvalidArgumentException;

final class ContributionAmount
{
    private function __construct(private float $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Contribution amount cannot be negative');
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

    public function add(ContributionAmount $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(ContributionAmount $other): self
    {
        $result = $this->value - $other->value;
        if ($result < 0) {
            throw new InvalidArgumentException('Cannot subtract to negative contribution amount');
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

    public function calculatePercentageOf(ContributionAmount $total): float
    {
        if ($total->isZero()) {
            return 0.0;
        }
        return ($this->value / $total->value) * 100;
    }

    public function calculateReturn(float $returnRate): self
    {
        return new self($this->value * ($returnRate / 100));
    }

    public function equals(ContributionAmount $other): bool
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