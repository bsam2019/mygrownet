<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserPoints;
use App\Models\PointTransaction;
use Illuminate\Console\Command;

class DiagnoseUserPoints extends Command
{
    protected $signature = 'points:diagnose {user_id? : The user ID to diagnose}';
    protected $description = 'Diagnose user points calculation issues';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if (!$userId) {
            // Show users with highest BP for selection
            $this->info('Users with highest BP:');
            $users = User::orderBy('bonus_points', 'desc')->limit(20)->get();
            
            $this->table(
                ['ID', 'Name', 'Email', 'BP (users table)', 'LP (users table)', 'Verified Referrals'],
                $users->map(function($u) {
                    return [
                        $u->id,
                        $u->name,
                        $u->email,
                        $u->bonus_points ?? 0,
                        $u->life_points ?? 0,
                        $u->directReferrals()->where('status', 'verified')->count()
                    ];
                })
            );
            
            $userId = $this->ask('Enter user ID to diagnose');
        }
        
        $user = User::with(['points', 'directReferrals', 'referredBy'])->find($userId);
        
        if (!$user) {
            $this->error("User not found!");
            return 1;
        }
        
        $this->info("=== USER INFORMATION ===");
        $this->line("ID: {$user->id}");
        $this->line("Name: {$user->name}");
        $this->line("Email: {$user->email}");
        $this->line("Status: {$user->status}");
        $this->line("Has Starter Kit: " . ($user->has_starter_kit ? 'Yes' : 'No'));
        $this->line("Referred By: " . ($user->referredBy ? $user->referredBy->name : 'None'));
        
        $this->newLine();
        $this->info("=== POINTS IN USERS TABLE ===");
        $this->line("Bonus Points (BP): " . ($user->bonus_points ?? 0));
        $this->line("Life Points (LP): " . ($user->life_points ?? 0));
        
        $this->newLine();
        $this->info("=== POINTS IN USER_POINTS TABLE ===");
        if ($user->points) {
            $this->line("Lifetime Points: " . $user->points->lifetime_points);
            $this->line("Monthly Points: " . $user->points->monthly_points);
            $this->line("Last Reset: " . ($user->points->last_reset_at ?? 'Never'));
        } else {
            $this->warn("No user_points record found!");
        }
        
        $this->newLine();
        $this->info("=== REFERRALS ===");
        $directReferrals = $user->directReferrals;
        $verifiedReferrals = $directReferrals->where('status', 'verified');
        
        $this->line("Total Direct Referrals: " . $directReferrals->count());
        $this->line("Verified Referrals: " . $verifiedReferrals->count());
        
        if ($verifiedReferrals->count() > 0) {
            $this->newLine();
            $this->table(
                ['ID', 'Name', 'Status', 'Has Starter Kit', 'Created'],
                $verifiedReferrals->map(function($r) {
                    return [
                        $r->id,
                        $r->name,
                        $r->status,
                        $r->has_starter_kit ? 'Yes' : 'No',
                        $r->created_at->format('Y-m-d H:i')
                    ];
                })
            );
        }
        
        $this->newLine();
        $this->info("=== POINT TRANSACTIONS ===");
        $transactions = PointTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
            
        if ($transactions->count() > 0) {
            $this->table(
                ['Date', 'Type', 'LP', 'BP', 'Description'],
                $transactions->map(function($t) {
                    return [
                        $t->created_at->format('Y-m-d H:i'),
                        $t->transaction_type,
                        $t->lp_amount ?? 0,
                        $t->bp_amount ?? 0,
                        $t->description
                    ];
                })
            );
            
            $totalLP = $transactions->sum('lp_amount');
            $totalBP = $transactions->sum('bp_amount');
            $this->line("Total LP from transactions: {$totalLP}");
            $this->line("Total BP from transactions: {$totalBP}");
        } else {
            $this->warn("No point transactions found!");
        }
        
        $this->newLine();
        $this->info("=== EXPECTED POINTS CALCULATION ===");
        
        // Calculate expected points
        $expectedLP = 0;
        $expectedBP = 0;
        
        // Starter kit gives 25 LP
        if ($user->has_starter_kit) {
            $expectedLP += 25;
            $this->line("+ 25 LP from Starter Kit purchase");
        }
        
        // Each verified referral gives 25 LP and 37.5 BP
        $verifiedCount = $verifiedReferrals->count();
        if ($verifiedCount > 0) {
            $expectedLP += ($verifiedCount * 25);
            $expectedBP += ($verifiedCount * 37.5);
            $this->line("+ " . ($verifiedCount * 25) . " LP from {$verifiedCount} verified referral(s) (25 LP each)");
            $this->line("+ " . ($verifiedCount * 37.5) . " BP from {$verifiedCount} verified referral(s) (37.5 BP each)");
        }
        
        $this->newLine();
        $this->line("Expected LP: {$expectedLP}");
        $this->line("Expected BP: {$expectedBP}");
        $this->line("Actual LP (users.life_points): " . ($user->life_points ?? 0));
        $this->line("Actual BP (users.bonus_points): " . ($user->bonus_points ?? 0));
        
        $lpDiff = ($user->life_points ?? 0) - $expectedLP;
        $bpDiff = ($user->bonus_points ?? 0) - $expectedBP;
        
        if ($lpDiff != 0 || $bpDiff != 0) {
            $this->newLine();
            $this->error("=== DISCREPANCY FOUND ===");
            $this->line("LP Difference: {$lpDiff}");
            $this->line("BP Difference: {$bpDiff}");
            
            if ($this->confirm('Do you want to fix these points?', false)) {
                $user->life_points = $expectedLP;
                $user->bonus_points = $expectedBP;
                $user->save();
                
                // Also update user_points table if it exists
                if ($user->points) {
                    $user->points->lifetime_points = $expectedLP;
                    $user->points->monthly_points = $expectedBP;
                    $user->points->save();
                }
                
                $this->info("Points updated successfully!");
            }
        } else {
            $this->info("✓ Points are correct!");
        }
        
        return 0;
    }
}
