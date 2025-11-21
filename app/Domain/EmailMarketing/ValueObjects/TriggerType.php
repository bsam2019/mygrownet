<?php

namespace App\Domain\EmailMarketing\ValueObjects;

enum TriggerType: string
{
    case IMMEDIATE = 'immediate';
    case SCHEDULED = 'scheduled';
    case BEHAVIORAL = 'behavioral';

    public static function immediate(): self { return self::IMMEDIATE; }
    public static function scheduled(): self { return self::SCHEDULED; }
    public static function behavioral(): self { return self::BEHAVIORAL; }

    public function isImmediate(): bool { return $this === self::IMMEDIATE; }
    public function isScheduled(): bool { return $this === self::SCHEDULED; }
    public function isBehavioral(): bool { return $this === self::BEHAVIORAL; }
}
