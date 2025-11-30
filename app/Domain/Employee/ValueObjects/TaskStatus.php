<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObjects;

final class TaskStatus
{
    private const TODO = 'todo';
    private const IN_PROGRESS = 'in_progress';
    private const REVIEW = 'review';
    private const COMPLETED = 'completed';
    private const CANCELLED = 'cancelled';

    private const VALID_STATUSES = [
        self::TODO,
        self::IN_PROGRESS,
        self::REVIEW,
        self::COMPLETED,
        self::CANCELLED,
    ];

    private const TRANSITIONS = [
        self::TODO => [self::IN_PROGRESS, self::CANCELLED],
        self::IN_PROGRESS => [self::TODO, self::REVIEW, self::COMPLETED, self::CANCELLED],
        self::REVIEW => [self::IN_PROGRESS, self::COMPLETED, self::CANCELLED],
        self::COMPLETED => [],
        self::CANCELLED => [],
    ];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException("Invalid task status: {$value}");
        }
    }

    public static function todo(): self
    {
        return new self(self::TODO);
    }

    public static function inProgress(): self
    {
        return new self(self::IN_PROGRESS);
    }

    public static function review(): self
    {
        return new self(self::REVIEW);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function fromString(string $value): self
    {
        return new self(strtolower($value));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::TRANSITIONS[$this->value] ?? [], true);
    }

    public function isTodo(): bool
    {
        return $this->value === self::TODO;
    }

    public function isInProgress(): bool
    {
        return $this->value === self::IN_PROGRESS;
    }

    public function isReview(): bool
    {
        return $this->value === self::REVIEW;
    }

    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    public function isOpen(): bool
    {
        return in_array($this->value, [self::TODO, self::IN_PROGRESS, self::REVIEW], true);
    }

    public function isClosed(): bool
    {
        return in_array($this->value, [self::COMPLETED, self::CANCELLED], true);
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
