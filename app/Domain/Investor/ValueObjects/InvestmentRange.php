<?php

namespace App\Domain\Investor\ValueObjects;

use InvalidArgumentException;

/**
 * Investment Range Value Object
 * 
 * Represents the range of investment a potential investor is interested in
 */
class InvestmentRange
{
    private const VALID_RANGES = [
        '25-50',
        '50-100',
        '100-250',
        '250+',
    ];

    private const HIGH_VALUE_THRESHOLD = '100-250';

    private function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, self::VALID_RANGES)) {
            throw new InvalidArgumentException("Invalid investment range: {$value}");
        }
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isHighValue(): bool
    {
        return in_array($this->value, ['100-250', '250+']);
    }

    public function getMinimumAmount(): int
    {
        return match($this->value) {
            '25-50' => 25000,
            '50-100' => 50000,
            '100-250' => 100000,
            '250+' => 250000,
        };
    }

    public function getMaximumAmount(): ?int
    {
        return match($this->value) {
            '25-50' => 50000,
            '50-100' => 100000,
            '100-250' => 250000,
            '250+' => null, // No maximum
        };
    }

    public function getDisplayName(): string
    {
        return match($this->value) {
            '25-50' => 'K25,000 - K50,000',
            '50-100' => 'K50,000 - K100,000',
            '100-250' => 'K100,000 - K250,000',
            '250+' => 'K250,000+',
        };
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
