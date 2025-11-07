<?php

namespace App\Services;

use App\Models\PaymentLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Initiate a deposit payment
     */
    public function initiateDeposit(
        User $user,
        float $amount,
        string $paymentMethod,
        ?string $provider = null,
        array $metadata = []
    ): PaymentLog {
        return DB::transaction(function () use ($user, $amount, $paymentMethod, $provider, $metadata) {
            $paymentLog = PaymentLog::create([
                'user_id' => $user->id,
                'payment_type' => 'deposit',
                'amount' => $amount,
                'currency' => 'ZMW',
                'payment_method' => $paymentMethod,
                'provider' => $provider,
                'internal_reference' => PaymentLog::generateReference('DEP'),
                'status' => 'initiated',
                'initiated_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'provider_data' => $metadata,
            ]);

            Log::info('Payment initiated', [
                'payment_log_id' => $paymentLog->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'reference' => $paymentLog->internal_reference,
            ]);

            return $paymentLog;
        });
    }

    /**
     * Complete a payment and create transaction
     */
    public function completePayment(
        PaymentLog $paymentLog,
        ?string $providerReference = null
    ): void {
        DB::transaction(function () use ($paymentLog, $providerReference) {
            // Update payment log
            $paymentLog->update([
                'status' => 'completed',
                'completed_at' => now(),
                'provider_reference' => $providerReference ?? $paymentLog->provider_reference,
            ]);

            // Check if transaction already exists
            if ($paymentLog->transaction_id) {
                // Update existing transaction
                DB::table('transactions')
                    ->where('id', $paymentLog->transaction_id)
                    ->update([
                        'status' => 'completed',
                        'processed_at' => now(),
                        'updated_at' => now(),
                    ]);
                
                Log::info('Payment completed - transaction updated', [
                    'payment_log_id' => $paymentLog->id,
                    'transaction_id' => $paymentLog->transaction_id,
                ]);
                
                return;
            }

            // Create transaction in ledger
            $transaction = DB::table('transactions')->insertGetId([
                'user_id' => $paymentLog->user_id,
                'amount' => $paymentLog->payment_type === 'withdrawal' ? -$paymentLog->amount : $paymentLog->amount,
                'transaction_type' => $paymentLog->payment_type,
                'status' => 'completed',
                'payment_method' => $paymentLog->payment_method,
                'reference_number' => $paymentLog->internal_reference,
                'description' => $this->getTransactionDescription($paymentLog),
                'created_at' => now(),
                'updated_at' => now(),
                'processed_at' => now(),
            ]);

            // Link transaction to payment log
            $paymentLog->linkTransaction($transaction);

            Log::info('Payment completed', [
                'payment_log_id' => $paymentLog->id,
                'transaction_id' => $transaction,
                'user_id' => $paymentLog->user_id,
                'amount' => $paymentLog->amount,
            ]);
        });
    }

    /**
     * Handle payment failure
     */
    public function failPayment(PaymentLog $paymentLog, string $reason): void
    {
        $paymentLog->markAsFailed($reason);

        Log::warning('Payment failed', [
            'payment_log_id' => $paymentLog->id,
            'user_id' => $paymentLog->user_id,
            'reason' => $reason,
        ]);
    }

    /**
     * Reconcile payment with provider
     */
    public function reconcilePayment(PaymentLog $paymentLog, int $reconciledBy): void
    {
        $paymentLog->markAsReconciled($reconciledBy);

        Log::info('Payment reconciled', [
            'payment_log_id' => $paymentLog->id,
            'reconciled_by' => $reconciledBy,
        ]);
    }

    /**
     * Find payment by provider reference
     */
    public function findByProviderReference(string $reference): ?PaymentLog
    {
        return PaymentLog::where('provider_reference', $reference)->first();
    }

    /**
     * Find payment by internal reference
     */
    public function findByInternalReference(string $reference): ?PaymentLog
    {
        return PaymentLog::where('internal_reference', $reference)->first();
    }

    /**
     * Get unreconciled payments
     */
    public function getUnreconciledPayments(int $limit = 100)
    {
        return PaymentLog::unreconciled()
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate transaction description
     */
    private function getTransactionDescription(PaymentLog $paymentLog): string
    {
        $descriptions = [
            'deposit' => 'Wallet Deposit',
            'withdrawal' => 'Withdrawal',
            'refund' => 'Refund',
        ];

        $base = $descriptions[$paymentLog->payment_type] ?? 'Payment';
        
        if ($paymentLog->provider) {
            $base .= " via {$paymentLog->provider}";
        }

        return $base;
    }

    /**
     * Initiate withdrawal
     */
    public function initiateWithdrawal(
        User $user,
        float $amount,
        string $paymentMethod,
        array $metadata = []
    ): PaymentLog {
        return DB::transaction(function () use ($user, $amount, $paymentMethod, $metadata) {
            // Check balance
            $balance = $this->getUserBalance($user);
            if ($balance < $amount) {
                throw new \Exception('Insufficient balance');
            }

            // Create payment log
            $paymentLog = PaymentLog::create([
                'user_id' => $user->id,
                'payment_type' => 'withdrawal',
                'amount' => $amount,
                'currency' => 'ZMW',
                'payment_method' => $paymentMethod,
                'internal_reference' => PaymentLog::generateReference('WTH'),
                'status' => 'pending',
                'initiated_at' => now(),
                'provider_data' => $metadata,
            ]);

            // Create negative transaction (debit)
            $transaction = DB::table('transactions')->insertGetId([
                'user_id' => $user->id,
                'amount' => -$amount,
                'transaction_type' => 'withdrawal',
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'reference_number' => $paymentLog->internal_reference,
                'description' => 'Withdrawal Request',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $paymentLog->linkTransaction($transaction);

            return $paymentLog;
        });
    }

    /**
     * Get user balance from transactions table
     */
    private function getUserBalance(User $user): float
    {
        return (float) DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');
    }
}
