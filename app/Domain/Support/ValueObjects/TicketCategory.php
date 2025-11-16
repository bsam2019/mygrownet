<?php

namespace App\Domain\Support\ValueObjects;

enum TicketCategory: string
{
    case TECHNICAL = 'technical';
    case FINANCIAL = 'financial';
    case ACCOUNT = 'account';
    case GENERAL = 'general';

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
        };
    }
}
