<?php

namespace App\AntiCorruption\Payments\AirtelMoney;

use App\Domain\Platform\Contracts\PaymentGateway;
use App\Exceptions\IntegrationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AirtelMoneyPaymentAdapter implements PaymentGateway
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private ?string $accessToken = null;

    public function __construct(array $config)
    {
        $sandbox = $config['sandbox'] ?? true;
        $this->baseUrl = $sandbox
            ? 'https://openapiuat.airtel.africa'
            : 'https://openapi.airtel.africa';
        $this->clientId = $config['client_id'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';
    }

    public function charge(array $params): array
    {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new IntegrationException('Failed to authenticate with Airtel Money API');
            }

            $transactionId = Str::uuid()->toString();
            $phone = $this->formatPhone($params['phone_number']);
            $amount = number_format((float) $params['amount'], 2, '.', '');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Country' => 'ZM',
                'X-Currency' => 'ZMW',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/merchant/v1/payments/', [
                'reference' => $params['reference'] ?? $transactionId,
                'subscriber' => ['country' => 'ZM', 'currency' => 'ZMW', 'msisdn' => $phone],
                'transaction' => [
                    'amount' => $amount,
                    'country' => 'ZM',
                    'currency' => 'ZMW',
                    'id' => $transactionId,
                ],
            ]);

            $data = $response->json();
            $status = $data['status']['code'] ?? null;

            if ($status === '200' || $response->status() === 200) {
                return [
                    'transaction_id' => $transactionId,
                    'status' => 'pending',
                    'provider' => 'airtel_money',
                    'reference' => $data['data']['transaction']['id'] ?? $params['reference'] ?? null,
                ];
            }

            throw new IntegrationException(
                'Airtel Money charge failed: ' . ($data['status']['message'] ?? 'Unknown')
            );
        } catch (IntegrationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new IntegrationException('Airtel Money charge error: ' . $e->getMessage(), 0, $e);
        }
    }

    public function refund(string $transactionId, ?float $amount = null): array
    {
        return ['transaction_id' => $transactionId, 'status' => 'refund_manual'];
    }

    public function verify(string $transactionId): array
    {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new IntegrationException('Failed to authenticate with Airtel Money API');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Country' => 'ZM',
                'X-Currency' => 'ZMW',
            ])->get($this->baseUrl . '/standard/v1/payments/' . $transactionId);

            $data = $response->json();
            $status = strtoupper($data['data']['transaction']['status'] ?? 'UNKNOWN');

            return match ($status) {
                'TS', 'TIP' => ['transaction_id' => $transactionId, 'status' => 'completed', 'data' => $data],
                'TF' => ['transaction_id' => $transactionId, 'status' => 'failed', 'data' => $data],
                default => ['transaction_id' => $transactionId, 'status' => 'processing', 'data' => $data],
            };
        } catch (\Throwable $e) {
            throw new IntegrationException('Airtel Money verification failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function webhook(array $payload): array
    {
        return ['event' => $payload['event_type'] ?? 'unknown', 'status' => 'processed'];
    }

    private function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($this->baseUrl . '/auth/oauth2/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]);

        if ($response->successful()) {
            $this->accessToken = $response->json('access_token');
            return $this->accessToken;
        }

        return null;
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[\s-+]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }
        if (str_starts_with($phone, '260')) {
            $phone = substr($phone, 3);
        }
        return $phone;
    }
}
