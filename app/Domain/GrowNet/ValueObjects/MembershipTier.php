<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\ValueObjects;

enum MembershipTier: string
{
    case Associate = 'associate';
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
    case Diamond = 'diamond';
    case Elite = 'elite';

    public function monthlyFee(): float
    {
        return match ($this) {
            self::Associate => 0,
            self::Bronze => config('mygrownet.membership_tiers.bronze.monthly_fee', 50),
            self::Silver => config('mygrownet.membership_tiers.silver.monthly_fee', 100),
            self::Gold => config('mygrownet.membership_tiers.gold.monthly_fee', 200),
            self::Diamond => config('mygrownet.membership_tiers.diamond.monthly_fee', 500),
            self::Elite => config('mygrownet.membership_tiers.elite.monthly_fee', 1000),
        };
    }

    public function teamVolumeRequirement(): float
    {
        return match ($this) {
            self::Associate => 0,
            self::Bronze => config('mygrownet.membership_tiers.bronze.team_volume_requirement', 0),
            self::Silver => config('mygrownet.membership_tiers.silver.team_volume_requirement', 5000),
            self::Gold => config('mygrownet.membership_tiers.gold.team_volume_requirement', 15000),
            self::Diamond => config('mygrownet.membership_tiers.diamond.team_volume_requirement', 50000),
            self::Elite => config('mygrownet.membership_tiers.elite.team_volume_requirement', 100000),
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Associate => self::Bronze,
            self::Bronze => self::Silver,
            self::Silver => self::Gold,
            self::Gold => self::Diamond,
            self::Diamond => self::Elite,
            self::Elite => null,
        };
    }

    public function displayName(): string
    {
        return ucfirst($this->value);
    }
}
