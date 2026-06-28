<?php

namespace App\Domain\Wallet\Repositories;

use App\Domain\Wallet\ValueObjects\Money;
use App\Models\User;

/**
 * Wallet Repository Interface
 * 
 * Defines the contract for wallet data access.
 * Implementations handle the actual data persistence.
 */
interface WalletRepositoryInterface
{
    /**
     * Get user's current wallet balance
     */
    public function getBalance(User $user): Money;

    /**
     * Get detailed wallet breakdown
     * 
     * @return array{
     *   balance: Money,
     *   credits: array{total: Money, breakdown: array},
     *   debits: array{total: Money, breakdown: array}
     * }
     */
    public function getBreakdown(User $user): array;

    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance(User $user, Money $amount): bool;

    /**
     * Clear cached balance for user
     */
    public function clearCache(User $user): void;

    /**
     * Get wallet balance snapshot at specific date
     */
    public function getBalanceAt(User $user, \DateTimeInterface $date): Money;
}
