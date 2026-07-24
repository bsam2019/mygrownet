<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Services;

use App\Domain\GrowNet\Entities\Member;
use App\Domain\GrowNet\Entities\TierUpgrade;
use App\Domain\GrowNet\Exceptions\TierUpgradeException;
use App\Domain\GrowNet\Repositories\MemberRepositoryInterface;
use App\Domain\GrowNet\Repositories\TeamVolumeRepositoryInterface;
use App\Domain\GrowNet\Repositories\TierUpgradeRepositoryInterface;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\MembershipTier;
use App\Domain\GrowNet\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TierAdvancementService
{
    public function __construct(
        private MemberRepositoryInterface $memberRepository,
        private TeamVolumeRepositoryInterface $teamVolumeRepository,
        private TierUpgradeRepositoryInterface $tierUpgradeRepository,
        private MemberService $memberService,
    ) {}

    public function checkEligibility(Member $member): ?MembershipTier
    {
        $currentTier = $member->tier() ?? MembershipTier::Associate;
        $nextTier = $currentTier->next();

        if (!$nextTier) {
            return null;
        }

        $teamVolume = $this->teamVolumeRepository->sumTeamVolumeByMemberId($member->id());
        $activeReferrals = $member->activeReferralsCount();

        if ($teamVolume >= $nextTier->teamVolumeRequirement() && $activeReferrals > 0) {
            return $nextTier;
        }

        return null;
    }

    public function calculateTierProgress(Member $member, MembershipTier $fromTier, MembershipTier $toTier): float
    {
        $currentVolume = $this->teamVolumeRepository->sumTeamVolumeByMemberId($member->id());
        $requirement = $toTier->teamVolumeRequirement();

        if ($requirement <= 0) return 100;
        return min(100, ($currentVolume / $requirement) * 100);
    }

    public function upgradeMember(Member $member, MembershipTier $toTier, string $reason = 'manual'): TierUpgrade
    {
        $fromTier = $member->tier() ?? MembershipTier::Associate;

        if ($fromTier === $toTier) {
            throw new TierUpgradeException('Member is already at this tier');
        }

        $achievementBonus = new Money($this->getAchievementBonus($toTier));

        DB::beginTransaction();
        try {
            $tierUpgrade = TierUpgrade::create(
                memberId: $member->id(),
                fromTier: $fromTier,
                toTier: $toTier,
                reason: $reason,
                achievementBonus: $achievementBonus,
            );

            $this->tierUpgradeRepository->save($tierUpgrade);

            DB::commit();
            return $tierUpgrade;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tier upgrade failed', [
                'member_id' => $member->id()->value(),
                'from' => $fromTier->value,
                'to' => $toTier->value,
                'error' => $e->getMessage(),
            ]);
            throw new TierUpgradeException($e->getMessage());
        }
    }

    public function processAutomaticUpgrades(): array
    {
        $processed = [];
        $failed = [];

        $members = $this->memberRepository->findEligibleForTierUpgrade();

        foreach ($members as $member) {
            try {
                $nextTier = $this->checkEligibility($member);
                if ($nextTier) {
                    $upgrade = $this->upgradeMember($member, $nextTier, 'automatic_qualification');
                    $processed[] = [
                        'member_id' => $member->id()->value(),
                        'from_tier' => $upgrade->fromTier()->value,
                        'to_tier' => $upgrade->toTier()->value,
                    ];
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'member_id' => $member->id()->value(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return ['processed' => $processed, 'failed' => $failed];
    }

    private function getAchievementBonus(MembershipTier $tier): float
    {
        return match ($tier) {
            MembershipTier::Bronze => config('mygrownet.tier_advancement_bonuses.Bronze', 100),
            MembershipTier::Silver => config('mygrownet.tier_advancement_bonuses.Silver', 250),
            MembershipTier::Gold => config('mygrownet.tier_advancement_bonuses.Gold', 500),
            MembershipTier::Diamond => config('mygrownet.tier_advancement_bonuses.Diamond', 1000),
            MembershipTier::Elite => config('mygrownet.tier_advancement_bonuses.Elite', 2500),
            default => 0,
        };
    }
}
