<?php

namespace App\Domain\VentureBuilder\ValueObjects;

use InvalidArgumentException;

final class InvestmentStatus
{
    private const PENDING = 'pending';
    private const PROCESSING = 'processing';
    private const CONFIRMED = 'confirmed';
    private const COMPLETED = 'completed';
    private const REFUNDED = 'refunded';

    private const VALID = [self::PENDING, self::PROCESSING, self::CONFIRMED, self::COMPLETED, self::REFUNDED];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID, true)) {
            throw new InvalidArgumentException("Invalid investment status: {$value}");
        }
    }

    public static function pending(): self { return new self(self::PENDING); }
    public static function processing(): self { return new self(self::PROCESSING); }
    public static function confirmed(): self { return new self(self::CONFIRMED); }
    public static function completed(): self { return new self(self::COMPLETED); }
    public static function refunded(): self { return new self(self::REFUNDED); }
    public static function fromString(string $value): self { return new self($value); }
    public function value(): string { return $this->value; }
    public function isPending(): bool { return $this->value === self::PENDING; }
    public function isConfirmed(): bool { return in_array($this->value, [self::CONFIRMED, self::COMPLETED], true); }
    public function canBeCancelled(): bool { return in_array($this->value, [self::PENDING, self::PROCESSING], true); }
}
