<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\PointTransaction;
use Illuminate\Console\Command;

class RecalculateAllUserPoints extends Command
{
    protected $signature = 'points:recalculate {--user_id=} {--dry-run}';
    protected $description = 'Recalculate user points based on actual data';

    public function handle()
    {
        $userId = $this->option('user_id');
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }
        
        $query = User::query();
        if ($userId) {
            $query->where('id', $userId);
        }
        
        $users = $query->get();
        $this->info("Processing {$users->count()} users...");
        
        $bar = $this->output->createProgressBar($users->count());
        $fixed = 0;
        
        foreach ($users as $user) {
            $result = $this->recalculateUserPoints($user, $dryRun);
            if ($result['changed']) {
                $fixed++;
                $this->newLine();
                $this->line("Fixed: {$user->name} (ID: {$user->id})");
                $this->line("  LP: {$result['old_lp']} → {$result['new_lp']}");
                $this->line("  BP: {$result['old_bp']} → {$result['new_bp']}");
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        $this->info("Fixed {$fixed} users");
        
        return 0;
    }
    
    private function recalculateUserPoints(User $user, bool $dryRun): array
    {
        $oldLP = $user->life_points ?? 0;
        $oldBP = $user->bonus_points ?? 0;
        
        $expectedLP = 0;
        $expectedBP = 0;
        
        // 1. Starter kit gives 25 LP (one-time)
        if ($user->has_starter_kit) {
            $expectedLP += 25;
        }
        
        // 2. Each verified referral gives 25 LP and 37.5 BP
        $verifiedReferrals = $user->directReferrals()
            ->where('status', 'verified')
            ->count();
            
        $expectedLP += ($verifiedReferrals * 25);
        $expectedBP += ($verifiedReferrals * 37.5);
        
        // 3. Check point_transactions table for any other points
        $transactionLP = PointTransaction::where('user_id', $user->id)
            ->sum('lp_amount') ?? 0;
        $transactionBP = PointTransaction::where('user_id', $user->id)
            ->sum('bp_amount') ?? 0;
            
        // Use the higher value (either calculated or from transactions)
        $finalLP = max($expectedLP, $transactionLP);
        $finalBP = max($expectedBP, $transactionBP);
        
        $changed = ($oldLP != $finalLP) || ($oldBP != $finalBP);
        
        if ($changed && !$dryRun) {
            $user->life_points = $finalLP;
            $user->bonus_points = $finalBP;
            $user->save();
            
            // Also update user_points table if exists
            if ($user->points) {
                $user->points->lifetime_points = $finalLP;
                $user->points->monthly_points = $finalBP;
                $user->points->save();
            }
        }
        
        return [
            'changed' => $changed,
            'old_lp' => $oldLP,
            'new_lp' => $finalLP,
            'old_bp' => $oldBP,
            'new_bp' => $finalBP,
        ];
    }
}
