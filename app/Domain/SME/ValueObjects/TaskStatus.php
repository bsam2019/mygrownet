<?php

declare(strict_types=1);

namespace App\Domain\SME\ValueObjects;

use InvalidArgumentException;

final class TaskStatus
{
    private const PENDING = 'pending';
    private const IN_PROGRESS = 'in_progress';
    private const DONE = 'done';

    private const VALID_STATUSES = [
        self::PENDING,
        self::IN_PROGRESS,
        self::DONE,
    ];

    private const TRANSITIONS = [
        self::PENDING => [self::IN_PROGRESS, self::DONE],
        self::IN_PROGRESS => [self::DONE, self::PENDING],
        self::DONE => [self::PENDING, self::IN_PROGRESS],
    ];

    private function __construct(private string $value)
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

    public static function done(): self
    {
        return new self(self::DONE);
    }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    public function getValue(): string
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

    public function isDone(): bool
    {
        return $this->value === self::DONE;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::TRANSITIONS[$this->value] ?? [], true);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
