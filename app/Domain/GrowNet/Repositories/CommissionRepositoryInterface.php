<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Repositories;

use App\Domain\GrowNet\Entities\Commission;
use App\Domain\GrowNet\ValueObjects\CommissionLevel;
use App\Domain\GrowNet\ValueObjects\MemberId;
use DateTimeImmutable;

interface CommissionRepositoryInterface
{
    /** @return Commission[] */
    public function findByReferrerId(MemberId $referrerId, ?int $limit = null): array;

    /** @return Commission[] */
    public function findByReferrerIdAndLevel(MemberId $referrerId, CommissionLevel $level): array;

    /** @return Commission[] */
    public function findByStatus(string $status): array;

    public function sumByReferrerId(MemberId $referrerId): float;

    public function sumByReferrerIdAndMonth(MemberId $referrerId, int $month, int $year): float;

    public function sumPendingByReferrerId(MemberId $referrerId): float;

    public function save(Commission $commission): Commission;

    /** @return array{level: int, total: float, paid: float, pending: float, this_month: float}[] */
    public function getLevelBreakdown(MemberId $referrerId): array;

    /** @return Commission[] */
    public function getPaymentHistory(MemberId $referrerId, int $limit = 10): array;
}
