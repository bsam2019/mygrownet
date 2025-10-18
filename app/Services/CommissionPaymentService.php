<?php

namespace App\Services;

use App\Models\ReferralCommission;
use App\Models\User;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Exception;

class CommissionPaymentService
{
    protected MobileMoneyService $mobileMoneyService;
    
    public function __construct(MobileMoneyService $mobileMoneyService)
    {
        $this->mobileMoneyService = $mobileMoneyService;
    }

    /**
     * Process all pending commission payments in batches
     */
    public function processAllPendingPayments(): array
    {
        $results = [
            'total_processed' => 0,
            'successful_payments' => 0,
            'failed_payments' => 0,
            'total_amount' => 0,
            'errors' => []
        ];

        try {
            // Get all pending commissions grouped by user
            $pendingCommissions = ReferralCommission::pending()
                ->with('referrer')
                ->where('created_at', '<=', now()->subHours(24)) // Only process after 24 hours
                ->get()
                ->groupBy('referrer_id');

            foreach ($pendingCommissions as $userId => $userCommissions) {
                $batchResult = $this->processBatchPayment($userCommissions);
                
                $results['total_processed'] += $batchResult['total_processed'];
                $results['successful_payments'] += $batchResult['successful_payments'];
                $results['failed_payments'] += $batchResult['failed_payments'];
                $results['total_amount'] += $batchResult['total_amount'];
                
                if (!empty($batchResult['errors'])) {
                    $results['errors'] = array_merge($results['errors'], $batchResult['errors']);
                }
            }

            Log::info('Commission payment batch processing completed', $results);

        } catch (Exception $e) {
            Log::error('Commission payment batch processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $results['errors'][] = 'Batch processing failed: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Process commission payments for a specific user in batch
     */
    public function processBatchPayment(Collection $commissions): array
    {
        $results = [
            'total_processed' => 0,
            'successful_payments' => 0,
            'failed_payments' => 0,
            'total_amount' => 0,
            'errors' => []
        ];

        if ($commissions->isEmpty()) {
            return $results;
        }

        $user = $commissions->first()->referrer;
        if (!$user) {
            $results['errors'][] = 'User not found for commission batch';
            return $results;
        }

        try {
            DB::beginTransaction();

            // Calculate total amount for batch payment
            $totalAmount = $commissions->sum('amount');
            $results['total_amount'] = $totalAmount;
            $results['total_processed'] = $commissions->count();

            // Validate minimum payment threshold
            if ($totalAmount < config('mygrownet.minimum_payment_threshold', 10)) {
                $results['errors'][] = "Payment amount K{$totalAmount} below minimum threshold for user {$user->id}";
                DB::rollBack();
                return $results;
            }

            // Validate user payment details
            if (!$this->validateUserPaymentDetails($user)) {
                $results['errors'][] = "Invalid payment details for user {$user->id}";
                $results['failed_payments'] = $commissions->count();
                DB::rollBack();
                return $results;
            }

            // Create payment transaction record
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'type' => 'commission_payment',
                'amount' => $totalAmount,
                'status' => 'pending',
                'payment_method' => $user->preferred_payment_method ?? 'mobile_money',
                'payment_details' => [
                    'phone_number' => $user->phone_number,
                    'commission_ids' => $commissions->pluck('id')->toArray(),
                    'commission_count' => $commissions->count()
                ],
                'reference' => $this->generatePaymentReference($user->id)
            ]);

            // Process mobile money payment
            $paymentResult = $this->processMobileMoneyPayment($user, $totalAmount, $paymentTransaction->reference);

            if ($paymentResult['success']) {
                // Mark commissions as paid
                foreach ($commissions as $commission) {
                    $commission->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'payment_transaction_id' => $paymentTransaction->id
                    ]);
                }

                // Update payment transaction
                $paymentTransaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'external_reference' => $paymentResult['external_reference'] ?? null,
                    'payment_response' => $paymentResult
                ]);

                // Update user balance
                $user->increment('total_earnings', $totalAmount);
                $user->increment('balance', $totalAmount);

                // Record user activity
                $user->recordActivity(
                    'commission_payment_received',
                    "Received K{$totalAmount} commission payment via {$user->preferred_payment_method}"
                );

                $results['successful_payments'] = $commissions->count();

