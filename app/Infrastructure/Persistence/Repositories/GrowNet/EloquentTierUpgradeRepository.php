<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowNet;

use App\Domain\GrowNet\Entities\TierUpgrade;
use App\Domain\GrowNet\Repositories\TierUpgradeRepositoryInterface;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\MembershipTier;
use App\Domain\GrowNet\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\GrowNet\TierUpgrade as TierUpgradeModel;
use App\Models\InvestmentTier;
use DateTimeImmutable;

class EloquentTierUpgradeRepository implements TierUpgradeRepositoryInterface
{
    public function findByMemberId(MemberId $memberId): array
    {
        return TierUpgradeModel::where('user_id', $memberId->value())
            ->latest()
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function save(TierUpgrade $tierUpgrade): TierUpgrade
    {
        $fromTierId = $this->ensureInvestmentTier($tierUpgrade->fromTier()->displayName());
        $toTierId = $this->ensureInvestmentTier($tierUpgrade->toTier()->displayName());

        $data = [
            'user_id' => $tierUpgrade->memberId()->value(),
            'from_tier_id' => $fromTierId,
            'to_tier_id' => $toTierId,
            'total_investment_amount' => 0,
            'upgrade_reason' => $tierUpgrade->reason(),
            'processed_at' => now(),
        ];

        if ($tierUpgrade->id() > 0) {
            TierUpgradeModel::where('id', $tierUpgrade->id())->update($data);
            return $tierUpgrade;
        }

        $model = TierUpgradeModel::create($data);
        return $this->toDomain($model);
    }

    private function toDomain($model): TierUpgrade
    {
        $model->loadMissing('fromTier', 'toTier');

        $fromTierName = $model->fromTier?->name ?? 'associate';
        $toTierName = $model->toTier?->name ?? 'bronze';

        $fromTier = MembershipTier::tryFrom(strtolower($fromTierName)) ?? MembershipTier::Associate;
        $toTier = MembershipTier::tryFrom(strtolower($toTierName)) ?? MembershipTier::Bronze;

        return TierUpgrade::create(
            memberId: new MemberId($model->user_id),
            fromTier: $fromTier,
            toTier: $toTier,
            reason: $model->upgrade_reason ?? 'manual',
            achievementBonus: new Money((float) ($model->getAttribute('achievement_bonus_awarded') ?? 0)),
            teamVolumeAtUpgrade: (float) ($model->getAttribute('team_volume') ?? 0),
            activeReferralsAtUpgrade: (int) ($model->getAttribute('active_referrals') ?? 0),
        );
    }

    private function ensureInvestmentTier(string $name): int
    {
        $tier = InvestmentTier::firstOrCreate(
            ['name' => $name],
            [
                'minimum_investment' => 0,
                'fixed_profit_rate' => 0,
                'direct_referral_rate' => 0,
                'description' => $name,
                'order' => 0,
            ]
        );
        return $tier->id;
    }
}
