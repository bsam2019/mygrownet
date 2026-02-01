<?php

namespace App\Domain\Events\ValueObjects;

class EventType
{
    private const VALID_TYPES = ['webinar', 'workshop', 'meeting', 'training', 'conference'];

    private function __construct(private string $value)
    {
        if (!in_array($value, self::VALID_TYPES)) {
            throw new \InvalidArgumentException(
                "Invalid event type. Must be one of: " . implode(', ', self::VALID_TYPES)
            );
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function webinar(): self
    {
        return new self('webinar');
    }

    public static function workshop(): self
    {
        return new self('workshop');
    }

    public function value(): string
    {
        return $this->value;
    }
}
