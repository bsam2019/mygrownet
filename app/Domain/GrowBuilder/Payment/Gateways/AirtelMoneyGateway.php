<?php

namespace App\Domain\GrowBuilder\Payment\Gateways;

use App\Domain\GrowBuilder\Payment\DTOs\PaymentRequest;
use App\Domain\GrowBuilder\Payment\DTOs\PaymentResponse;
use App\Domain\GrowBuilder\Payment\DTOs\RefundRequest;
use App\Domain\GrowBuilder\Payment\DTOs\RefundResponse;
use App\Domain\GrowBuilder\Payment\Enums\PaymentStatus;
use Illuminate\Support\Facades\Cache;

class AirtelMoneyGateway extends AbstractPaymentGateway
{
    private string $baseUrl;

    public function __construct(array $credentials, bool $testMode = false)
    {
        parent::__construct($credentials, $testMode);
        $this->baseUrl = $testMode 
            ? 'https://openapiuat.airtel.africa'
            : 'https://openapi.airtel.africa';
    }

    public function initiatePayment(PaymentRequest $request): PaymentResponse
    {
        try {
            $this->logActivity('Initiating Airtel payment', $request->toArray());
            
            $accessToken = $this->getAccessToken();

            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/merchant/v1/payments/",
                [
                    'reference' => $request->reference,
                    'subscriber' => [
                        'country' => 'ZM',
                        'currency' => $request->currency,
                        'msisdn' => $this->formatPhoneNumber($request->phoneNumber),
                    ],
                    'transaction' => [
                        'amount' => $request->amount,
                        'country' => 'ZM',
                        'currency' => $request->currency,
                        'id' => $request->reference,
                    ],
                ],
                [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'X-Country' => 'ZM',
                    'X-Currency' => $request->currency,
                ]
            );

            if ($response['success']) {
                $data = $response['data']['data'] ?? $response['data'];
                
                return new PaymentResponse(
                    success: true,
                    status: PaymentStatus::PENDING,
                    transactionReference: $request->reference,
                    externalReference: $data['transaction']['id'] ?? null,
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
            $this->logError('Airtel payment initiation', $e, $request->toArray());

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
                "{$this->baseUrl}/standard/v1/payments/{$transactionReference}",
                [],
                [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'X-Country' => 'ZM',
                    'X-Currency' => 'ZMW',
                ]
            );

            if ($response['success']) {
                $data = $response['data']['data'] ?? $response['data'];
                $status = $this->mapStatus($data['transaction']['status'] ?? 'PENDING');

                return new PaymentResponse(
                    success: $status === PaymentStatus::COMPLETED,
                    status: $status,
                    transactionReference: $transactionReference,
                    externalReference: $data['transaction']['airtel_money_id'] ?? null,
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
            $this->logError('Airtel payment verification', $e, ['reference' => $transactionReference]);

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
            $accessToken = $this->getAccessToken();

            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/standard/v1/payments/refund",
                [
                    'transaction' => [
                        'airtel_money_id' => $request->transactionReference,
                    ],
                    'refund' => [
                        'amount' => $request->amount,
                        'currency' => 'ZMW',
                    ],
                ],
                [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'X-Country' => 'ZM',
                    'X-Currency' => 'ZMW',
                ]
            );

            if ($response['success']) {
                return new RefundResponse(
                    success: true,
                    refundReference: $response['data']['data']['transaction']['id'] ?? '',
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
            $this->logError('Airtel refund processing', $e, $request->toArray());

            return new RefundResponse(
                success: false,
                refundReference: '',
                message: $e->getMessage(),
            );
        }
    }

    public function getName(): string
    {
        return 'Airtel Money';
    }

    public function validateConfiguration(array $credentials): array
    {
        $errors = [];

        if (empty($credentials['client_id'])) {
            $errors[] = 'Client ID is required';
        }

        if (empty($credentials['client_secret'])) {
            $errors[] = 'Client secret is required';
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
                'name' => 'client_id',
                'label' => 'Client ID',
                'type' => 'text',
                'required' => true,
                'description' => 'Your Airtel Money client ID',
            ],
            [
                'name' => 'client_secret',
                'label' => 'Client Secret',
                'type' => 'password',
                'required' => true,
                'description' => 'Your Airtel Money client secret',
            ],
        ];
    }

    public function supportsTestMode(): bool
    {
        return true;
    }

    private function getAccessToken(): string
    {
        $cacheKey = 'airtel_money_token_' . md5($this->credentials['client_id']);

        return Cache::remember($cacheKey, 3600, function () {
            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/auth/oauth2/token",
                [
                    'client_id' => $this->credentials['client_id'],
                    'client_secret' => $this->credentials['client_secret'],
                    'grant_type' => 'client_credentials',
                ],
                [
                    'Content-Type' => 'application/json',
                ]
            );

            if ($response['success']) {
                return $response['data']['access_token'];
            }

            throw new \Exception('Failed to get Airtel access token');
        });
    }

    private function mapStatus(string $airtelStatus): PaymentStatus
    {
        return match(strtoupper($airtelStatus)) {
            'TS', 'SUCCESS', 'SUCCESSFUL' => PaymentStatus::COMPLETED,
            'TF', 'FAILED' => PaymentStatus::FAILED,
            'TA', 'PENDING' => PaymentStatus::PENDING,
            'TIP', 'PROCESSING' => PaymentStatus::PROCESSING,
            default => PaymentStatus::PENDING,
        };
    }
}
