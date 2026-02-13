<?php

namespace App\Domain\CMS\Core\ValueObjects;

enum JobStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function canTransitionTo(JobStatus $newStatus): bool
    {
        return match ($this) {
            self::PENDING => in_array($newStatus, [self::IN_PROGRESS, self::CANCELLED]),
            self::IN_PROGRESS => in_array($newStatus, [self::COMPLETED, self::CANCELLED]),
            self::COMPLETED => false, // Completed jobs cannot change status
            self::CANCELLED => false, // Cancelled jobs cannot change status
        };
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::IN_PROGRESS]);
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::IN_PROGRESS => 'blue',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
        };
    }
}
