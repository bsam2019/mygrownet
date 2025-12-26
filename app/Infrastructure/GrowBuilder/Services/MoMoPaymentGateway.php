<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowBuilder\Services;

use App\Domain\GrowBuilder\Services\PaymentGatewayInterface;
use App\Domain\GrowBuilder\Services\PaymentResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MoMoPaymentGateway implements PaymentGatewayInterface
{
    private string $baseUrl;
    private string $apiUser;
    private string $apiKey;
    private string $subscriptionKey;
    private bool $sandbox;

    public function __construct(array $config)
    {
        $this->sandbox = $config['sandbox'] ?? true;
        $this->baseUrl = $this->sandbox 
            ? 'https://sandbox.momodeveloper.mtn.com'
            : 'https://proxy.momoapi.mtn.com';
        $this->apiUser = $config['api_user'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->subscriptionKey = $config['subscription_key'] ?? '';
    }

    public function initiatePayment(
        int $amountInNgwee,
        string $phoneNumber,
        string $reference,
        string $description,
    ): PaymentResult {
        try {
            // Get access token
            $token = $this->getAccessToken();
            if (!$token) {
                return PaymentResult::failed('Failed to authenticate with MoMo API');
            }

            $transactionId = Str::uuid()->toString();
            $amountInKwacha = number_format($amountInNgwee / 100, 2, '.', '');

            // Format phone number (remove leading 0, add country code)
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Reference-Id' => $transactionId,
                'X-Target-Environment' => $this->sandbox ? 'sandbox' : 'production',
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/collection/v1_0/requesttopay', [
                'amount' => $amountInKwacha,
                'currency' => 'ZMW',
                'externalId' => $reference,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $formattedPhone,
                ],
                'payerMessage' => $description,
                'payeeNote' => $reference,
            ]);

            if ($response->status() === 202) {
                return PaymentResult::pending(
                    transactionId: $transactionId,
                    externalReference: $reference,
                    rawResponse: ['status_code' => 202]
                );
            }

            Log::error('MoMo payment initiation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return PaymentResult::failed(
                'Payment initiation failed: ' . ($response->json('message') ?? 'Unknown error'),
                $transactionId,
                $response->json() ?? []
            );
        } catch (\Exception $e) {
            Log::error('MoMo payment exception', ['error' => $e->getMessage()]);
            return PaymentResult::failed('Payment service error: ' . $e->getMessage());
        }
    }

    public function checkStatus(string $transactionId): PaymentResult
    {
        try {
            $token = $this->getAccessToken();
            if (!$token) {
                return PaymentResult::failed('Failed to authenticate with MoMo API');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Target-Environment' => $this->sandbox ? 'sandbox' : 'production',
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
            ])->get($this->baseUrl . '/collection/v1_0/requesttopay/' . $transactionId);

            if (!$response->successful()) {
                return PaymentResult::failed('Failed to check payment status');
            }

            $data = $response->json();
            $status = $data['status'] ?? 'UNKNOWN';

            return match ($status) {
                'SUCCESSFUL' => PaymentResult::success(
                    $transactionId,
                    $data['externalId'] ?? null,
                    $data
                ),
                'PENDING' => PaymentResult::pending(
                    $transactionId,
                    $data['externalId'] ?? null,
                    $data
                ),
                'FAILED' => PaymentResult::failed(
                    $data['reason'] ?? 'Payment failed',
                    $transactionId,
                    $data
                ),
                default => PaymentResult::processing(
                    $transactionId,
                    $data['externalId'] ?? null,
                    $data
                ),
            };
        } catch (\Exception $e) {
            Log::error('MoMo status check exception', ['error' => $e->getMessage()]);
            return PaymentResult::failed('Status check error: ' . $e->getMessage());
        }
    }

    public function getProviderName(): string
    {
        return 'momo';
    }

    private function getAccessToken(): ?string
    {
        try {
            $response = Http::withBasicAuth($this->apiUser, $this->apiKey)
                ->withHeaders([
                    'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                ])
                ->post($this->baseUrl . '/collection/token/');

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('MoMo token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('MoMo token exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function formatPhoneNumber(string $phone): string
    {
        // Remove spaces and dashes
        $phone = preg_replace('/[\s-]/', '', $phone);
        
        // Remove leading + if present
        $phone = ltrim($phone, '+');
        
        // If starts with 0, replace with country code
        if (str_starts_with($phone, '0')) {
            $phone = '260' . substr($phone, 1);
        }
        
        // If doesn't start with country code, add it
        if (!str_starts_with($phone, '260')) {
            $phone = '260' . $phone;
        }

        return $phone;
    }
}
