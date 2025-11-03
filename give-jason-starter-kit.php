<?php

/**
 * Give Jason Mwale Starter Kit
 * 
 * Issue: Jason deposited K1000 but starter kit was never activated
 * Solution: Activate his starter kit and record the purchase
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🎁 Activating Jason Mwale's starter kit...\n\n";

$userId = 7; // Jason Mwale

$user = DB::table('users')->where('id', $userId)->first();

if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "✅ Found user: {$user->name} (ID: {$user->id})\n\n";

if ($user->has_starter_kit) {
    echo "✅ User already has a starter kit\n";
    exit(0);
}

DB::beginTransaction();
try {
    // 1. Update user to have starter kit
    DB::table('users')
        ->where('id', $userId)
        ->update([
            'has_starter_kit' => true,
            'starter_kit_tier' => 'premium',
            'updated_at' => now(),
        ]);
    
    echo "✅ Updated user record\n";
    
    // 2. Create starter kit purchase record
    $invoiceNumber = 'INV-SK-' . strtoupper(uniqid());
    $purchaseId = DB::table('starter_kit_purchases')->insertGetId([
        'user_id' => $userId,
        'tier' => 'premium',
        'amount' => 500,
        'payment_method' => 'wallet',
        'invoice_number' => $invoiceNumber,
        'status' => 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Created purchase record (ID: {$purchaseId})\n";
    
    // 3. Create transaction record for the expense
    DB::table('transactions')->insert([
        'user_id' => $userId,
        'transaction_type' => 'starter_kit_purchase',
        'amount' => -500,
        'reference_number' => 'STARTER-KIT-' . strtoupper(uniqid()),
        'description' => 'Premium Starter Kit Purchase',
        'status' => 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Created transaction record\n";
    
    // 4. Award shop credit (K200 for premium)
    DB::table('users')
        ->where('id', $userId)
        ->increment('bonus_balance', 200);
    
    echo "✅ Awarded K200 shop credit\n";
    
    DB::commit();
    
    echo "\n🎉 Successfully activated Jason Mwale's Premium Starter Kit!\n";
    echo "💰 Wallet balance should now show K500 (K1000 - K500 starter kit)\n";
    echo "🎁 Shop credit: K200\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
