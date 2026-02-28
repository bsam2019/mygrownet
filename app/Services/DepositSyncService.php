<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Deposit Sync Service
 * 
 * Syncs deposits from member_payments to transactions table
 * Prevents double-counting by checking for existing transactions
 */
class DepositSyncService
{
    private $walletService;
    
    public function __construct()
    {
        $this->walletService = app(\App\Domain\Wallet\Services\UnifiedWalletService::class);
    }
    
    /**
     * Sync deposit from member_payments to transactions
     * IMPORTANT: Checks for existing transaction to prevent double-counting
     * 
     * @param int $memberPaymentId
     * @return bool True if synced, false if already exists
     */
    public function syncDeposit(int $memberPaymentId): bool
    {
        $deposit = DB::table('member_payments')->find($memberPaymentId);
        
        if (!$deposit) {
            Log::warning('Member payment not found', ['id' => $memberPaymentId]);
            return false;
        }
        
        if ($deposit->payment_type !== 'wallet_topup') {
            Log::info('Not a wallet topup, skipping sync', [
                'id' => $memberPaymentId,
                'type' => $deposit->payment_type
            ]);
            return false;
        }
        
        if ($deposit->status !== 'verified') {
            Log::info('Deposit not verified, skipping sync', [
                'id' => $memberPaymentId,
                'status' => $deposit->status
            ]);
            return false;
        }
        
        // Check if transaction already exists
        $exists = $this->transactionExists($deposit);
        
        if ($exists) {
            Log::info('Deposit already synced', [
                'member_payment_id' => $memberPaymentId,
                'user_id' => $deposit->user_id
            ]);
            return false;
        }
        
        // Create transaction
        DB::table('transactions')->insert([
            'user_id' => $deposit->user_id,
            'transaction_type' => 'wallet_topup',
            'amount' => $deposit->amount,
            'reference_number' => $deposit->payment_reference ?? ('MP-' . $deposit->id),
            'description' => 'Wallet Top-up - ' . ($deposit->payment_method ?? 'Mobile Money'),
            'status' => 'completed',
            'payment_method' => $deposit->payment_method,
            'created_at' => $deposit->created_at,
            'updated_at' => $deposit->updated_at,
        ]);
        
        // Clear wallet cache
        $user = User::find($deposit->user_id);
        if ($user) {
            $this->walletService->clearCache($user);
        }
        
        Log::info('Deposit synced to transactions', [
            'member_payment_id' => $memberPaymentId,
            'user_id' => $deposit->user_id,
            'amount' => $deposit->amount,
            'reference' => $deposit->payment_reference ?? ('MP-' . $deposit->id),
        ]);
        
        return true;
    }
    
    /**
     * Check if transaction already exists for this deposit
     * 
     * @param object $deposit
     * @return bool
     */
    private function transactionExists($deposit): bool
    {
        return DB::table('transactions')
            ->where('user_id', $deposit->user_id)
            ->where(function($q) use ($deposit) {
                // Check by reference number
                if ($deposit->payment_reference) {
                    $q->where('reference_number', $deposit->payment_reference);
                }
                // Or check by amount and date (fallback)
                $q->orWhere(function($q2) use ($deposit) {
                    $q2->where('user_id', $deposit->user_id)
                       ->where('transaction_type', 'wallet_topup')
                       ->where('amount', $deposit->amount)
                       ->whereDate('created_at', $deposit->created_at);
                });
            })
            ->exists();
    }
    
    /**
     * Sync all unsynced deposits for a user
     * 
     * @param User $user
     * @return array Statistics
     */
    public function syncAllDepositsForUser(User $user): array
    {
        $deposits = DB::table('member_payments')
            ->where('user_id', $user->id)
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->get();
        
        $synced = 0;
        $skipped = 0;
        
        foreach ($deposits as $deposit) {
            if ($this->syncDeposit($deposit->id)) {
                $synced++;
            } else {
                $skipped++;
            }
        }
        
        return [
            'total_deposits' => $deposits->count(),
            'synced' => $synced,
            'skipped' => $skipped,
        ];
    }
    
    /**
     * Sync all unsynced deposits in the system
     * 
     * @return array Statistics
     */
    public function syncAllDeposits(): array
    {
        $deposits = DB::table('member_payments')
            ->where('payment_type', 'wallet_topup')
            ->where('status', 'verified')
            ->get();
        
        $synced = 0;
        $skipped = 0;
        
        foreach ($deposits as $deposit) {
            if ($this->syncDeposit($deposit->id)) {
                $synced++;
            } else {
                $skipped++;
            }
        }
        
        return [
            'total_deposits' => $deposits->count(),
            'synced' => $synced,
            'skipped' => $skipped,
        ];
    }
}
