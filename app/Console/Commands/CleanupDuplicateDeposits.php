<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateDeposits extends Command
{
    protected $signature = 'finance:cleanup-duplicate-deposits {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Remove duplicate deposit transactions that exist in both member_payments and transactions';

    public function handle(): int
    {
        $this->info('🔍 Analyzing duplicate deposits...');
        $this->newLine();
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }
        
        // Find all wallet_topup transactions that have matching member_payments
        $duplicates = DB::table('transactions as t')
            ->join('member_payments as mp', function($join) {
                $join->on('t.user_id', '=', 'mp.user_id')
                     ->on(DB::raw('DATE(t.created_at)'), '=', DB::raw('DATE(mp.created_at)'))
                     ->on('t.amount', '=', 'mp.amount');
            })
            ->where('t.transaction_type', 'wallet_topup')
            ->where('t.status', 'completed')
            ->where('mp.payment_type', 'wallet_topup')
            ->where('mp.status', 'verified')
            ->select('t.id as transaction_id', 't.user_id', 't.amount', 't.created_at', 'mp.id as payment_id')
            ->get();
        
        $this->info("Found {$duplicates->count()} duplicate wallet_topup transactions");
        
        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicates found!');
            return 0;
        }
        
        // Group by user
        $byUser = $duplicates->groupBy('user_id');
        
        $this->table(
            ['User ID', 'Duplicates', 'Total Amount'],
            $byUser->map(function($userDuplicates, $userId) {
                return [
                    $userId,
                    $userDuplicates->count(),
                    'K' . number_format($userDuplicates->sum('amount'), 2),
                ];
            })->values()
        );
        
        if ($dryRun) {
            $this->newLine();
            $this->info('To actually remove duplicates, run without --dry-run flag');
            return 0;
        }
        
        // Confirm before deleting
        if (!$this->confirm('Do you want to delete these duplicate transactions?')) {
            $this->info('Cancelled');
            return 1;
        }
        
        // Delete duplicates
        $transactionIds = $duplicates->pluck('transaction_id')->toArray();
        $deleted = DB::table('transactions')->whereIn('id', $transactionIds)->delete();
        
        $this->info("✅ Deleted {$deleted} duplicate transactions");
        
        // Clear wallet caches
        $this->info('Clearing wallet caches...');
        foreach ($byUser->keys() as $userId) {
            \Illuminate\Support\Facades\Cache::forget("wallet_balance_{$userId}");
            \Illuminate\Support\Facades\Cache::forget("wallet_totals_{$userId}");
            \Illuminate\Support\Facades\Cache::forget("wallet:balance:{$userId}");
            \Illuminate\Support\Facades\Cache::forget("wallet:breakdown:{$userId}");
        }
        
        $this->info('✅ Caches cleared');
        $this->newLine();
        $this->info('✅ Cleanup complete!');
        
        return 0;
    }
}
