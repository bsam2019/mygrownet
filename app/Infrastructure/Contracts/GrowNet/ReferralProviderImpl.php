<?php

namespace App\Infrastructure\Contracts\GrowNet;

use App\Domain\GrowNet\Contracts\ReferralProvider;
use App\Domain\GrowNet\Reward\Services\ReferralMatrixService;
use App\Models\User;

class ReferralProviderImpl implements ReferralProvider
{
    public function __construct(
        private readonly ReferralMatrixService $referralMatrixService
    ) {}

    public function capability(): string
    {
        return 'grownet.referral';
    }

    public function findNextAvailablePosition(User $sponsor): ?array
    {
        return $this->referralMatrixService->findNextAvailablePosition($sponsor);
    }
}
