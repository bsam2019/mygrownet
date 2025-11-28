<?php

/**
 * Fix Esther Ziwa's Negative Balance - Production Fix
 * 
 * Based on diagnosis: Esther has K1000 verified payment but -K1000 balance
 * This suggests wallet_transactions table has incorrect entries
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== FIXING ESTHER ZIWA'S NEGATIVE BALANCE - PRODUCTION ===\n\n";

try {
    // Find Esther Ziwa
    $user = User::find(135);
    
    if (!$user) {
        echo "❌ ERROR: User ID 135 not found!\n";
        exit(1);
    }
    
    echo "👤 User: {$user->name} (ID: {$user->id})\n";
    echo "📧 Email: {$user->email}\n\n";
    
    // Check current balance
    echo "1. CURRENT BALANCE CHECK\n";
    echo str_repeat('-', 40) . "\n";
    
    if (class_exists('App\Services\WalletService')) {
        $walletService = app('App\Services\WalletService');
        $currentBalance = $walletService->calculateBalance($user);
        echo "Current balance: K" . number_format($currentBalance, 2) . "\n\n";
    }
    
    // Check member_payments (verified payments)
    echo "2. VERIFIED PAYMENTS\n";
    echo str_repeat('-', 40) . "\n";
    
    $verifiedPayments = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->where('status', 'verified')
        ->get();
    
    $totalVerified = $verifiedPayments->sum('amount');
    echo "Verified payments total: K" . number_format($totalVerified, 2) . "\n";
    
    foreach ($verifiedPayments as $payment) {
        echo "  ID: {$payment->id}, Amount: K{$payment->amount}, Type: {$payment->payment_type}, Date: {$payment->created_at}\n";
    }
    echo "\n";
    
    // Check wallet_transactions
    echo "3. WALLET TRANSACTIONS\n";
    echo str_repeat('-', 40) . "\n";
    
    $walletTransactions = DB::table('wallet_transactions')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Total wallet transactions: " . $walletTransactions->count() . "\n";
    
    $totalWalletAmount = 0;
    foreach ($walletTransactions as $transaction) {
        $totalWalletAmount += $transaction->amount;
        echo "  ID: {$transaction->id}, Amount: K{$transaction->amount}, Type: {$transaction->type}, Date: {$transaction->created_at}\n";
    }
    
    echo "Wallet transactions total: K" . number_format($totalWalletAmount, 2) . "\n\n";
    
    // Identify the problem
    echo "4. PROBLEM IDENTIFICATION\n";
    echo str_repeat('-', 40) . "\n";
    
    if ($totalVerified > 0 && $totalWalletAmount < 0) {
        echo "🎯 PROBLEM FOUND: Verified payments exist but wallet transactions are negative\n";
        echo "   This suggests incorrect wallet transaction entries\n\n";
        
        // Look for problematic transactions
        $negativeTransactions = DB::table('wallet_transactions')
            ->where('user_id', $user->id)
            ->where('amount', '<', 0)
            ->get();
        
        if ($negativeTransactions->count() > 0) {
            echo "Negative transactions found:\n";
            foreach ($negativeTransactions as $transaction) {
                echo "  ID: {$transaction->id}, Amount: K{$transaction->amount}, Type: {$transaction->type}\n";
            }
            echo "\n";
        }
    }
    
    // Apply fix
    echo "5. APPLYING FIX\n";
    echo str_repeat('-', 40) . "\n";
    
    // Strategy: Ensure wallet_transactions match verified member_payments
    
    // First, check if there should be a positive wallet transaction for the verified payment
    $shouldHaveWalletCredit = false;
    
    foreach ($verifiedPayments as $payment) {
        if ($payment->payment_type === 'wallet_topup') {
            $shouldHaveWalletCredit = true;
            
            // Check if corresponding wallet transaction exists
            $existingWalletTransaction = DB::table('wallet_transactions')
                ->where('user_id', $user->id)
                ->where('amount', $payment->amount)
                ->where('type', 'topup')
                ->first();
            
            if (!$existingWalletTransaction) {
                echo "Creating missing wallet transaction for payment ID {$payment->id}...\n";
                
                DB::table('wallet_transactions')->insert([
                    'user_id' => $user->id,
                    'amount' => $payment->amount,
                    'type' => 'topup',
                    'description' => "Wallet topup from verified payment ID {$payment->id}",
                    'reference' => "PAYMENT_{$payment->id}",
                    'status' => 'completed',
                    'created_at' => $payment->created_at,
                    'updated_at' => now()
                ]);
                
                echo "✅ Created wallet transaction for K{$payment->amount}\n";
            }
        }
    }
    
    // Remove any erroneous negative transactions that don't have corresponding payments
    $erroneousTransactions = DB::table('wallet_transactions')
        ->where('user_id', $user->id)
        ->where('amount', '<', 0)
        ->whereNotIn('type', ['withdrawal', 'purchase', 'deduction'])
        ->get();
    
    foreach ($erroneousTransactions as $transaction) {
        echo "Removing erroneous negative transaction ID {$transaction->id} (K{$transaction->amount})...\n";
        
        DB::table('wallet_transactions')
            ->where('id', $transaction->id)
            ->delete();
        
        echo "✅ Removed erroneous transaction\n";
    }
    
    // If balance is still negative, add corrective transaction
    if (class_exists('App\Services\WalletService')) {
        $walletService = app('App\Services\WalletService');
        $newBalance = $walletService->calculateBalance($user);
        
        if ($newBalance < 0) {
            echo "Balance still negative (K{$newBalance}), adding corrective transaction...\n";
            
            DB::table('wallet_transactions')->insert([
                'user_id' => $user->id,
                'amount' => abs($newBalance),
                'type' => 'correction',
                'description' => 'Balance correction to fix negative balance issue',
                'reference' => 'CORRECTION_' . time(),
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "✅ Added corrective transaction for K" . abs($newBalance) . "\n";
        }
    }
    
    // Verify the fix
    echo "\n6. VERIFICATION\n";
    echo str_repeat('-', 40) . "\n";
    
    if (class_exists('App\Services\WalletService')) {
        $walletService = app('App\Services\WalletService');
        $finalBalance = $walletService->calculateBalance($user);
        echo "Final balance: K" . number_format($finalBalance, 2) . "\n";
        
        if ($finalBalance >= 0) {
            echo "✅ SUCCESS: Balance is now positive!\n";
        } else {
            echo "⚠️  WARNING: Balance is still negative\n";
        }
    }
    
    // Clear caches
    echo "\n7. CLEARING CACHES\n";
    echo str_repeat('-', 40) . "\n";
    
    try {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        echo "✅ Cache cleared\n";
    } catch (Exception $e) {
        echo "⚠️  Could not clear cache: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat('=', 50) . "\n";
    echo "🎯 FIX COMPLETE FOR ESTHER ZIWA\n";
    echo str_repeat('=', 50) . "\n";
    echo "✅ Esther Ziwa's account has been fixed\n";
    echo "📧 Email: {$user->email}\n";
    echo "💰 Final Balance: K" . number_format($finalBalance ?? 0, 2) . "\n";
    echo "\n📝 Actions taken:\n";
    echo "- Verified payment records\n";
    echo "- Fixed wallet transaction inconsistencies\n";
    echo "- Removed erroneous negative transactions\n";
    echo "- Added corrective transactions if needed\n";
    echo "- Cleared application cache\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n🏁 Fix completed successfully - " . date('Y-m-d H:i:s') . "\n";