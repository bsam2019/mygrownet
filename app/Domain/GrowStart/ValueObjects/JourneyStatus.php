<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\ValueObjects;

use InvalidArgumentException;

final class JourneyStatus
{
    private const ACTIVE = 'active';
    private const PAUSED = 'paused';
    private const COMPLETED = 'completed';
    private const ARCHIVED = 'archived';

    private const VALID_STATUSES = [
        self::ACTIVE,
        self::PAUSED,
        self::COMPLETED,
        self::ARCHIVED,
    ];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid journey status: {$value}");
        }
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function paused(): self
    {
        return new self(self::PAUSED);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function archived(): self
    {
        return new self(self::ARCHIVED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isPaused(): bool
    {
        return $this->value === self::PAUSED;
    }

    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    public function isArchived(): bool
    {
        return $this->value === self::ARCHIVED;
    }

    public function canTransitionTo(self $newStatus): bool
    {
        $transitions = [
            self::ACTIVE => [self::PAUSED, self::COMPLETED, self::ARCHIVED],
            self::PAUSED => [self::ACTIVE, self::ARCHIVED],
            self::COMPLETED => [self::ARCHIVED],
            self::ARCHIVED => [],
        ];

        return in_array($newStatus->value, $transitions[$this->value] ?? [], true);
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
