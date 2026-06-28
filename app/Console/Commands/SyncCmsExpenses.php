<?php

namespace App\Console\Commands;

use App\Services\CmsExpenseSyncService;
use App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel;
use Illuminate\Console\Command;

class SyncCmsExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:sync-expenses 
                            {--all : Sync all approved expenses}
                            {--retry : Retry failed syncs}
                            {--stats : Show sync statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync CMS expenses to transactions table';

    /**
     * Execute the console command.
     */
    public function handle(CmsExpenseSyncService $syncService): int
    {
        // Show statistics
        if ($this->option('stats')) {
            $stats = $syncService->getSyncStatistics();
            
            $this->info('CMS Expense Sync Statistics:');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Synced', $stats['total_synced']],
                    ['Pending', $stats['pending']],
                    ['Failed', $stats['failed']],
                    ['Last Sync', $stats['last_sync'] ?? 'Never'],
                ]
            );
            
            return Command::SUCCESS;
        }

        // Retry failed syncs
        if ($this->option('retry')) {
            $this->info('Retrying failed syncs...');
            
            $results = $syncService->retryFailedSyncs();
            
            $this->info("Retry complete:");
            $this->line("  Total: {$results['total']}");
            $this->line("  Success: {$results['success']}");
            $this->line("  Failed: {$results['failed']}");
            
            return Command::SUCCESS;
        }

        // Sync all approved expenses
        if ($this->option('all')) {
            $this->info('Syncing all approved expenses...');
            
            $expenses = ExpenseModel::where('approval_status', 'approved')
                ->whereDoesntHave('transaction')
                ->get();
            
            if ($expenses->isEmpty()) {
                $this->info('No expenses to sync.');
                return Command::SUCCESS;
            }
            
            $this->info("Found {$expenses->count()} expenses to sync.");
            
            $bar = $this->output->createProgressBar($expenses->count());
            $bar->start();
            
            $synced = 0;
            $failed = 0;
            
            foreach ($expenses as $expense) {
                try {
                    $syncService->syncExpenseToTransaction($expense);
                    $synced++;
                } catch (\Exception $e) {
                    $failed++;
                    $this->error("\nFailed to sync expense {$expense->id}: {$e->getMessage()}");
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            
            $this->info("Sync complete:");
            $this->line("  Synced: {$synced}");
            $this->line("  Failed: {$failed}");
            
            return Command::SUCCESS;
        }

        // Default: show help
        $this->info('CMS Expense Sync Command');
        $this->line('');
        $this->line('Options:');
        $this->line('  --all     Sync all approved expenses');
        $this->line('  --retry   Retry failed syncs');
        $this->line('  --stats   Show sync statistics');
        $this->line('');
        $this->line('Examples:');
        $this->line('  php artisan cms:sync-expenses --all');
        $this->line('  php artisan cms:sync-expenses --retry');
        $this->line('  php artisan cms:sync-expenses --stats');

        return Command::SUCCESS;
    }
}
