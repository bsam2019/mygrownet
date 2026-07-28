<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\ValueObjects;

enum JournalStatus: string
{
    case DRAFT = 'draft';
    case POSTED = 'posted';
    case REVERSED = 'reversed';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::DRAFT => $target === self::POSTED,
            self::POSTED => $target === self::REVERSED,
            self::REVERSED => false,
        };
    }

    public function isPostable(): bool
    {
        return $this === self::DRAFT;
    }

    public function isReversible(): bool
    {
        return $this === self::POSTED;
    }
}
