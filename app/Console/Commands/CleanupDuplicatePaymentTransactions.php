<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CleanupDuplicatePaymentTransactions extends Command
{
    protected $signature = 'finance:cleanup-duplicates 
                            {--dry-run : Run without making changes}
                            {--user= : Clean duplicates for specific user ID}';

    protected $description = 'Clean up duplicate payment transactions (deposit + wallet_topup for same payment)';

    private int $removed = 0;
    private int $kept = 0;

    public function handle(): int
    {
        $this->info('Starting duplicate transaction cleanup...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $userId = $this->option('user');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Find duplicate transactions: same user, same amount, within 7 days
        $query = "
            SELECT 
                t1.id as id1,
                t1.transaction_type as type1,
                t1.reference_number as ref1,
                t1.created_at as date1,
                t2.id as id2,
                t2.transaction_type as type2,
                t2.reference_number as ref2,
                t2.created_at as date2,
                t1.user_id,
                t1.amount
            FROM transactions t1
            INNER JOIN transactions t2 ON 
                t1.user_id = t2.user_id 
                AND t1.amount = t2.amount 
                AND ABS(DATEDIFF(t1.created_at, t2.created_at)) <= 7
                AND t1.id < t2.id
            WHERE 
                t1.transaction_type IN ('deposit', 'wallet_topup')
                AND t2.transaction_type IN ('deposit', 'wallet_topup')
                AND t1.transaction_type != t2.transaction_type
                AND t1.status = 'completed'
                AND t2.status = 'completed'
        ";

        if ($userId) {
            $query .= " AND t1.user_id = {$userId}";
        }

        $duplicates = DB::select($query);

        $this->info("Found " . count($duplicates) . " duplicate transaction pairs");
        $this->newLine();

        if (count($duplicates) === 0) {
            $this->info('No duplicates found!');
            return Command::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar(count($duplicates));
        $progressBar->start();

        foreach ($duplicates as $duplicate) {
            $progressBar->advance();
            
            try {
                $this->cleanupDuplicate($duplicate, $dryRun);
            } catch (\Exception $e) {
                $this->error("\nError cleaning duplicate: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->displaySummary($dryRun);

        return Command::SUCCESS;
    }

    private function cleanupDuplicate($duplicate, bool $dryRun): void
    {
        // Determine which transaction to keep
        // Priority: wallet_topup from migration (has payment_X reference) > deposit from old sync
        $keepId = null;
        $removeId = null;

        $ref1HasPayment = str_contains($duplicate->ref1 ?? '', 'payment_');
        $ref2HasPayment = str_contains($duplicate->ref2 ?? '', 'payment_');

        if ($duplicate->type1 === 'wallet_topup' && $ref1HasPayment) {
            // Keep wallet_topup from migration
            $keepId = $duplicate->id1;
            $removeId = $duplicate->id2;
        } elseif ($duplicate->type2 === 'wallet_topup' && $ref2HasPayment) {
            // Keep wallet_topup from migration
            $keepId = $duplicate->id2;
            $removeId = $duplicate->id1;
        } else {
            // Keep the older transaction (lower ID)
            $keepId = $duplicate->id1;
            $removeId = $duplicate->id2;
        }

        if ($dryRun) {
            $this->kept++;
            $this->removed++;
            return;
        }

        // Remove the duplicate
        $removed = Transaction::where('id', $removeId)->delete();

        if ($removed) {
            $this->removed++;
            $this->kept++;

            // Clear wallet cache for this user
            Cache::forget("wallet_balance_{$duplicate->user_id}");
            Cache::forget("wallet_breakdown_{$duplicate->user_id}");
            Cache::forget("wallet_totals_{$duplicate->user_id}");

            \Log::info('Duplicate transaction removed', [
                'user_id' => $duplicate->user_id,
                'kept_id' => $keepId,
                'removed_id' => $removeId,
                'amount' => $duplicate->amount,
                'date1' => $duplicate->date1,
                'date2' => $duplicate->date2,
            ]);
        }
    }

    private function displaySummary(bool $dryRun): void
    {
        $this->info('Cleanup Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Transactions Kept', $this->kept],
                ['Transactions Removed', $this->removed],
                ['Total Pairs Processed', $this->kept],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a DRY RUN - no changes were made');
            $this->info('Run without --dry-run to perform actual cleanup');
        } else {
            $this->newLine();
            $this->info('Cleanup completed successfully!');
            $this->info('Wallet caches have been cleared for affected users');
        }
    }
}

