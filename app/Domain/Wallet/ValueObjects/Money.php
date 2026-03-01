<?php

namespace App\Domain\Wallet\ValueObjects;

use InvalidArgumentException;

/**
 * Money Value Object
 * 
 * Represents a monetary amount in Zambian Kwacha (ZMW).
 * Immutable and self-validating.
 */
final class Money
{
    private function __construct(
        private readonly float $amount
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative');
        }
    }

    /**
     * Create Money from Kwacha amount
     */
    public static function fromKwacha(float $amount): self
    {
        return new self($amount);
    }

    /**
     * Create zero amount
     */
    public static function zero(): self
    {
        return new self(0);
    }

    /**
     * Get the amount as float
     */
    public function amount(): float
    {
        return $this->amount;
    }

    /**
     * Add another Money amount
     */
    public function add(Money $other): self
    {
        return new self($this->amount + $other->amount);
    }

    /**
     * Subtract another Money amount
     */
    public function subtract(Money $other): self
    {
        $result = $this->amount - $other->amount;
        
        if ($result < 0) {
            throw new InvalidArgumentException('Subtraction would result in negative amount');
        }
        
        return new self($result);
    }

    /**
     * Multiply by a factor
     */
    public function multiply(float $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Multiplication factor cannot be negative');
        }
        
        return new self($this->amount * $factor);
    }

    /**
     * Divide by a divisor
     */
    public function divide(float $divisor): self
    {
        if ($divisor <= 0) {
            throw new InvalidArgumentException('Division by zero or negative number');
        }
        
        return new self($this->amount / $divisor);
    }

    /**
     * Calculate percentage of this amount
     */
    public function percentage(float $percent): self
    {
        if ($percent < 0 || $percent > 100) {
            throw new InvalidArgumentException('Percentage must be between 0 and 100');
        }
        
        return new self($this->amount * ($percent / 100));
    }

    /**
     * Check if this amount is greater than another
     */
    public function isGreaterThan(Money $other): bool
    {
        return $this->amount > $other->amount;
    }

    /**
     * Check if this amount is less than another
     */
    public function isLessThan(Money $other): bool
    {
        return $this->amount < $other->amount;
    }

    /**
     * Check if this amount equals another
     */
    public function equals(Money $other): bool
    {
        return abs($this->amount - $other->amount) < 0.01; // Account for floating point precision
    }

    /**
     * Check if amount is zero
     */
    public function isZero(): bool
    {
        return $this->amount < 0.01;
    }

    /**
     * Check if amount is positive
     */
    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Format as currency string
     */
    public function format(): string
    {
        return 'K' . number_format($this->amount, 2);
    }

    /**
     * Format without currency symbol
     */
    public function formatWithoutSymbol(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * Get as array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'formatted' => $this->format(),
            'currency' => 'ZMW',
        ];
    }
}
