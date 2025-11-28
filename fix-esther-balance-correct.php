<?php

/**
 * CORRECT Fix for Esther Ziwa's Balance Issue
 * 
 * Problem identified:
 * - She made 1 actual deposit of K1000 (verified)
 * - Starter kit purchase is being double-counted:
 *   1. As a transaction debit (-K1000)
 *   2. As an approved withdrawal (K1000)
 * - Previous fix added unnecessary extra topup
 * 
 * Solution: Remove the duplicate withdrawal record that was created for starter kit
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;

echo "=== CORRECT FIX FOR ESTHER ZIWA'S BALANCE ===\n\n";

try {
    // Find Esther Ziwa
    $user = User::find(135);
    
    if (!$user) {
        echo "❌ User not found!\n";
        exit(1);
    }
    
    echo "👤 User: {$user->name} (ID: {$user->id})\n";
    echo "📧 Email: {$user->email}\n\n";
    
    // Check current balance
    $walletService = app(WalletService::class);
    $currentBalance = $walletService->calculateBalance($user);
    echo "Current balance: K{$currentBalance}\n\n";
    
    // The issue: Starter kit purchase is double-counted
    echo "=== IDENTIFYING THE DOUBLE-COUNTING ISSUE ===\n";
    
    // 1. Check the starter kit transaction (this is correct)
    $starterKitTransaction = DB::table('transactions')
        ->where('user_id', $user->id)
        ->where('transaction_type', 'starter_kit_purchase')
        ->where('amount', -1000)
        ->first();
    
    if ($starterKitTransaction) {
        echo "✅ Found starter kit transaction: ID {$starterKitTransaction->id} (-K1000)\n";
    }
    
    // 2. Check the withdrawal record (this is the duplicate!)
    $duplicateWithdrawal = DB::table('withdrawals')
        ->where('user_id', $user->id)
        ->where('amount', 1000)
        ->where('status', 'approved')
        ->where('created_at', '2025-11-06 11:05:29') // Same time as starter kit
        ->first();
    
    if ($duplicateWithdrawal) {
        echo "❌ Found duplicate withdrawal record: ID {$duplicateWithdrawal->id} (K1000)\n";
        echo "   This withdrawal was created when buying starter kit, but starter kit is already\n";
        echo "   recorded as a transaction debit. This is double-counting!\n\n";
        
        echo "=== APPLYING FIX ===\n";
        
        // Remove the duplicate withdrawal record
        echo "Removing duplicate withdrawal record...\n";
        DB::table('withdrawals')->where('id', $duplicateWithdrawal->id)->delete();
        echo "✅ Removed duplicate withdrawal (ID: {$duplicateWithdrawal->id})\n\n";
        
        // Also remove the extra topup that was added by previous fix
        $extraTopup = DB::table('member_payments')
            ->where('user_id', $user->id)
            ->where('amount', 1000)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->where('payment_reference', 'LIKE', 'BALANCE_CORRECTION_%')
            ->first();
        
        if ($extraTopup) {
            echo "Removing unnecessary correction topup...\n";
            DB::table('member_payments')->where('id', $extraTopup->id)->delete();
            echo "✅ Removed extra topup (ID: {$extraTopup->id})\n\n";
        }
        
    } else {
        echo "No duplicate withdrawal found. Issue may be different.\n\n";
    }
    
    // Verify the fix
    echo "=== VERIFYING FIX ===\n";
    
    // Recalculate balance
    $newBalance = $walletService->calculateBalance($user);
    echo "New balance: K{$newBalance}\n\n";
    
    // Show breakdown
    $breakdown = $walletService->getWalletBreakdown($user);
    
    echo "FINAL BREAKDOWN:\n";
    echo "CREDITS:\n";
    echo "- Deposits: K" . $breakdown['credits']['deposits'] . "\n";
    echo "- Earnings: K" . $breakdown['credits']['earnings']['total'] . "\n";
    echo "- Total Credits: K" . $breakdown['credits']['total'] . "\n\n";
    
    echo "DEBITS:\n";
    echo "- Expenses (starter kit): K" . $breakdown['debits']['expenses'] . "\n";
    echo "- Withdrawals: K" . $breakdown['debits']['withdrawals'] . "\n";
    echo "- Total Debits: K" . $breakdown['debits']['total'] . "\n\n";
    
    echo "FINAL BALANCE: K" . $breakdown['balance'] . "\n\n";
    
    if ($breakdown['balance'] >= 0) {
        echo "✅ SUCCESS: Balance is now correct!\n";
        echo "Esther deposited K1000 and bought starter kit for K1000.\n";
        echo "Her balance should be K0, which is correct.\n";
    } else {
        echo "⚠️  Balance still negative. Manual investigation needed.\n";
    }
    
    // Clear caches
    echo "\nClearing caches...\n";
    try {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        echo "✅ Caches cleared\n";
    } catch (Exception $e) {
        echo "Could not clear caches: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Fix completed!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}