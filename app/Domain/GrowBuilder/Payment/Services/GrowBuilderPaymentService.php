<?php

namespace App\Domain\GrowBuilder\Payment\Services;

use App\Domain\GrowBuilder\Payment\Contracts\PaymentGatewayInterface;
use App\Domain\GrowBuilder\Payment\DTOs\PaymentRequest;
use App\Domain\GrowBuilder\Payment\DTOs\PaymentResponse;
use App\Domain\GrowBuilder\Payment\DTOs\RefundRequest;
use App\Domain\GrowBuilder\Payment\DTOs\RefundResponse;
use App\Domain\GrowBuilder\Payment\Enums\PaymentGateway;
use App\Domain\GrowBuilder\Payment\Enums\PaymentStatus;
use App\Models\GrowBuilder\SitePaymentConfig;
use App\Models\GrowBuilder\SitePaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrowBuilderPaymentService
{
    /**
     * Initialize payment for a GrowBuilder site
     */
    public function initiatePayment(
        int $siteId,
        PaymentRequest $request
    ): PaymentResponse {
        try {
            // Get site payment configuration
            $config = SitePaymentConfig::where('site_id', $siteId)
                ->where('is_active', true)
                ->firstOrFail();

            // Create gateway instance
            $gateway = $this->createGateway($config);

            // Initiate payment
            $response = $gateway->initiatePayment($request);

            // Log transaction
            $this->logTransaction($siteId, $config->id, $request, $response);

            return $response;

        } catch (\Exception $e) {
            Log::error('GrowBuilder payment initiation failed', [
                'site_id' => $siteId,
                'error' => $e->getMessage(),
                'request' => $request->toArray(),
            ]);

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $request->reference,
                message: $e->getMessage(),
            );
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(int $siteId, string $transactionReference): PaymentResponse
    {
        try {
            $config = SitePaymentConfig::where('site_id', $siteId)
                ->where('is_active', true)
                ->firstOrFail();

            $gateway = $this->createGateway($config);
            $response = $gateway->verifyPayment($transactionReference);

            // Update transaction status
            $this->updateTransactionStatus($transactionReference, $response);

            return $response;

        } catch (\Exception $e) {
            Log::error('GrowBuilder payment verification failed', [
                'site_id' => $siteId,
                'reference' => $transactionReference,
                'error' => $e->getMessage(),
            ]);

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $transactionReference,
                message: $e->getMessage(),
            );
        }
    }

    /**
     * Process refund
     */
    public function refundPayment(
        int $siteId,
        RefundRequest $request
    ): RefundResponse {
        try {
            $config = SitePaymentConfig::where('site_id', $siteId)
                ->where('is_active', true)
                ->firstOrFail();

            $gateway = $this->createGateway($config);
            $response = $gateway->refundPayment($request);

            // Log refund
            if ($response->success) {
                $this->logRefund($siteId, $request, $response);
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('GrowBuilder refund failed', [
                'site_id' => $siteId,
                'error' => $e->getMessage(),
                'request' => $request->toArray(),
            ]);

            return new RefundResponse(
                success: false,
                refundReference: '',
                message: $e->getMessage(),
            );
        }
    }

    /**
     * Handle payment webhook
     */
    public function handleWebhook(int $siteId, array $payload): bool
    {
        try {
            $config = SitePaymentConfig::where('site_id', $siteId)
                ->where('is_active', true)
                ->firstOrFail();

            // Verify webhook signature if applicable
            if (!$this->verifyWebhookSignature($config, $payload)) {
                Log::warning('Invalid webhook signature', [
                    'site_id' => $siteId,
                ]);
                return false;
            }

            // Extract transaction reference from payload
            $reference = $this->extractTransactionReference($config->gateway, $payload);

            if ($reference) {
                // Verify payment status
                $response = $this->verifyPayment($siteId, $reference);
                return $response->success;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'site_id' => $siteId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Create gateway instance from configuration
     */
    private function createGateway(SitePaymentConfig $config): PaymentGatewayInterface
    {
        $gateway = PaymentGateway::from($config->gateway);
        $credentials = $config->decryptedCredentials();

        return PaymentGatewayFactory::create(
            $gateway,
            $credentials,
            $config->test_mode
        );
    }

    /**
     * Log payment transaction
     */
    private function logTransaction(
        int $siteId,
        int $configId,
        PaymentRequest $request,
        PaymentResponse $response
    ): void {
        SitePaymentTransaction::create([
            'site_id' => $siteId,
            'payment_config_id' => $configId,
            'transaction_reference' => $request->reference,
            'external_reference' => $response->externalReference,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'phone_number' => $request->phoneNumber,
            'customer_name' => $request->customerName,
            'customer_email' => $request->customerEmail,
            'description' => $request->description,
            'status' => $response->status->value,
            'metadata' => $request->metadata,
            'raw_response' => $response->rawResponse,
        ]);
    }

    /**
     * Update transaction status
     */
    private function updateTransactionStatus(
        string $transactionReference,
        PaymentResponse $response
    ): void {
        SitePaymentTransaction::where('transaction_reference', $transactionReference)
            ->update([
                'status' => $response->status->value,
                'external_reference' => $response->externalReference ?? DB::raw('external_reference'),
                'raw_response' => $response->rawResponse ?? DB::raw('raw_response'),
                'verified_at' => now(),
            ]);
    }

    /**
     * Log refund
     */
    private function logRefund(
        int $siteId,
        RefundRequest $request,
        RefundResponse $response
    ): void {
        SitePaymentTransaction::where('transaction_reference', $request->transactionReference)
            ->update([
                'status' => PaymentStatus::REFUNDED->value,
                'refund_reference' => $response->refundReference,
                'refund_amount' => $request->amount,
                'refund_reason' => $request->reason,
                'refunded_at' => now(),
            ]);
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature(SitePaymentConfig $config, array $payload): bool
    {
        // Implementation depends on gateway
        // For now, return true
        return true;
    }

    /**
     * Extract transaction reference from webhook payload
     */
    private function extractTransactionReference(string $gateway, array $payload): ?string
    {
        return match($gateway) {
            'pawapay' => $payload['depositId'] ?? null,
            'flutterwave' => $payload['tx_ref'] ?? $payload['txRef'] ?? null,
            'dpo' => $payload['CompanyRef'] ?? null,
            default => null,
        };
    }
}
