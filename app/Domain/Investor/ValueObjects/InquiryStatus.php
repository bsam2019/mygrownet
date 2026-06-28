<?php

namespace App\Domain\Investor\ValueObjects;

use InvalidArgumentException;

/**
 * Inquiry Status Value Object
 * 
 * Represents the status of an investor inquiry
 */
class InquiryStatus
{
    private const NEW = 'new';
    private const CONTACTED = 'contacted';
    private const MEETING_SCHEDULED = 'meeting_scheduled';
    private const CLOSED = 'closed';

    private const VALID_STATUSES = [
        self::NEW,
        self::CONTACTED,
        self::MEETING_SCHEDULED,
        self::CLOSED,
    ];

    private function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, self::VALID_STATUSES)) {
            throw new InvalidArgumentException("Invalid inquiry status: {$value}");
        }
    }

    public static function new(): self
    {
        return new self(self::NEW);
    }

    public static function contacted(): self
    {
        return new self(self::CONTACTED);
    }

    public static function meetingScheduled(): self
    {
        return new self(self::MEETING_SCHEDULED);
    }

    public static function closed(): self
    {
        return new self(self::CLOSED);
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function getDisplayName(): string
    {
        return match($this->value) {
            self::NEW => 'New',
            self::CONTACTED => 'Contacted',
            self::MEETING_SCHEDULED => 'Meeting Scheduled',
            self::CLOSED => 'Closed',
        };
    }

    public function getBadgeColor(): string
    {
        return match($this->value) {
            self::NEW => 'blue',
            self::CONTACTED => 'yellow',
            self::MEETING_SCHEDULED => 'green',
            self::CLOSED => 'gray',
        };
    }
}
