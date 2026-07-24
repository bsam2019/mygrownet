<?php

namespace App\Domain\VentureBuilder\ValueObjects;

use InvalidArgumentException;

final class TransferStatus
{
    private const PENDING = 'pending';
    private const APPROVED = 'approved';
    private const REJECTED = 'rejected';

    private const VALID = [self::PENDING, self::APPROVED, self::REJECTED];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID, true)) {
            throw new InvalidArgumentException("Invalid transfer status: {$value}");
        }
    }

    public static function pending(): self { return new self(self::PENDING); }
    public static function approved(): self { return new self(self::APPROVED); }
    public static function rejected(): self { return new self(self::REJECTED); }
    public static function fromString(string $value): self { return new self($value); }
    public function value(): string { return $this->value; }
    public function isPending(): bool { return $this->value === self::PENDING; }
}
