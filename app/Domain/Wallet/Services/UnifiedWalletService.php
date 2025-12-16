<?php

namespace App\Domain\Wallet\Services;

use App\Models\User;
use App\Enums\AccountType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Unified Wallet Service
 * 
 * Handles wallet operations for ALL account types:
 * - MEMBER: Full earnings (commissions, profit shares) + deposits
 * - CLIENT: Deposits + venture dividends
 * - BUSINESS: Deposits + business revenue
 * 
 * PERFORMANCE: Uses caching and optimized queries to reduce DB load
 */
class UnifiedWalletService
{
    /**
     * Cache TTL in seconds (2 minutes for balance data)
     */
    private const CACHE_TTL = 120;

    /**
     * Calculate wallet balance for any account type - CACHED
     */
    public function calculateBalance(User $user): float
    {
        return Cache::remember("wallet_balance_{$user->id}", self::CACHE_TTL, function () use ($user) {
            $totals = $this->getWalletTotals($user);
            return max(0, $totals['credits'] - $totals['debits']);
        });
    }
    
    /**
     * Get all wallet totals in a single optimized query batch - CACHED
     */
    private function getWalletTotals(User $user): array
    {
        return Cache::remember("wallet_totals_{$user->id}", self::CACHE_TTL, function () use ($user) {
            $isMember = $user->hasAccountType(AccountType::MEMBER);
            $isBusiness = $user->hasAccountType(AccountType::BUSINESS);
            
            // Build a single query with UNION ALL for better performance
            $results = DB::select("
                SELECT 'deposits_mp' as type, COALESCE(SUM(amount), 0) as total 
                FROM member_payments 
                WHERE user_id = ? AND payment_type = 'wallet_topup' AND status = 'verified'
                
                UNION ALL
                
                SELECT 'deposits_tx' as type, COALESCE(SUM(amount), 0) as total 
                FROM transactions 
                WHERE user_id = ? AND transaction_type = 'wallet_topup' AND status = 'completed'
                
                UNION ALL
                
                SELECT 'withdrawals' as type, COALESCE(SUM(amount), 0) as total 
                FROM withdrawals 
                WHERE user_id = ? AND status = 'approved'
                
                UNION ALL
                
                SELECT 'withdrawal_tx' as type, COALESCE(SUM(ABS(amount)), 0) as total 
                FROM transactions 
                WHERE user_id = ? AND transaction_type = 'withdrawal' AND status = 'completed'
                
                UNION ALL
                
                SELECT 'shop_expenses' as type, COALESCE(SUM(total_amount), 0) as total 
                FROM orders 
                WHERE user_id = ? AND payment_status = 'paid' AND payment_method = 'wallet'
                
                UNION ALL
                
                SELECT 'service_expenses' as type, COALESCE(SUM(ABS(amount)), 0) as total 
                FROM transactions 
                WHERE user_id = ? AND transaction_type IN ('purchase', 'service_payment', 'starter_kit_purchase') AND status = 'completed'
            ", [$user->id, $user->id, $user->id, $user->id, $user->id, $user->id]);
            
            // Parse results into associative array
            $totals = [];
            foreach ($results as $row) {
                $totals[$row->type] = (float) $row->total;
            }
            
            // Calculate deposits
            $deposits = ($totals['deposits_mp'] ?? 0) + ($totals['deposits_tx'] ?? 0);
            
            // Calculate withdrawals and expenses
            $withdrawals = ($totals['withdrawals'] ?? 0) + ($totals['withdrawal_tx'] ?? 0);
            $expenses = ($totals['shop_expenses'] ?? 0) + ($totals['service_expenses'] ?? 0);
            
            // Get earnings (separate queries for conditional tables)
            $commissions = 0;
            $profitShares = 0;
            $ventureDividends = 0;
            $businessRevenue = 0;
            
            if ($isMember) {
                $commissions = (float) DB::table('referral_commissions')
                    ->where('referrer_id', $user->id)
                    ->where('status', 'paid')
                    ->sum('amount');
                    
                $profitShares = (float) DB::table('profit_shares')
                    ->where('user_id', $user->id)
                    ->where('status', 'paid')
                    ->sum('amount');
            }
            
            // Venture dividends - check table exists once
            // Note: venture_dividends links to venture_shareholders which has user_id
            static $hasVentureDividends = null;
            if ($hasVentureDividends === null) {
                $hasVentureDividends = DB::getSchemaBuilder()->hasTable('venture_dividends') 
                    && DB::getSchemaBuilder()->hasTable('venture_shareholders');
            }
            if ($hasVentureDividends) {
                $ventureDividends = (float) DB::table('venture_dividends')
                    ->join('venture_shareholders', 'venture_dividends.shareholder_id', '=', 'venture_shareholders.id')
                    ->where('venture_shareholders.user_id', $user->id)
                    ->where('venture_dividends.status', 'paid')
                    ->sum('venture_dividends.amount');
            }
            
            if ($isBusiness) {
                $businessRevenue = (float) DB::table('transactions')
                    ->where('user_id', $user->id)
                    ->where('transaction_type', 'business_revenue')
                    ->where('status', 'completed')
                    ->sum('amount');
            }
            
            $earnings = $commissions + $profitShares + $ventureDividends + $businessRevenue;
            
            return [
                'deposits' => $deposits,
                'commissions' => $commissions,
                'profit_shares' => $profitShares,
                'venture_dividends' => $ventureDividends,
                'business_revenue' => $businessRevenue,
                'earnings' => $earnings,
                'withdrawals' => $withdrawals,
                'expenses' => $expenses,
                'credits' => $deposits + $earnings,
                'debits' => $withdrawals + $expenses,
            ];
        });
    }
    
    /**
     * Calculate total credits based on account type
     */
    public function calculateCredits(User $user): float
    {
        return $this->getWalletTotals($user)['credits'];
    }
    
    /**
     * Calculate total debits
     */
    public function calculateDebits(User $user): float
    {
        return $this->getWalletTotals($user)['debits'];
    }
    
    /**
     * Get deposits/topups
     */
    public function getDeposits(User $user): float
    {
        return $this->getWalletTotals($user)['deposits'];
    }
    
    /**
     * Get earnings based on account type
     */
    public function getEarnings(User $user): float
    {
        return $this->getWalletTotals($user)['earnings'];
    }
    
    /**
     * Get commissions (MLM members only)
     */
    private function getCommissions(User $user): float
    {
        return $this->getWalletTotals($user)['commissions'];
    }
    
    /**
     * Get profit shares (MLM members only)
     */
    private function getProfitShares(User $user): float
    {
        return $this->getWalletTotals($user)['profit_shares'];
    }
    
    /**
     * Get venture dividends (all users)
     */
    private function getVentureDividends(User $user): float
    {
        return $this->getWalletTotals($user)['venture_dividends'];
    }
    
    /**
     * Get business revenue (business accounts)
     */
    private function getBusinessRevenue(User $user): float
    {
        return $this->getWalletTotals($user)['business_revenue'];
    }
    
    /**
     * Get withdrawals
     */
    public function getWithdrawals(User $user): float
    {
        return $this->getWalletTotals($user)['withdrawals'];
    }
    
    /**
     * Get expenses (purchases, services, etc.)
     */
    public function getExpenses(User $user): float
    {
        return $this->getWalletTotals($user)['expenses'];
    }
    
    /**
     * Clear wallet cache for a user (call after transactions)
     */
    public function clearCache(User $user): void
    {
        Cache::forget("wallet_balance_{$user->id}");
        Cache::forget("wallet_totals_{$user->id}");
    }

    
    /**
     * Get wallet breakdown for display - uses cached totals
     */
    public function getWalletBreakdown(User $user): array
    {
        $totals = $this->getWalletTotals($user);
        $isMember = $user->hasAccountType(AccountType::MEMBER);
        $isBusiness = $user->hasAccountType(AccountType::BUSINESS);
        
        $breakdown = [
            'balance' => max(0, $totals['credits'] - $totals['debits']),
            'credits' => [
                'deposits' => $totals['deposits'],
                'venture_dividends' => $totals['venture_dividends'],
                'total' => $totals['credits'],
            ],
            'debits' => [
                'withdrawals' => $totals['withdrawals'],
                'expenses' => $totals['expenses'],
                'total' => $totals['debits'],
            ],
            'account_type' => $user->getPrimaryAccountType()?->value ?? 'client',
        ];
        
        // Add MLM-specific earnings for members
        if ($isMember) {
            $breakdown['credits']['commissions'] = $totals['commissions'];
            $breakdown['credits']['profit_shares'] = $totals['profit_shares'];
        }
        
        // Add business revenue for business accounts
        if ($isBusiness) {
            $breakdown['credits']['business_revenue'] = $totals['business_revenue'];
        }
        
        return $breakdown;
    }
    
    /**
     * Get recent transactions
     */
    public function getRecentTransactions(User $user, int $limit = 10): array
    {
        $transactions = collect();
        
        // Deposits/Topups
        $topups = DB::table('member_payments')
            ->where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($t) => [
                'id' => 'topup_' . $t->id,
                'type' => 'deposit',
                'amount' => (float) $t->amount,
                'status' => 'completed',
                'date' => $t->created_at,
                'description' => 'Wallet Top-up',
                'is_credit' => true,
            ]);
        $transactions = $transactions->concat($topups);
        
        // Withdrawals
        $withdrawals = DB::table('withdrawals')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($w) => [
                'id' => 'withdrawal_' . $w->id,
                'type' => 'withdrawal',
                'amount' => (float) $w->amount,
                'status' => $w->status,
                'date' => $w->created_at,
                'description' => 'Withdrawal to ' . ($w->withdrawal_method ?? 'Mobile Money'),
                'is_credit' => false,
            ]);
        $transactions = $transactions->concat($withdrawals);
        
