<?php

namespace App\Domain\PrimeEdge\ValueObjects;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::PAID => 'Paid',
            self::OVERDUE => 'Overdue',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::DRAFT => in_array($newStatus, [self::SENT, self::CANCELLED], true),
            self::SENT => in_array($newStatus, [self::PAID, self::OVERDUE, self::CANCELLED], true),
            self::OVERDUE => in_array($newStatus, [self::PAID, self::CANCELLED], true),
            self::PAID, self::CANCELLED => false,
        };
    }

    public function isPayable(): bool
    {
        return in_array($this, [self::SENT, self::OVERDUE], true);
    }
}
