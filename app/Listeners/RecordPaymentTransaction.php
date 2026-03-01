<?php

namespace App\Listeners;

use App\Domain\Payment\Events\PaymentVerified;
use App\Domain\Financial\Services\TransactionIntegrityService;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Enums\TransactionStatus;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Record Payment Transaction Listener
 * 
 * Creates a transaction record when a payment is verified by admin.
 * This ensures all verified payments are recorded in the transactions table.
 * 
 * CRITICAL: This fixes the issue where verified payments in member_payments
 * don't create corresponding transaction records, causing negative balances.
 */
class RecordPaymentTransaction
{
    public function __construct(
        private readonly TransactionIntegrityService $transactionService
    ) {}

    /**
     * Handle the event - Create transaction record for verified payment
     */
    public function handle(PaymentVerified $event): void
    {
        Log::info("RecordPaymentTransaction listener triggered", [
            'payment_id' => $event->paymentId,
            'user_id' => $event->userId,
            'amount' => $event->amount,
            'payment_type' => $event->paymentType
        ]);

        try {
            $user = User::find($event->userId);
            
            if (!$user) {
                Log::warning("User not found for transaction recording", ['user_id' => $event->userId]);
                return;
            }

            // Map payment type to transaction type
            $transactionType = $this->mapPaymentTypeToTransactionType($event->paymentType, $event->amount);
            
            if (!$transactionType) {
                Log::info("Payment type not eligible for transaction recording", [
                    'payment_type' => $event->paymentType
                ]);
                return;
            }

            // Determine transaction source (module)
            $source = $this->determineTransactionSource($event->paymentType);

            // Create unique reference for this payment
            $reference = "payment_{$event->paymentId}_" . time();

            // Create transaction record using TransactionIntegrityService
            // This prevents duplicates and ensures data integrity
            $transaction = $this->transactionService->recordTransaction(
                user: $user,
                amount: $event->amount,
                type: $transactionType->value,
                description: $this->generateDescription($event->paymentType, $event->amount),
                reference: $reference,
                status: TransactionStatus::COMPLETED->value,
                metadata: [
                    'payment_id' => $event->paymentId,
                    'payment_type' => $event->paymentType,
                    'verified_by' => $event->verifiedBy,
                    'verified_at' => $event->occurredAt->format('Y-m-d H:i:s'),
                    'source' => 'payment_verification',
                ],
                source: $source
            );

            // Clear wallet cache to reflect new balance
            Cache::forget("wallet_balance_{$user->id}");
            Cache::forget("wallet_breakdown_{$user->id}");

            Log::info("Transaction recorded for verified payment", [
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'amount' => $event->amount,
                'type' => $transactionType->value,
                'reference' => $reference,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to record transaction for verified payment", [
                'user_id' => $event->userId,
                'payment_id' => $event->paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't re-throw - we don't want to fail payment verification if transaction recording fails
            // The payment is still verified, we just log the error for manual review
        }
    }

    /**
     * Map payment type to transaction type
     */
    private function mapPaymentTypeToTransactionType(string $paymentType, float $amount): ?TransactionType
    {
        return match($paymentType) {
            'wallet_topup' => TransactionType::WALLET_TOPUP,
            'subscription' => TransactionType::SUBSCRIPTION_PAYMENT,
            'workshop' => TransactionType::WORKSHOP_PAYMENT,
            'learning_pack' => TransactionType::LEARNING_PACK_PURCHASE,
            'coaching' => TransactionType::COACHING_PAYMENT,
            'upgrade' => TransactionType::SUBSCRIPTION_PAYMENT,
            'product' => $this->determineProductTransactionType($amount),
            default => null,
        };
    }

    /**
     * Determine transaction type for product payments
     * 
     * Products can be:
     * - Starter kits (K300, K500, K1000, K2000)
     * - Shop purchases (variable amounts)
     */
    private function determineProductTransactionType(float $amount): TransactionType
    {
        // Starter kit amounts
        $starterKitAmounts = [300, 500, 1000, 2000];
        
        if (in_array($amount, $starterKitAmounts)) {
            return TransactionType::STARTER_KIT_PURCHASE;
        }
        
        return TransactionType::SHOP_PURCHASE;
    }

    /**
     * Determine transaction source (module) based on payment type
     */
    private function determineTransactionSource(string $paymentType): string
    {
        return match($paymentType) {
            'wallet_topup' => 'wallet',
            'subscription' => 'platform',
            'workshop' => 'workshops',
            'learning_pack' => 'learning',
            'coaching' => 'coaching',
            'upgrade' => 'platform',
            'product' => 'shop',
            default => 'platform',
        };
    }

    /**
     * Generate transaction description
     */
    private function generateDescription(string $paymentType, float $amount): string
    {
        return match($paymentType) {
            'wallet_topup' => "Wallet top-up - K" . number_format($amount, 2),
            'subscription' => "Platform subscription payment - K" . number_format($amount, 2),
            'workshop' => "Workshop registration payment - K" . number_format($amount, 2),
            'learning_pack' => "Learning pack purchase - K" . number_format($amount, 2),
            'coaching' => "Coaching session payment - K" . number_format($amount, 2),
            'upgrade' => "Subscription upgrade - K" . number_format($amount, 2),
            'product' => "Product purchase - K" . number_format($amount, 2),
            default => "Payment - K" . number_format($amount, 2),
        };
    }
}

