<?php

declare(strict_types=1);

namespace App\Domain\Payment\Enums;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case REFUNDED = 'refunded';

    public function isPending(): bool
    {
        return in_array($this, [self::PENDING, self::PROCESSING]);
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isFailed(): bool
    {
        return in_array($this, [self::FAILED, self::CANCELLED, self::EXPIRED]);
    }
}
