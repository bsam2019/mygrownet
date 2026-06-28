<?php

namespace App\Domain\Payment\ValueObjects;

use InvalidArgumentException;

class PaymentAmount
{
    private function __construct(
        private readonly float $amount
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Payment amount cannot be negative');
        }

        if ($amount < 50) {
            throw new InvalidArgumentException('Payment amount must be at least K50');
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

    public function formatted(): string
    {
        return 'K' . number_format($this->amount, 2);
    }

    public function equals(PaymentAmount $other): bool
    {
        return $this->amount === $other->amount;
    }
}
