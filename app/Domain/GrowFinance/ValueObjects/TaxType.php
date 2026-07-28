<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\ValueObjects;

enum TaxType: string
{
    case VAT = 'vat';
    case WITHHOLDING = 'withholding';
    case SALES_TAX = 'sales_tax';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::VAT => 'VAT',
            self::WITHHOLDING => 'Withholding Tax',
            self::SALES_TAX => 'Sales Tax',
            self::OTHER => 'Other',
        };
    }
}
