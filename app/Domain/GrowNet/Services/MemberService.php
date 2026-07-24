<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Services;

use App\Domain\GrowNet\Entities\Member;
use App\Domain\GrowNet\Exceptions\MemberNotFoundException;
use App\Domain\GrowNet\Repositories\MemberRepositoryInterface;
use App\Domain\GrowNet\Repositories\ReferralRepositoryInterface;
use App\Domain\GrowNet\Repositories\TeamVolumeRepositoryInterface;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\MembershipTier;
use App\Domain\GrowNet\ValueObjects\Money;

class MemberService
{
    public function __construct(
        private MemberRepositoryInterface $memberRepository,
        private ReferralRepositoryInterface $referralRepository,
        private TeamVolumeRepositoryInterface $teamVolumeRepository,
    ) {}

    public function getMember(int $userId): Member
    {
        $member = $this->memberRepository->findByUserId($userId);
        if (!$member) {
            throw new MemberNotFoundException($userId);
        }
        return $member;
    }

    public function getMemberById(MemberId $id): Member
    {
        $member = $this->memberRepository->findById($id);
        if (!$member) {
            throw new MemberNotFoundException($id->value());
        }
        return $member;
    }

    public function getOrCreateMember(int $userId, ?int $referrerId = null, ?string $referralCode = null): Member
    {
        $existing = $this->memberRepository->findByUserId($userId);
        if ($existing) {
            return $existing;
        }

        $member = Member::create($userId, $referrerId, $referralCode);
        return $this->memberRepository->save($member);
    }

    public function getEligibleForTierUpgrade(): array
    {
        return $this->memberRepository->findEligibleForTierUpgrade();
    }

    public function getLeaderboard(int $limit = 10): array
    {
        return $this->memberRepository->getLeaderboard($limit);
    }

    public function getMemberStats(Member $member): array
    {
        $memberId = $member->id();
        $referralCount = $this->referralRepository->countByReferrerId($memberId);
        $activeReferrals = $this->referralRepository->countActiveByReferrerId($memberId);
        $teamVolume = $this->teamVolumeRepository->sumTeamVolumeByMemberId($memberId);
        $networkSize = $this->referralRepository->getTotalNetworkSize($memberId);

        return [
            'total_referrals' => $referralCount,
            'active_referrals' => $activeReferrals,
            'team_volume' => $teamVolume,
            'network_size' => $networkSize,
            'total_earnings' => $member->totalEarnings()->amount(),
            'balance' => $member->balance()->amount(),
            'bonus_balance' => $member->bonusBalance()->amount(),
            'loyalty_points' => $member->loyaltyPoints(),
            'current_tier' => $member->tier()?->displayName() ?? 'Associate',
            'professional_level' => $member->professionalLevel() ?? 'associate',
            'has_starter_kit' => $member->hasStarterKit(),
        ];
    }
}
