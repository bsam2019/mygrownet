<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Repositories;

use App\Domain\GrowNet\Entities\Referral;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\NetworkLevel;

interface ReferralRepositoryInterface
{
    /** @return Referral[] */
    public function findByReferrerId(MemberId $referrerId): array;

    /** @return Referral[] */
    public function findByReferrerIdAndLevel(MemberId $referrerId, NetworkLevel $level): array;

    /** @return Referral[] */
    public function findByReferredMemberId(MemberId $referredMemberId): array;

    public function countByReferrerId(MemberId $referrerId): int;

    public function countByReferrerIdAndLevel(MemberId $referrerId, NetworkLevel $level): int;

    public function countActiveByReferrerId(MemberId $referrerId): int;

    public function countByReferrerIdAndMonth(MemberId $referrerId, int $month, int $year): int;

    public function getMaxLevelByReferrerId(MemberId $referrerId): int;

    public function getTotalNetworkSize(MemberId $referrerId): int;

    public function getActiveNetworkMembers(MemberId $referrerId): int;

    /** @return array{level: int, total: int, active: int, percentage: float} */
    public function getLevelBreakdown(MemberId $referrerId): array;

    /** @return array{month: string, count: int}[] */
    public function getNetworkGrowthData(MemberId $referrerId, int $months = 6): array;

    /** @return Referral[] */
    public function getDetailedNetwork(MemberId $referrerId, int $maxDepth = 3): array;
}
