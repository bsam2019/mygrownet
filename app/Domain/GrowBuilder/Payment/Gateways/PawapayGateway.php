<?php

namespace App\Domain\GrowBuilder\Payment\Gateways;

use App\Domain\GrowBuilder\Payment\DTOs\PaymentRequest;
use App\Domain\GrowBuilder\Payment\DTOs\PaymentResponse;
use App\Domain\GrowBuilder\Payment\DTOs\RefundRequest;
use App\Domain\GrowBuilder\Payment\DTOs\RefundResponse;
use App\Domain\GrowBuilder\Payment\Enums\PaymentStatus;
use Illuminate\Support\Facades\Cache;

class PawapayGateway extends AbstractPaymentGateway
{
    private string $baseUrl;

    public function __construct(array $credentials, bool $testMode = false)
    {
        parent::__construct($credentials, $testMode);
        $this->baseUrl = $testMode 
            ? 'https://api.sandbox.pawapay.io'
            : 'https://api.pawapay.io';
    }

    public function initiatePayment(PaymentRequest $request): PaymentResponse
    {
        try {
            $this->logActivity('Initiating payment', $request->toArray());

            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/deposits",
                [
                    'depositId' => $request->reference,
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'correspondent' => $this->detectCorrespondent($request->phoneNumber),
                    'payer' => [
                        'type' => 'MSISDN',
                        'address' => [
                            'value' => $this->formatPhoneNumber($request->phoneNumber),
                        ],
                    ],
                    'customerTimestamp' => now()->toIso8601String(),
                    'statementDescription' => $request->description,
                ],
                [
                    'Authorization' => 'Bearer ' . $this->credentials['api_token'],
                    'Content-Type' => 'application/json',
                ]
            );

            if ($response['success']) {
                return new PaymentResponse(
                    success: true,
                    status: PaymentStatus::PENDING,
                    transactionReference: $request->reference,
                    externalReference: $response['data']['depositId'] ?? null,
                    message: 'Payment initiated successfully',
                    rawResponse: $response['data'],
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
                "{$this->baseUrl}/deposits/{$transactionReference}",
                [],
                [
                    'Authorization' => 'Bearer ' . $this->credentials['api_token'],
                ]
            );

            if ($response['success']) {
                $status = $this->mapStatus($response['data']['status'] ?? 'PENDING');

                return new PaymentResponse(
                    success: $status === PaymentStatus::COMPLETED,
                    status: $status,
                    transactionReference: $transactionReference,
                    externalReference: $response['data']['depositId'] ?? null,
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
                    'refundId' => uniqid('refund_'),
                    'depositId' => $request->transactionReference,
                    'amount' => $request->amount,
                    'reason' => $request->reason,
                ],
                [
                    'Authorization' => 'Bearer ' . $this->credentials['api_token'],
                    'Content-Type' => 'application/json',
                ]
            );

            if ($response['success']) {
                return new RefundResponse(
                    success: true,
                    refundReference: $response['data']['refundId'] ?? '',
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
        return 'PawaPay';
    }

    public function validateConfiguration(array $credentials): array
    {
        $errors = [];

        if (empty($credentials['api_token'])) {
            $errors[] = 'API token is required';
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
                'name' => 'api_token',
                'label' => 'API Token',
                'type' => 'password',
                'required' => true,
                'description' => 'Your PawaPay API token from the dashboard',
            ],
        ];
    }

    public function supportsTestMode(): bool
    {
        return true;
    }

    private function detectCorrespondent(string $phoneNumber): string
    {
        $cleanNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (str_starts_with($cleanNumber, '260')) {
            $cleanNumber = substr($cleanNumber, 3);
        }
        
        // MTN: 096, 076, 077
        if (preg_match('/^(096|076|077)/', $cleanNumber)) {
            return 'MTN_MOMO_ZMB';
        }
        
        // Airtel: 097
        if (preg_match('/^(097)/', $cleanNumber)) {
            return 'AIRTEL_OAPI_ZMB';
        }
        
        // Zamtel: 095
        if (preg_match('/^(095)/', $cleanNumber)) {
            return 'ZAMTEL_ZMB';
        }
        
        return 'MTN_MOMO_ZMB'; // Default
    }

    private function mapStatus(string $pawapayStatus): PaymentStatus
    {
        return match($pawapayStatus) {
            'COMPLETED' => PaymentStatus::COMPLETED,
            'FAILED' => PaymentStatus::FAILED,
            'CANCELLED' => PaymentStatus::CANCELLED,
            'PENDING' => PaymentStatus::PENDING,
            default => PaymentStatus::PROCESSING,
        };
    }
}
