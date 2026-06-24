<?php

namespace App\Domain\BizBoost\Contracts;

use App\Infrastructure\Persistence\Eloquent\BizBoostClientWalletModel;

interface WalletServiceInterface
{
    public function getOrCreateWallet(int $userId, string $currency = 'ZMW'): BizBoostClientWalletModel;
    public function getBalance(int $userId): float;
    public function getAvailableBalance(int $userId): float;
    public function deposit(int $userId, float $amount, string $reference, string $description = '', array $meta = []): BizBoostClientWalletModel;
    public function withdraw(int $userId, float $amount, string $reference, string $description = '', array $meta = []): BizBoostClientWalletModel;
    public function lockFunds(int $userId, float $amount, string $reference): bool;
    public function releaseLockedFunds(int $userId, float $amount, string $reference): bool;
    public function hasSufficientFunds(int $userId, float $amount): bool;
    public function getTransactions(int $userId, int $limit = 20);
}
