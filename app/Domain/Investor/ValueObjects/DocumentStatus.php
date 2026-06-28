<?php

namespace App\Domain\Investor\ValueObjects;

enum DocumentStatus: string
{
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';
    case DRAFT = 'draft';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::ARCHIVED => 'Archived',
            self::DRAFT => 'Draft',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this === self::ARCHIVED;
    }

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }
}