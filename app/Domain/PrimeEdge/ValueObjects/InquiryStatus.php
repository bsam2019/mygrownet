<?php

namespace App\Domain\PrimeEdge\ValueObjects;

enum InquiryStatus: string
{
    case NEW = 'new';
    case QUOTED = 'quoted';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::QUOTED => 'Quoted',
            self::ACCEPTED => 'Accepted',
            self::DECLINED => 'Declined',
            self::EXPIRED => 'Expired',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::NEW => in_array($newStatus, [self::QUOTED, self::DECLINED, self::EXPIRED], true),
            self::QUOTED => in_array($newStatus, [self::ACCEPTED, self::DECLINED, self::EXPIRED], true),
            self::ACCEPTED, self::DECLINED, self::EXPIRED => false,
        };
    }
}
