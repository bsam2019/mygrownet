<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\ValueObjects;

use InvalidArgumentException;

final class TaskStatus
{
    private const PENDING = 'pending';
    private const IN_PROGRESS = 'in_progress';
    private const COMPLETED = 'completed';
    private const SKIPPED = 'skipped';

    private const VALID_STATUSES = [
        self::PENDING,
        self::IN_PROGRESS,
        self::COMPLETED,
        self::SKIPPED,
    ];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid task status: {$value}");
        }
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function inProgress(): self
    {
        return new self(self::IN_PROGRESS);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function skipped(): self
    {
        return new self(self::SKIPPED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->value === self::IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    public function isSkipped(): bool
    {
        return $this->value === self::SKIPPED;
    }

    public function isDone(): bool
    {
        return $this->isCompleted() || $this->isSkipped();
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
