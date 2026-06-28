<?php

namespace App\Domain\Wedding\ValueObjects;

class WeddingStatus
{
    private const PLANNING = 'planning';
    private const CONFIRMED = 'confirmed';
    private const COMPLETED = 'completed';
    private const CANCELLED = 'cancelled';

    private const VALID_STATUSES = [
        self::PLANNING,
        self::CONFIRMED,
        self::COMPLETED,
        self::CANCELLED
    ];

    private function __construct(
        private string $status
    ) {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException("Invalid wedding status: {$status}");
        }
    }

    public static function planning(): self
    {
        return new self(self::PLANNING);
    }

    public static function confirmed(): self
    {
        return new self(self::CONFIRMED);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    public function isPlanning(): bool
    {
        return $this->status === self::PLANNING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::CONFIRMED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::CANCELLED;
    }

    public function isActive(): bool
    {
        return $this->isPlanning() || $this->isConfirmed();
    }

    public function canTransitionTo(WeddingStatus $newStatus): bool
    {
        return match ($this->status) {
            self::PLANNING => in_array($newStatus->status, [self::CONFIRMED, self::CANCELLED]),
            self::CONFIRMED => in_array($newStatus->status, [self::COMPLETED, self::CANCELLED]),
            self::COMPLETED => false, // Cannot transition from completed
            self::CANCELLED => false, // Cannot transition from cancelled
        };
    }

    public function getValue(): string
    {
        return $this->status;
    }

    public function getLabel(): string
    {
        return match ($this->status) {
            self::PLANNING => 'Planning',
            self::CONFIRMED => 'Confirmed',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this->status) {
            self::PLANNING => 'blue',
            self::CONFIRMED => 'green',
            self::COMPLETED => 'gray',
            self::CANCELLED => 'red',
        };
    }

    public function equals(WeddingStatus $other): bool
    {
        return $this->status === $other->status;
    }
}