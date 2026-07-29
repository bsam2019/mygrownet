<?php

namespace App\Domain\GrowBuilder\Payment\Services;

use App\Domain\PlatformPayments\Contracts\PaymentGatewayInterface;
use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use App\Domain\PlatformPayments\DTOs\PaymentResponse;
use App\Domain\PlatformPayments\DTOs\RefundRequest;
use App\Domain\PlatformPayments\DTOs\RefundResponse;
use App\Domain\PlatformPayments\Enums\GatewayProvider;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use App\Domain\PlatformPayments\Services\PaymentGatewayFactory;
use App\Models\GrowBuilder\SitePaymentConfig;
use App\Models\GrowBuilder\SitePaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrowBuilderPaymentService
{
    public function initiatePayment(
        int $siteId,
        PaymentRequest $request
    ): PaymentResponse {
        try {
            $config = SitePaymentConfig::where('site_id', $siteId)
                ->where('is_active', true)
                ->firstOrFail();

            $gateway = $this->createGateway($config);

            $response = $gateway->initiatePayment($request);

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

    public function verifyPayment(int $siteId, string $transactionReference): PaymentResponse
    {
        try {
            $config = SitePaymentConfig::where('site_id', $siteId)
                ->where('is_active', true)
                ->firstOrFail();

            $gateway = $this->createGateway($config);
            $response = $gateway->verifyPayment($transactionReference);

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

    public function handleWebhook(int $siteId, array $payload): bool
    {
        try {
            $config = SitePaymentConfig::where('site_id', $siteId)
                ->where('is_active', true)
                ->firstOrFail();

            if (!$this->verifyWebhookSignature($config, $payload)) {
                Log::warning('Invalid webhook signature', [
                    'site_id' => $siteId,
                ]);
                return false;
            }

            $reference = $this->extractTransactionReference($config->gateway, $payload);

            if ($reference) {
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

    private function createGateway(SitePaymentConfig $config): PaymentGatewayInterface
    {
        $gateway = GatewayProvider::from($config->gateway);
        $credentials = $config->decryptedCredentials();

        return PaymentGatewayFactory::create(
            $gateway,
            $credentials,
            $config->test_mode
        );
    }

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

    private function verifyWebhookSignature(SitePaymentConfig $config, array $payload): bool
    {
        return true;
    }

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
