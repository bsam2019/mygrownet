<?php

namespace App\Infrastructure\Contracts\GrowNet;

use App\Domain\GrowNet\Contracts\StarterKitProvider;
use App\Domain\GrowNet\Services\StarterKitService;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\GrowNet\StarterKitPurchaseModel;

class StarterKitProviderImpl implements StarterKitProvider
{
    public function __construct(
        private readonly StarterKitService $starterKitService
    ) {}

    public function capability(): string
    {
        return 'grownet.starter_kit';
    }

    public function completePurchase(StarterKitPurchaseModel $purchase): void
    {
        $this->starterKitService->completePurchase($purchase);
    }

    public function getUserProgress(User $user): array
    {
        return $this->starterKitService->getUserProgress($user);
    }
}
