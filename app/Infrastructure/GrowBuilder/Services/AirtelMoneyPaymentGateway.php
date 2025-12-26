<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowBuilder\Services;

use App\Domain\GrowBuilder\Services\PaymentGatewayInterface;
use App\Domain\GrowBuilder\Services\PaymentResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AirtelMoneyPaymentGateway implements PaymentGatewayInterface
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private bool $sandbox;
    private ?string $accessToken = null;

    public function __construct(array $config)
    {
        $this->sandbox = $config['sandbox'] ?? true;
        $this->baseUrl = $this->sandbox
            ? 'https://openapiuat.airtel.africa'
            : 'https://openapi.airtel.africa';
        $this->clientId = $config['client_id'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';
    }

    public function initiatePayment(
        int $amountInNgwee,
        string $phoneNumber,
        string $reference,
        string $description,
    ): PaymentResult {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                return PaymentResult::failed('Failed to authenticate with Airtel Money API');
            }

            $transactionId = Str::uuid()->toString();
            $amountInKwacha = number_format($amountInNgwee / 100, 2, '.', '');
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Country' => 'ZM',
                'X-Currency' => 'ZMW',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/merchant/v1/payments/', [
                'reference' => $reference,
                'subscriber' => [
                    'country' => 'ZM',
                    'currency' => 'ZMW',
                    'msisdn' => $formattedPhone,
                ],
                'transaction' => [
                    'amount' => $amountInKwacha,
                    'country' => 'ZM',
                    'currency' => 'ZMW',
                    'id' => $transactionId,
                ],
            ]);

            $data = $response->json();
            $status = $data['status']['code'] ?? null;

            if ($status === '200' || $response->status() === 200) {
                return PaymentResult::pending(
                    transactionId: $transactionId,
                    externalReference: $data['data']['transaction']['id'] ?? $reference,
                    rawResponse: $data
                );
            }

            Log::error('Airtel Money payment initiation failed', [
                'status' => $response->status(),
                'body' => $data,
            ]);

            return PaymentResult::failed(
                $data['status']['message'] ?? 'Payment initiation failed',
                $transactionId,
                $data
            );
        } catch (\Exception $e) {
            Log::error('Airtel Money payment exception', ['error' => $e->getMessage()]);
            return PaymentResult::failed('Payment service error: ' . $e->getMessage());
        }
    }

    public function checkStatus(string $transactionId): PaymentResult
    {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                return PaymentResult::failed('Failed to authenticate with Airtel Money API');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Country' => 'ZM',
                'X-Currency' => 'ZMW',
            ])->get($this->baseUrl . '/standard/v1/payments/' . $transactionId);

            $data = $response->json();
            $status = $data['data']['transaction']['status'] ?? 'UNKNOWN';

            return match (strtoupper($status)) {
                'TS', 'TIP' => PaymentResult::success(
                    $transactionId,
                    $data['data']['transaction']['airtel_money_id'] ?? null,
                    $data
                ),
                'TF' => PaymentResult::failed(
                    $data['data']['transaction']['message'] ?? 'Payment failed',
                    $transactionId,
                    $data
                ),
                default => PaymentResult::processing(
                    $transactionId,
                    null,
                    $data
                ),
            };
        } catch (\Exception $e) {
            Log::error('Airtel Money status check exception', ['error' => $e->getMessage()]);
            return PaymentResult::failed('Status check error: ' . $e->getMessage());
        }
    }

    public function getProviderName(): string
    {
        return 'airtel';
    }

    private function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/auth/oauth2/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                $this->accessToken = $response->json('access_token');
                return $this->accessToken;
            }

            Log::error('Airtel Money token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Airtel Money token exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[\s-]/', '', $phone);
        $phone = ltrim($phone, '+');
        
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }
        
        if (str_starts_with($phone, '260')) {
            $phone = substr($phone, 3);
        }

        return $phone;
    }
}
