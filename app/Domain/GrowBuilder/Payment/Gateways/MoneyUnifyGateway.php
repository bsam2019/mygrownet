<?php

namespace App\Domain\GrowBuilder\Payment\Gateways;

use App\Domain\GrowBuilder\Payment\DTOs\PaymentRequest;
use App\Domain\GrowBuilder\Payment\DTOs\PaymentResponse;
use App\Domain\GrowBuilder\Payment\DTOs\RefundRequest;
use App\Domain\GrowBuilder\Payment\DTOs\RefundResponse;
use App\Domain\GrowBuilder\Payment\Enums\PaymentStatus;

class MoneyUnifyGateway extends AbstractPaymentGateway
{
    private string $baseUrl;

    public function __construct(array $credentials, bool $testMode = false)
    {
        parent::__construct($credentials, $testMode);
        $this->baseUrl = $testMode 
            ? 'https://api.sandbox.moneyunify.com/v2'
            : 'https://api.moneyunify.com/v2';
    }

    public function initiatePayment(PaymentRequest $request): PaymentResponse
    {
        try {
            $this->logActivity('Initiating payment', $request->toArray());

            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/collections",
                [
                    'muid' => $this->credentials['muid'],
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'phone' => $this->formatPhoneNumber($request->phoneNumber),
                    'reference' => $request->reference,
                    'description' => $request->description,
                    'callback_url' => $request->callbackUrl,
                    'metadata' => $request->metadata,
                ],
                [
                    'Content-Type' => 'application/json',
                ]
            );

            if ($response['success']) {
                return new PaymentResponse(
                    success: true,
                    status: PaymentStatus::PENDING,
                    transactionReference: $request->reference,
                    externalReference: $response['data']['transaction_id'] ?? null,
                    message: 'Payment initiated successfully',
                    rawResponse: $response['data'],
                    checkoutUrl: $response['data']['checkout_url'] ?? null,
                );
            }

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $request->reference,
                message: $response['data']['message'] ?? 'Payment initiation failed',
                rawResponse: $response['data'],
            );

        } catch (\Exception $e) {
            $this->logError('Payment initiation', $e, $request->toArray());

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $request->reference,
                message: $e->getMessage(),
            );
        }
    }

    public function verifyPayment(string $transactionReference): PaymentResponse
    {
        try {
            $response = $this->makeRequest(
                'get',
                "{$this->baseUrl}/collections/{$transactionReference}",
                [],
                [
                    'Content-Type' => 'application/json',
                ]
            );

            if ($response['success']) {
                $status = $this->mapStatus($response['data']['status'] ?? 'pending');

                return new PaymentResponse(
                    success: $status === PaymentStatus::COMPLETED,
                    status: $status,
                    transactionReference: $transactionReference,
                    externalReference: $response['data']['transaction_id'] ?? null,
                    rawResponse: $response['data'],
                );
            }

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $transactionReference,
                message: 'Failed to verify payment',
            );

        } catch (\Exception $e) {
            $this->logError('Payment verification', $e, ['reference' => $transactionReference]);

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $transactionReference,
                message: $e->getMessage(),
            );
        }
    }

    public function refundPayment(RefundRequest $request): RefundResponse
    {
        try {
            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/refunds",
                [
                    'muid' => $this->credentials['muid'],
                    'transaction_reference' => $request->transactionReference,
                    'amount' => $request->amount,
                    'reason' => $request->reason,
                ],
                [
                    'Content-Type' => 'application/json',
                ]
            );

            if ($response['success']) {
                return new RefundResponse(
                    success: true,
                    refundReference: $response['data']['refund_id'] ?? '',
                    message: 'Refund processed successfully',
                    rawResponse: $response['data'],
                );
            }

            return new RefundResponse(
                success: false,
                refundReference: '',
                message: $response['data']['message'] ?? 'Refund failed',
                rawResponse: $response['data'],
            );

        } catch (\Exception $e) {
            $this->logError('Refund processing', $e, $request->toArray());

            return new RefundResponse(
                success: false,
                refundReference: '',
                message: $e->getMessage(),
            );
        }
    }

    public function getName(): string
    {
        return 'MoneyUnify';
    }

    public function validateConfiguration(array $credentials): array
    {
        $errors = [];

        if (empty($credentials['muid'])) {
            $errors[] = 'MUID (MoneyUnify ID) is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function getRequiredFields(): array
    {
        return [
            [
                'name' => 'muid',
                'label' => 'MUID (MoneyUnify ID)',
                'type' => 'text',
                'required' => true,
                'description' => 'Your MoneyUnify merchant ID from the dashboard',
            ],
        ];
    }

    public function supportsTestMode(): bool
    {
        return true;
    }

    private function mapStatus(string $moneyunifyStatus): PaymentStatus
    {
        return match(strtolower($moneyunifyStatus)) {
            'completed', 'success', 'successful' => PaymentStatus::COMPLETED,
            'failed', 'error' => PaymentStatus::FAILED,
            'cancelled', 'canceled' => PaymentStatus::CANCELLED,
            'pending' => PaymentStatus::PENDING,
            'processing' => PaymentStatus::PROCESSING,
            default => PaymentStatus::PENDING,
        };
    }
}
