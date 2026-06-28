<?php

namespace App\Domain\EmailMarketing\ValueObjects;

enum CampaignStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';

    public static function draft(): self { return self::DRAFT; }
    public static function active(): self { return self::ACTIVE; }
    public static function paused(): self { return self::PAUSED; }
    public static function completed(): self { return self::COMPLETED; }

    public function isDraft(): bool { return $this === self::DRAFT; }
    public function isActive(): bool { return $this === self::ACTIVE; }
    public function isPaused(): bool { return $this === self::PAUSED; }
    public function isCompleted(): bool { return $this === self::COMPLETED; }
}
