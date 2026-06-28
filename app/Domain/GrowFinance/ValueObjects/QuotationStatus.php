<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\ValueObjects;

enum QuotationStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case CONVERTED = 'converted';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
            self::EXPIRED => 'Expired',
            self::CONVERTED => 'Converted to Invoice',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'blue',
            self::ACCEPTED => 'emerald',
            self::REJECTED => 'red',
            self::EXPIRED => 'amber',
            self::CONVERTED => 'indigo',
        };
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::DRAFT, self::SENT]);
    }

    public function canConvert(): bool
    {
        return $this === self::ACCEPTED;
    }

    public function canSend(): bool
    {
        return in_array($this, [self::DRAFT, self::SENT]);
    }
}
