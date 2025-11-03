<?php

/**
 * Fix Jason Mwale's Wallet Balance
 * 
 * Issue: Jason deposited K500 twice but only one payment is showing
 * Solution: Add the missing K500 wallet topup
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Fixing Jason Mwale's wallet balance...\n\n";

// Find Jason Mwale
$user = DB::table('users')
    ->where('name', 'LIKE', '%Jason%Mwale%')
    ->orWhere('email', 'LIKE', '%jason%')
    ->first();

if (!$user) {
    echo "❌ User not found. Searching by phone or other details...\n";
    exit(1);
}

echo "✅ Found user: {$user->name} (ID: {$user->id}, Email: {$user->email})\n\n";

// Check current wallet topups
echo "📊 Current wallet topups:\n";
$topups = DB::table('member_payments')
    ->where('user_id', $user->id)
    ->where('payment_type', 'wallet_topup')
    ->where('status', 'verified')
    ->get();

foreach ($topups as $topup) {
    echo "  - K{$topup->amount} on {$topup->created_at}\n";
}

$totalTopups = $topups->sum('amount');
echo "\nTotal topups: K{$totalTopups}\n\n";

if ($totalTopups >= 1000) {
    echo "✅ Wallet already has K1000 or more in topups. No fix needed.\n";
    exit(0);
}

// Add missing K500 topup
echo "💰 Adding missing K500 wallet topup...\n";

DB::beginTransaction();
try {
    $reference = 'MANUAL-FIX-' . strtoupper(uniqid());
    
    DB::table('member_payments')->insert([
        'user_id' => $user->id,
        'amount' => 500,
        'phone_number' => $user->phone ?? '0000000000',
        'account_name' => $user->name,
        'payment_type' => 'wallet_topup',
        'notes' => 'Manual correction: Missing second K500 deposit',
        'status' => 'verified',
        'verified_by' => 1, // Admin
        'verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    DB::commit();
    
    echo "✅ Successfully added K500 wallet topup\n";
    echo "📝 Reference: {$reference}\n\n";
    
    // Show new balance
    $newTopups = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->where('payment_type', 'wallet_topup')
        ->where('status', 'verified')
        ->sum('amount');
    
    echo "💵 New wallet topup total: K{$newTopups}\n";
    echo "✅ Jason Mwale's wallet balance has been corrected!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
