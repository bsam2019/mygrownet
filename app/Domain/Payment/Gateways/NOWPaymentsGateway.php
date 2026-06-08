<?php

declare(strict_types=1);

namespace App\Domain\Payment\Gateways;

use App\Domain\Payment\DTOs\CollectionRequest;
use App\Domain\Payment\DTOs\CollectionResponse;
use App\Domain\Payment\DTOs\CryptoPaymentRequest;
use App\Domain\Payment\DTOs\DisbursementRequest;
use App\Domain\Payment\DTOs\DisbursementResponse;
use App\Domain\Payment\Enums\TransactionStatus;
use App\Domain\Payment\Services\CurrencyConversionService;
use Illuminate\Support\Facades\Log;

/**
 * NOWPayments Cryptocurrency Payment Gateway
 * 
 * API Docs: https://documenter.getpostman.com/view/7907941/S1a32n38
 * Website: https://nowpayments.io
 * 
 * Supports: 300+ cryptocurrencies including BTC, ETH, USDT, LTC, XRP, etc.
 * 
 * Features:
 * - Accept crypto payments
 * - Automatic conversion to fiat
 * - Low fees (0.5% - 1%)
 * - Instant settlements
 * - Mass payouts support
 * 
 * Configuration:
 * - API Key: Required for all requests
 * - IPN Secret: For webhook signature verification
 * - Sandbox mode: For testing
 */
class NOWPaymentsGateway extends AbstractPaymentGateway
{
    private ?string $apiKey;
    private ?string $ipnSecret;
    private bool $sandboxMode;
    private CurrencyConversionService $currencyService;

