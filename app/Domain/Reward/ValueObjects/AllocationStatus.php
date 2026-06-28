<?php

namespace App\Domain\Reward\ValueObjects;

class AllocationStatus
{
    private const ALLOCATED = 'allocated';
    private const DELIVERED = 'delivered';
    private const OWNERSHIP_TRANSFERRED = 'ownership_transferred';
    private const FORFEITED = 'forfeited';
    private const RECOVERED = 'recovered';

    private const VALID_STATUSES = [
        self::ALLOCATED,
        self::DELIVERED,
        self::OWNERSHIP_TRANSFERRED,
        self::FORFEITED,
        self::RECOVERED
    ];

    private function __construct(private string $value)
    {
        if (!in_array($value, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException("Invalid allocation status: {$value}");
        }
    }

    public static function allocated(): self
    {
        return new self(self::ALLOCATED);
    }

    public static function delivered(): self
    {
        return new self(self::DELIVERED);
    }

    public static function ownershipTransferred(): self
    {
        return new self(self::OWNERSHIP_TRANSFERRED);
    }

    public static function forfeited(): self
    {
        return new self(self::FORFEITED);
    }

    public static function recovered(): self
    {
        return new self(self::RECOVERED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isAllocated(): bool
    {
        return $this->value === self::ALLOCATED;
    }

    public function isDelivered(): bool
    {
        return $this->value === self::DELIVERED;
    }

    public function isOwnershipTransferred(): bool
    {
        return $this->value === self::OWNERSHIP_TRANSFERRED;
    }

    public function isForfeited(): bool
    {
        return $this->value === self::FORFEITED;
    }

    public function isRecovered(): bool
    {
        return $this->value === self::RECOVERED;
    }

    public function isActive(): bool
    {
        return in_array($this->value, [self::ALLOCATED, self::DELIVERED, self::OWNERSHIP_TRANSFERRED]);
    }

    public function equals(AllocationStatus $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}