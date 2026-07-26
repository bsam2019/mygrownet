<?php

namespace App\Domain\GrowNet\Contracts;

use App\Domain\Core\Contracts\ProviderContract;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel;

interface StarterKitProvider extends ProviderContract
{
    public function completePurchase(StarterKitPurchaseModel $purchase): void;

    public function getUserProgress(User $user): array;
}
