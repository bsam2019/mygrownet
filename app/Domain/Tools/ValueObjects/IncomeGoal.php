<?php

namespace App\Domain\Tools\ValueObjects;

use InvalidArgumentException;

class IncomeGoal
{
    private function __construct(private readonly float $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Income goal must be non-negative');
        }
    }

    public static function fromAmount(float $amount): self
    {
        return new self($amount);
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function isGreaterThan(IncomeGoal $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function formatted(): string
    {
        return 'K' . number_format($this->amount, 2);
    }
}
