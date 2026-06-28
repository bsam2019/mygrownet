<?php

declare(strict_types=1);

namespace App\Domain\Reward\ValueObjects;

use InvalidArgumentException;

final class AssetValue
{
    private function __construct(private float $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Asset value cannot be negative');
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

    public function add(AssetValue $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(AssetValue $other): self
    {
        $result = $this->value - $other->value;
        if ($result < 0) {
            throw new InvalidArgumentException('Cannot subtract to negative asset value');
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

    public function applyDepreciation(float $depreciationRate): self
    {
        if ($depreciationRate < 0 || $depreciationRate > 1) {
            throw new InvalidArgumentException('Depreciation rate must be between 0 and 1');
        }
        
        $depreciatedValue = $this->value * (1 - $depreciationRate);
        return new self($depreciatedValue);
    }

    public function applyAppreciation(float $appreciationRate): self
    {
        if ($appreciationRate < 0) {
            throw new InvalidArgumentException('Appreciation rate cannot be negative');
        }
        
        $appreciatedValue = $this->value * (1 + $appreciationRate);
        return new self($appreciatedValue);
    }

    public function calculateMonthlyIncome(float $incomeRate): float
    {
        return $this->value * ($incomeRate / 100);
    }

    public function isWithinRange(float $minValue, float $maxValue): bool
    {
        return $this->value >= $minValue && $this->value <= $maxValue;
    }

    public function equals(AssetValue $other): bool
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