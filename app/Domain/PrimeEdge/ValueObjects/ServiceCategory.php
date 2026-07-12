<?php

namespace App\Domain\PrimeEdge\ValueObjects;

enum ServiceCategory: string
{
    case ACCOUNTING_COMPLIANCE = 'accounting_compliance';
    case BUSINESS_ADVISORY = 'business_advisory';
    case PERSONAL_FINANCIAL = 'personal_financial';
    case ADDITIONAL_SUPPORT = 'additional_support';

    public function label(): string
    {
        return match ($this) {
            self::ACCOUNTING_COMPLIANCE => 'Accounting & Compliance',
            self::BUSINESS_ADVISORY => 'Business Advisory',
            self::PERSONAL_FINANCIAL => 'Personal Financial Advisory',
            self::ADDITIONAL_SUPPORT => 'Additional Support',
        };
    }
}
