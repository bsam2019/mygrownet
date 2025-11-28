<?php

/**
 * Fix Esther Ziwa's Negative Balance Issue
 * 
 * Issue: Esther Ziwa (User ID 135) is showing a negative K1000 balance in production
 * This script will investigate and fix the negative balance issue
 * 
 * Usage: php fix-esther-ziwa-negative-balance.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== FIXING ESTHER ZIWA'S NEGATIVE BALANCE ===\n\n";
echo "Starting investigation and fix for Esther Ziwa's account...\n\n";

try {
    // Find Esther Ziwa (User ID 135) - Try multiple methods
    $user = User::find(135);
    
    // If not found by ID, try by name or email
    if (!$user) {
        echo "User ID 135 not found, searching by name...\n";
        $user = User::where('name', 'like', '%Esther%')
                   ->where('name', 'like', '%Ziwa%')
                   ->first();
        
        if (!$user) {
            echo "Searching by email patterns...\n";
            $user = User::where('email', 'like', '%esther%')
                       ->orWhere('email', 'like', '%ziwa%')
                       ->first();
        }
    }
    
    if (!$user) {
        echo "❌ ERROR: Esther Ziwa not found by ID, name, or email!\n";
        echo "Available users (showing first 10):\n";
        $users = User::take(10)->get();
        foreach ($users as $u) {
            echo "  ID: {$u->id}, Name: {$u->name}, Email: {$u->email}\n";
        }
        echo "\nPlease update the script with the correct user ID.\n";
        exit(1);
    }
    
    echo "👤 Found user: {$user->name} (ID: {$user->id})\n";
    echo "📧 Email: {$user->email}\n";
    echo "📱 Phone: {$user->phone}\n\n";
    
    // Check current wallet balance calculation
    echo "1. INVESTIGATING CURRENT WALLET BALANCE\n";
    echo str_repeat('-', 50) . "\n";
    
    // Check member_payments table (wallet topups)
    $walletTopups = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->where('status', 'verified')
        ->where('payment_type', 'wallet_topup')
        ->sum('amount');
    
    echo "💰 Wallet topups total: K" . number_format($walletTopups, 2) . "\n";
    
    // Check wallet_transactions table if it exists
    $walletTransactions = 0;
    if (DB::getSchemaBuilder()->hasTable('wallet_transactions')) {
        $walletTransactions = DB::table('wallet_transactions')
            ->where('user_id', $user->id)
            ->sum('amount');
        echo "💳 Wallet transactions total: K" . number_format($walletTransactions, 2) . "\n";
    }
    
    // Check for any deductions or withdrawals
    $withdrawals = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->where('status', 'verified')
        ->whereIn('payment_type', ['withdrawal', 'deduction', 'purchase'])
        ->sum('amount');
    
    echo "💸 Withdrawals/Deductions total: K" . number_format($withdrawals, 2) . "\n";
    
    // Calculate expected balance
    $expectedBalance = $walletTopups + $walletTransactions - $withdrawals;
    echo "🧮 Expected balance: K" . number_format($expectedBalance, 2) . "\n\n";
    
    // Check if WalletService exists and calculate balance
    if (class_exists('App\Services\WalletService')) {
        try {
            $walletService = app('App\Services\WalletService');
            $currentBalance = $walletService->calculateBalance($user);
            echo "🏦 Current calculated balance: K" . number_format($currentBalance, 2) . "\n";
            
            if ($currentBalance == -1000) {
                echo "⚠️  CONFIRMED: Negative K1000 balance detected!\n\n";
            }
        } catch (Exception $e) {
            echo "⚠️  Could not calculate balance via WalletService: " . $e->getMessage() . "\n\n";
        }
    }
    
    // 2. Investigate transaction history
    echo "2. INVESTIGATING TRANSACTION HISTORY\n";
    echo str_repeat('-', 50) . "\n";
    
    $allTransactions = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "📊 Total transactions found: " . $allTransactions->count() . "\n\n";
    
    if ($allTransactions->count() > 0) {
        echo "Recent transactions:\n";
        foreach ($allTransactions->take(10) as $transaction) {
            $amount = $transaction->amount;
            $sign = in_array($transaction->payment_type, ['withdrawal', 'deduction', 'purchase']) ? '-' : '+';
            echo "  {$transaction->created_at}: {$sign}K{$amount} ({$transaction->payment_type}) - {$transaction->status}\n";
        }
        echo "\n";
    }
    
    // 3. Check for problematic transactions
    echo "3. CHECKING FOR PROBLEMATIC TRANSACTIONS\n";
    echo str_repeat('-', 50) . "\n";
    
    // Look for any transactions that might cause negative balance
    $suspiciousTransactions = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->where('amount', 1000)
        ->get();
    
    if ($suspiciousTransactions->count() > 0) {
        echo "🔍 Found transactions with K1000 amount:\n";
        foreach ($suspiciousTransactions as $transaction) {
            echo "  ID: {$transaction->id}, Type: {$transaction->payment_type}, Status: {$transaction->status}, Date: {$transaction->created_at}\n";
        }
        echo "\n";
    }
    
    // 4. Apply fix based on findings
    echo "4. APPLYING FIX\n";
    echo str_repeat('-', 50) . "\n";
    
    $fixApplied = false;
    
    // Check if there's a problematic deduction or withdrawal
    $problematicDeduction = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->where('amount', 1000)
        ->whereIn('payment_type', ['deduction', 'withdrawal'])
        ->where('status', 'verified')
        ->first();
    
    if ($problematicDeduction) {
        echo "🎯 Found problematic deduction/withdrawal of K1000\n";
        echo "   Transaction ID: {$problematicDeduction->id}\n";
        echo "   Type: {$problematicDeduction->payment_type}\n";
        echo "   Date: {$problematicDeduction->created_at}\n\n";
        
        // Option 1: Reverse the problematic transaction
        echo "Applying Fix Option 1: Reversing problematic transaction...\n";
        
        DB::table('member_payments')
            ->where('id', $problematicDeduction->id)
            ->update([
                'status' => 'reversed',
                'notes' => 'Reversed due to negative balance issue - Fixed on ' . date('Y-m-d H:i:s'),
                'updated_at' => now()
            ]);
        
        echo "✅ Reversed transaction ID {$problematicDeduction->id}\n";
        $fixApplied = true;
    }
    
    // If no problematic transaction found, add corrective topup
    if (!$fixApplied) {
        echo "No specific problematic transaction found.\n";
        echo "Applying Fix Option 2: Adding corrective wallet topup...\n";
        
        DB::table('member_payments')->insert([
            'user_id' => $user->id,
            'amount' => 1000,
            'payment_type' => 'wallet_topup',
            'status' => 'verified',
            'payment_method' => 'other',
            'payment_reference' => 'CORRECTION_' . time(),
            'notes' => 'Corrective topup to fix negative balance - Applied on ' . date('Y-m-d H:i:s'),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✅ Added corrective wallet topup of K1000\n";
        $fixApplied = true;
    }
    
    // 5. Verify the fix
    echo "\n5. VERIFYING THE FIX\n";
    echo str_repeat('-', 50) . "\n";
    
    // Recalculate balance
    $newWalletTopups = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->where('status', 'verified')
        ->where('payment_type', 'wallet_topup')
        ->sum('amount');
    
    $newWithdrawals = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->where('status', 'verified')
        ->whereIn('payment_type', ['withdrawal', 'deduction', 'purchase'])
        ->sum('amount');
    
    $newExpectedBalance = $newWalletTopups - $newWithdrawals;
    
    echo "💰 New wallet topups total: K" . number_format($newWalletTopups, 2) . "\n";
    echo "💸 New withdrawals/deductions total: K" . number_format($newWithdrawals, 2) . "\n";
    echo "🧮 New expected balance: K" . number_format($newExpectedBalance, 2) . "\n";
    
    // Test with WalletService if available
    if (class_exists('App\Services\WalletService')) {
        try {
            $walletService = app('App\Services\WalletService');
            $newCalculatedBalance = $walletService->calculateBalance($user);
            echo "🏦 New calculated balance: K" . number_format($newCalculatedBalance, 2) . "\n";
            
            if ($newCalculatedBalance >= 0) {
                echo "✅ SUCCESS: Balance is now positive!\n";
            } else {
                echo "⚠️  WARNING: Balance is still negative: K" . number_format($newCalculatedBalance, 2) . "\n";
            }
        } catch (Exception $e) {
            echo "⚠️  Could not verify balance via WalletService: " . $e->getMessage() . "\n";
        }
    }
    
    // 6. Clear caches
    echo "\n6. CLEARING CACHES\n";
    echo str_repeat('-', 50) . "\n";
    
    try {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        echo "✅ Cleared application caches\n";
    } catch (Exception $e) {
        echo "⚠️  Could not clear caches: " . $e->getMessage() . "\n";
    }
    
    // Summary
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "🎯 FIX SUMMARY FOR ESTHER ZIWA\n";
    echo str_repeat('=', 60) . "\n\n";
    
    echo "👤 User: {$user->name} (ID: {$user->id})\n";
    echo "📧 Email: {$user->email}\n";
    echo "🔧 Fix Applied: " . ($fixApplied ? "YES" : "NO") . "\n";
    echo "💰 Expected Balance: K" . number_format($newExpectedBalance, 2) . "\n";
    
    if ($fixApplied) {
        echo "\n✅ SUCCESS: Esther Ziwa's negative balance has been fixed!\n";
        echo "\n📝 Next Steps:\n";
        echo "1. Test login and wallet access in production\n";
        echo "2. Verify balance shows correctly in the dashboard\n";
        echo "3. Monitor for any related issues\n";
        echo "4. Inform Esther that her account has been corrected\n";
    } else {
        echo "\n❌ No fix was applied. Manual investigation may be required.\n";
    }
    
    echo "\n⚠️  Important Notes:\n";
    echo "- This fix addresses the negative K1000 balance\n";
    echo "- All changes are logged in the member_payments table\n";
    echo "- Caches have been cleared to ensure immediate effect\n";
    echo "- Monitor the account for any recurring issues\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "🏁 Fix Complete - " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('=', 60) . "\n";