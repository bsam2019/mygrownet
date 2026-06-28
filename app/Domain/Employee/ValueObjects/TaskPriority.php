<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObjects;

final class TaskPriority
{
    private const LOW = 'low';
    private const MEDIUM = 'medium';
    private const HIGH = 'high';
    private const URGENT = 'urgent';

    private const VALID_PRIORITIES = [
        self::LOW,
        self::MEDIUM,
        self::HIGH,
        self::URGENT,
    ];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID_PRIORITIES, true)) {
            throw new \InvalidArgumentException("Invalid task priority: {$value}");
        }
    }

    public static function low(): self
    {
        return new self(self::LOW);
    }

    public static function medium(): self
    {
        return new self(self::MEDIUM);
    }

    public static function high(): self
    {
        return new self(self::HIGH);
    }

    public static function urgent(): self
    {
        return new self(self::URGENT);
    }

    public static function fromString(string $value): self
    {
        return new self(strtolower($value));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isLow(): bool
    {
        return $this->value === self::LOW;
    }

    public function isMedium(): bool
    {
        return $this->value === self::MEDIUM;
    }

    public function isHigh(): bool
    {
        return $this->value === self::HIGH;
    }

    public function isUrgent(): bool
    {
        return $this->value === self::URGENT;
    }

    public function getWeight(): int
    {
        return match ($this->value) {
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::URGENT => 4,
        };
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
