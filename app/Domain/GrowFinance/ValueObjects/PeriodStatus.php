<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\ValueObjects;

enum PeriodStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case LOCKED = 'locked';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::OPEN => $target === self::CLOSED,
            self::CLOSED => $target === self::OPEN || $target === self::LOCKED,
            self::LOCKED => false,
        };
    }

    public function isOpen(): bool
    {
        return $this === self::OPEN;
    }

    public function isPostable(): bool
    {
        return $this === self::OPEN;
    }
}
