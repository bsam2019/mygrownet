<?php

namespace App\Domain\Module\ValueObjects;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case TRIAL = 'trial';
    case PENDING = 'pending';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isTrial(): bool
    {
        return $this === self::TRIAL;
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::TRIAL => 'Trial',
            self::PENDING => 'Pending',
            self::SUSPENDED => 'Suspended',
            self::CANCELLED => 'Cancelled',
        };
    }
}
