<?php

namespace App\Domain\StarterKit\ValueObjects;

use InvalidArgumentException;

final class PurchaseStatus
{
    private const PENDING = 'pending';
    private const COMPLETED = 'completed';
    private const FAILED = 'failed';
    private const REFUNDED = 'refunded';

    private function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, [self::PENDING, self::COMPLETED, self::FAILED, self::REFUNDED])) {
            throw new InvalidArgumentException("Invalid purchase status: {$value}");
        }
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function failed(): self
    {
        return new self(self::FAILED);
    }

    public static function refunded(): self
    {
        return new self(self::REFUNDED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->value === self::FAILED;
    }

    public function isRefunded(): bool
    {
        return $this->value === self::REFUNDED;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(PurchaseStatus $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return ucfirst($this->value);
    }
}
