<?php

namespace App\Infrastructure\Contracts\GrowNet;

use App\Domain\GrowNet\Contracts\WalletProvider;
use App\Domain\GrowNet\Wallet\Services\WalletService;
use App\Models\User;

class WalletProviderImpl implements WalletProvider
{
    public function __construct(
        private readonly WalletService $walletService
    ) {}

    public function capability(): string
    {
        return 'grownet.wallet';
    }

    public function calculateBalance(User $user): float
    {
        return $this->walletService->calculateBalance($user);
    }

    public function getWalletBreakdown(User $user): array
    {
        return $this->walletService->getWalletBreakdown($user);
    }

    public function getRecentTransactions(User $user, int $limit = 10): array
    {
        return $this->walletService->getRecentTransactions($user, $limit);
    }

    public function clearCache(User $user): void
    {
        $this->walletService->clearCache($user);
    }
}
