<?php

namespace App\Domain\GrowNet\Contracts;

use App\Domain\Core\Contracts\ProviderContract;
use App\Models\User;

interface WalletProvider extends ProviderContract
{
    public function calculateBalance(User $user): float;

    public function getWalletBreakdown(User $user): array;

    public function getRecentTransactions(User $user, int $limit = 10): array;

    public function clearCache(User $user): void;
}
