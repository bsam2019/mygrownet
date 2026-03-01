<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupTestWithdrawals extends Command
{
    protected $signature = 'finance:cleanup-test-withdrawals {--dry-run : Show what would be deleted without making changes}';
    protected $description = 'Remove test/fake withdrawal transactions from October 2025';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }
        
        $this->info('🧹 Cleaning up test withdrawal transactions...');
        
        // Find test withdrawals (dates in October 2025, random amounts)
        // Real withdrawals are from November 2025 onwards
        $testWithdrawals = DB::table('transactions')
            ->where('transaction_type', 'withdrawal')
            ->where('created_at', '<', '2025-11-01')
            ->get();
        
        if ($testWithdrawals->isEmpty()) {
            $this->info('✅ No test withdrawals found!');
            return 0;
        }
        
        $this->warn("Found {$testWithdrawals->count()} test withdrawal transactions");
        
        // Show summary by status
        $byStatus = $testWithdrawals->groupBy('status');
        foreach ($byStatus as $status => $txs) {
            $total = $txs->sum('amount');
            $this->line("  {$status}: {$txs->count()} transactions (K" . number_format(abs($total), 2) . ")");
        }
        
        if ($this->confirm('Delete these test transactions?', !$dryRun)) {
            if (!$dryRun) {
                $deleted = DB::table('transactions')
                    ->where('transaction_type', 'withdrawal')
                    ->where('created_at', '<', '2025-11-01')
                    ->delete();
                
                $this->info("✅ Deleted {$deleted} test withdrawal transactions");
                
                // Clear wallet caches for affected users
                $userIds = $testWithdrawals->pluck('user_id')->unique();
                foreach ($userIds as $userId) {
                    \Cache::forget("wallet_balance_{$userId}");
                    \Cache::forget("wallet_totals_{$userId}");
                }
                
                $this->info("🔄 Cleared wallet cache for {$userIds->count()} users");
            } else {
                $this->info('💡 Run without --dry-run to delete');
            }
        } else {
            $this->info('❌ Cancelled');
        }
        
        return 0;
    }
}
