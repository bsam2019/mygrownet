<?php

namespace App\Domain\VentureBuilder\ValueObjects;

use InvalidArgumentException;

final class ShareholderStatus
{
    private const ACTIVE = 'active';
    private const INACTIVE = 'inactive';
    private const REMOVED = 'removed';

    private const VALID = [self::ACTIVE, self::INACTIVE, self::REMOVED];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID, true)) {
            throw new InvalidArgumentException("Invalid shareholder status: {$value}");
        }
    }

    public static function active(): self { return new self(self::ACTIVE); }
    public static function inactive(): self { return new self(self::INACTIVE); }
    public static function removed(): self { return new self(self::REMOVED); }
    public static function fromString(string $value): self { return new self($value); }
    public function value(): string { return $this->value; }
    public function isActive(): bool { return $this->value === self::ACTIVE; }
}
