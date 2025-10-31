<?php

namespace App\Domain\LoyaltyReward\ValueObjects;

enum CycleStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case SUSPENDED = 'suspended';
    case TERMINATED = 'terminated';

    public static function active(): self
    {
        return self::ACTIVE;
    }

    public static function completed(): self
    {
        return self::COMPLETED;
    }

    public static function suspended(): self
    {
        return self::SUSPENDED;
    }

    public static function terminated(): self
    {
        return self::TERMINATED;
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }

    public function isTerminated(): bool
    {
        return $this === self::TERMINATED;
    }

    public function getDisplayName(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::COMPLETED => 'Completed',
            self::SUSPENDED => 'Suspended',
            self::TERMINATED => 'Terminated',
        };
    }
}
