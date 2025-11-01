<?php

/**
 * Reset Test Accounts Script
 * 
 * This script resets user accounts for testing while preserving wallet deposits.
 * 
 * What it does:
 * - Removes starter kit purchases
 * - Removes starter kit unlocks
 * - Removes receipts
 * - Resets starter kit flags
 * - Resets shop credit
 * - Resets library access
 * 
 * What it preserves:
 * - Wallet top-up payments (deposits)
 * - User accounts
 * - Referral structure
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Starting account reset...\n\n";

// Get all non-admin users
$users = DB::table('users')
    ->where('email', 'not like', '%admin%')
    ->get();

echo "Found " . count($users) . " user accounts to reset\n\n";

foreach ($users as $user) {
    echo "Resetting user: {$user->name} (ID: {$user->id})\n";
    
    // 1. Delete starter kit purchases
    $deletedPurchases = DB::table('starter_kit_purchases')
        ->where('user_id', $user->id)
        ->delete();
    if ($deletedPurchases > 0) {
        echo "  ✓ Deleted {$deletedPurchases} starter kit purchase(s)\n";
    }
    
    // 2. Delete starter kit unlocks
    $deletedUnlocks = DB::table('starter_kit_unlocks')
        ->where('user_id', $user->id)
        ->delete();
    if ($deletedUnlocks > 0) {
        echo "  ✓ Deleted {$deletedUnlocks} content unlock(s)\n";
    }
    
    // 3. Delete receipts
    $deletedReceipts = DB::table('receipts')
        ->where('user_id', $user->id)
        ->get();
    
    foreach ($deletedReceipts as $receipt) {
        // Delete PDF file if exists
        if ($receipt->pdf_path && file_exists($receipt->pdf_path)) {
            unlink($receipt->pdf_path);
        }
    }
    
    $receiptCount = DB::table('receipts')
        ->where('user_id', $user->id)
        ->delete();
    if ($receiptCount > 0) {
        echo "  ✓ Deleted {$receiptCount} receipt(s)\n";
    }
    
    // 4. Delete member achievements
    $deletedAchievements = DB::table('member_achievements')
        ->where('user_id', $user->id)
        ->delete();
    if ($deletedAchievements > 0) {
        echo "  ✓ Deleted {$deletedAchievements} achievement(s)\n";
    }
    
    // 5. Delete withdrawals created for wallet payments and upgrades
    $deletedWithdrawals = DB::table('withdrawals')
        ->where('user_id', $user->id)
        ->where(function($query) {
            $query->where('withdrawal_method', 'wallet_payment')
                  ->orWhere('reason', 'LIKE', '%Upgrade%');
        })
        ->delete();
    if ($deletedWithdrawals > 0) {
        echo "  ✓ Deleted {$deletedWithdrawals} wallet payment/upgrade withdrawal(s)\n";
    }
    
    // 6. Reset user starter kit flags
    DB::table('users')
        ->where('id', $user->id)
        ->update([
            'has_starter_kit' => false,
            'starter_kit_tier' => null,
            'starter_kit_purchased_at' => null,
            'starter_kit_shop_credit' => 0,
            'starter_kit_credit_expiry' => null,
            'starter_kit_terms_accepted' => false,
            'starter_kit_terms_accepted_at' => null,
            'library_access_until' => null,
        ]);
    echo "  ✓ Reset starter kit flags and tier\n";
    
    // 7. Show preserved wallet balance
    $walletTopups = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->where('payment_type', 'wallet_topup')
        ->where('status', 'verified')
        ->sum('amount');
    
    if ($walletTopups > 0) {
        echo "  💰 Preserved wallet balance: K" . number_format($walletTopups, 2) . "\n";
    }
    
    echo "\n";
}

echo "✅ Account reset complete!\n\n";
echo "Summary:\n";
echo "- User accounts: Preserved\n";
echo "- Wallet deposits: Preserved\n";
echo "- Starter kit purchases: Removed\n";
echo "- Receipts: Removed\n";
echo "- Shop credit: Reset\n";
echo "- Library access: Reset\n\n";
echo "Users can now test purchasing starter kit again!\n";
