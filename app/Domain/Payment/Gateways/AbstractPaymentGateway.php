<?php

declare(strict_types=1);

namespace App\Domain\Payment\Gateways;

use App\Domain\Payment\Contracts\PaymentGatewayInterface;
use App\Domain\Payment\Enums\TransactionStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected array $headers = [];
    protected int $timeout = 30;

    /**
     * Make HTTP request to gateway API
     */
    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        try {
            $response = Http::withHeaders($this->headers)
                ->timeout($this->timeout)
                ->$method($url, $data);

            $body = $response->json() ?? [];

            if (!$response->successful()) {
                Log::error("Payment gateway request failed", [
                    'gateway' => $this->getIdentifier(),
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response' => $body,
                ]);
            }

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $body,
            ];
        } catch (\Exception $e) {
            Log::error("Payment gateway exception", [
                'gateway' => $this->getIdentifier(),
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status_code' => 0,
                'data' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Format phone number to E.164 format
     */
    protected function formatPhoneNumber(string $phone, string $country): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Country code mapping
        $countryCodes = [
            'ZM' => '260',
            'KE' => '254',
            'TZ' => '255',
            'UG' => '256',
            'GH' => '233',
            'NG' => '234',
            'ZA' => '27',
            'MW' => '265',
            'ZW' => '263',
            'BW' => '267',
            'RW' => '250',
        ];

        $countryCode = $countryCodes[$country] ?? '260';

        // If already has country code, return as is
        if (str_starts_with($phone, $countryCode)) {
            return $phone;
        }

        // Remove leading zero if present
        $phone = ltrim($phone, '0');

        return $countryCode . $phone;
    }

    /**
     * Generate unique transaction reference
     */
    protected function generateReference(string $prefix = 'TXN'): string
    {
        return $prefix . '_' . date('YmdHis') . '_' . strtoupper(substr(md5(uniqid()), 0, 8));
    }

    /**
     * Generate unique transaction ID (UUID format)
     */
    protected function generateTransactionId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Check if gateway is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl);
    }

    /**
     * Log transaction for debugging
     */
    protected function logTransaction(string $action, array $data): void
    {
        Log::info("Payment gateway: {$action}", [
            'gateway' => $this->getIdentifier(),
            'data' => $data,
        ]);
    }
}
