<?php

namespace App\Domain\PrimeEdge\ValueObjects;

enum PartnerType: string
{
    case AUDIT = 'audit';
    case LEGAL = 'legal';
    case TAX_SPECIALIST = 'tax_specialist';
    case BANK = 'bank';
    case INSURANCE = 'insurance';
    case CONSULTANT = 'consultant';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::AUDIT => 'Audit Firm',
            self::LEGAL => 'Legal',
            self::TAX_SPECIALIST => 'Tax Specialist',
            self::BANK => 'Bank',
            self::INSURANCE => 'Insurance Broker',
            self::CONSULTANT => 'Consultant',
            self::OTHER => 'Other',
        };
    }
}
