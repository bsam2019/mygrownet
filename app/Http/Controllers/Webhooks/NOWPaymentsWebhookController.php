<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Domain\Payment\Gateways\NOWPaymentsGateway;
use App\Domain\Payment\Enums\TransactionStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel;
use App\Models\GrowMart\GrowMartOrder;

class NOWPaymentsWebhookController extends Controller
{
    public function __construct(
        private NOWPaymentsGateway $gateway
    ) {}

    public function handle(Request $request): JsonResponse
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('x-nowpayments-sig');

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

    private function processWebhook(array $data): void
    {
        $paymentId = $data['payment_id'] ?? null;
        $orderId = $data['order_id'] ?? null;
        $paymentStatus = $data['payment_status'] ?? null;

        if (!$paymentId || !$orderId) {
            Log::warning('NOWPayments webhook missing required fields', $data);
            return;
        }

        // Check if this is a GrowMart order (prefix "GM-")
        if (str_starts_with($orderId, 'GM-')) {
            $this->processGrowMartOrder($orderId, $paymentId, $paymentStatus, $data);
            return;
        }

        // Default: handle MemberPaymentModel (core system)
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

        $status = $this->mapStatus($paymentStatus);

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

        if ($status === TransactionStatus::COMPLETED) {
            event(new \App\Domain\Payment\Events\PaymentVerified($payment));
        } elseif ($status === TransactionStatus::FAILED) {
            Log::warning('NOWPayments payment failed', [
                'payment_id' => $payment->id,
                'order_id' => $orderId,
            ]);
        }
    }

    private function processGrowMartOrder(string $orderId, string $paymentId, ?string $paymentStatus, array $data): void
    {
        $growMartOrderId = (int) substr($orderId, 3); // Strip "GM-" prefix
        $order = GrowMartOrder::find($growMartOrderId);

        if (!$order) {
            Log::warning('NOWPayments webhook: GrowMart order not found', [
                'growmart_order_id' => $growMartOrderId,
                'order_id' => $orderId,
            ]);
            return;
        }

        $status = $this->mapStatus($paymentStatus);

        $nowpaymentsData = [
            'nowpayments_status' => $paymentStatus,
            'nowpayments_payment_id' => $paymentId,
            'pay_amount' => $data['pay_amount'] ?? null,
            'pay_currency' => $data['pay_currency'] ?? null,
            'price_amount' => $data['price_amount'] ?? null,
            'price_currency' => $data['price_currency'] ?? null,
            'actually_paid' => $data['actually_paid'] ?? null,
            'outcome_amount' => $data['outcome_amount'] ?? null,
            'outcome_currency' => $data['outcome_currency'] ?? null,
            'updated_at' => now()->toIso8601String(),
        ];

        $orderUpdates = [
            'payment_details' => array_merge($order->payment_details ?? [], $nowpaymentsData),
        ];

        if ($status === TransactionStatus::COMPLETED) {
            $orderUpdates['payment_status'] = 'paid';
            $orderUpdates['paid_at'] = now();
            $orderUpdates['status'] = 'pending';
            $orderUpdates['payment_reference'] = $paymentId;
            $orderUpdates['payment_submitted_at'] = now();
        } elseif (in_array($status->value, ['failed', 'expired'])) {
            $orderUpdates['payment_status'] = 'failed';
        }

        $order->update($orderUpdates);

        Log::info('NOWPayments GrowMart order status updated', [
            'growmart_order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_status' => $paymentStatus,
            'new_status' => $status->value,
            'order_payment_status' => $orderUpdates['payment_status'] ?? 'unchanged',
        ]);
    }

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
