<?php

namespace App\AntiCorruption\Payments\MTNMoMo;

use App\Domain\Platform\Contracts\PaymentGateway;
use App\Exceptions\IntegrationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MTNMoMoPaymentAdapter implements PaymentGateway
{
    private string $baseUrl;
    private string $apiUser;
    private string $apiKey;
    private string $subscriptionKey;

    public function __construct(array $config)
    {
        $sandbox = $config['sandbox'] ?? true;
        $this->baseUrl = $sandbox
            ? 'https://sandbox.momodeveloper.mtn.com'
            : 'https://proxy.momoapi.mtn.com';
        $this->apiUser = $config['api_user'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->subscriptionKey = $config['subscription_key'] ?? '';
    }

    public function charge(array $params): array
    {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                throw new IntegrationException('Failed to authenticate with MTN MoMo API');
            }

            $transactionId = Str::uuid()->toString();
            $phone = $this->formatPhone($params['phone_number']);
            $amount = number_format((float) $params['amount'], 2, '.', '');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Reference-Id' => $transactionId,
                'X-Target-Environment' => str_contains($this->baseUrl, 'sandbox') ? 'sandbox' : 'production',
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/collection/v1_0/requesttopay', [
                'amount' => $amount,
                'currency' => 'ZMW',
                'externalId' => $params['reference'] ?? $transactionId,
                'payer' => ['partyIdType' => 'MSISDN', 'partyId' => $phone],
                'payerMessage' => $params['description'] ?? 'Payment',
                'payeeNote' => $params['reference'] ?? $transactionId,
            ]);

            if ($response->status() === 202) {
                return [
                    'transaction_id' => $transactionId,
                    'status' => 'pending',
                    'provider' => 'mtn_momo',
                ];
            }

            throw new IntegrationException('MTN MoMo charge failed: ' . ($response->json('message') ?? 'Unknown'));
        } catch (IntegrationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new IntegrationException('MTN MoMo charge error: ' . $e->getMessage(), 0, $e);
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
                throw new IntegrationException('Failed to authenticate with MTN MoMo API');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Target-Environment' => str_contains($this->baseUrl, 'sandbox') ? 'sandbox' : 'production',
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
            ])->get($this->baseUrl . '/collection/v1_0/requesttopay/' . $transactionId);

            $data = $response->json();
            $status = $data['status'] ?? 'UNKNOWN';

            return match ($status) {
                'SUCCESSFUL' => ['transaction_id' => $transactionId, 'status' => 'completed', 'data' => $data],
                'PENDING' => ['transaction_id' => $transactionId, 'status' => 'pending', 'data' => $data],
                'FAILED' => ['transaction_id' => $transactionId, 'status' => 'failed', 'data' => $data],
                default => ['transaction_id' => $transactionId, 'status' => 'processing', 'data' => $data],
            };
        } catch (\Throwable $e) {
            throw new IntegrationException('MTN MoMo verification failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function webhook(array $payload): array
    {
        return ['event' => $payload['event'] ?? 'unknown', 'status' => 'processed'];
    }

    private function getAccessToken(): ?string
    {
        $response = Http::withBasicAuth($this->apiUser, $this->apiKey)
            ->withHeaders(['Ocp-Apim-Subscription-Key' => $this->subscriptionKey])
            ->post($this->baseUrl . '/collection/token/');

        return $response->successful() ? $response->json('access_token') : null;
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[\s-+]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '260' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '260')) {
            $phone = '260' . $phone;
        }
        return $phone;
    }
}
