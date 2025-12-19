<?php

namespace App\Domain\Marketplace\ValueObjects;

use InvalidArgumentException;

final class KycStatus
{
    private const PENDING = 'pending';
    private const APPROVED = 'approved';
    private const REJECTED = 'rejected';

    private const VALID_STATUSES = [
        self::PENDING,
        self::APPROVED,
        self::REJECTED,
    ];

    private function __construct(private readonly string $status)
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid KYC status: {$status}");
        }
    }

    public static function pending(): self { return new self(self::PENDING); }
    public static function approved(): self { return new self(self::APPROVED); }
    public static function rejected(): self { return new self(self::REJECTED); }

    public static function fromString(string $status): self
    {
        return new self($status);
    }

    public function value(): string { return $this->status; }
    public function isPending(): bool { return $this->status === self::PENDING; }
    public function isApproved(): bool { return $this->status === self::APPROVED; }
    public function isRejected(): bool { return $this->status === self::REJECTED; }

    public function label(): string
    {
        return match ($this->status) {
            self::PENDING => 'Pending Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this->status) {
            self::PENDING => 'yellow',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
        };
    }
}
