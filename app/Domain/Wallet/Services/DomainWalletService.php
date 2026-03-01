<?php

namespace App\Domain\Wallet\Services;

use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;
use App\Models\User;

/**
 * Domain Wallet Service
 * 
 * Business logic for wallet operations using domain objects.
 * This is the NEW service that will eventually replace UnifiedWalletService.
 * 
 * Key Improvements:
 * - Uses Money value object instead of raw floats
 * - Depends on repository interface (testable)
 * - Clean separation of concerns
 * - Type-safe with domain objects
 */
class DomainWalletService
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepository
    ) {}

    /**
     * Get user's current wallet balance
     */
    public function getBalance(User $user): Money
    {
        return $this->walletRepository->getBalance($user);
    }

    /**
     * Get detailed wallet breakdown
     * 
     * @return array{
     *   balance: Money,
     *   credits: array{total: Money, by_type: array, by_source: array},
     *   debits: array{total: Money, by_type: array, by_source: array}
     * }
     */
    public function getBreakdown(User $user): array
    {
        return $this->walletRepository->getBreakdown($user);
    }

    /**
     * Check if user can withdraw amount
     */
    public function canWithdraw(User $user, Money $amount): array
    {
        $balance = $this->getBalance($user);
        $limits = $this->getWithdrawalLimits($user);
        
        $errors = [];
        
        // Check sufficient balance
        if ($amount->isGreaterThan($balance)) {
            $errors[] = 'Insufficient balance';
        }
        
        // Check single transaction limit
        $singleLimit = Money::fromKwacha($limits['single_transaction']);
        if ($amount->isGreaterThan($singleLimit)) {
            $errors[] = "Amount exceeds single transaction limit of {$singleLimit->format()}";
        }
        
        // Check daily limit
        $dailyUsed = Money::fromKwacha($user->daily_withdrawal_used ?? 0);
        $dailyLimit = Money::fromKwacha($limits['daily_withdrawal']);
        $remainingDaily = $dailyLimit->subtract($dailyUsed);
        
        if ($amount->isGreaterThan($remainingDaily)) {
            $errors[] = "Amount exceeds remaining daily limit of {$remainingDaily->format()}";
        }
        
        return [
            'can_withdraw' => empty($errors),
            'errors' => $errors,
            'balance' => $balance,
            'limits' => [
                'daily_withdrawal' => Money::fromKwacha($limits['daily_withdrawal']),
                'monthly_withdrawal' => Money::fromKwacha($limits['monthly_withdrawal']),
                'single_transaction' => Money::fromKwacha($limits['single_transaction']),
            ],
            'remaining_daily' => $remainingDaily,
        ];
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance(User $user, Money $amount): bool
    {
        return $this->walletRepository->hasSufficientBalance($user, $amount);
    }

    /**
     * Clear wallet cache (call after transactions)
     */
    public function clearCache(User $user): void
    {
        $this->walletRepository->clearCache($user);
    }

    /**
     * Get wallet balance at specific date
     */
    public function getBalanceAt(User $user, \DateTimeInterface $date): Money
    {
        return $this->walletRepository->getBalanceAt($user, $date);
    }

    /**
     * Get withdrawal limits based on verification level
     */
    public function getWithdrawalLimits(User $user): array
    {
        $level = $user->verification_level ?? 'basic';
        
        return match($level) {
            'basic' => [
                'daily_withdrawal' => 1000,
                'monthly_withdrawal' => 10000,
                'single_transaction' => 500,
            ],
            'enhanced' => [
                'daily_withdrawal' => 5000,
                'monthly_withdrawal' => 50000,
                'single_transaction' => 2000,
            ],
            'premium' => [
                'daily_withdrawal' => 20000,
                'monthly_withdrawal' => 200000,
                'single_transaction' => 10000,
            ],
            default => [
                'daily_withdrawal' => 1000,
                'monthly_withdrawal' => 10000,
                'single_transaction' => 500,
            ],
        };
    }

    /**
     * Format balance for display
     */
    public function getFormattedBalance(User $user): string
    {
        return $this->getBalance($user)->format();
    }

    /**
     * Get balance as float (for backward compatibility)
     */
    public function getBalanceAsFloat(User $user): float
    {
        return $this->getBalance($user)->amount();
    }
}
