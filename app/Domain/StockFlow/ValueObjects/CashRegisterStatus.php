<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\ValueObjects;

class CashRegisterStatus
{
    private const OPEN = 'open';
    private const CLOSED = 'closed';
    private const VERIFIED = 'verified';

    private function __construct(private string $value) {}

    public static function open(): self { return new self(self::OPEN); }
    public static function closed(): self { return new self(self::CLOSED); }
    public static function verified(): self { return new self(self::VERIFIED); }
    public static function fromString(string $value): self
    {
        $valid = [self::OPEN, self::CLOSED, self::VERIFIED];
        if (!in_array($value, $valid, true)) { throw new \InvalidArgumentException("Invalid register status: {$value}"); }
        return new self($value);
    }
    public function value(): string { return $this->value; }
    public function isOpen(): bool { return $this->value === self::OPEN; }
    public function isClosed(): bool { return $this->value === self::CLOSED; }
    public function label(): string { return ucfirst($this->value); }
    public static function all(): array { return [self::OPEN, self::CLOSED, self::VERIFIED]; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
