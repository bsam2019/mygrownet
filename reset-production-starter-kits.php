<?php

/**
 * Reset starter kit purchases on production to allow testing of new tier system
 * This will:
 * 1. Refund all starter kit purchases to user wallets
 * 2. Remove starter kit access from users
 * 3. Delete all starter kit related data
 * 
 * Run with: php reset-production-starter-kits.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Resetting Starter Kit purchases on production...\n\n";

// Get all completed starter kit purchases
$purchases = DB::table('starter_kit_purchases')
    ->where('status', 'completed')
    ->get();

if ($purchases->isEmpty()) {
    echo "✅ No completed starter kit purchases found.\n";
    exit(0);
}

echo "Found " . count($purchases) . " completed starter kit purchases:\n\n";
foreach ($purchases as $purchase) {
    $user = DB::table('users')->where('id', $purchase->user_id)->first();
    echo "- {$user->name} ({$user->email}): K{$purchase->amount} ({$purchase->tier})\n";
}

echo "\n⚠️  WARNING: This will:\n";
echo "   1. Refund K{$purchases->sum('amount')} to user wallets\n";
echo "   2. Remove starter kit access from " . count($purchases) . " users\n";
echo "   3. Delete all starter kit unlocks, achievements, and related data\n";
echo "   4. Allow users to purchase starter kits again\n\n";

echo "Are you sure you want to continue? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'yes') {
    echo "❌ Reset cancelled.\n";
    exit(0);
}

echo "\nProceeding with reset...\n\n";

DB::beginTransaction();

try {
    $totalRefunded = 0;
    $usersReset = [];
    
    foreach ($purchases as $purchase) {
        $user = DB::table('users')->where('id', $purchase->user_id)->first();
        
        echo "Processing {$user->name}...\n";
        
        // 1. Refund to wallet by removing the withdrawal record
        $withdrawalDeleted = DB::table('withdrawals')
            ->where('user_id', $purchase->user_id)
            ->where('amount', $purchase->amount)
            ->where('withdrawal_method', 'wallet_payment')
            ->where('reason', 'like', '%Starter Kit%')
            ->delete();
        
        if ($withdrawalDeleted > 0) {
            echo "  ✓ Refunded K{$purchase->amount} to wallet\n";
            $totalRefunded += $purchase->amount;
        }
        
        // 2. Delete starter kit unlocks
        $unlocksDeleted = DB::table('starter_kit_unlocks')
            ->where('user_id', $purchase->user_id)
            ->delete();
        echo "  ✓ Deleted {$unlocksDeleted} content unlocks\n";
        
        // 3. Delete member achievements related to starter kit
        $achievementsDeleted = DB::table('member_achievements')
            ->where('user_id', $purchase->user_id)
            ->where('achievement_type', 'like', '%starter_kit%')
            ->delete();
        if ($achievementsDeleted > 0) {
            echo "  ✓ Deleted {$achievementsDeleted} achievements\n";
        }
        
        // 4. Remove point transactions for starter kit
        $pointsDeleted = DB::table('point_transactions')
            ->where('user_id', $purchase->user_id)
            ->where('source', 'starter_kit_purchase')
            ->delete();
        if ($pointsDeleted > 0) {
            echo "  ✓ Removed {$pointsDeleted} point transactions\n";
        }
        
        // 5. Update user record
        DB::table('users')
            ->where('id', $purchase->user_id)
            ->update([
                'has_starter_kit' => false,
                'starter_kit_tier' => null,
                'starter_kit_purchased_at' => null,
                'starter_kit_shop_credit' => 0,
                'starter_kit_credit_expiry' => null,
                'library_access_until' => null,
            ]);
        echo "  ✓ Reset user starter kit status\n";
        
        // 6. Delete the purchase record
        DB::table('starter_kit_purchases')
            ->where('id', $purchase->id)
            ->delete();
        echo "  ✓ Deleted purchase record\n\n";
        
        $usersReset[] = $user->name;
    }
    
    // Commit transaction
    DB::commit();
    
    echo "✅ Reset complete!\n\n";
    echo "📊 Summary:\n";
    echo "   - Users reset: " . count($usersReset) . "\n";
    echo "   - Total refunded: K{$totalRefunded}\n";
    echo "   - Users can now purchase starter kits again\n\n";
    
    echo "Reset users:\n";
    foreach ($usersReset as $userName) {
        echo "   - {$userName}\n";
    }
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error during reset: " . $e->getMessage() . "\n";
    echo "Transaction rolled back. No changes were made.\n";
    exit(1);
}
