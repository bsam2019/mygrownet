<?php

namespace App\Domain\GrowBuilder\Payment\Gateways;

use App\Domain\GrowBuilder\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected array $credentials;
    protected bool $testMode;

    public function __construct(array $credentials, bool $testMode = false)
    {
        $this->credentials = $credentials;
        $this->testMode = $testMode;
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber(string $phoneNumber, string $countryCode = '260'): string
    {
        $cleanNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (str_starts_with($cleanNumber, $countryCode)) {
            return $cleanNumber;
        }
        
        if (str_starts_with($cleanNumber, '0')) {
            return $countryCode . substr($cleanNumber, 1);
        }
        
        return $countryCode . $cleanNumber;
    }

    /**
     * Log payment activity
     */
    protected function logActivity(string $action, array $data): void
    {
        Log::channel('payment')->info("[{$this->getName()}] {$action}", $data);
    }

    /**
     * Log payment error
     */
    protected function logError(string $action, \Throwable $e, array $context = []): void
    {
        Log::channel('payment')->error("[{$this->getName()}] {$action} failed", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'context' => $context,
        ]);
    }

    /**
     * Make HTTP request with error handling
     */
    protected function makeRequest(string $method, string $url, array $data = [], array $headers = []): array
    {
        try {
            $response = Http::withHeaders($headers)
                ->$method($url, $data);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json(),
                'body' => $response->body(),
            ];
        } catch (\Exception $e) {
            $this->logError('HTTP Request', $e, [
                'method' => $method,
                'url' => $url,
            ]);

            return [
                'success' => false,
                'status' => 0,
                'data' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
