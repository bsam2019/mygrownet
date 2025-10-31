<?php

namespace App\Domain\Notification\ValueObjects;

enum NotificationPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function isUrgent(): bool
    {
        return $this === self::URGENT;
    }

    public function isHighOrAbove(): bool
    {
        return in_array($this, [self::HIGH, self::URGENT]);
    }
}
