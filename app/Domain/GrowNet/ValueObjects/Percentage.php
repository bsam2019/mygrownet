<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\ValueObjects;

class Percentage
{
    public function __construct(private float $value)
    {
        if ($value < 0 || $value > 100) {
            throw new \InvalidArgumentException('Percentage must be between 0 and 100');
        }
    }

    public function value(): float
    {
        return $this->value;
    }

    public function asDecimal(): float
    {
        return $this->value / 100;
    }

    public function applyTo(Money $amount): Money
    {
        return $amount->multiply($this->asDecimal());
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value . '%';
    }
}
