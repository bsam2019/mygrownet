<?php

namespace App\Domain\Investor\ValueObjects;

enum AnnouncementPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match($this) {
            self::LOW => 'Low',
            self::NORMAL => 'Normal',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::LOW => 'gray',
            self::NORMAL => 'blue',
            self::HIGH => 'amber',
            self::URGENT => 'red',
        };
    }

    public function sortOrder(): int
    {
        return match($this) {
            self::URGENT => 1,
            self::HIGH => 2,
            self::NORMAL => 3,
            self::LOW => 4,
        };
    }
}
