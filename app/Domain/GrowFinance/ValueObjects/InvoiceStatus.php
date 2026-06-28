<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\ValueObjects;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PAID = 'paid';
    case PARTIAL = 'partial';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::PAID => 'Paid',
            self::PARTIAL => 'Partial',
            self::OVERDUE => 'Overdue',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'blue',
            self::PAID => 'emerald',
            self::PARTIAL => 'amber',
            self::OVERDUE => 'red',
            self::CANCELLED => 'gray',
        };
    }
}
