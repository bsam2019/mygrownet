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
     * Matches UnifiedWalletService calculation exactly:
     * - Credits: deposits (member_payments + transactions) + commissions + profit shares + LGR awards
     * - Debits: withdrawals + purchases + subscriptions
     * 
     * Note: Uses ABS() for withdrawals to handle inconsistent storage
     */
    public function getBalance(User $user): Money
    {
        $cacheKey = $this->getCacheKey($user, 'balance');
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            // Get deposits from member_payments table (legacy)
            $depositsMp = DB::table('member_payments')
                ->where('user_id', $user->id)
                ->where('payment_type', 'wallet_topup')
                ->where('status', 'verified')
                ->sum('amount');
            
            // Get deposits from transactions table
            $depositsTx = DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereIn('transaction_type', ['deposit', 'wallet_topup'])
                ->sum('amount');
            
            // Total deposits
            $deposits = $depositsMp + $depositsTx;
            
            // Get commissions from referral_commissions table
            $commissions = DB::table('referral_commissions')
                ->where('referrer_id', $user->id)
                ->where('status', 'paid')
                ->sum('amount');
            
            // Get profit shares from profit_shares table
            $profitShares = DB::table('profit_shares')
                ->where('user_id', $user->id)
                ->where('status', 'paid')
                ->sum('amount');
            
            // Get LGR manual awards from transactions
            $lgrAwards = DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->where('transaction_type', 'lgr_manual_award')
                ->sum('amount');
            
            // Total credits
            $credits = $deposits + $commissions + $profitShares + $lgrAwards;
            
            // Get debits - use ABS to handle inconsistent storage
            $debits = DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereIn('transaction_type', [
                    'withdrawal',
                    'starter_kit_purchase',
                    'starter_kit_upgrade',
                    'subscription_payment',
                    'lgr_transfer_out',
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
            // Define credit and debit transaction types based on actual production data
            $creditTypes = [
                'deposit',
                'wallet_topup',
                'lgr_manual_award',
            ];
            
            $debitTypes = [
                'withdrawal',
                'starter_kit_purchase',
                'starter_kit_upgrade',
                'subscription_payment',
                'lgr_transfer_out',
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
