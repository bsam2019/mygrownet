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
     */
    public function getBalance(User $user): Money
    {
        $cacheKey = $this->getCacheKey($user, 'balance');
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $balance = DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount');
            
            return Money::fromKwacha(max(0, $balance));
        });
    }

    /**
     * Get detailed wallet breakdown
     */
    public function getBreakdown(User $user): array
    {
        $cacheKey = $this->getCacheKey($user, 'breakdown');
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            // Get all completed transactions
            $transactions = DB::table('transactions')
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->select('transaction_type', 'transaction_source', 'amount')
                ->get();
            
            // Calculate credits
            $credits = $transactions->where('amount', '>', 0);
            $creditsByType = $credits->groupBy('transaction_type')
                ->map(fn($group) => Money::fromKwacha($group->sum('amount')))
                ->toArray();
            
            $creditsBySource = $credits->groupBy('transaction_source')
                ->map(fn($group) => Money::fromKwacha($group->sum('amount')))
                ->toArray();
            
            // Calculate debits
            $debits = $transactions->where('amount', '<', 0);
            $debitsByType = $debits->groupBy('transaction_type')
                ->map(fn($group) => Money::fromKwacha(abs($group->sum('amount'))))
                ->toArray();
            
            $debitsBySource = $debits->groupBy('transaction_source')
                ->map(fn($group) => Money::fromKwacha(abs($group->sum('amount'))))
                ->toArray();
            
            return [
                'balance' => $this->getBalance($user),
                'credits' => [
                    'total' => Money::fromKwacha($credits->sum('amount')),
                    'by_type' => $creditsByType,
                    'by_source' => $creditsBySource,
                ],
                'debits' => [
                    'total' => Money::fromKwacha(abs($debits->sum('amount'))),
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
