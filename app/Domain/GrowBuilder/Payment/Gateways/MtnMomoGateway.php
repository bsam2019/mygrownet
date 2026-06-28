<?php

namespace App\Domain\GrowBuilder\Payment\Gateways;

use App\Domain\GrowBuilder\Payment\DTOs\PaymentRequest;
use App\Domain\GrowBuilder\Payment\DTOs\PaymentResponse;
use App\Domain\GrowBuilder\Payment\DTOs\RefundRequest;
use App\Domain\GrowBuilder\Payment\DTOs\RefundResponse;
use App\Domain\GrowBuilder\Payment\Enums\PaymentStatus;
use Illuminate\Support\Facades\Cache;

class MtnMomoGateway extends AbstractPaymentGateway
{
    private string $baseUrl;

    public function __construct(array $credentials, bool $testMode = false)
    {
        parent::__construct($credentials, $testMode);
        $this->baseUrl = $testMode 
            ? 'https://sandbox.momodeveloper.mtn.com'
            : 'https://proxy.momoapi.mtn.com';
    }

    public function initiatePayment(PaymentRequest $request): PaymentResponse
    {
        try {
            $this->logActivity('Initiating MTN payment', $request->toArray());
            
            $accessToken = $this->getAccessToken();
            $referenceId = $request->reference;

            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/collection/v1_0/requesttopay",
                [
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'externalId' => $referenceId,
                    'payer' => [
                        'partyIdType' => 'MSISDN',
                        'partyId' => $this->formatPhoneNumber($request->phoneNumber),
                    ],
                    'payerMessage' => $request->description,
                    'payeeNote' => $request->description,
                ],
                [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'X-Reference-Id' => $referenceId,
                    'X-Target-Environment' => $testMode ? 'sandbox' : 'production',
                    'Ocp-Apim-Subscription-Key' => $this->credentials['subscription_key'],
                    'Content-Type' => 'application/json',
                ]
            );

            if ($response['success'] && $response['status'] === 202) {
                return new PaymentResponse(
                    success: true,
                    status: PaymentStatus::PENDING,
                    transactionReference: $referenceId,
                    externalReference: $referenceId,
                    message: 'Payment initiated successfully',
                    rawResponse: $response['data'],
                );
            }

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $referenceId,
                message: $response['data']['message'] ?? 'Payment initiation failed',
                rawResponse: $response['data'],
            );

        } catch (\Exception $e) {
            $this->logError('MTN payment initiation', $e, $request->toArray());

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
                "{$this->baseUrl}/collection/v1_0/requesttopay/{$transactionReference}",
                [],
                [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'X-Target-Environment' => $this->testMode ? 'sandbox' : 'production',
                    'Ocp-Apim-Subscription-Key' => $this->credentials['subscription_key'],
                ]
            );

            if ($response['success']) {
                $status = $this->mapStatus($response['data']['status'] ?? 'PENDING');

                return new PaymentResponse(
                    success: $status === PaymentStatus::COMPLETED,
                    status: $status,
                    transactionReference: $transactionReference,
                    externalReference: $response['data']['financialTransactionId'] ?? null,
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
            $this->logError('MTN payment verification', $e, ['reference' => $transactionReference]);

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
        // MTN MoMo doesn't support automated refunds via API
        // Refunds must be processed manually through MTN support
        return new RefundResponse(
            success: false,
            refundReference: '',
            message: 'MTN MoMo refunds must be processed manually through MTN support',
        );
    }

    public function getName(): string
    {
        return 'MTN Mobile Money';
    }

    public function validateConfiguration(array $credentials): array
    {
        $errors = [];

        if (empty($credentials['subscription_key'])) {
            $errors[] = 'Subscription key is required';
        }

        if (empty($credentials['user_id'])) {
            $errors[] = 'User ID is required';
        }

        if (empty($credentials['api_key'])) {
            $errors[] = 'API key is required';
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
                'name' => 'subscription_key',
                'label' => 'Subscription Key',
                'type' => 'password',
                'required' => true,
                'description' => 'Your MTN MoMo subscription key',
            ],
            [
                'name' => 'user_id',
                'label' => 'User ID',
                'type' => 'text',
                'required' => true,
                'description' => 'Your MTN MoMo user ID',
            ],
            [
                'name' => 'api_key',
                'label' => 'API Key',
                'type' => 'password',
                'required' => true,
                'description' => 'Your MTN MoMo API key',
            ],
        ];
    }

    public function supportsTestMode(): bool
    {
        return true;
    }

    private function getAccessToken(): string
    {
        $cacheKey = 'mtn_momo_token_' . md5($this->credentials['subscription_key']);

        return Cache::remember($cacheKey, 3600, function () {
            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/collection/token/",
                [],
                [
                    'Authorization' => 'Basic ' . base64_encode(
                        $this->credentials['user_id'] . ':' . $this->credentials['api_key']
                    ),
                    'Ocp-Apim-Subscription-Key' => $this->credentials['subscription_key'],
                ]
            );

            if ($response['success']) {
                return $response['data']['access_token'];
            }

            throw new \Exception('Failed to get MTN access token');
        });
    }

    private function mapStatus(string $mtnStatus): PaymentStatus
    {
        return match($mtnStatus) {
            'SUCCESSFUL' => PaymentStatus::COMPLETED,
            'FAILED' => PaymentStatus::FAILED,
            'PENDING' => PaymentStatus::PENDING,
            default => PaymentStatus::PROCESSING,
        };
    }
}
