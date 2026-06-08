<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Domain\Payment\Gateways\NOWPaymentsGateway;
use App\Domain\Payment\Enums\TransactionStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel;

/**
 * NOWPayments IPN (Instant Payment Notification) Webhook Handler
 * 
 * Handles payment status updates from NOWPayments
 * 
 * Webhook Events:
 * - finished: Payment completed successfully
 * - confirmed: Payment confirmed on blockchain
 * - failed: Payment failed
 * - refunded: Payment refunded
 * - expired: Payment expired
 */
class NOWPaymentsWebhookController extends Controller
{
    public function __construct(
        private NOWPaymentsGateway $gateway
    ) {}

    /**
     * Handle NOWPayments IPN callback
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // Get raw payload for signature verification
            $payload = $request->getContent();
            $signature = $request->header('x-nowpayments-sig');

            // Verify signature
            if (!$this->gateway->verifyIPNSignature($payload, $signature ?? '')) {
                Log::warning('NOWPayments webhook signature verification failed', [
                    'ip' => $request->ip(),
                    'signature' => $signature,
                ]);

                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $data = $request->all();

            Log::info('NOWPayments webhook received', [
                'payment_id' => $data['payment_id'] ?? null,
                'payment_status' => $data['payment_status'] ?? null,
                'order_id' => $data['order_id'] ?? null,
            ]);

            // Process the webhook
            $this->processWebhook($data);

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('NOWPayments webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Process webhook data and update payment status
     */
    private function processWebhook(array $data): void
    {
        $paymentId = $data['payment_id'] ?? null;
        $orderId = $data['order_id'] ?? null;
        $paymentStatus = $data['payment_status'] ?? null;

        if (!$paymentId || !$orderId) {
            Log::warning('NOWPayments webhook missing required fields', $data);
            return;
        }

        // Find payment by order_id (transaction_id in our system)
        $payment = MemberPaymentModel::where('transaction_id', $orderId)
            ->orWhere('provider_reference', $paymentId)
            ->first();

        if (!$payment) {
            Log::warning('NOWPayments webhook: Payment not found', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
            ]);
            return;
        }

        // Map NOWPayments status to our internal status
        $status = $this->mapStatus($paymentStatus);

        // Update payment status
        $payment->update([
            'status' => $status->value,
            'provider_reference' => $paymentId,
            'payment_details' => array_merge($payment->payment_details ?? [], [
                'nowpayments_status' => $paymentStatus,
                'pay_amount' => $data['pay_amount'] ?? null,
                'pay_currency' => $data['pay_currency'] ?? null,
                'price_amount' => $data['price_amount'] ?? null,
                'price_currency' => $data['price_currency'] ?? null,
                'actually_paid' => $data['actually_paid'] ?? null,
                'outcome_amount' => $data['outcome_amount'] ?? null,
                'outcome_currency' => $data['outcome_currency'] ?? null,
                'updated_at' => $data['updated_at'] ?? now()->toIso8601String(),
            ]),
        ]);

        Log::info('NOWPayments payment status updated', [
            'payment_id' => $payment->id,
            'order_id' => $orderId,
            'old_status' => $payment->status,
            'new_status' => $status->value,
            'nowpayments_status' => $paymentStatus,
        ]);

        // Fire events based on status
        if ($status === TransactionStatus::COMPLETED) {
            // Payment completed - trigger success actions
            event(new \App\Domain\Payment\Events\PaymentVerified($payment));
        } elseif ($status === TransactionStatus::FAILED) {
            // Payment failed - trigger failure actions
            Log::warning('NOWPayments payment failed', [
                'payment_id' => $payment->id,
                'order_id' => $orderId,
            ]);
        }
    }

    /**
     * Map NOWPayments status to internal TransactionStatus
     */
    private function mapStatus(string $status): TransactionStatus
    {
        return match (strtolower($status)) {
            'finished', 'confirmed' => TransactionStatus::COMPLETED,
            'waiting', 'confirming' => TransactionStatus::PENDING,
            'sending', 'processing' => TransactionStatus::PROCESSING,
            'failed', 'refunded' => TransactionStatus::FAILED,
            'expired' => TransactionStatus::EXPIRED,
            default => TransactionStatus::PENDING,
        };
    }
}
