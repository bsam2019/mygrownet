<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowNet;

use App\Domain\GrowNet\Entities\TierUpgrade;
use App\Domain\GrowNet\Repositories\TierUpgradeRepositoryInterface;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\MembershipTier;
use App\Domain\GrowNet\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\GrowNet\TierUpgrade as TierUpgradeModel;
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
        $data = [
            'user_id' => $tierUpgrade->memberId()->value(),
            'from_tier' => $tierUpgrade->fromTier()->value,
            'to_tier' => $tierUpgrade->toTier()->value,
            'upgrade_reason' => $tierUpgrade->reason(),
            'achievement_bonus' => $tierUpgrade->achievementBonus()->amount(),
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
        $fromTier = MembershipTier::tryFrom($model->from_tier ?? $model->fromTier?->value ?? 'associate') ?? MembershipTier::Associate;
        $toTier = MembershipTier::tryFrom($model->to_tier ?? $model->toTier?->value ?? 'bronze') ?? MembershipTier::Bronze;

        return TierUpgrade::create(
            memberId: new MemberId($model->user_id),
            fromTier: $fromTier,
            toTier: $toTier,
            reason: $model->upgrade_reason ?? 'manual',
            achievementBonus: new Money((float) ($model->achievement_bonus ?? 0)),
            teamVolumeAtUpgrade: (float) ($model->team_volume ?? 0),
            activeReferralsAtUpgrade: (int) ($model->active_referrals ?? 0),
        );
    }
}
