<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LinkWithdrawalsToTransactions extends Command
{
    protected $signature = 'finance:link-withdrawals {--dry-run : Show what would be done without making changes}';
    protected $description = 'Link existing withdrawals to their corresponding transactions';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }
        
        $this->info('🔗 Linking withdrawals to transactions...');
        
        // Get all withdrawals without transaction_id
        $withdrawals = DB::table('withdrawals')
            ->whereNull('transaction_id')
            ->get();
        
        if ($withdrawals->isEmpty()) {
            $this->info('✅ All withdrawals already linked!');
            return 0;
        }
        
        $this->info("Found {$withdrawals->count()} withdrawals to link");
        
        $linked = 0;
        $notFound = 0;
        
        foreach ($withdrawals as $withdrawal) {
            // Try to find matching transaction by user_id, amount, and date
            $transaction = DB::table('transactions')
                ->where('user_id', $withdrawal->user_id)
                ->where('transaction_type', 'withdrawal')
                ->where('amount', -abs($withdrawal->amount)) // Withdrawals are negative
                ->where('status', 'completed')
                ->whereDate('created_at', $withdrawal->created_at)
                ->first();
            
            if ($transaction) {
                $this->line("  ✓ Withdrawal #{$withdrawal->id} → Transaction #{$transaction->id}");
                
                if (!$dryRun) {
                    DB::table('withdrawals')
                        ->where('id', $withdrawal->id)
                        ->update(['transaction_id' => $transaction->id]);
                }
                
                $linked++;
            } else {
                $this->warn("  ✗ Withdrawal #{$withdrawal->id} - No matching transaction found");
                $notFound++;
            }
        }
        
        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("  Linked: {$linked}");
        $this->warn("  Not found: {$notFound}");
        
        if ($dryRun) {
            $this->info("\n💡 Run without --dry-run to apply changes");
        } else {
            $this->info("\n✅ Withdrawals linked successfully!");
        }
        
        return 0;
    }
}
