<?php

namespace App\Domain\Financial\ValueObjects;

use InvalidArgumentException;

class LoanAmount
{
    private function __construct(
        private readonly float $amount
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Loan amount cannot be negative');
        }
    }

    public static function fromFloat(float $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function value(): float
    {
        return $this->amount;
    }

    public function isZero(): bool
    {
        return $this->amount === 0.0;
    }

    public function isGreaterThan(LoanAmount $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function add(LoanAmount $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function subtract(LoanAmount $other): self
    {
        $result = $this->amount - $other->amount;
        if ($result < 0) {
            throw new InvalidArgumentException('Cannot subtract more than current amount');
        }
        return new self($result);
    }

    public function min(LoanAmount $other): self
    {
        return new self(min($this->amount, $other->amount));
    }
}
