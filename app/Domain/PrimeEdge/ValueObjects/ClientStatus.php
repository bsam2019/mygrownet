<?php

namespace App\Domain\PrimeEdge\ValueObjects;

enum ClientStatus: string
{
    case LEAD = 'lead';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::LEAD => 'Lead',
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::ARCHIVED => 'Archived',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::LEAD => in_array($newStatus, [self::ACTIVE, self::ARCHIVED], true),
            self::ACTIVE => in_array($newStatus, [self::INACTIVE, self::ARCHIVED], true),
            self::INACTIVE => in_array($newStatus, [self::ACTIVE, self::ARCHIVED], true),
            self::ARCHIVED => false,
        };
    }
}
