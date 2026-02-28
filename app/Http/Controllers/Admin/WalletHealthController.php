<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Domain\Wallet\Services\UnifiedWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Wallet Health Dashboard
 * 
 * Admin interface for monitoring wallet system health
 */
class WalletHealthController extends Controller
{
    private UnifiedWalletService $walletService;

    public function __construct(UnifiedWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Display wallet health dashboard
     */
    public function index()
    {
        // System-wide statistics
        $stats = $this->getSystemStats();
        
        // Recent issues
        $issues = $this->getRecentIssues();
        
        // Transaction statistics
        $transactionStats = $this->getTransactionStats();
        
        return Inertia::render('Admin/WalletHealth/Index', [
            'stats' => $stats,
            'issues' => $issues,
            'transactionStats' => $transactionStats,
        ]);
    }
    
    /**
     * Get detailed breakdown for a specific user
     */
    public function userBreakdown(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $breakdown = $this->walletService->getWalletBreakdown($user);
        $transactions = $this->walletService->getRecentTransactions($user, 50);
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'breakdown' => $breakdown,
            'transactions' => $transactions,
        ]);
    }
    
    /**
     * Run integrity check
     */
    public function integrityCheck()
    {
        $results = [
            'deposits_without_transactions' => $this->checkDepositsWithoutTransactions(),
            'transactions_without_status' => $this->checkTransactionsWithoutStatus(),
            'orphaned_withdrawals' => $this->checkOrphanedWithdrawals(),
        ];
        
        return response()->json($results);
    }
    
    private function getSystemStats(): array
    {
        // Total users with transactions
        $totalUsers = User::whereHas('transactions')->count();
        
        // Users with positive balance
        $usersWithBalance = 0;
        $totalSystemBalance = 0;
        $negativeBalanceCount = 0;
        
        $users = User::whereHas('transactions')->get();
        foreach ($users as $user) {
            $balance = $this->walletService->calculateBalance($user);
            if ($balance > 0) {
                $usersWithBalance++;
                $totalSystemBalance += $balance;
            } elseif ($balance < 0) {
                $negativeBalanceCount++;
            }
        }
        
        // Transaction counts
        $totalTransactions = DB::table('transactions')->count();
        $completedTransactions = DB::table('transactions')
            ->where('status', 'completed')
            ->count();
        $pendingTransactions = DB::table('transactions')
            ->where('status', 'pending')
            ->count();
        
        // Deposit/Withdrawal stats
        $totalDeposits = DB::table('transactions')
            ->where('transaction_type', 'wallet_topup')
            ->where('status', 'completed')
            ->sum('amount');
        
        $totalWithdrawals = DB::table('transactions')
            ->where('transaction_type', 'withdrawal')
            ->where('status', 'completed')
            ->sum('amount');
        
        return [
            'total_users' => $totalUsers,
            'users_with_balance' => $usersWithBalance,
            'total_system_balance' => $totalSystemBalance,
            'negative_balance_count' => $negativeBalanceCount,
            'total_transactions' => $totalTransactions,
            'completed_transactions' => $completedTransactions,
            'pending_transactions' => $pendingTransactions,
            'total_deposits' => $totalDeposits,
            'total_withdrawals' => abs($totalWithdrawals),
            'health_score' => $this->calculateHealthScore($negativeBalanceCount, $totalUsers),
        ];
    }
    
    private function getRecentIssues(): array
    {
        $issues = [];
        
        // Find users with negative balances
        $users = User::whereHas('transactions')->limit(100)->get();
        foreach ($users as $user) {
            $balance = $this->walletService->calculateBalance($user);
            if ($balance < 0) {
                $issues[] = [
                    'type' => 'negative_balance',
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'balance' => $balance,
                    'severity' => 'high',
                    'detected_at' => now()->toDateTimeString(),
                ];
            }
        }
        
        return array_slice($issues, 0, 20); // Return top 20 issues
    }
    
    private function getTransactionStats(): array
    {
        // Last 30 days transaction volume
        $last30Days = DB::table('transactions')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
        
        // Transaction types breakdown
        $typeBreakdown = DB::table('transactions')
            ->where('status', 'completed')
            ->selectRaw('transaction_type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('transaction_type')
            ->get();
        
        return [
            'daily_volume' => $last30Days,
            'type_breakdown' => $typeBreakdown,
        ];
    }
    
    private function calculateHealthScore(int $negativeCount, int $totalUsers): float
    {
        if ($totalUsers == 0) return 100;
        
        $negativePercentage = ($negativeCount / $totalUsers) * 100;
        $score = 100 - ($negativePercentage * 10); // Each 1% negative = -10 points
        
        return max(0, min(100, $score));
    }
    
    private function checkDepositsWithoutTransactions(): int
    {
        return DB::table('member_payments')
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.user_id', 'member_payments.user_id')
                    ->where('transactions.transaction_type', 'wallet_topup')
                    ->whereColumn('transactions.amount', 'member_payments.amount');
            })
            ->count();
    }
    
    private function checkTransactionsWithoutStatus(): int
    {
        return DB::table('transactions')
            ->whereNull('status')
            ->orWhere('status', '')
            ->count();
    }
    
    private function checkOrphanedWithdrawals(): int
    {
        return DB::table('withdrawals')
            ->where('status', 'approved')
            ->whereNull('transaction_id')
            ->count();
    }
}
