<?php

namespace App\Domain\Financial\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Domain\Financial\Exceptions\DuplicateTransactionException;
use App\Domain\Financial\Exceptions\InsufficientFundsException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionIntegrityService
{
    /**
     * Unified Wallet Service for cache management
     */
    private $walletService;
    
    public function __construct()
    {
        $this->walletService = app(\App\Domain\Wallet\Services\UnifiedWalletService::class);
    }
    
    /**
     * Record a wallet debit transaction with integrity checks
     */
    public function recordWalletDebit(
        User $user, 
        float $amount, 
        string $type, 
        string $description,
        ?string $reference = null
    ): Transaction {
        
        // Generate reference if not provided
        $reference = $reference ?? $this->generateReference($type);
        
        // Prevent duplicate transactions
        $existingTransaction = $this->findDuplicateTransaction(
            $user->id, 
            $amount, 
            $type, 
            $reference
        );
        
        if ($existingTransaction) {
            throw new DuplicateTransactionException(
                "Transaction already exists: {$existingTransaction->id}"
            );
        }
        
        return DB::transaction(function () use ($user, $amount, $type, $description, $reference) {
            // Create single transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'transaction_type' => $type,
                'amount' => -abs($amount), // Ensure negative for debits
                'reference_number' => $reference,
                'description' => $description,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Log for audit trail
            $this->logTransaction($transaction, 'wallet_debit');
            
            // Clear wallet cache immediately
            $this->walletService->clearCache($user);
            
            return $transaction;
        });
    }
    
    /**
     * Record a wallet credit transaction with integrity checks
     */
    public function recordWalletCredit(
        User $user, 
        float $amount, 
        string $type, 
        string $description,
        ?string $reference = null
    ): Transaction {
        
        // Generate reference if not provided
        $reference = $reference ?? $this->generateReference($type);
        
        // Prevent duplicate transactions
        $existingTransaction = $this->findDuplicateTransaction(
            $user->id, 
            $amount, 
            $type, 
            $reference
        );
        
        if ($existingTransaction) {
            throw new DuplicateTransactionException(
                "Transaction already exists: {$existingTransaction->id}"
            );
        }
        
        return DB::transaction(function () use ($user, $amount, $type, $description, $reference) {
            // Create single transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'transaction_type' => $type,
                'amount' => abs($amount), // Ensure positive for credits
                'reference_number' => $reference,
                'description' => $description,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Log for audit trail
            $this->logTransaction($transaction, 'wallet_credit');
            
            // Clear wallet cache immediately
            $this->walletService->clearCache($user);
            
            return $transaction;
        });
    }
    
    /**
     * Check if user has sufficient balance for a transaction
     */
    public function checkSufficientBalance(User $user, float $amount): bool
    {
        // Use UnifiedWalletService (primary service)
        $currentBalance = $this->walletService->calculateBalance($user);
        
        return $currentBalance >= $amount;
    }
    
    /**
     * Validate and process wallet payment
     */
    public function processWalletPayment(
        User $user, 
        float $amount, 
        string $type, 
        string $description,
        ?string $reference = null
    ): Transaction {
        
        // Check sufficient balance
        if (!$this->checkSufficientBalance($user, $amount)) {
            throw new InsufficientFundsException(
                "Insufficient wallet balance. Required: K{$amount}"
            );
        }
        
        // Record the debit transaction
        return $this->recordWalletDebit($user, $amount, $type, $description, $reference);
    }
    
    /**
     * Find duplicate transaction within time window
     */
    private function findDuplicateTransaction(
        int $userId, 
        float $amount, 
        string $type, 
        string $reference
    ): ?Transaction {
        
        return Transaction::where('user_id', $userId)
            ->where('transaction_type', $type)
            ->where('reference_number', $reference)
            ->where('created_at', '>=', now()->subMinutes(5)) // 5-minute window
            ->first();
    }
    
    /**
     * Generate unique transaction reference
     */
    private function generateReference(string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3));
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(substr(uniqid(), -4));
        
        return "{$prefix}-{$timestamp}-{$random}";
    }
    
    /**
     * Log transaction for audit trail
     */
    private function logTransaction(Transaction $transaction, string $action): void
    {
        Log::info("Transaction {$action}", [
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'type' => $transaction->transaction_type,
            'amount' => $transaction->amount,
            'reference' => $transaction->reference_number,
            'description' => $transaction->description,
            'action' => $action,
        ]);
    }
    
    /**
     * Get transaction history for user with integrity checks
     */
    public function getUserTransactionHistory(User $user, int $limit = 50): array
    {
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
            
        // Check for any integrity issues
        $duplicates = $this->findDuplicateTransactionsForUser($user);
        
        return [
            'transactions' => $transactions,
            'integrity_issues' => [
                'duplicate_count' => count($duplicates),
                'duplicates' => $duplicates,
            ],
        ];
    }
    
    /**
     * Find duplicate transactions for a user
     */
    private function findDuplicateTransactionsForUser(User $user): array
    {
        $duplicates = DB::select("
            SELECT reference_number, transaction_type, amount, COUNT(*) as count
            FROM transactions 
            WHERE user_id = ? 
            AND reference_number IS NOT NULL
            GROUP BY reference_number, transaction_type, amount
            HAVING COUNT(*) > 1
        ", [$user->id]);
        
        return array_map(function($duplicate) {
            return (array) $duplicate;
        }, $duplicates);
    }
    
    /**
     * Fix duplicate transactions for a user
     */
    public function fixDuplicateTransactions(User $user): array
    {
        $duplicates = $this->findDuplicateTransactionsForUser($user);
        $fixed = [];
        
        foreach ($duplicates as $duplicate) {
            // Keep the first transaction, remove the rest
            $transactions = Transaction::where('user_id', $user->id)
                ->where('reference_number', $duplicate['reference_number'])
                ->where('transaction_type', $duplicate['transaction_type'])
                ->where('amount', $duplicate['amount'])
                ->orderBy('created_at')
                ->get();
                
            // Remove all but the first
            for ($i = 1; $i < $transactions->count(); $i++) {
                $removedTransaction = $transactions[$i];
                $removedTransaction->delete();
                
                $fixed[] = [
                    'removed_id' => $removedTransaction->id,
                    'reference' => $removedTransaction->reference_number,
                    'amount' => $removedTransaction->amount,
                    'type' => $removedTransaction->transaction_type,
                ];
                
                Log::info('Duplicate transaction removed', [
                    'user_id' => $user->id,
                    'removed_transaction_id' => $removedTransaction->id,
                    'reference' => $removedTransaction->reference_number,
                ]);
            }
        }
        
        return $fixed;
    }
}