        // Shop purchases
        $purchases = DB::table('orders')
            ->where('user_id', $user->id)
            ->where('payment_method', 'wallet')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($o) => [
                'id' => 'order_' . $o->id,
                'type' => 'purchase',
                'amount' => (float) $o->total_amount,
                'status' => $o->payment_status,
                'date' => $o->created_at,
                'description' => 'Shop Purchase #' . $o->id,
                'is_credit' => false,
            ]);
        $transactions = $transactions->concat($purchases);
        
        // MLM earnings for members
        if ($user->hasAccountType(AccountType::MEMBER)) {
            $commissions = DB::table('referral_commissions')
                ->where('referrer_id', $user->id)
                ->where('status', 'paid')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(fn($c) => [
                    'id' => 'commission_' . $c->id,
                    'type' => 'commission',
                    'amount' => (float) $c->amount,
                    'status' => 'completed',
                    'date' => $c->created_at,
                    'description' => 'Level ' . ($c->level ?? 1) . ' Commission',
                    'is_credit' => true,
                ]);
            $transactions = $transactions->concat($commissions);
        }
        
        // Sort by date and limit
        return $transactions
            ->sortByDesc('date')
            ->take($limit)
            ->values()
            ->map(fn($t) => array_merge($t, [
                'date' => \Carbon\Carbon::parse($t['date'])->format('M d, Y'),
            ]))
            ->toArray();
    }
    
    /**
     * Check if user can withdraw amount
     */
    public function canWithdraw(User $user, float $amount): array
    {
        $balance = $this->calculateBalance($user);
        $limits = $this->getWithdrawalLimits($user);
        
        $errors = [];
        
        if ($amount > $balance) {
            $errors[] = 'Insufficient balance';
        }
        
        if ($amount > $limits['single_transaction']) {
            $errors[] = "Amount exceeds single transaction limit of K{$limits['single_transaction']}";
        }
        
        $dailyUsed = $user->daily_withdrawal_used ?? 0;
        $remainingDaily = $limits['daily_withdrawal'] - $dailyUsed;
        
        if ($amount > $remainingDaily) {
            $errors[] = "Amount exceeds remaining daily limit of K{$remainingDaily}";
        }
        
        return [
            'can_withdraw' => empty($errors),
            'errors' => $errors,
            'balance' => $balance,
            'limits' => $limits,
            'remaining_daily' => max(0, $remainingDaily),
        ];
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
}
