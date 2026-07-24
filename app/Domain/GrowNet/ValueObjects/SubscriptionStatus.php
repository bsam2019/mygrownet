<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\ValueObjects;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
    case Pending = 'pending';
    case Grace = 'grace';

    public function isActive(): bool
    {
        return $this === self::Active || $this === self::Grace;
    }
}
