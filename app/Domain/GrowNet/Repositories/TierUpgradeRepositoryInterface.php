<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Repositories;

use App\Domain\GrowNet\Entities\TierUpgrade;
use App\Domain\GrowNet\ValueObjects\MemberId;

interface TierUpgradeRepositoryInterface
{
    /** @return TierUpgrade[] */
    public function findByMemberId(MemberId $memberId): array;

    public function save(TierUpgrade $tierUpgrade): TierUpgrade;
}
