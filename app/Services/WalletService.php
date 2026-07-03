<?php

namespace App\Services;

use App\Models\User;

class WalletService
{
    public function calculateBalance(User $user): float
    {
        return app(\App\Domain\Wallet\Services\WalletService::class)->calculateBalance($user);
    }

    public function getWalletBreakdown(User $user): array
    {
        return app(\App\Domain\Wallet\Services\WalletService::class)->getWalletBreakdown($user);
    }
}