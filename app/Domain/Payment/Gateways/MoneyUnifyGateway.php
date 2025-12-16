<?php

declare(strict_types=1);

namespace App\Domain\Payment\Gateways;

use App\Domain\Payment\DTOs\CollectionRequest;
use App\Domain\Payment\DTOs\CollectionResponse;
use App\Domain\Payment\DTOs\DisbursementRequest;
use App\Domain\Payment\DTOs\DisbursementResponse;
use App\Domain\Payment\Enums\TransactionStatus;
use App\Domain\Payment\Gateways\MoneyUnifyClient;
use Illuminate\Support\Facades\Log;

/**
 * MoneyUnify Payment Gateway
 * 
 * Uses official package: blessedjasonmwanza/moneyunify
 * API Docs: https://moneyunify.com/developers
 * GitHub: https://github.com/blessedjasonmwanza/MoneyUnify
 * 
 * Supports: MTN, Airtel, Zamtel in Zambia
 * 
 * Note: MoneyUnify uses a single MUID (MoneyUnify ID) for authentication.
 * The package auto-detects the mobile network from the phone number.
 */
class MoneyUnifyGateway extends AbstractPaymentGateway
{
    private string $muid;
    private ?MoneyUnifyClient $client = null;
    private bool $mockMode = false;

    public function __construct()
    {
        // MoneyUnify uses MUID (MoneyUnify ID) - stored as api_key in config
        $this->muid = config('services.moneyunify.muid', config('services.moneyunify.api_key', ''));
        $this->baseUrl = config('services.moneyunify.base_url', 'https://api.moneyunify.com/v2');

        // Enable mock mode if MUID is not configured or is a placeholder
        $this->mockMode = empty($this->muid) 
            || str_contains($this->muid, 'your_') 
            || str_contains($this->muid, '_here');

        $this->headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Get or create MoneyUnify client instance
     * Uses custom client that handles SSL issues in development
     */
    private function getClient(): MoneyUnifyClient
    {
        if ($this->client === null) {
            $this->client = new MoneyUnifyClient($this->muid);
        }
        return $this->client;
    }

    public function getIdentifier(): string
    {
        return 'moneyunify';
    }

    public function getName(): string
    {
        return 'MoneyUnify';
    }

    public function getSupportedCountries(): array
    {
        return ['ZM'];
    }

    public function getSupportedCurrencies(): array
    {
        return ['ZMW'];
    }

    public function supportsCollections(): bool
    {
        return true;
    }

    public function supportsDisbursements(): bool
    {
        // MoneyUnify package supports disbursements via settleFunds
        // but it's for settling your balance, not arbitrary payouts
        return false;
    }


    /**
     * Check if mock mode is enabled for testing
     */
    private function isMockMode(): bool
    {
        return $this->mockMode || config('services.moneyunify.mock_mode', false);
    }

    /**
     * Collect payment from customer (Request to Pay)
     * 
     * Uses MoneyUnify package's requestPayment method
     * The package auto-detects the network from the phone number
     * 
     * @param CollectionRequest $request
     * @return CollectionResponse
     */
    public function collect(CollectionRequest $request): CollectionResponse
    {
        $transactionId = $this->generateTransactionId();

        // Mock mode for testing without real API calls
        if ($this->isMockMode()) {
            Log::info('MoneyUnify MOCK collection', [
                'phone' => $request->phoneNumber,
                'amount' => $request->amount,
                'mock_mode' => true,
            ]);

            return new CollectionResponse(
                success: true,
                transactionId: $transactionId,
                status: TransactionStatus::PENDING,
                providerReference: 'MOCK-' . substr($transactionId, 0, 8),
                message: '[MOCK] Payment request sent. In production, user would receive USSD prompt.'
            );
        }

        try {
            $phone = $this->formatPhoneNumber($request->phoneNumber, 'ZM');
            
            Log::info('MoneyUnify collection request', [
                'phone' => $phone,
                'amount' => $request->amount,
                'reference' => $request->reference ?? $transactionId,
            ]);

            // Use the official MoneyUnify package
            $client = $this->getClient();
            $response = $client->requestPayment(
                $phone,
                (string) $request->amount
            );

            Log::info('MoneyUnify collection response', [
                'message' => $response->message ?? null,
                'isError' => $response->isError ?? null,
                'data' => $response->data ?? null,
            ]);

            // MoneyUnify returns stdClass with: message, isError, data
            if (!($response->isError ?? true)) {
                $data = $response->data ?? null;
                return new CollectionResponse(
                    success: true,
                    transactionId: $data->reference ?? $transactionId,
                    status: TransactionStatus::PENDING,
                    providerReference: $data->reference ?? null,
                    message: $response->message ?? 'Payment request sent. Please approve on your phone.'
                );
            }

            Log::error('MoneyUnify collection failed', [
                'message' => $response->message ?? 'Unknown error',
                'console' => $response->console ?? null,
            ]);

            return new CollectionResponse(
                success: false,
                transactionId: $transactionId,
                status: TransactionStatus::FAILED,
                message: $response->message ?? 'Collection failed'
            );
        } catch (\Exception $e) {
            Log::error('MoneyUnify collection exception', ['error' => $e->getMessage()]);
            return new CollectionResponse(
                success: false,
                transactionId: '',
                status: TransactionStatus::FAILED,
                message: $e->getMessage()
            );
        }
    }

    /**
     * Disburse payment to customer (Send Money)
     * 
     * Note: MoneyUnify package doesn't support arbitrary disbursements.
     * It only supports settleFunds which moves your balance to a specified account.
     * For disbursements, use PawaPay gateway instead.
     */
    public function disburse(DisbursementRequest $request): DisbursementResponse
    {
        return new DisbursementResponse(
            success: false,
            transactionId: '',
            status: TransactionStatus::FAILED,
            message: 'MoneyUnify does not support direct disbursements. Use PawaPay for payouts.'
        );
    }

    /**
     * Check collection/payment status
     * 
     * Uses MoneyUnify package's verifyPayment method
     */
    public function checkCollectionStatus(string $transactionId): TransactionStatus
    {
        try {
            $client = $this->getClient();
            $response = $client->verifyPayment($transactionId);

            Log::info('MoneyUnify status check response', [
                'transaction_id' => $transactionId,
                'message' => $response->message ?? null,
                'isError' => $response->isError ?? null,
                'data' => $response->data ?? null,
            ]);

            if (!($response->isError ?? true)) {
                $data = $response->data ?? null;
                $status = $data->status ?? 'pending';
                return $this->mapStatus($status);
            }

            return TransactionStatus::PENDING;
        } catch (\Exception $e) {
            Log::error('MoneyUnify status check failed', ['error' => $e->getMessage()]);
            return TransactionStatus::FAILED;
        }
    }

    public function checkDisbursementStatus(string $transactionId): TransactionStatus
    {
        // MoneyUnify doesn't support disbursements
        return TransactionStatus::FAILED;
    }

    public function isConfigured(): bool
    {
        return !empty($this->muid);
    }

    protected function mapStatus(string $status): TransactionStatus
    {
        return match (strtolower($status)) {
            'completed', 'successful', 'success', 'paid' => TransactionStatus::COMPLETED,
            'pending', 'initiated', 'waiting' => TransactionStatus::PENDING,
            'processing' => TransactionStatus::PROCESSING,
            'failed', 'failure', 'declined' => TransactionStatus::FAILED,
            'cancelled', 'canceled' => TransactionStatus::CANCELLED,
            'expired', 'timeout' => TransactionStatus::EXPIRED,
            default => TransactionStatus::PENDING,
        };
    }

    /**
     * Verify webhook signature from MoneyUnify
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = config('services.moneyunify.webhook_secret', '');
        if (empty($webhookSecret)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }
}
