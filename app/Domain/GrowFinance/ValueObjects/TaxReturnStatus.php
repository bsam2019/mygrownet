<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\ValueObjects;

enum TaxReturnStatus: string
{
    case DRAFT = 'draft';
    case FILED = 'filed';
    case SUBMITTED = 'submitted';
    case PAID = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::FILED => 'Filed',
            self::SUBMITTED => 'Submitted',
            self::PAID => 'Paid',
        };
    }
}
