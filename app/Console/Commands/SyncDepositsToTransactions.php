<?php

namespace App\Console\Commands;

use App\Services\DepositSyncService;
use Illuminate\Console\Command;

class SyncDepositsToTransactions extends Command
{
    protected $signature = 'finance:sync-deposits 
                            {--user= : Sync deposits for specific user ID}
                            {--dry-run : Show what would be synced without making changes}';
    
    protected $description = 'Sync verified deposits from member_payments to transactions table';
    
    public function handle(DepositSyncService $syncService): int
    {
        $this->info('Starting deposit sync...');
        $this->newLine();
        
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }
        
        if ($userId = $this->option('user')) {
            // Sync for specific user
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                $this->error("User not found: {$userId}");
                return 1;
            }
            
            $this->info("Syncing deposits for user: {$user->name} (ID: {$user->id})");
            
            if (!$this->option('dry-run')) {
                $stats = $syncService->syncAllDepositsForUser($user);
                
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Total Deposits', $stats['total_deposits']],
                        ['Synced', $stats['synced']],
                        ['Skipped (already synced)', $stats['skipped']],
                    ]
                );
                
                if ($stats['synced'] > 0) {
                    $this->info("✓ Synced {$stats['synced']} deposits");
                }
            }
        } else {
            // Sync all deposits
            $this->info('Syncing all deposits in the system...');
            
            if (!$this->option('dry-run')) {
                $stats = $syncService->syncAllDeposits();
                
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Total Deposits', $stats['total_deposits']],
                        ['Synced', $stats['synced']],
                        ['Skipped (already synced)', $stats['skipped']],
                    ]
                );
                
                if ($stats['synced'] > 0) {
                    $this->info("✓ Synced {$stats['synced']} deposits");
                } else {
                    $this->info('✓ All deposits already synced');
                }
            }
        }
        
        $this->newLine();
        $this->info('Deposit sync complete!');
        
        return 0;
    }
}
