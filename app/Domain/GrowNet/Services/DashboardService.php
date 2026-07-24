<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Services;

use App\Domain\GrowNet\Entities\Member;
use App\Domain\GrowNet\Repositories\CommissionRepositoryInterface;
use App\Domain\GrowNet\Repositories\MemberRepositoryInterface;
use App\Domain\GrowNet\Repositories\ReferralRepositoryInterface;
use App\Domain\GrowNet\Repositories\TeamVolumeRepositoryInterface;
use App\Domain\GrowNet\Repositories\LoyaltyPointsRepositoryInterface;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\NetworkLevel;

class DashboardService
{
    public function __construct(
        private MemberRepositoryInterface $memberRepository,
        private ReferralRepositoryInterface $referralRepository,
        private CommissionRepositoryInterface $commissionRepository,
        private TeamVolumeRepositoryInterface $teamVolumeRepository,
        private LoyaltyPointsRepositoryInterface $loyaltyPointsRepository,
        private MemberService $memberService,
    ) {}

    public function getDashboardData(int $userId): array
    {
        $member = $this->memberService->getMember($userId);
        $memberId = $member->id();

        return [
            'referralStats' => $this->getReferralStats($memberId),
            'networkData' => $this->getNetworkData($memberId),
            'commissionSummary' => $this->getCommissionSummary($memberId),
            'teamVolumeData' => $this->getTeamVolumeData($memberId),
            'memberStats' => $this->memberService->getMemberStats($member),
            'earningsTrend' => $this->getEarningsTrend($memberId),
            'networkGrowth' => $this->referralRepository->getNetworkGrowthData($memberId),
        ];
    }

    public function getReferralStats(MemberId $memberId): array
    {
        $stats = [
            'total_referrals' => $this->referralRepository->countByReferrerId($memberId),
            'active_referrals' => $this->referralRepository->countActiveByReferrerId($memberId),
            'total_earnings' => $this->commissionRepository->sumByReferrerId($memberId),
            'levels' => [],
        ];

        for ($level = 1; $level <= 7; $level++) {
            $levelCommissions = $this->commissionRepository->findByReferrerIdAndLevel(
                $memberId, \App\Domain\GrowNet\ValueObjects\CommissionLevel::from($level)
            );
            $totalLevelEarnings = array_sum(array_map(fn($c) => $c->amount()->amount(), $levelCommissions));

            $stats['levels'][] = [
                'level' => $level,
                'count' => $this->referralRepository->countByReferrerIdAndLevel($memberId, new NetworkLevel($level)),
                'total_earnings' => $totalLevelEarnings,
            ];
        }

        return $stats;
    }

    public function getNetworkData(MemberId $memberId): array
    {
        return [
            'direct_referrals' => $this->referralRepository->findByReferrerId($memberId),
            'network_depth' => $this->referralRepository->getMaxLevelByReferrerId($memberId),
            'total_network_size' => $this->referralRepository->getTotalNetworkSize($memberId),
            'active_members' => $this->referralRepository->getActiveNetworkMembers($memberId),
            'level_breakdown' => $this->referralRepository->getLevelBreakdown($memberId),
        ];
    }

    public function getCommissionSummary(MemberId $memberId): array
    {
        return [
            'total_commissions' => $this->commissionRepository->sumByReferrerId($memberId),
            'pending_commissions' => $this->commissionRepository->sumPendingByReferrerId($memberId),
            'level_breakdown' => $this->commissionRepository->getLevelBreakdown($memberId),
            'payment_history' => $this->commissionRepository->getPaymentHistory($memberId),
        ];
    }

    public function getTeamVolumeData(MemberId $memberId): array
    {
        $current = $this->teamVolumeRepository->findCurrentByMemberId($memberId);
        $history = $this->teamVolumeRepository->findHistoryByMemberId($memberId);

        return [
            'current' => $current?->toArray(),
            'monthly_trend' => array_map(fn($tv) => [
                'month' => $tv->periodStart()->format('M Y'),
                'volume' => $tv->teamVolume(),
            ], $history),
        ];
    }

    private function getEarningsTrend(MemberId $memberId): array
    {
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = \Carbon\Carbon::now()->subMonths($i);
            $earnings = $this->commissionRepository->sumByReferrerIdAndMonth(
                $memberId, $month->month, $month->year
            );
            $trend[] = [
                'month' => $month->format('Y-m'),
                'label' => $month->format('M'),
                'amount' => $earnings,
            ];
        }
        return $trend;
    }

    public function getVerificationLimits(string $verificationLevel): array
    {
        $level = \App\Domain\GrowNet\ValueObjects\VerificationLevel::tryFrom($verificationLevel)
            ?? \App\Domain\GrowNet\ValueObjects\VerificationLevel::Basic;

        return [
            'level' => $level->value,
            'daily_withdrawal' => $level->dailyWithdrawalLimit(),
        ];
    }
}
