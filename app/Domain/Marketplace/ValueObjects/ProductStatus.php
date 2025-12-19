<?php

namespace App\Domain\Marketplace\ValueObjects;

use InvalidArgumentException;

final class ProductStatus
{
    private const DRAFT = 'draft';
    private const PENDING = 'pending';
    private const ACTIVE = 'active';
    private const REJECTED = 'rejected';
    private const SUSPENDED = 'suspended';

    private const VALID_STATUSES = [
        self::DRAFT,
        self::PENDING,
        self::ACTIVE,
        self::REJECTED,
        self::SUSPENDED,
    ];

    private function __construct(private readonly string $status)
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid product status: {$status}");
        }
    }

    public static function draft(): self { return new self(self::DRAFT); }
    public static function pending(): self { return new self(self::PENDING); }
    public static function active(): self { return new self(self::ACTIVE); }
    public static function rejected(): self { return new self(self::REJECTED); }
    public static function suspended(): self { return new self(self::SUSPENDED); }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    public function value(): string { return $this->status; }
    public function isDraft(): bool { return $this->status === self::DRAFT; }
    public function isPending(): bool { return $this->status === self::PENDING; }
    public function isActive(): bool { return $this->status === self::ACTIVE; }
    public function isRejected(): bool { return $this->status === self::REJECTED; }
    public function isSuspended(): bool { return $this->status === self::SUSPENDED; }

    public function label(): string
    {
        return match ($this->status) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending Review',
            self::ACTIVE => 'Active',
            self::REJECTED => 'Rejected',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function color(): string
    {
        return match ($this->status) {
            self::DRAFT => 'gray',
            self::PENDING => 'yellow',
            self::ACTIVE => 'green',
            self::REJECTED => 'red',
            self::SUSPENDED => 'orange',
        };
    }
}
