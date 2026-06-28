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
        
        // IMPORTANT: Users without starter kit should have ZERO points
        if (!$user->has_starter_kit) {
            $correctLP = 0;
            $correctBP = 0;
        } else {
            // The point_transactions table is the SOURCE OF TRUTH
            // Sum all transactions to get the correct totals
            $correctLP = PointTransaction::where('user_id', $user->id)
                ->sum('lp_amount') ?? 0;
            $correctBP = PointTransaction::where('user_id', $user->id)
                ->sum('bp_amount') ?? 0;
            
            // If no transactions exist, calculate expected points from actual data
            if ($correctLP == 0 && $correctBP == 0) {
                // 1. Starter kit gives 25 LP (one-time)
                $correctLP += 25;
                
                // 2. Each referral with ANY verified payment gives 25 LP and 37.5 BP
                // (subscription, wallet_topup, or product payments all count)
                $verifiedReferrals = $user->directReferrals()
                    ->whereHas('memberPayments', function($query) {
                        $query->where('status', 'verified');
                    })
                    ->count();
                    
                $correctLP += ($verifiedReferrals * 25);
                $correctBP += ($verifiedReferrals * 37.5);
            }
        }
        
        $changed = ($oldLP != $correctLP) || ($oldBP != $correctBP);
        
        if ($changed && !$dryRun) {
            // Update users table (cached totals)
            $user->life_points = $correctLP;
            $user->bonus_points = $correctBP;
            $user->save();
            
            // Also update user_points table if exists (also cached totals)
            if ($user->points) {
                $user->points->lifetime_points = $correctLP;
                $user->points->monthly_points = $correctBP;
                $user->points->save();
            } else {
                // Create user_points record if it doesn't exist
                $user->points()->create([
                    'lifetime_points' => $correctLP,
                    'monthly_points' => $correctBP,
                    'last_reset_at' => now()->startOfMonth(),
                ]);
            }
        }
        
        return [
            'changed' => $changed,
            'old_lp' => $oldLP,
            'new_lp' => $correctLP,
            'old_bp' => $oldBP,
            'new_bp' => $correctBP,
        ];
    }
}
