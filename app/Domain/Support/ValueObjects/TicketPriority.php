<?php

namespace App\Domain\Support\ValueObjects;

enum TicketPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public static function fromString(string $value): self
    {
        return self::from($value);
    }

    public function label(): string
    {
        return match($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::LOW => 'gray',
            self::MEDIUM => 'blue',
            self::HIGH => 'amber',
            self::URGENT => 'red',
        };
    }

    public function slaHours(): int
    {
        return match($this) {
            self::LOW => 72,
            self::MEDIUM => 48,
            self::HIGH => 24,
            self::URGENT => 4,
        };
    }
}
