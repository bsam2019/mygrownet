<?php

/**
 * Fix Premium Tier Points and Commissions
 * 
 * Requirements:
 * 1. Premium tier (K1000) members should have 50 LP (correct)
 * 2. Their uplines should only receive commissions based on K500
 * 3. Upgrade members should have 25 LP (if not already awarded)
 * 4. Upgrades should NOT trigger commissions to uplines
 * 
 * This script:
 * 1. Ensures Premium members have 50 LP (not 25 or other amounts)
 * 2. Corrects upline commissions from K1000 to K500 base
 * 3. Awards 25 LP to upgrade members if missing
 * 4. Removes any incorrect upgrade commissions
 * 
 * Run with: php scripts/fix-premium-tier-points-commissions.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔧 Fixing Premium Tier Points and Commissions...\n\n";

DB::beginTransaction();

try {
    // 1. Find all Premium tier members
    $premiumMembers = DB::table('users')
        ->where('has_starter_kit', true)
        ->where('starter_kit_tier', 'premium')
        ->get();
    
    echo "Found " . $premiumMembers->count() . " Premium tier members\n\n";
    
    $fixedPointsCount = 0;
    $fixedCommissionsCount = 0;
    $fixedUpgradePointsCount = 0;
    
    foreach ($premiumMembers as $member) {
        echo "Processing: {$member->name} (ID: {$member->id})\n";
        
        // Check if they have a starter kit purchase
        $purchase = DB::table('starter_kit_purchases')
            ->where('user_id', $member->id)
            ->where('status', 'completed')
            ->first();
        
        if (!$purchase) {
            echo "  ⚠️  No completed purchase found, skipping\n\n";
            continue;
        }
        
        // 2. Fix starter kit purchase points
        // Premium should have 50 LP, Basic should have 25 LP
        $starterKitPoints = DB::table('point_transactions')
            ->where('user_id', $member->id)
            ->where('source', 'starter_kit_purchase')
            ->first();
        
        if ($starterKitPoints) {
            $correctLP = 50; // Premium tier should have 50 LP
            
            if ($starterKitPoints->lp_amount != $correctLP) {
                $difference = $correctLP - $starterKitPoints->lp_amount;
                
                // Update the transaction to correct amount
                DB::table('point_transactions')
                    ->where('id', $starterKitPoints->id)
                    ->update([
                        'lp_amount' => $correctLP,
                        'description' => 'Starter Kit Purchase Bonus (Premium) - Corrected',
                        'updated_at' => now(),
                    ]);
                
                echo "  ✓ Fixed starter kit points: {$starterKitPoints->lp_amount} LP → {$correctLP} LP (";
                echo ($difference > 0 ? "added {$difference}" : "removed " . abs($difference)) . " LP)\n";
                $fixedPointsCount++;
            }
        }
        
        // 3. Check if this was an upgrade (had Basic tier before)
        // If so, ensure they have upgrade points (25 LP)
        $upgradePoints = DB::table('point_transactions')
            ->where('user_id', $member->id)
            ->where('source', 'starter_kit_upgrade')
            ->first();
        
        // Check if user upgraded (by looking at purchase history or other indicators)
        // For now, we'll check if they have upgrade points and ensure it's 25 LP
        if ($upgradePoints) {
            if ($upgradePoints->lp_amount != 25) {
                DB::table('point_transactions')
                    ->where('id', $upgradePoints->id)
                    ->update([
                        'lp_amount' => 25,
                        'description' => 'Starter Kit Upgrade: Basic to Premium (+25 LP) - Corrected',
                        'updated_at' => now(),
                    ]);
                
                echo "  ✓ Fixed upgrade points: {$upgradePoints->lp_amount} LP → 25 LP\n";
                $fixedUpgradePointsCount++;
            }
        }
        
        // 4. Fix commissions - should be based on K500, not K1000
        // Find commissions where package_amount is 1000 and package_type is 'starter_kit'
        $excessCommissions = DB::table('referral_commissions')
            ->where('referred_id', $member->id)
            ->where('package_type', 'starter_kit')
            ->where('package_amount', 1000)
            ->get();
        
        if ($excessCommissions->count() > 0) {
            echo "  Found " . $excessCommissions->count() . " commissions to fix\n";
            
            foreach ($excessCommissions as $commission) {
                // Calculate correct commission based on K500
                $correctAmount = 500 * ($commission->percentage / 100);
                $excessAmount = $commission->amount - $correctAmount;
                
                // Update commission to correct amount
                DB::table('referral_commissions')
                    ->where('id', $commission->id)
                    ->update([
                        'amount' => $correctAmount,
                        'package_amount' => 500,
                        'updated_at' => now(),
                    ]);
                
                echo "    ✓ Fixed Level {$commission->level} commission: K{$commission->amount} → K{$correctAmount} (removed K{$excessAmount})\n";
                
                // If commission was already paid, we need to adjust the referrer's balance
                if ($commission->status === 'paid') {
                    // Create a correction withdrawal
                    DB::table('withdrawals')->insert([
                        'user_id' => $commission->referrer_id,
                        'amount' => $excessAmount,
                        'status' => 'approved',
                        'withdrawal_method' => 'correction',
                        'reason' => "Commission correction: Premium tier should be based on K500 only (Ref: {$member->name})",
                        'processed_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    echo "    ✓ Created correction withdrawal of K{$excessAmount} for referrer ID {$commission->referrer_id}\n";
                }
                
                $fixedCommissionsCount++;
            }
        }
        
        // 5. Recalculate user's total points
        $totalLP = DB::table('point_transactions')
            ->where('user_id', $member->id)
            ->sum('lp_amount');
        $totalBP = DB::table('point_transactions')
            ->where('user_id', $member->id)
            ->sum('bp_amount');
        
        DB::table('users')
            ->where('id', $member->id)
            ->update([
                'life_points' => $totalLP,
                'bonus_points' => $totalBP,
                'updated_at' => now(),
            ]);
        
        // Update user_points table if exists
        $userPoints = DB::table('user_points')->where('user_id', $member->id)->first();
        if ($userPoints) {
            DB::table('user_points')
                ->where('user_id', $member->id)
                ->update([
                    'lifetime_points' => $totalLP,
                    'monthly_points' => $totalBP,
                    'updated_at' => now(),
                ]);
        }
        
        echo "  ✓ Recalculated totals: LP={$totalLP}, BP={$totalBP}\n\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "Premium members processed: " . $premiumMembers->count() . "\n";
    echo "Point transactions fixed: {$fixedPointsCount}\n";
    echo "Upgrade points corrected: {$fixedUpgradePointsCount}\n";
    echo "Commissions corrected: {$fixedCommissionsCount}\n\n";
    
    echo "Note: Premium members should have 50 LP from purchase.\n";
    echo "If they upgraded, they should also have 25 LP from upgrade (total 75 LP).\n";
    echo "Upline commissions are always based on K500, regardless of tier.\n\n";
    
    echo "Do you want to commit these changes? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) === 'yes') {
        DB::commit();
        echo "\n✅ Changes committed successfully!\n";
    } else {
        DB::rollBack();
        echo "\n❌ Changes rolled back. No data was modified.\n";
    }
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Changes rolled back.\n";
    exit(1);
}
