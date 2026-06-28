<?php

namespace App\Domain\GrowBuilder\Payment\Gateways;

use App\Domain\GrowBuilder\Payment\DTOs\PaymentRequest;
use App\Domain\GrowBuilder\Payment\DTOs\PaymentResponse;
use App\Domain\GrowBuilder\Payment\DTOs\RefundRequest;
use App\Domain\GrowBuilder\Payment\DTOs\RefundResponse;
use App\Domain\GrowBuilder\Payment\Enums\PaymentStatus;
use Illuminate\Support\Facades\Cache;

class ZamtelKwachaGateway extends AbstractPaymentGateway
{
    private string $baseUrl;

    public function __construct(array $credentials, bool $testMode = false)
    {
        parent::__construct($credentials, $testMode);
        // Note: Zamtel Kwacha API documentation is limited
        // This is a placeholder implementation based on common patterns
        $this->baseUrl = $credentials['base_url'] ?? 'https://api.zamtel.co.zm';
    }

    public function initiatePayment(PaymentRequest $request): PaymentResponse
    {
        try {
            $this->logActivity('Initiating Zamtel payment', $request->toArray());
            
            $accessToken = $this->getAccessToken();

            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/payments/collect",
                [
                    'msisdn' => $this->formatPhoneNumber($request->phoneNumber),
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'reference' => $request->reference,
                    'description' => $request->description,
                ],
                [
                    'Authorization' => 'Bearer ' . $accessToken,
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
            $this->logError('Zamtel payment initiation', $e, $request->toArray());

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
            $accessToken = $this->getAccessToken();

            $response = $this->makeRequest(
                'get',
                "{$this->baseUrl}/payments/{$transactionReference}",
                [],
                [
                    'Authorization' => 'Bearer ' . $accessToken,
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
            $this->logError('Zamtel payment verification', $e, ['reference' => $transactionReference]);

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
        // Zamtel Kwacha refund implementation
        // Note: This may need to be updated based on actual API documentation
        return new RefundResponse(
            success: false,
            refundReference: '',
            message: 'Zamtel Kwacha refunds must be processed manually',
        );
    }

    public function getName(): string
    {
        return 'Zamtel Kwacha';
    }

    public function validateConfiguration(array $credentials): array
    {
        $errors = [];

        if (empty($credentials['username'])) {
            $errors[] = 'Username is required';
        }

        if (empty($credentials['password'])) {
            $errors[] = 'Password is required';
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
                'name' => 'username',
                'label' => 'Username',
                'type' => 'text',
                'required' => true,
                'description' => 'Your Zamtel Kwacha API username',
            ],
            [
                'name' => 'password',
                'label' => 'Password',
                'type' => 'password',
                'required' => true,
                'description' => 'Your Zamtel Kwacha API password',
            ],
            [
                'name' => 'base_url',
                'label' => 'API Base URL',
                'type' => 'text',
                'required' => false,
                'description' => 'Custom API base URL (optional)',
            ],
        ];
    }

    public function supportsTestMode(): bool
    {
        return false;
    }

    private function getAccessToken(): string
    {
        $cacheKey = 'zamtel_kwacha_token_' . md5($this->credentials['username']);

        return Cache::remember($cacheKey, 3600, function () {
            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/auth/token",
                [
                    'username' => $this->credentials['username'],
                    'password' => $this->credentials['password'],
                ],
                [
                    'Content-Type' => 'application/json',
                ]
            );

            if ($response['success']) {
                return $response['data']['access_token'] ?? $response['data']['token'];
            }

            throw new \Exception('Failed to get Zamtel access token');
        });
    }

    private function mapStatus(string $zamtelStatus): PaymentStatus
    {
        return match(strtolower($zamtelStatus)) {
            'completed', 'success', 'successful' => PaymentStatus::COMPLETED,
            'failed', 'error' => PaymentStatus::FAILED,
            'cancelled', 'canceled' => PaymentStatus::CANCELLED,
            'pending' => PaymentStatus::PENDING,
            'processing' => PaymentStatus::PROCESSING,
            default => PaymentStatus::PENDING,
        };
    }
}
