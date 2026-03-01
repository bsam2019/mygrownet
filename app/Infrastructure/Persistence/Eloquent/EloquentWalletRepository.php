<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObjects\Money;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent Wallet Repository
 * 
 * Concrete implementation of WalletRepositoryInterface using Eloquent ORM.
 * Handles wallet balance calculations and caching.
 */
class EloquentWalletRepository implements WalletRepositoryInterface
{
    private const CACHE_TTL = 120; // 2 minutes
    private const CACHE_PREFIX = 'wallet:';

    /**
     * Get user's current wallet balance
     * 
     * Note: Handles inconsistent withdrawal storage (some positive, some negative)
     * by using ABS() for debit transaction types to match UnifiedWalletService behavior
     */
    public function getBalance(User $user): Money
    {
        $cacheKey = $this->getCacheKey($user, 'balance');
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            // Get credits (deposits, earnings, etc.)
            $credits = DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereIn('transaction_type', [
                    'wallet_topup',
                    'deposit',
                    'commission_earned',
                    'profit_share',
                    'lgr_earned',
                    'lgr_manual_award',
                    'lgr_transfer_in',
                    'loan_disbursement',
                    'shop_credit_allocation',
                ])
                ->sum('amount');
            
            // Get debits (withdrawals, purchases, etc.) - use ABS to handle inconsistent storage
            $debits = DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereIn('transaction_type', [
                    'withdrawal',
                    'starter_kit_purchase',
                    'starter_kit_upgrade',
                    'shop_purchase',
                    'shop_credit_usage',
                    'loan_repayment',
                    'lgr_transfer_out',
                    'subscription_payment',
                    'workshop_payment',
                    'learning_pack_purchase',
                    'coaching_payment',
                    'growbuilder_payment',
                    'marketplace_purchase',
                ])
                ->sum(DB::raw('ABS(amount)'));
            
            $balance = $credits - $debits;
            
            return Money::fromKwacha(max(0, $balance));
        });
    }

    /**
     * Get detailed wallet breakdown
     * 
     * Note: Uses ABS() for debit types to handle inconsistent storage
     */
    public function getBreakdown(User $user): array
    {
        $cacheKey = $this->getCacheKey($user, 'breakdown');
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            // Define credit and debit transaction types
            $creditTypes = [
                'wallet_topup',
                'deposit',
                'commission_earned',
                'profit_share',
                'lgr_earned',
                'lgr_manual_award',
                'lgr_transfer_in',
                'loan_disbursement',
                'shop_credit_allocation',
            ];
            
            $debitTypes = [
                'withdrawal',
                'starter_kit_purchase',
                'starter_kit_upgrade',
                'shop_purchase',
                'shop_credit_usage',
                'loan_repayment',
                'lgr_transfer_out',
                'subscription_payment',
                'workshop_payment',
                'learning_pack_purchase',
                'coaching_payment',
                'growbuilder_payment',
                'marketplace_purchase',
            ];
            
            // Get credit transactions
            $credits = DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereIn('transaction_type', $creditTypes)
                ->select('transaction_type', 'transaction_source', 'amount')
                ->get();
            
            $creditsByType = $credits->groupBy('transaction_type')
                ->map(fn($group) => Money::fromKwacha($group->sum('amount')))
                ->toArray();
            
            $creditsBySource = $credits->groupBy('transaction_source')
                ->filter(fn($group, $key) => !empty($key))
                ->map(fn($group) => Money::fromKwacha($group->sum('amount')))
                ->toArray();
            
            // Get debit transactions - use ABS for amounts
            $debits = DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereIn('transaction_type', $debitTypes)
                ->select('transaction_type', 'transaction_source', DB::raw('ABS(amount) as amount'))
                ->get();
            
            $debitsByType = $debits->groupBy('transaction_type')
                ->map(fn($group) => Money::fromKwacha($group->sum('amount')))
                ->toArray();
            
            $debitsBySource = $debits->groupBy('transaction_source')
                ->filter(fn($group, $key) => !empty($key))
                ->map(fn($group) => Money::fromKwacha($group->sum('amount')))
                ->toArray();
            
            return [
                'balance' => $this->getBalance($user),
                'credits' => [
                    'total' => Money::fromKwacha($credits->sum('amount')),
                    'by_type' => $creditsByType,
                    'by_source' => $creditsBySource,
                ],
                'debits' => [
                    'total' => Money::fromKwacha($debits->sum('amount')),
                    'by_type' => $debitsByType,
                    'by_source' => $debitsBySource,
                ],
            ];
        });
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance(User $user, Money $amount): bool
    {
        $balance = $this->getBalance($user);
        return $balance->isGreaterThan($amount) || $balance->equals($amount);
    }

    /**
     * Clear cached balance for user
     */
    public function clearCache(User $user): void
    {
        Cache::forget($this->getCacheKey($user, 'balance'));
        Cache::forget($this->getCacheKey($user, 'breakdown'));
    }

    /**
     * Get wallet balance snapshot at specific date
     */
    public function getBalanceAt(User $user, \DateTimeInterface $date): Money
    {
        $balance = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('created_at', '<=', $date->format('Y-m-d H:i:s'))
            ->sum('amount');
        
        return Money::fromKwacha(max(0, $balance));
    }

    /**
     * Generate cache key for user
     */
    private function getCacheKey(User $user, string $suffix): string
    {
        return self::CACHE_PREFIX . $user->id . ':' . $suffix;
    }
}
