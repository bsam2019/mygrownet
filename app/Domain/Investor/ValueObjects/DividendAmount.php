<?php

namespace App\Domain\Investor\ValueObjects;

use InvalidArgumentException;

class DividendAmount
{
    private function __construct(
        private readonly float $amount,
        private readonly string $currency = 'ZMW'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Dividend amount cannot be negative');
        }
    }

    public static function fromFloat(float $amount, string $currency = 'ZMW'): self
    {
        return new self($amount, $currency);
    }

    public function value(): float
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function calculateTax(float $taxRate): self
    {
        return new self($this->amount * $taxRate, $this->currency);
    }

    public function afterTax(float $taxRate): self
    {
        return new self($this->amount * (1 - $taxRate), $this->currency);
    }

    public function add(DividendAmount $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot add amounts in different currencies');
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function formatted(): string
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
