<?php

namespace App\Domain\GrowBiz\ValueObjects;

enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';
    case RESCHEDULED = 'rescheduled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::NO_SHOW => 'No Show',
            self::RESCHEDULED => 'Rescheduled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::CONFIRMED => 'blue',
            self::IN_PROGRESS => 'indigo',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
            self::NO_SHOW => 'gray',
            self::RESCHEDULED => 'orange',
        };
    }

    public function bgColor(): string
    {
        return match($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-800',
            self::CONFIRMED => 'bg-blue-100 text-blue-800',
            self::IN_PROGRESS => 'bg-indigo-100 text-indigo-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::CANCELLED => 'bg-red-100 text-red-800',
            self::NO_SHOW => 'bg-gray-100 text-gray-800',
            self::RESCHEDULED => 'bg-orange-100 text-orange-800',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::PENDING => in_array($newStatus, [self::CONFIRMED, self::CANCELLED]),
            self::CONFIRMED => in_array($newStatus, [self::IN_PROGRESS, self::CANCELLED, self::NO_SHOW, self::RESCHEDULED]),
            self::IN_PROGRESS => in_array($newStatus, [self::COMPLETED, self::CANCELLED]),
            self::COMPLETED, self::CANCELLED, self::NO_SHOW, self::RESCHEDULED => false,
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED, self::IN_PROGRESS]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED, self::NO_SHOW]);
    }

    public static function activeStatuses(): array
    {
        return [self::PENDING, self::CONFIRMED, self::IN_PROGRESS];
    }

    public static function completedStatuses(): array
    {
        return [self::COMPLETED, self::CANCELLED, self::NO_SHOW, self::RESCHEDULED];
    }
}
