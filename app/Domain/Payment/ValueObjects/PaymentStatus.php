<?php

namespace App\Domain\Payment\ValueObjects;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isVerified(): bool
    {
        return $this === self::VERIFIED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::VERIFIED => 'Verified',
            self::REJECTED => 'Rejected',
        };
    }
}
