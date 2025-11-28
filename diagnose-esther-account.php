<?php

/**
 * Diagnose Esther Ziwa's Account Issue
 * Simple diagnostic script to check database structure and account status
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSING ESTHER ZIWA'S ACCOUNT ===\n\n";

try {
    // Find Esther Ziwa
    $user = User::find(135);
    
    if (!$user) {
        echo "❌ ERROR: User ID 135 not found!\n";
        exit(1);
    }
    
    echo "👤 Found user: {$user->name} (ID: {$user->id})\n";
    echo "📧 Email: {$user->email}\n\n";
    
    // Check what tables exist
    echo "1. CHECKING DATABASE STRUCTURE\n";
    echo str_repeat('-', 40) . "\n";
    
    $tables = DB::select("SHOW TABLES");
    $tableNames = [];
    foreach ($tables as $table) {
        $tableArray = (array) $table;
        $tableNames[] = array_values($tableArray)[0];
    }
    
    echo "Available tables:\n";
    foreach ($tableNames as $tableName) {
        if (strpos($tableName, 'payment') !== false || strpos($tableName, 'wallet') !== false || strpos($tableName, 'transaction') !== false) {
            echo "  - {$tableName}\n";
        }
    }
    echo "\n";
    
    // Check member_payments table structure
    if (in_array('member_payments', $tableNames)) {
        echo "2. MEMBER_PAYMENTS TABLE STRUCTURE\n";
        echo str_repeat('-', 40) . "\n";
        
        $columns = DB::select("DESCRIBE member_payments");
        foreach ($columns as $column) {
            echo "  {$column->Field} - {$column->Type}\n";
        }
        echo "\n";
        
        // Check Esther's payments
        echo "3. ESTHER'S PAYMENT RECORDS\n";
        echo str_repeat('-', 40) . "\n";
        
        $payments = DB::table('member_payments')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        echo "Total payment records: " . $payments->count() . "\n\n";
        
        if ($payments->count() > 0) {
            echo "Recent payments:\n";
            foreach ($payments->take(10) as $payment) {
                echo "  ID: {$payment->id}, Amount: K{$payment->amount}, Status: {$payment->status}, Date: {$payment->created_at}\n";
            }
        }
        echo "\n";
    }
    
    // Check if wallet_transactions table exists
    if (in_array('wallet_transactions', $tableNames)) {
        echo "4. WALLET_TRANSACTIONS TABLE\n";
        echo str_repeat('-', 40) . "\n";
        
        $walletTransactions = DB::table('wallet_transactions')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        echo "Total wallet transactions: " . $walletTransactions->count() . "\n\n";
        
        if ($walletTransactions->count() > 0) {
            echo "Recent wallet transactions:\n";
            foreach ($walletTransactions->take(10) as $transaction) {
                echo "  ID: {$transaction->id}, Amount: K{$transaction->amount}, Type: {$transaction->type}, Date: {$transaction->created_at}\n";
            }
        }
        echo "\n";
    }
    
    // Check user's current balance if WalletService exists
    echo "5. CURRENT BALANCE CHECK\n";
    echo str_repeat('-', 40) . "\n";
    
    if (class_exists('App\Services\WalletService')) {
        try {
            $walletService = app('App\Services\WalletService');
            $balance = $walletService->calculateBalance($user);
            echo "Current wallet balance: K" . number_format($balance, 2) . "\n";
            
            if ($balance == -1000) {
                echo "⚠️  CONFIRMED: Negative K1000 balance!\n";
            }
        } catch (Exception $e) {
            echo "Error calculating balance: " . $e->getMessage() . "\n";
        }
    } else {
        echo "WalletService not found\n";
    }
    
    // Check if user has wallet_balance field
    if (isset($user->wallet_balance)) {
        echo "User wallet_balance field: K" . number_format($user->wallet_balance, 2) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";