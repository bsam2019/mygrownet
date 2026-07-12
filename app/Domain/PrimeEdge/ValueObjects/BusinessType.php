<?php

namespace App\Domain\PrimeEdge\ValueObjects;

use InvalidArgumentException;

final class BusinessType
{
    private const TYPES = [
        'sole_proprietor', 'partnership', 'limited_company', 'ngo',
        'church', 'school', 'community_org', 'individual', 'other',
    ];

    private function __construct(private string $value)
    {
        if (!in_array($value, self::TYPES, true)) {
            throw new InvalidArgumentException('Invalid business type: ' . $value);
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match ($this->value) {
            'sole_proprietor' => 'Sole Proprietor',
            'partnership' => 'Partnership',
            'limited_company' => 'Limited Company',
            'ngo' => 'NGO',
            'church' => 'Church',
            'school' => 'School',
            'community_org' => 'Community Organization',
            'individual' => 'Individual',
            'other' => 'Other',
        };
    }

    public static function all(): array
    {
        return self::TYPES;
    }
}
