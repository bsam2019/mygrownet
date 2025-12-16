<?php

declare(strict_types=1);

namespace App\Domain\Payment\Gateways;

use App\Domain\Payment\DTOs\CollectionRequest;
use App\Domain\Payment\DTOs\CollectionResponse;
use App\Domain\Payment\DTOs\DisbursementRequest;
use App\Domain\Payment\DTOs\DisbursementResponse;
use App\Domain\Payment\Enums\TransactionStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PawapayGateway extends AbstractPaymentGateway
{
    private string $apiToken;
    private string $webhookSecret;

    public function __construct()
    {
        $this->apiToken = config('services.pawapay.api_token', '');
        $this->webhookSecret = config('services.pawapay.webhook_secret', '');
        $this->baseUrl = config('services.pawapay.base_url', 'https://api.pawapay.io');

        $this->headers = [
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function getIdentifier(): string
    {
        return 'pawapay';
    }

    public function getName(): string
    {
        return 'PawaPay';
    }

    public function getSupportedCountries(): array
    {
        return ['ZM', 'TZ', 'UG', 'KE', 'GH', 'CI', 'SN', 'CM', 'RW', 'BJ', 'BF', 'MW'];
    }

    public function getSupportedCurrencies(): array
    {
        return ['ZMW', 'TZS', 'UGX', 'KES', 'GHS', 'XOF', 'XAF', 'RWF', 'MWK', 'USD'];
    }

    public function supportsCollections(): bool
    {
        return true;
    }

    public function supportsDisbursements(): bool
    {
        return true;
    }

    public function getSupportedProviders(): array
    {
        return [
            'ZM' => ['MTN_MOMO_ZMB', 'AIRTEL_ZMB'],
            'TZ' => ['VODACOM_TZN', 'TIGO_TZN', 'AIRTEL_TZN'],
            'UG' => ['MTN_MOMO_UGA', 'AIRTEL_UGA'],
            'KE' => ['MPESA_KEN'],
            'GH' => ['MTN_MOMO_GHA', 'VODAFONE_GHA', 'AIRTELTIGO_GHA'],
        ];
    }

    /**
     * Get HTTP client with SSL handling for development
     */
    private function getHttpClient(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
        ]);

        // Disable SSL verification in local/development environment
        // WARNING: Never disable SSL verification in production!
        if (app()->environment('local', 'development', 'testing')) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Check if mock mode is enabled for testing
     */
    private function isMockMode(): bool
    {
        return config('services.pawapay.mock_mode', false) 
            || empty($this->apiToken) 
            || str_contains($this->apiToken, 'your_');
    }

    public function collect(CollectionRequest $request): CollectionResponse
    {
        $depositId = $this->generateTransactionId();

        // Mock mode for testing without real API calls
        if ($this->isMockMode()) {
            Log::info('PawaPay MOCK collection', [
                'phone' => $request->phoneNumber,
                'amount' => $request->amount,
                'mock_mode' => true,
            ]);

            return new CollectionResponse(
                success: true,
                transactionId: $depositId,
                status: TransactionStatus::PENDING,
                providerReference: 'MOCK-PP-' . substr($depositId, 0, 8),
                message: '[MOCK] Payment request sent. In production, user would receive mobile money prompt.'
            );
        }

        try {
            $response = $this->getHttpClient()->post("{$this->baseUrl}/deposits", [
                'depositId' => $depositId,
                'amount' => (string) $request->amount,
                'currency' => $request->currency,
                'correspondent' => $this->mapProviderToCorrespondent($request->provider),
                'payer' => [
                    'type' => 'MSISDN',
                    'address' => [
                        'value' => $this->formatPhoneNumber($request->phoneNumber, 'ZM'),
                    ],
                ],
                'customerTimestamp' => now()->toIso8601String(),
                'statementDescription' => $request->description ?? 'Payment',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return new CollectionResponse(
                    success: true,
                    transactionId: $depositId,
                    providerReference: $data['depositId'] ?? $depositId,
                    status: $this->mapStatus($data['status'] ?? 'SUBMITTED'),
                    message: 'Collection initiated successfully'
                );
            }

            Log::error('PawaPay collection failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return new CollectionResponse(
                success: false,
                transactionId: $depositId,
                status: TransactionStatus::FAILED,
                message: $response->json('message') ?? 'Collection failed'
            );
        } catch (\Exception $e) {
            Log::error('PawaPay collection exception', ['error' => $e->getMessage()]);
            return new CollectionResponse(
                success: false,
                transactionId: '',
                status: TransactionStatus::FAILED,
                message: $e->getMessage()
            );
        }
    }

    public function disburse(DisbursementRequest $request): DisbursementResponse
    {
        $payoutId = $this->generateTransactionId();

        // Mock mode for testing without real API calls
        if ($this->isMockMode()) {
            Log::info('PawaPay MOCK disbursement', [
                'phone' => $request->phoneNumber,
                'amount' => $request->amount,
                'mock_mode' => true,
            ]);

            return new DisbursementResponse(
                success: true,
                transactionId: $payoutId,
                status: TransactionStatus::PENDING,
                providerReference: 'MOCK-PP-' . substr($payoutId, 0, 8),
                message: '[MOCK] Payout initiated. In production, recipient would receive mobile money.'
            );
        }

        try {
            $response = $this->getHttpClient()->post("{$this->baseUrl}/payouts", [
                'payoutId' => $payoutId,
                'amount' => (string) $request->amount,
                'currency' => $request->currency,
                'correspondent' => $this->mapProviderToCorrespondent($request->provider),
                'recipient' => [
                    'type' => 'MSISDN',
                    'address' => [
                        'value' => $this->formatPhoneNumber($request->phoneNumber, 'ZM'),
                    ],
                ],
                'customerTimestamp' => now()->toIso8601String(),
                'statementDescription' => $request->description ?? 'Payout',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return new DisbursementResponse(
                    success: true,
                    transactionId: $payoutId,
                    providerReference: $data['payoutId'] ?? $payoutId,
                    status: $this->mapStatus($data['status'] ?? 'SUBMITTED'),
                    message: 'Disbursement initiated successfully'
                );
            }

            Log::error('PawaPay disbursement failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return new DisbursementResponse(
                success: false,
                transactionId: $payoutId,
                status: TransactionStatus::FAILED,
                message: $response->json('message') ?? 'Disbursement failed'
            );
        } catch (\Exception $e) {
            Log::error('PawaPay disbursement exception', ['error' => $e->getMessage()]);
            return new DisbursementResponse(
                success: false,
                transactionId: '',
                status: TransactionStatus::FAILED,
                message: $e->getMessage()
            );
        }
    }

    public function checkCollectionStatus(string $transactionId): TransactionStatus
    {
        try {
            $response = $this->getHttpClient()->get("{$this->baseUrl}/deposits/{$transactionId}");

            if ($response->successful()) {
                return $this->mapStatus($response->json('status') ?? 'UNKNOWN');
            }

            return TransactionStatus::FAILED;
        } catch (\Exception $e) {
            Log::error('PawaPay status check failed', ['error' => $e->getMessage()]);
            return TransactionStatus::FAILED;
        }
    }

    public function checkDisbursementStatus(string $transactionId): TransactionStatus
    {
        try {
            $response = $this->getHttpClient()->get("{$this->baseUrl}/payouts/{$transactionId}");

            if ($response->successful()) {
                return $this->mapStatus($response->json('status') ?? 'UNKNOWN');
            }

            return TransactionStatus::FAILED;
        } catch (\Exception $e) {
            Log::error('PawaPay payout status check failed', ['error' => $e->getMessage()]);
            return TransactionStatus::FAILED;
        }
    }

    public function supportsProvider(string $provider): bool
    {
        $allProviders = [];
        foreach ($this->getSupportedProviders() as $providers) {
            $allProviders = array_merge($allProviders, $providers);
        }
        return in_array(strtoupper($provider), $allProviders);
    }

    protected function mapStatus(string $status): TransactionStatus
    {
        return match (strtoupper($status)) {
            'COMPLETED', 'ACCEPTED' => TransactionStatus::COMPLETED,
            'SUBMITTED', 'PENDING' => TransactionStatus::PENDING,
            'FAILED', 'REJECTED' => TransactionStatus::FAILED,
            'CANCELLED' => TransactionStatus::CANCELLED,
            default => TransactionStatus::PENDING,
        };
    }

    protected function mapProviderToCorrespondent(string $provider): string
    {
        $mapping = [
            'mtn' => 'MTN_MOMO_ZMB',
            'airtel' => 'AIRTEL_ZMB',
            'mtn_zm' => 'MTN_MOMO_ZMB',
            'airtel_zm' => 'AIRTEL_ZMB',
        ];

        return $mapping[strtolower($provider)] ?? strtoupper($provider);
    }
}