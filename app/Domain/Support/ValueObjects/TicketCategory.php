<?php

namespace App\Domain\Support\ValueObjects;

enum TicketCategory: string
{
    // General categories
    case TECHNICAL = 'technical';
    case FINANCIAL = 'financial';
    case ACCOUNT = 'account';
    case GENERAL = 'general';
    
    // MyGrowNet member categories
    case SUBSCRIPTION = 'subscription';
    case LEARNING = 'learning';
    case COMMISSION = 'commission';
    
    // Investor categories
    case INVESTMENT = 'investment';
    case WITHDRAWAL = 'withdrawal';
    case RETURNS = 'returns';
    
    // Employee categories
    case IT = 'it';
    case HR = 'hr';
    case PAYROLL = 'payroll';
    case FACILITIES = 'facilities';
    case EQUIPMENT = 'equipment';
    case ACCESS = 'access';
    case OTHER = 'other';

    public static function fromString(string $value): self
    {
        return self::from($value);
    }

    public function label(): string
    {
        return match($this) {
            self::TECHNICAL => 'Technical Support',
            self::FINANCIAL => 'Financial Issue',
            self::ACCOUNT => 'Account Management',
            self::GENERAL => 'General Inquiry',
            self::SUBSCRIPTION => 'Subscription Help',
            self::LEARNING => 'Learning Content',
            self::COMMISSION => 'Commissions & Earnings',
            self::INVESTMENT => 'Investment Question',
            self::WITHDRAWAL => 'Withdrawal Help',
            self::RETURNS => 'Returns & Dividends',
            self::IT => 'IT Support',
            self::HR => 'HR Related',
            self::PAYROLL => 'Payroll Question',
            self::FACILITIES => 'Facilities',
            self::EQUIPMENT => 'Equipment',
            self::ACCESS => 'Access Request',
            self::OTHER => 'Other',
        };
    }
}
