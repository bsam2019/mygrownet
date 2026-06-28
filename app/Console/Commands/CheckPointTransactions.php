<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\PointTransaction;
use Illuminate\Console\Command;

class CheckPointTransactions extends Command
{
    protected $signature = 'points:check-transactions {user_id?}';
    protected $description = 'Check if point transactions match user activities';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if (!$userId) {
            $this->info('Recent Point Transactions:');
            $transactions = PointTransaction::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
                
            $this->table(
                ['ID', 'User', 'LP', 'BP', 'Source', 'Description', 'Date'],
                $transactions->map(function($t) {
                    return [
                        $t->id,
                        $t->user->name ?? 'N/A',
                        $t->lp_amount,
                        $t->bp_amount,
                        $t->source,
                        substr($t->description, 0, 40),
                        $t->created_at->format('Y-m-d H:i')
                    ];
                })
            );
            
            $userId = $this->ask('Enter user ID to check in detail (or press Enter to exit)');
            if (!$userId) {
                return 0;
            }
        }
        
        $user = User::with(['directReferrals', 'points'])->find($userId);
        
        if (!$user) {
            $this->error("User not found!");
            return 1;
        }
        
        $this->info("=== USER: {$user->name} (ID: {$user->id}) ===");
        $this->newLine();
        
        // Check verified referrals
        $verifiedReferrals = $user->directReferrals()->where('status', 'verified')->get();
        $this->info("Verified Referrals: {$verifiedReferrals->count()}");
        
        if ($verifiedReferrals->count() > 0) {
            $this->table(
                ['ID', 'Name', 'Status', 'Verified At'],
                $verifiedReferrals->map(function($r) {
                    return [
                        $r->id,
                        $r->name,
                        $r->status,
                        $r->updated_at->format('Y-m-d H:i')
                    ];
                })
            );
        }
        
        $this->newLine();
        
        // Check point transactions
        $transactions = PointTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $this->info("Point Transactions: {$transactions->count()}");
        
        if ($transactions->count() > 0) {
            $this->table(
                ['ID', 'LP', 'BP', 'Source', 'Description', 'Date'],
                $transactions->map(function($t) {
                    return [
                        $t->id,
                        $t->lp_amount,
                        $t->bp_amount,
                        $t->source,
                        $t->description,
                        $t->created_at->format('Y-m-d H:i')
                    ];
                })
            );
            
            $totalLP = $transactions->sum('lp_amount');
            $totalBP = $transactions->sum('bp_amount');
            
            $this->newLine();
            $this->line("Total from transactions: LP={$totalLP}, BP={$totalBP}");
        } else {
            $this->warn("No point transactions found!");
        }
        
        $this->newLine();
        
        // Compare with cached values
        $this->info("=== CACHED VALUES ===");
        $this->line("users.life_points: " . ($user->life_points ?? 0));
        $this->line("users.bonus_points: " . ($user->bonus_points ?? 0));
        
        if ($user->points) {
            $this->line("user_points.lifetime_points: " . $user->points->lifetime_points);
            $this->line("user_points.monthly_points: " . $user->points->monthly_points);
        } else {
            $this->warn("No user_points record!");
        }
        
        $this->newLine();
        
        // Expected values
        $expectedLP = 0;
        $expectedBP = 0;
        
        if ($user->has_starter_kit) {
            $expectedLP += 25;
            $this->line("Expected: +25 LP from starter kit");
        }
        
        $verifiedCount = $verifiedReferrals->count();
        if ($verifiedCount > 0) {
            $expectedLP += ($verifiedCount * 25);
            $expectedBP += ($verifiedCount * 37.5);
            $this->line("Expected: +{$verifiedCount} × 25 LP = " . ($verifiedCount * 25) . " LP from referrals");
            $this->line("Expected: +{$verifiedCount} × 37.5 BP = " . ($verifiedCount * 37.5) . " BP from referrals");
        }
        
        $this->newLine();
        $this->info("=== SUMMARY ===");
        $this->line("Expected: LP={$expectedLP}, BP={$expectedBP}");
        $this->line("From Transactions: LP=" . ($transactions->sum('lp_amount') ?? 0) . ", BP=" . ($transactions->sum('bp_amount') ?? 0));
        $this->line("Cached (users): LP=" . ($user->life_points ?? 0) . ", BP=" . ($user->bonus_points ?? 0));
        
        // Check for discrepancies
        $transactionLP = $transactions->sum('lp_amount') ?? 0;
        $transactionBP = $transactions->sum('bp_amount') ?? 0;
        
        if ($transactionLP == 0 && $transactionBP == 0 && ($expectedLP > 0 || $expectedBP > 0)) {
            $this->newLine();
            $this->error("⚠️  ISSUE: User has verified referrals but NO point transactions!");
            $this->line("This means points were never awarded when referrals were verified.");
            
            if ($this->confirm('Create missing point transactions?', false)) {
                $this->createMissingTransactions($user, $verifiedReferrals);
            }
        } elseif ($transactionLP != ($user->life_points ?? 0) || $transactionBP != ($user->bonus_points ?? 0)) {
            $this->newLine();
            $this->warn("⚠️  ISSUE: Cached values don't match transaction totals!");
            $this->line("Run: php artisan points:recalculate --user_id={$user->id}");
        } else {
            $this->newLine();
            $this->info("✓ Everything looks correct!");
        }
        
        return 0;
    }
    
    private function createMissingTransactions(User $user, $verifiedReferrals)
    {
        $this->info("Creating missing transactions...");
        
        // Starter kit transaction
        if ($user->has_starter_kit) {
            PointTransaction::create([
                'user_id' => $user->id,
                'lp_amount' => 25,
                'bp_amount' => 0,
                'source' => 'starter_kit_purchase',
                'description' => 'Starter Kit Purchase Bonus (Retroactive)',
                'reference_type' => 'starter_kit',
                'reference_id' => $user->id,
            ]);
            $this->line("✓ Created starter kit transaction (+25 LP)");
        }
        
        // Referral transactions
        foreach ($verifiedReferrals as $referral) {
            PointTransaction::create([
                'user_id' => $user->id,
                'lp_amount' => 25,
                'bp_amount' => 37.5,
                'source' => 'direct_referral',
                'description' => "Direct referral: {$referral->name} verified (Retroactive)",
                'reference_type' => 'user',
                'reference_id' => $referral->id,
            ]);
            $this->line("✓ Created referral transaction for {$referral->name} (+25 LP, +37.5 BP)");
        }
        
        $this->info("Done! Now run: php artisan points:recalculate --user_id={$user->id}");
    }
}