    public function __construct()
    {
        $this->apiKey = config('services.nowpayments.api_key');
        $this->ipnSecret = config('services.nowpayments.ipn_secret');
        $this->sandboxMode = config('services.nowpayments.sandbox', false);
        $this->currencyService = new CurrencyConversionService();
        
        // Use sandbox or production URL
        $this->baseUrl = $this->sandboxMode
            ? 'https://api-sandbox.nowpayments.io/v1'
            : 'https://api.nowpayments.io/v1';

        $this->headers = [
            'x-api-key' => $this->apiKey ?? '',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function getIdentifier(): string
    {
        return 'nowpayments';
    }

    public function getName(): string
    {
        return 'NOWPayments (Crypto)';
    }

    public function getSupportedCountries(): array
    {
        // Cryptocurrency payments are global
        return ['*']; // All countries
    }

    public function getSupportedCurrencies(): array
    {
        // Fiat currencies that can be converted from crypto
        // NOWPayments supports 300+ cryptocurrencies
        return [
            'USD', 'EUR', 'GBP', 'ZMW', 'ZAR', 'KES', 'NGN', 'GHS',
            // Cryptocurrencies
            'BTC', 'ETH', 'USDT', 'USDC', 'LTC', 'XRP', 'BNB', 'ADA', 'DOT', 'DOGE',
        ];
    }

    public function supportsCollections(): bool
    {
        return true;
    }

    public function supportsDisbursements(): bool
    {
        return true; // NOWPayments supports mass payouts
    }

    /**
     * Collect payment from customer (Create Invoice/Payment)
     * 
     * Creates a cryptocurrency payment invoice that customer can pay
     * 
     * @param CollectionRequest $request
     * @return CollectionResponse
     */
    public function collect(CollectionRequest $request): CollectionResponse
    {
        $transactionId = $this->generateTransactionId();

        try {
            // Convert to USD if not already (NOWPayments works best with USD/EUR)
            $amount = $request->amount;
            $currency = strtoupper($request->currency);
            
            if (!in_array($currency, ['USD', 'EUR'])) {
                // Convert to USD
                $convertedAmount = $this->currencyService->convert($amount, $currency, 'USD');
                
                if ($convertedAmount === null) {
                    Log::warning('Currency conversion failed, using original amount', [
                        'from' => $currency,
                        'to' => 'USD',
                        'amount' => $amount,
                    ]);
                    // Fallback: use original amount with USD
                    $convertedAmount = $amount;
                }
                
                Log::info('Currency converted for NOWPayments', [
                    'original' => "{$amount} {$currency}",
                    'converted' => "{$convertedAmount} USD",
                ]);
                
                $amount = $convertedAmount;
                $currency = 'USD';
            }
            
            // Get available currencies first
            $availableCurrencies = $this->getAvailableCurrencies();
            
            if (empty($availableCurrencies)) {
                return new CollectionResponse(
                    success: false,
                    transactionId: $transactionId,
                    status: TransactionStatus::FAILED,
                    message: 'Unable to fetch available cryptocurrencies'
                );
            }

            // Determine payment currency (crypto)
            $payCurrency = $this->determinePayCurrency($currency, $availableCurrencies);

            // Create payment
            $payload = [
                'price_amount' => $amount,
                'price_currency' => $currency,
                'pay_currency' => $payCurrency,
                'order_id' => $request->reference ?? $transactionId,
                'order_description' => $request->description ?? 'Payment to MyGrowNet',
                'ipn_callback_url' => route('webhooks.nowpayments'),
            ];
            
            // Add optional URLs if routes exist
            try {
                $payload['success_url'] = route('payment.success');
                $payload['cancel_url'] = route('payment.cancel');
            } catch (\Exception $e) {
                // Routes not defined, skip them (NOWPayments will use default behavior)
                Log::info('NOWPayments: Success/cancel routes not defined, using defaults');
            }

            // Add customer info if available
            if ($request->customerEmail) {
                $payload['payer_email'] = $request->customerEmail;
            }

            Log::info('NOWPayments collection request', [
                'order_id' => $payload['order_id'],
                'amount' => $amount,
                'currency' => $currency,
                'original_amount' => $request->amount,
                'original_currency' => $request->currency,
                'pay_currency' => $payCurrency,
            ]);

            $response = $this->request('POST', '/invoice', $payload);

            if ($response['success'] && isset($response['data']['id'])) {
                $data = $response['data'];
                
                Log::info('NOWPayments invoice created', [
                    'invoice_id' => $data['id'],
                    'invoice_url' => $data['invoice_url'] ?? null,
                    'pay_address' => $data['pay_address'] ?? null,
                ]);

                return new CollectionResponse(
                    success: true,
                    transactionId: $data['id'],
                    status: $this->mapStatus($data['payment_status'] ?? 'waiting'),
                    providerReference: $data['order_id'] ?? null,
                    message: 'Payment invoice created. Customer can pay with cryptocurrency.',
                    rawResponse: [
                        'invoice_url' => $data['invoice_url'] ?? null,
                        'pay_address' => $data['pay_address'] ?? null,
                        'pay_amount' => $data['pay_amount'] ?? null,
                        'pay_currency' => $data['pay_currency'] ?? null,
                        'price_amount' => $data['price_amount'] ?? null,
                        'price_currency' => $data['price_currency'] ?? null,
                        'original_amount' => $request->amount,
                        'original_currency' => $request->currency,
                        'created_at' => $data['created_at'] ?? null,
                        'expiration_estimate_date' => $data['expiration_estimate_date'] ?? null,
                    ]
                );
            }

            $errorMessage = $response['data']['message'] ?? 'Failed to create payment invoice';
            
            Log::error('NOWPayments collection failed', [
                'error' => $errorMessage,
                'response' => $response['data'],
            ]);

            return new CollectionResponse(
                success: false,
                transactionId: $transactionId,
                status: TransactionStatus::FAILED,
                message: $errorMessage
            );

        } catch (\Exception $e) {
            Log::error('NOWPayments collection exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new CollectionResponse(
                success: false,
                transactionId: $transactionId,
                status: TransactionStatus::FAILED,
                message: 'Payment gateway error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Create a cryptocurrency payment invoice
     * 
     * This is a simplified method specifically for crypto payments
     * that doesn't require phone numbers or mobile money providers
     * 
     * @param CryptoPaymentRequest $request
     * @return CollectionResponse
     */
    public function createCryptoInvoice(CryptoPaymentRequest $request): CollectionResponse
    {
        $transactionId = $this->generateTransactionId();

        try {
            // Convert to USD if not already (NOWPayments works best with USD/EUR)
            $amount = $request->amount;
            $currency = strtoupper($request->currency);
            
            if (!in_array($currency, ['USD', 'EUR'])) {
                // Convert to USD
                $convertedAmount = $this->currencyService->convert($amount, $currency, 'USD');
                
                if ($convertedAmount === null) {
                    Log::warning('Currency conversion failed, using original amount', [
                        'from' => $currency,
                        'to' => 'USD',
                        'amount' => $amount,
                    ]);
                    // Fallback: use original amount with USD
                    $convertedAmount = $amount;
                }
                
                Log::info('Currency converted for NOWPayments', [
                    'original' => "{$amount} {$currency}",
                    'converted' => "{$convertedAmount} USD",
                ]);
                
                $amount = $convertedAmount;
                $currency = 'USD';
            }
            
            // Get available currencies first
            $availableCurrencies = $this->getAvailableCurrencies();
            
            if (empty($availableCurrencies)) {
                return new CollectionResponse(
                    success: false,
                    transactionId: $transactionId,
                    status: TransactionStatus::FAILED,
                    message: 'Unable to fetch available cryptocurrencies'
                );
            }

            // Determine payment currency (crypto) - default to USDT if available
            $payCurrency = $this->determinePayCurrency($currency, $availableCurrencies);

            // Create payment
            $payload = [
                'price_amount' => $amount,
                'price_currency' => $currency,
                'pay_currency' => $payCurrency,
                'order_id' => $request->reference ?? $transactionId,
                'order_description' => $request->description ?? 'Payment to MyGrowNet',
                'ipn_callback_url' => route('webhooks.nowpayments'),
            ];
            
            // Add optional URLs if routes exist
            try {
                $payload['success_url'] = route('payment.success');
                $payload['cancel_url'] = route('payment.cancel');
            } catch (\Exception $e) {
                // Routes not defined, skip them
                Log::info('NOWPayments: Success/cancel routes not defined, using defaults');
            }

            // Note: payer_email is not supported by NOWPayments invoice API
            // Customer email is only collected during payment process

            Log::info('NOWPayments crypto invoice request', [
                'order_id' => $payload['order_id'],
                'amount' => $amount,
                'currency' => $currency,
                'pay_currency' => $payCurrency,
            ]);

            $response = $this->request('POST', '/invoice', $payload);

            if ($response['success'] && isset($response['data']['id'])) {
                $data = $response['data'];
                
                Log::info('NOWPayments crypto invoice created', [
                    'invoice_id' => $data['id'],
                    'invoice_url' => $data['invoice_url'] ?? null,
                    'pay_address' => $data['pay_address'] ?? null,
                ]);

                return new CollectionResponse(
                    success: true,
                    transactionId: $data['id'],
                    status: $this->mapStatus($data['payment_status'] ?? 'waiting'),
                    providerReference: $data['order_id'] ?? null,
                    message: 'Crypto payment invoice created successfully.',
                    rawResponse: [
                        'invoice_url' => $data['invoice_url'] ?? null,
                        'pay_address' => $data['pay_address'] ?? null,
                        'pay_amount' => $data['pay_amount'] ?? null,
                        'pay_currency' => $data['pay_currency'] ?? null,
                        'price_amount' => $data['price_amount'] ?? null,
                        'price_currency' => $data['price_currency'] ?? null,
                        'original_amount' => $request->amount,
                        'original_currency' => $request->currency,
                        'created_at' => $data['created_at'] ?? null,
                        'expiration_estimate_date' => $data['expiration_estimate_date'] ?? null,
                    ]
                );
            }

            $errorMessage = $response['data']['message'] ?? 'Failed to create crypto payment invoice';
            
            Log::error('NOWPayments crypto invoice creation failed', [
                'error' => $errorMessage,
                'response' => $response['data'],
            ]);

            return new CollectionResponse(
                success: false,
                transactionId: $transactionId,
                status: TransactionStatus::FAILED,
                message: $errorMessage
            );

        } catch (\Exception $e) {
            Log::error('NOWPayments crypto invoice exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new CollectionResponse(
                success: false,
                transactionId: $transactionId,
                status: TransactionStatus::FAILED,
                message: 'Payment gateway error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Disburse payment to recipient (Mass Payout)
     * 
     * Send cryptocurrency to recipient's wallet address
     * 
     * @param DisbursementRequest $request
     * @return DisbursementResponse
     */
    public function disburse(DisbursementRequest $request): DisbursementResponse
    {
        $transactionId = $this->generateTransactionId();

        try {
            // For crypto payouts, we need wallet address instead of phone number
            $walletAddress = $request->accountNumber; // Wallet address should be in accountNumber field

            if (empty($walletAddress)) {
                return new DisbursementResponse(
                    success: false,
                    transactionId: $transactionId,
                    status: TransactionStatus::FAILED,
                    message: 'Wallet address is required for cryptocurrency payouts'
                );
            }

            // Determine cryptocurrency to use
            $currency = $this->determineCryptoCurrency($request->currency);

            $payload = [
                'withdrawals' => [
                    [
                        'address' => $walletAddress,
                        'currency' => $currency,
                        'amount' => $request->amount,
                        'ipn_callback_url' => route('webhooks.nowpayments'),
                    ]
                ]
            ];

            Log::info('NOWPayments payout request', [
                'wallet' => $walletAddress,
                'amount' => $request->amount,
                'currency' => $currency,
            ]);

            $response = $this->request('POST', '/payout', $payload);

            if ($response['success'] && isset($response['data']['id'])) {
                $data = $response['data'];
                
                Log::info('NOWPayments payout created', [
                    'payout_id' => $data['id'],
                    'batch_withdrawal_id' => $data['batch_withdrawal_id'] ?? null,
                ]);

                return new DisbursementResponse(
                    success: true,
                    transactionId: $data['id'],
                    status: TransactionStatus::PROCESSING,
                    providerReference: $data['batch_withdrawal_id'] ?? null,
                    message: 'Cryptocurrency payout initiated'
                );
            }

            $errorMessage = $response['data']['message'] ?? 'Failed to create payout';
            
            Log::error('NOWPayments payout failed', [
                'error' => $errorMessage,
                'response' => $response['data'],
            ]);

            return new DisbursementResponse(
                success: false,
                transactionId: $transactionId,
                status: TransactionStatus::FAILED,
                message: $errorMessage
            );

        } catch (\Exception $e) {
            Log::error('NOWPayments payout exception', [
                'error' => $e->getMessage(),
            ]);

            return new DisbursementResponse(
                success: false,
                transactionId: $transactionId,
                status: TransactionStatus::FAILED,
                message: 'Payout error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Check collection/payment status
     */
    public function checkCollectionStatus(string $transactionId): TransactionStatus
    {
        try {
            $response = $this->request('GET', "/payment/{$transactionId}");

            if ($response['success'] && isset($response['data']['payment_status'])) {
                $status = $response['data']['payment_status'];
                
                Log::info('NOWPayments status check', [
                    'transaction_id' => $transactionId,
                    'status' => $status,
                ]);

                return $this->mapStatus($status);
            }

            return TransactionStatus::PENDING;
        } catch (\Exception $e) {
            Log::error('NOWPayments status check failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);
            
            return TransactionStatus::FAILED;
        }
    }

    /**
     * Check disbursement/payout status
     */
    public function checkDisbursementStatus(string $transactionId): TransactionStatus
    {
        try {
            // NOWPayments doesn't have a direct payout status endpoint
            // Status updates come via IPN callbacks
            // For now, return processing status
            return TransactionStatus::PROCESSING;
        } catch (\Exception $e) {
            Log::error('NOWPayments payout status check failed', [
                'error' => $e->getMessage(),
            ]);
            
            return TransactionStatus::FAILED;
        }
    }

    /**
     * Get available cryptocurrencies
     */
    public function getAvailableCurrencies(): array
    {
        try {
            $response = $this->request('GET', '/currencies');

            if ($response['success'] && isset($response['data']['currencies'])) {
                return $response['data']['currencies'];
            }

            // Log the issue for debugging
            Log::warning('NOWPayments: Unable to fetch currencies', [
                'status_code' => $response['status_code'] ?? null,
                'data' => $response['data'] ?? null,
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch NOWPayments currencies', [
                'error' => $e->getMessage(),
            ]);
            
            return [];
        }
    }

    /**
     * Get minimum payment amount for a currency
     */
    public function getMinimumAmount(string $currency): ?float
    {
        try {
            $response = $this->request('GET', "/min-amount?currency_from={$currency}&currency_to=usd");

            if ($response['success'] && isset($response['data']['min_amount'])) {
                return (float) $response['data']['min_amount'];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Estimate price in cryptocurrency
     */
    public function estimatePrice(float $amount, string $fromCurrency, string $toCurrency): ?array
    {
        try {
            $response = $this->request('GET', "/estimate?amount={$amount}&currency_from={$fromCurrency}&currency_to={$toCurrency}");

            if ($response['success']) {
                return $response['data'];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verify IPN (Instant Payment Notification) callback signature
     */
    public function verifyIPNSignature(string $payload, string $signature): bool
    {
        if (empty($this->ipnSecret)) {
            Log::warning('NOWPayments IPN secret not configured');
            return false;
        }

        $expectedSignature = hash_hmac('sha512', $payload, $this->ipnSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Map NOWPayments status to internal TransactionStatus
     */
    protected function mapStatus(string $status): TransactionStatus
    {
        return match (strtolower($status)) {
            'finished', 'confirmed' => TransactionStatus::COMPLETED,
            'waiting', 'confirming' => TransactionStatus::PENDING,
            'sending', 'processing' => TransactionStatus::PROCESSING,
            'failed', 'refunded' => TransactionStatus::FAILED,
            'expired' => TransactionStatus::EXPIRED,
            default => TransactionStatus::PENDING,
        };
    }

    /**
     * Determine which cryptocurrency to use for payment
     */
    private function determinePayCurrency(string $fiatCurrency, array $availableCurrencies): string
    {
        // Default preferences based on stability and popularity
        $preferences = ['usdttrc20', 'usdt', 'btc', 'eth', 'ltc'];

        foreach ($preferences as $crypto) {
            if (in_array($crypto, $availableCurrencies)) {
                return $crypto;
            }
        }

        // Fallback to first available currency
        return $availableCurrencies[0] ?? 'btc';
    }

    /**
     * Determine cryptocurrency for payout
     */
    private function determineCryptoCurrency(string $currency): string
    {
        // If already a crypto currency, use it
        $cryptos = ['btc', 'eth', 'usdt', 'usdc', 'ltc', 'xrp', 'bnb', 'ada', 'dot', 'doge'];
        
        if (in_array(strtolower($currency), $cryptos)) {
            return strtolower($currency);
        }

        // Default to USDT for fiat currencies (most stable)
        return 'usdttrc20';
    }
}
