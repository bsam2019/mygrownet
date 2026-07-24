<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Repositories;

use App\Domain\GrowNet\Entities\LoyaltyPoints;
use App\Domain\GrowNet\ValueObjects\MemberId;

interface LoyaltyPointsRepositoryInterface
{
    /** @return LoyaltyPoints[] */
    public function findByMemberId(MemberId $memberId): array;

    public function sumByMemberId(MemberId $memberId, string $type = 'lp'): float;

    public function sumByMemberIdAndMonth(MemberId $memberId, int $month, int $year, string $type = 'lp'): float;

    public function save(LoyaltyPoints $points): LoyaltyPoints;
}