                Log::info('Commission batch payment successful', [
                    'user_id' => $user->id,
                    'amount' => $totalAmount,
                    'commission_count' => $commissions->count(),
                    'transaction_id' => $paymentTransaction->id
                ]);

            } else {
                // Mark payment as failed
                $paymentTransaction->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $paymentResult['error'] ?? 'Unknown error',
                    'payment_response' => $paymentResult
                ]);

                $results['failed_payments'] = $commissions->count();
                $results['errors'][] = "Payment failed for user {$user->id}: " . ($paymentResult['error'] ?? 'Unknown error');

                Log::error('Commission batch payment failed', [
                    'user_id' => $user->id,
                    'amount' => $totalAmount,
                    'error' => $paymentResult['error'] ?? 'Unknown error',
                    'transaction_id' => $paymentTransaction->id
                ]);
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            
            $results['failed_payments'] = $commissions->count();
            $results['errors'][] = "Batch payment exception for user {$user->id}: " . $e->getMessage();
            
            Log::error('Commission batch payment exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Process individual commission payment
     */
    public function processIndividualPayment(ReferralCommission $commission): bool
    {
        if (!$commission->isEligibleForPayment()) {
            return false;
        }

        $user = $commission->referrer;
        if (!$user) {
            return false;
        }

        try {
            DB::beginTransaction();

            // Create payment transaction
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'type' => 'commission_payment',
                'amount' => $commission->amount,
                'status' => 'pending',
                'payment_method' => $user->preferred_payment_method ?? 'mobile_money',
                'payment_details' => [
                    'phone_number' => $user->phone_number,
                    'commission_ids' => [$commission->id]
                ],
                'reference' => $this->generatePaymentReference($user->id)
            ]);

            // Process payment
            $paymentResult = $this->processMobileMoneyPayment($user, $commission->amount, $paymentTransaction->reference);

            if ($paymentResult['success']) {
                $commission->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_transaction_id' => $paymentTransaction->id
                ]);

                $paymentTransaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'external_reference' => $paymentResult['external_reference'] ?? null
                ]);

                $user->increment('total_earnings', $commission->amount);
                $user->increment('balance', $commission->amount);

                DB::commit();
                return true;
            } else {
                $paymentTransaction->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $paymentResult['error'] ?? 'Unknown error'
                ]);

                DB::rollBack();
                return false;
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Individual commission payment failed', [
                'commission_id' => $commission->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Retry failed payments
     */
    public function retryFailedPayments(int $maxRetries = 3): array
    {
        $results = [
            'total_retried' => 0,
            'successful_retries' => 0,
            'failed_retries' => 0,
            'errors' => []
        ];

        // Get failed payment transactions that haven't exceeded retry limit
        $failedTransactions = PaymentTransaction::where('status', 'failed')
            ->where('type', 'commission_payment')
            ->where('retry_count', '<', $maxRetries)
            ->where('created_at', '>=', now()->subDays(7)) // Only retry within 7 days
            ->with('user')
            ->get();

        foreach ($failedTransactions as $transaction) {
            $results['total_retried']++;
            
            try {
                // Increment retry count
                $transaction->increment('retry_count');
                
                // Retry the payment
                $paymentResult = $this->processMobileMoneyPayment(
                    $transaction->user,
                    $transaction->amount,
                    $transaction->reference
                );

                if ($paymentResult['success']) {
                    $transaction->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'external_reference' => $paymentResult['external_reference'] ?? null
                    ]);

                    // Mark associated commissions as paid
                    $commissionIds = $transaction->payment_details['commission_ids'] ?? [];
                    ReferralCommission::whereIn('id', $commissionIds)->update([
                        'status' => 'paid',
                        'paid_at' => now()
                    ]);

                    $results['successful_retries']++;
                } else {
                    $transaction->update([
                        'failure_reason' => $paymentResult['error'] ?? 'Retry failed'
                    ]);
                    
                    $results['failed_retries']++;
                    $results['errors'][] = "Retry failed for transaction {$transaction->id}: " . ($paymentResult['error'] ?? 'Unknown error');
                }

            } catch (Exception $e) {
                $results['failed_retries']++;
                $results['errors'][] = "Retry exception for transaction {$transaction->id}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Validate user payment details
     */
    protected function validateUserPaymentDetails(User $user): bool
    {
        // Check if user has valid phone number for mobile money
        if (empty($user->phone_number)) {
            return false;
        }

        // Validate phone number format (Zambian format)
        if (!preg_match('/^(\+260|0)?[79]\d{8}$/', $user->phone_number)) {
            return false;
        }

        // Check if user has active subscription
        if (!$user->hasActiveSubscription()) {
            return false;
        }

        return true;
    }

    /**
     * Process mobile money payment
     */
    protected function processMobileMoneyPayment(User $user, float $amount, string $reference): array
    {
        try {
            return $this->mobileMoneyService->sendPayment([
                'phone_number' => $user->phone_number,
                'amount' => $amount,
                'reference' => $reference,
                'description' => "MyGrowNet commission payment - K{$amount}",
                'recipient_name' => $user->name
            ]);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate unique payment reference
     */
    protected function generatePaymentReference(int $userId): string
    {
        return 'MGN-COM-' . $userId . '-' . now()->format('YmdHis') . '-' . rand(1000, 9999);
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $transactions = PaymentTransaction::where('type', 'commission_payment')
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'total_transactions' => $transactions->count(),
            'successful_payments' => $transactions->where('status', 'completed')->count(),
            'failed_payments' => $transactions->where('status', 'failed')->count(),
            'pending_payments' => $transactions->where('status', 'pending')->count(),
            'total_amount_paid' => $transactions->where('status', 'completed')->sum('amount'),
            'total_amount_failed' => $transactions->where('status', 'failed')->sum('amount'),
            'average_payment_amount' => $transactions->where('status', 'completed')->avg('amount'),
            'success_rate' => $transactions->count() > 0 
                ? ($transactions->where('status', 'completed')->count() / $transactions->count()) * 100 
                : 0
        ];
    }
}