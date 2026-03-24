<?php

namespace App\Domain\BizDocs\DocumentManagement\ValueObjects;

use InvalidArgumentException;

final class Money
{
    private function __construct(
        private readonly int $amount,
        private readonly string $currency
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-letter ISO code');
        }
    }

    public static function fromAmount(int $amount, string $currency = 'ZMW'): self
    {
        return new self($amount, strtoupper($currency));
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        $result = $this->amount - $other->amount;
        
        // Ensure result is not negative (clamp to 0)
        if ($result < 0) {
            $result = 0;
        }
        
        return new self($result, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        return new self((int)($this->amount * $multiplier), $this->currency);
    }

    public function percentage(float $percentage): self
    {
        return new self((int)($this->amount * $percentage / 100), $this->currency);
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount > $other->amount;
    }

    public function isLessThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount < $other->amount;
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function greaterThanOrEqual(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount >= $other->amount;
    }

    public function lessThanOrEqual(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount <= $other->amount;
    }

    public function formatted(): string
    {
        return $this->currency . ' ' . number_format($this->amount / 100, 2);
    }

    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot operate on different currencies');
        }
    }
}
