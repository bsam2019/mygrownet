<?php

namespace App\Domain\PrimeEdge\ValueObjects;

use InvalidArgumentException;

final class Money
{
    private function __construct(
        private readonly float $amount,
        private readonly string $currency = 'ZMW'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative');
        }
        if (!in_array($currency, ['ZMW', 'USD'], true)) {
            throw new InvalidArgumentException('Currency must be ZMW or USD');
        }
    }

    public static function fromKwacha(float $amount): self
    {
        return new self($amount, 'ZMW');
    }

    public static function fromUsd(float $amount): self
    {
        return new self($amount, 'USD');
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot add amounts with different currencies');
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot subtract amounts with different currencies');
        }
        $result = $this->amount - $other->amount;
        if ($result < 0) {
            throw new InvalidArgumentException('Subtraction would result in negative amount');
        }
        return new self($result, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function isLessThan(self $other): bool
    {
        return $this->amount < $other->amount;
    }

    public function equals(self $other): bool
    {
        return $this->currency === $other->currency && abs($this->amount - $other->amount) < 0.01;
    }

    public function isZero(): bool
    {
        return $this->amount < 0.01;
    }

    public function format(): string
    {
        $symbol = $this->currency === 'ZMW' ? 'K' : '$';
        return $symbol . number_format($this->amount, 2);
    }

    public function __toString(): string
    {
        return $this->format();
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'formatted' => $this->format(),
            'currency' => $this->currency,
        ];
    }
}
