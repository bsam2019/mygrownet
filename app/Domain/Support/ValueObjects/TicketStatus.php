<?php

namespace App\Domain\Support\ValueObjects;

enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case WAITING = 'waiting';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public static function fromString(string $value): self
    {
        return self::from($value);
    }

    public static function open(): self { return self::OPEN; }
    public static function inProgress(): self { return self::IN_PROGRESS; }
    public static function waiting(): self { return self::WAITING; }
    public static function resolved(): self { return self::RESOLVED; }
    public static function closed(): self { return self::CLOSED; }

    public function isOpen(): bool { return $this === self::OPEN; }
    public function isInProgress(): bool { return $this === self::IN_PROGRESS; }
    public function isWaiting(): bool { return $this === self::WAITING; }
    public function isResolved(): bool { return $this === self::RESOLVED; }
    public function isClosed(): bool { return $this === self::CLOSED; }

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Open',
            self::IN_PROGRESS => 'In Progress',
            self::WAITING => 'Waiting for Response',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPEN => 'blue',
            self::IN_PROGRESS => 'amber',
            self::WAITING => 'purple',
            self::RESOLVED => 'green',
            self::CLOSED => 'gray',
        };
    }
}
