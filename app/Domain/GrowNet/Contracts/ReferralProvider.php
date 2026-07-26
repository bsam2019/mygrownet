<?php

namespace App\Domain\GrowNet\Contracts;

use App\Domain\Core\Contracts\ProviderContract;
use App\Models\User;

interface ReferralProvider extends ProviderContract
{
    public function findNextAvailablePosition(User $sponsor): ?array;
}
