<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DiagnoseWalletBalance extends Command
{
    protected $signature = 'wallet:diagnose {user_id}';
    protected $description = 'Diagnose wallet balance issues for a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User not found!");
            return 1;
        }
        
        $this->info("=== WALLET DIAGNOSIS FOR: {$user->name} (ID: {$userId}) ===\n");
        
        // Check deposits in member_payments
        $this->info("--- DEPOSITS (member_payments) ---");
        $memberPayments = DB::table('member_payments')
            ->where('user_id', $userId)
            ->where('payment_type', 'wallet_topup')
            ->get();
        
        foreach ($memberPayments as $mp) {
            $this->line("ID: {$mp->id} | Amount: K{$mp->amount} | Status: {$mp->status} | Date: {$mp->created_at}");
        }
        $verifiedTotal = DB::table('member_payments')
            ->where('user_id', $userId)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->sum('amount');
        $this->info("Total Verified: K{$verifiedTotal}\n");
        
        // Check deposits in transactions
        $this->info("--- DEPOSITS (transactions) ---");
        $transactionTopups = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('transaction_type', 'wallet_topup')
            ->get();
        
        foreach ($transactionTopups as $tx) {
            $this->line("ID: {$tx->id} | Amount: K{$tx->amount} | Status: {$tx->status} | Date: {$tx->created_at}");
        }
        $txTopupTotal = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('transaction_type', 'wallet_topup')
            ->where('status', 'completed')
            ->sum('amount');
        $this->info("Total Completed: K{$txTopupTotal}\n");
        
        // Check starter kit purchases
        $this->info("--- STARTER KIT PURCHASES (transactions) ---");
        $starterKitTx = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('transaction_type', 'LIKE', '%starter_kit%')
            ->get();
        
        foreach ($starterKitTx as $tx) {
            $this->line("ID: {$tx->id} | Type: {$tx->transaction_type} | Amount: K{$tx->amount} | Status: {$tx->status} | Date: {$tx->created_at}");
        }
        $starterKitTotal = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('transaction_type', 'LIKE', '%starter_kit%')
            ->where('status', 'completed')
            ->sum('amount');
        $this->info("Total Starter Kit Debits: K" . abs($starterKitTotal) . "\n");
        
        // Check all transactions
        $this->info("--- ALL TRANSACTIONS ---");
        $allTx = DB::table('transactions')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        foreach ($allTx as $tx) {
            $this->line("ID: {$tx->id} | Type: {$tx->transaction_type} | Amount: K{$tx->amount} | Status: {$tx->status} | Ref: {$tx->reference_number}");
        }
        
        // Calculate balance
        $this->info("\n--- BALANCE CALCULATION ---");
        $totalDeposits = $verifiedTotal + $txTopupTotal;
        $totalDebits = abs($starterKitTotal);
        $calculatedBalance = $totalDeposits - $totalDebits;
        
        $this->info("Total Deposits: K{$totalDeposits}");
        $this->info("Total Debits: K{$totalDebits}");
        $this->info("Calculated Balance: K{$calculatedBalance}");
        
        // Compare with services
        $this->info("\n--- SERVICE COMPARISON ---");
        $ws = app(\App\Services\WalletService::class);
        $wsBalance = $ws->calculateBalance($user);
        $this->info("WalletService Balance: K{$wsBalance}");
        
        $uws = app(\App\Domain\Wallet\Services\UnifiedWalletService::class);
        $uwsBalance = $uws->calculateBalance($user);
        $this->info("UnifiedWalletService Balance: K{$uwsBalance}");
        
        if ($wsBalance != $uwsBalance) {
            $this->error("\n⚠️  MISMATCH DETECTED! Services show different balances!");
        }
        
        if ($uwsBalance < 0) {
            $this->error("\n⚠️  NEGATIVE BALANCE DETECTED!");
        }
        
        return 0;
    }
}
