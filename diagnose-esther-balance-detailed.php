<?php

/**
 * Detailed Diagnosis for Esther Ziwa's Balance Issue
 * 
 * This script will examine all transactions and identify why her balance is negative
 * when she only made one K1000 deposit.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\WalletService;
use App\Services\EarningsService;
use Illuminate\Support\Facades\DB;

echo "=== DETAILED DIAGNOSIS FOR ESTHER ZIWA'S BALANCE ===\n\n";

try {
    // Find Esther Ziwa
    $user = User::find(135);
    
    if (!$user) {
        echo "❌ User not found!\n";
        exit(1);
    }
    
    echo "👤 User: {$user->name} (ID: {$user->id})\n";
    echo "📧 Email: {$user->email}\n\n";
    
    // 1. Check ALL member_payments
    echo "=== MEMBER PAYMENTS ANALYSIS ===\n";
    $memberPayments = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    $verifiedTopups = 0;
    $rejectedTopups = 0;
    
    foreach ($memberPayments as $payment) {
        echo "ID: {$payment->id} | Amount: K{$payment->amount} | Type: {$payment->payment_type} | Status: {$payment->status} | Date: {$payment->created_at}\n";
        
        if ($payment->payment_type === 'wallet_topup') {
            if ($payment->status === 'verified') {
                $verifiedTopups += $payment->amount;
            } elseif ($payment->status === 'rejected') {
                $rejectedTopups += $payment->amount;
            }
        }
    }
    
    echo "\nSummary:\n";
    echo "- Verified topups: K{$verifiedTopups}\n";
    echo "- Rejected topups: K{$rejectedTopups}\n\n";
    
    // 2. Check transactions table
    echo "=== TRANSACTIONS TABLE ANALYSIS ===\n";
    $transactions = DB::table('transactions')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    if ($transactions->isEmpty()) {
        echo "No transactions found in transactions table.\n\n";
    } else {
        foreach ($transactions as $transaction) {
            echo "ID: {$transaction->id} | Amount: {$transaction->amount} | Type: {$transaction->transaction_type} | Status: {$transaction->status} | Date: {$transaction->created_at}\n";
        }
        echo "\n";
    }
    
    // 3. Check withdrawals table
    echo "=== WITHDRAWALS TABLE ANALYSIS ===\n";
    $withdrawals = DB::table('withdrawals')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    if ($withdrawals->isEmpty()) {
        echo "No withdrawals found.\n\n";
    } else {
        foreach ($withdrawals as $withdrawal) {
            echo "ID: {$withdrawal->id} | Amount: K{$withdrawal->amount} | Status: {$withdrawal->status} | Date: {$withdrawal->created_at}\n";
        }
        echo "\n";
    }
    
    // 4. Check workshop registrations
    echo "=== WORKSHOP REGISTRATIONS ANALYSIS ===\n";
    $workshops = DB::table('workshop_registrations')
        ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
        ->where('workshop_registrations.user_id', $user->id)
        ->select('workshop_registrations.*', 'workshops.title', 'workshops.price')
        ->get();
    
    if ($workshops->isEmpty()) {
        echo "No workshop registrations found.\n\n";
    } else {
        foreach ($workshops as $workshop) {
            echo "Workshop: {$workshop->title} | Price: K{$workshop->price} | Status: {$workshop->status}\n";
        }
        echo "\n";
    }
    
    // 5. Use WalletService to get detailed breakdown
    echo "=== WALLET SERVICE BREAKDOWN ===\n";
    try {
        $walletService = app(WalletService::class);
        $breakdown = $walletService->getWalletBreakdown($user);
        
        echo "CREDITS:\n";
        echo "- Earnings: K" . $breakdown['credits']['earnings']['total'] . "\n";
        echo "- Deposits: K" . $breakdown['credits']['deposits'] . "\n";
        echo "- Loans: K" . $breakdown['credits']['loans'] . "\n";
        echo "- Total Credits: K" . $breakdown['credits']['total'] . "\n\n";
        
        echo "DEBITS:\n";
        echo "- Withdrawals: K" . $breakdown['debits']['withdrawals'] . "\n";
        echo "- Expenses: K" . $breakdown['debits']['expenses'] . "\n";
        echo "- Loan Repayments: K" . $breakdown['debits']['loan_repayments'] . "\n";
        echo "- Total Debits: K" . $breakdown['debits']['total'] . "\n\n";
        
        echo "FINAL BALANCE: K" . $breakdown['balance'] . "\n\n";
        
        // If balance is negative, let's identify the issue
        if ($breakdown['balance'] < 0) {
            echo "🔍 ISSUE IDENTIFIED:\n";
            
            if ($breakdown['debits']['expenses'] > 0) {
                echo "- Expenses detected: K" . $breakdown['debits']['expenses'] . "\n";
                echo "- This likely includes starter kit purchase that wasn't properly recorded as a topup\n";
            }
            
            if ($breakdown['debits']['withdrawals'] > 0) {
                echo "- Withdrawals detected: K" . $breakdown['debits']['withdrawals'] . "\n";
            }
            
            echo "\nRECOMMENDED FIX:\n";
            if ($breakdown['debits']['expenses'] > 0 && $breakdown['credits']['deposits'] >= $breakdown['debits']['expenses']) {
                echo "- The user has sufficient deposits to cover expenses\n";
                echo "- The issue is likely in how starter kit purchases are being recorded\n";
                echo "- Need to check if starter kit purchase created a debit without corresponding credit\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error with WalletService: " . $e->getMessage() . "\n\n";
    }
    
    // 6. Check for starter kit related transactions
    echo "=== STARTER KIT ANALYSIS ===\n";
    
    // Check if user has starter kit
    $hasStarterKit = DB::table('users')->where('id', $user->id)->value('has_starter_kit');
    echo "Has starter kit: " . ($hasStarterKit ? 'Yes' : 'No') . "\n";
    
    // Look for starter kit transactions
    $starterKitTransactions = DB::table('transactions')
        ->where('user_id', $user->id)
        ->where('transaction_type', 'LIKE', '%starter_kit%')
        ->get();
    
    if ($starterKitTransactions->isEmpty()) {
        echo "No starter kit transactions found in transactions table.\n";
    } else {
        foreach ($starterKitTransactions as $transaction) {
            echo "Starter Kit Transaction: ID {$transaction->id} | Amount: {$transaction->amount} | Type: {$transaction->transaction_type} | Status: {$transaction->status}\n";
        }
    }
    
    echo "\n=== CONCLUSION ===\n";
    echo "Based on the analysis above, the issue should be clear.\n";
    echo "If balance is negative despite having deposits, check for:\n";
    echo "1. Starter kit purchases creating debits without credits\n";
    echo "2. Rejected transactions being counted as debits\n";
    echo "3. Duplicate expense calculations\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}