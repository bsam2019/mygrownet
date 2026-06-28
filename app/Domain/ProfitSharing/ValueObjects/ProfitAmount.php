<?php

namespace App\Domain\ProfitSharing\ValueObjects;

use InvalidArgumentException;

class ProfitAmount
{
    private function __construct(
        private readonly float $amount
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Profit amount cannot be negative');
        }
    }

    public static function fromFloat(float $amount): self
    {
        return new self($amount);
    }

    public function value(): float
    {
        return $this->amount;
    }

    public function memberShare(): self
    {
        return new self($this->amount * 0.60); // 60% to members
    }

    public function companyRetained(): self
    {
        return new self($this->amount * 0.40); // 40% to company
    }

    public function formatted(): string
    {
        return 'K' . number_format($this->amount, 2);
    }
}
