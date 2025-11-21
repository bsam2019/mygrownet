<?php

namespace App\Domain\EmailMarketing\ValueObjects;

enum CampaignType: string
{
    case ONBOARDING = 'onboarding';
    case ENGAGEMENT = 'engagement';
    case REACTIVATION = 'reactivation';
    case UPGRADE = 'upgrade';
    case CUSTOM = 'custom';

    public static function onboarding(): self { return self::ONBOARDING; }
    public static function engagement(): self { return self::ENGAGEMENT; }
    public static function reactivation(): self { return self::REACTIVATION; }
    public static function upgrade(): self { return self::UPGRADE; }
    public static function custom(): self { return self::CUSTOM; }

    public function isOnboarding(): bool { return $this === self::ONBOARDING; }
    public function isEngagement(): bool { return $this === self::ENGAGEMENT; }
    public function isReactivation(): bool { return $this === self::REACTIVATION; }
    public function isUpgrade(): bool { return $this === self::UPGRADE; }
}
