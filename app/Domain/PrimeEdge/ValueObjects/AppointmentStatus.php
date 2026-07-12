<?php

namespace App\Domain\PrimeEdge\ValueObjects;

enum AppointmentStatus: string
{
    case SCHEDULED = 'scheduled';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::CONFIRMED => 'Confirmed',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::NO_SHOW => 'No Show',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::SCHEDULED => in_array($newStatus, [self::CONFIRMED, self::CANCELLED], true),
            self::CONFIRMED => in_array($newStatus, [self::COMPLETED, self::NO_SHOW, self::CANCELLED], true),
            self::COMPLETED, self::NO_SHOW, self::CANCELLED => false,
        };
    }
}
