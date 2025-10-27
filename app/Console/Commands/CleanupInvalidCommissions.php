<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ReferralCommission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupInvalidCommissions extends Command
{
    protected $signature = 'commissions:cleanup {--dry-run}';
    protected $description = 'Remove commissions for users without starter kits';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }
        
        $this->info('Finding commissions for users without starter kits...');
        
        // Get all users without starter kits
        $usersWithoutKit = User::where('has_starter_kit', false)
            ->orWhereNull('has_starter_kit')
            ->pluck('id');
            
        $this->line("Users without starter kit: {$usersWithoutKit->count()}");
        
        // Find commissions where referrer doesn't have starter kit
        $invalidCommissions = ReferralCommission::whereIn('referrer_id', $usersWithoutKit)->get();
        
        $this->info("Invalid commissions found: {$invalidCommissions->count()}");
        
        if ($invalidCommissions->count() == 0) {
            $this->info('No invalid commissions to clean up!');
            return 0;
        }
        
        // Group by status
        $byStatus = $invalidCommissions->groupBy('status');
        
        $this->newLine();
        $this->info('Breakdown by status:');
        foreach ($byStatus as $status => $commissions) {
            $totalAmount = $commissions->sum('amount');
            $this->line("  {$status}: {$commissions->count()} commissions (K{$totalAmount})");
        }
        
        $this->newLine();
        
        // Show sample
        $this->info('Sample invalid commissions:');
        $this->table(
            ['ID', 'Referrer', 'Amount', 'Status', 'Level', 'Date'],
            $invalidCommissions->take(10)->map(function($c) {
                $referrer = User::find($c->referrer_id);
                return [
                    $c->id,
                    $referrer ? $referrer->name : 'Unknown',
                    'K' . $c->amount,
                    $c->status,
                    $c->level,
                    $c->created_at->format('Y-m-d'),
                ];
            })
        );
        
        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN - No changes made');
            $this->line('Run without --dry-run to delete these commissions');
            return 0;
        }
        
        $this->newLine();
        if (!$this->confirm('Delete these ' . $invalidCommissions->count() . ' invalid commissions?', false)) {
            $this->info('Cancelled');
            return 0;
        }
        
        // Delete the commissions
        $deleted = ReferralCommission::whereIn('referrer_id', $usersWithoutKit)->delete();
        
        $this->info("Deleted {$deleted} invalid commissions");
        
        // Also clean up point_transactions for users without starter kits
        $this->newLine();
        $this->info('Cleaning up point transactions for users without starter kits...');
        
        $deletedTransactions = DB::table('point_transactions')
            ->whereIn('user_id', $usersWithoutKit)
            ->delete();
            
        $this->info("Deleted {$deletedTransactions} point transactions");
        
        // Reset points to 0 for users without starter kits
        $this->newLine();
        $this->info('Resetting points to 0 for users without starter kits...');
        
        User::whereIn('id', $usersWithoutKit)->update([
            'life_points' => 0,
            'bonus_points' => 0,
        ]);
        
        DB::table('user_points')
            ->whereIn('user_id', $usersWithoutKit)
            ->update([
                'lifetime_points' => 0,
                'monthly_points' => 0,
            ]);
            
        $this->info('Points reset complete');
        
        $this->newLine();
        $this->info('✓ Cleanup complete!');
        $this->line("  - Deleted {$deleted} invalid commissions");
        $this->line("  - Deleted {$deletedTransactions} point transactions");
        $this->line("  - Reset points for {$usersWithoutKit->count()} users");
        
        return 0;
    }
}
