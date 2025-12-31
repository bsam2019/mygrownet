<?php

namespace App\Domain\GrowBuilder\Payment\Gateways;

use App\Domain\GrowBuilder\Payment\DTOs\PaymentRequest;
use App\Domain\GrowBuilder\Payment\DTOs\PaymentResponse;
use App\Domain\GrowBuilder\Payment\DTOs\RefundRequest;
use App\Domain\GrowBuilder\Payment\DTOs\RefundResponse;
use App\Domain\GrowBuilder\Payment\Enums\PaymentStatus;

class FlutterwaveGateway extends AbstractPaymentGateway
{
    private string $baseUrl;

    public function __construct(array $credentials, bool $testMode = false)
    {
        parent::__construct($credentials, $testMode);
        $this->baseUrl = $testMode 
            ? 'https://api.flutterwave.com/v3'
            : 'https://api.flutterwave.com/v3';
    }

    public function initiatePayment(PaymentRequest $request): PaymentResponse
    {
        try {
            $this->logActivity('Initiating Flutterwave payment', $request->toArray());

            $response = $this->makeRequest(
                'post',
                "{$this->baseUrl}/payments",
                [
                    'tx_ref' => $request->reference,
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'redirect_url' => $request->returnUrl,
                    'payment_options' => 'mobilemoneyzambia,card,banktransfer',
                    'customer' => [
                        'email' => $request->customerEmail ?? 'customer@example.com',
                        'phonenumber' => $this->formatPhoneNumber($request->phoneNumber),
                        'name' => $request->customerName ?? 'Customer',
                    ],
                    'customizations' => [
                        'title' => $request->description,
                        'description' => $request->description,
                    ],
                    'meta' => $request->metadata,
                ],
                [
                    'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
                    'Content-Type' => 'application/json',
                ]
            );

            if ($response['success'] && isset($response['data']['data']['link'])) {
                return new PaymentResponse(
                    success: true,
                    status: PaymentStatus::PENDING,
                    transactionReference: $request->reference,
                    externalReference: $response['data']['data']['id'] ?? null,
                    message: 'Payment initiated successfully',
                    rawResponse: $response['data'],
                    checkoutUrl: $response['data']['data']['link'],
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
            $this->logError('Flutterwave payment initiation', $e, $request->toArray());

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
                "{$this->baseUrl}/transactions/verify_by_reference?tx_ref={$transactionReference}",
                [],
                [
                    'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
                ]
            );

            if ($response['success'] && isset($response['data']['data'])) {
                $data = $response['data']['data'];
                $status = $this->mapStatus($data['status'] ?? 'pending');

                return new PaymentResponse(
                    success: $status === PaymentStatus::COMPLETED,
                    status: $status,
                    transactionReference: $transactionReference,
                    externalReference: $data['id'] ?? null,
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
            $this->logError('Flutterwave payment verification', $e, ['reference' => $transactionReference]);

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
                "{$this->baseUrl}/transactions/{$request->transactionReference}/refund",
                [
                    'amount' => $request->amount,
                ],
                [
                    'Authorization' => 'Bearer ' . $this->credentials['secret_key'],
                    'Content-Type' => 'application/json',
                ]
            );

            if ($response['success']) {
                return new RefundResponse(
                    success: true,
                    refundReference: $response['data']['data']['id'] ?? '',
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
            $this->logError('Flutterwave refund processing', $e, $request->toArray());

            return new RefundResponse(
                success: false,
                refundReference: '',
                message: $e->getMessage(),
            );
        }
    }

    public function getName(): string
    {
        return 'Flutterwave';
    }

    public function validateConfiguration(array $credentials): array
    {
        $errors = [];

        if (empty($credentials['public_key'])) {
            $errors[] = 'Public key is required';
        }

        if (empty($credentials['secret_key'])) {
            $errors[] = 'Secret key is required';
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
                'name' => 'public_key',
                'label' => 'Public Key',
                'type' => 'text',
                'required' => true,
                'description' => 'Your Flutterwave public key',
            ],
            [
                'name' => 'secret_key',
                'label' => 'Secret Key',
                'type' => 'password',
                'required' => true,
                'description' => 'Your Flutterwave secret key',
            ],
        ];
    }

    public function supportsTestMode(): bool
    {
        return true;
    }

    private function mapStatus(string $flutterwaveStatus): PaymentStatus
    {
        return match(strtolower($flutterwaveStatus)) {
            'successful', 'success' => PaymentStatus::COMPLETED,
            'failed' => PaymentStatus::FAILED,
            'cancelled' => PaymentStatus::CANCELLED,
            'pending' => PaymentStatus::PENDING,
            default => PaymentStatus::PROCESSING,
        };
    }
}
