<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class MobileMoneyService
{
    protected array $config;
    
    public function __construct()
    {
        $this->config = config('mygrownet.mobile_money', []);
    }

    /**
     * Send payment to mobile money account
     */
    public function sendPayment(array $paymentData): array
    {
        $provider = $this->detectProvider($paymentData['phone_number']);
        
        return match($provider) {
            'mtn' => $this->sendMTNPayment($paymentData),
            'airtel' => $this->sendAirtelPayment($paymentData),
            'zamtel' => $this->sendZamtelPayment($paymentData),
            default => $this->sendGenericPayment($paymentData)
        };
    }

    /**
     * Send MTN Mobile Money payment
     */
    protected function sendMTNPayment(array $paymentData): array
    {
        try {
            $accessToken = $this->getMTNAccessToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-Reference-Id' => $paymentData['reference'],
                'X-Target-Environment' => $this->config['mtn']['environment'] ?? 'sandbox',
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => $this->config['mtn']['subscription_key']
            ])->post($this->config['mtn']['disbursement_url'], [
                'amount' => (string) $paymentData['amount'],
                'currency' => 'ZMW',
                'externalId' => $paymentData['reference'],
                'payee' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => $this->formatPhoneNumber($paymentData['phone_number'])
                ],
                'payerMessage' => $paymentData['description'],
                'payeeNote' => 'MyGrowNet Commission Payment'
            ]);

            if ($response->successful()) {
                // Check transaction status
                $statusResult = $this->checkMTNTransactionStatus($paymentData['reference'], $accessToken);
                
                return [
                    'success' => $statusResult['success'],
                    'external_reference' => $paymentData['reference'],
                    'provider' => 'mtn',
                    'response' => $response->json(),
                    'status_check' => $statusResult
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'MTN payment failed: ' . $response->body(),
                    'provider' => 'mtn',
                    'response' => $response->json()
                ];
            }

        } catch (Exception $e) {
            Log::error('MTN payment error', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);

            return [
                'success' => false,
                'error' => 'MTN payment exception: ' . $e->getMessage(),
                'provider' => 'mtn'
            ];
        }
    }

    /**
     * Send Airtel Money payment
     */
    protected function sendAirtelPayment(array $paymentData): array
    {
        try {
            $accessToken = $this->getAirtelAccessToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'X-Country' => 'ZM',
                'X-Currency' => 'ZMW'
            ])->post($this->config['airtel']['disbursement_url'], [
                'payee' => [
                    'msisdn' => $this->formatPhoneNumber($paymentData['phone_number'])
                ],
                'reference' => $paymentData['reference'],
                'amount' => $paymentData['amount'],
                'country' => 'ZM',
                'currency' => 'ZMW',
                'narration' => $paymentData['description']
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => isset($responseData['status']) && $responseData['status']['success'],
                    'external_reference' => $responseData['data']['transaction']['id'] ?? $paymentData['reference'],
                    'provider' => 'airtel',
                    'response' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Airtel payment failed: ' . $response->body(),
                    'provider' => 'airtel',
                    'response' => $response->json()
                ];
            }

        } catch (Exception $e) {
            Log::error('Airtel payment error', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);

            return [
                'success' => false,
                'error' => 'Airtel payment exception: ' . $e->getMessage(),
                'provider' => 'airtel'
            ];
        }
    }

    /**
     * Send Zamtel Kwacha payment
     */
    protected function sendZamtelPayment(array $paymentData): array
    {
        try {
            // Zamtel Kwacha API implementation
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getZamtelAccessToken(),
                'Content-Type' => 'application/json'
            ])->post($this->config['zamtel']['disbursement_url'], [
                'msisdn' => $this->formatPhoneNumber($paymentData['phone_number']),
                'amount' => $paymentData['amount'],
                'reference' => $paymentData['reference'],
                'description' => $paymentData['description']
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => $responseData['success'] ?? false,
                    'external_reference' => $responseData['transaction_id'] ?? $paymentData['reference'],
                    'provider' => 'zamtel',
                    'response' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Zamtel payment failed: ' . $response->body(),
                    'provider' => 'zamtel',
                    'response' => $response->json()
                ];
            }

        } catch (Exception $e) {
            Log::error('Zamtel payment error', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);

            return [
                'success' => false,
                'error' => 'Zamtel payment exception: ' . $e->getMessage(),
                'provider' => 'zamtel'
            ];
        }
    }

    /**
     * Generic payment method (fallback)
     */
    protected function sendGenericPayment(array $paymentData): array
    {
        // For development/testing - simulate payment
        if (app()->environment(['local', 'testing'])) {
            return [
                'success' => true,
                'external_reference' => $paymentData['reference'],
                'provider' => 'simulation',
                'response' => [
                    'message' => 'Payment simulated successfully',
                    'amount' => $paymentData['amount'],
                    'phone' => $paymentData['phone_number']
                ]
            ];
        }

        return [
            'success' => false,
            'error' => 'No supported mobile money provider detected',
            'provider' => 'unknown'
        ];
    }

    /**
     * Detect mobile money provider from phone number
     */
    protected function detectProvider(string $phoneNumber): string
    {
        $cleanNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Remove country code if present
        if (str_starts_with($cleanNumber, '260')) {
            $cleanNumber = substr($cleanNumber, 3);
        }
        
        // MTN prefixes: 096, 076, 077
        if (preg_match('/^(096|076|077)/', $cleanNumber)) {
            return 'mtn';
        }
        
        // Airtel prefixes: 097, 077 (some overlap with MTN)
        if (preg_match('/^(097)/', $cleanNumber)) {
            return 'airtel';
        }
        
        // Zamtel prefixes: 095
        if (preg_match('/^(095)/', $cleanNumber)) {
            return 'zamtel';
        }
        
        return 'unknown';
    }

    /**
     * Format phone number for API calls
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        $cleanNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add country code if not present
        if (!str_starts_with($cleanNumber, '260')) {
            if (str_starts_with($cleanNumber, '0')) {
                $cleanNumber = '260' . substr($cleanNumber, 1);
            } else {
                $cleanNumber = '260' . $cleanNumber;
            }
        }
        
        return $cleanNumber;
    }

    /**
     * Get MTN access token
     */
    protected function getMTNAccessToken(): string
    {
        $cacheKey = 'mtn_access_token';
        
        return Cache::remember($cacheKey, 3600, function () {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->config['mtn']['subscription_key'],
                'Authorization' => 'Basic ' . base64_encode($this->config['mtn']['user_id'] . ':' . $this->config['mtn']['api_key'])
            ])->post($this->config['mtn']['token_url']);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            throw new Exception('Failed to get MTN access token: ' . $response->body());
        });
    }

    /**
     * Get Airtel access token
     */
    protected function getAirtelAccessToken(): string
    {
        $cacheKey = 'airtel_access_token';
        
        return Cache::remember($cacheKey, 3600, function () {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post($this->config['airtel']['token_url'], [
                'client_id' => $this->config['airtel']['client_id'],
                'client_secret' => $this->config['airtel']['client_secret'],
                'grant_type' => 'client_credentials'
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            throw new Exception('Failed to get Airtel access token: ' . $response->body());
        });
    }

    /**
     * Get Zamtel access token
     */
    protected function getZamtelAccessToken(): string
    {
        $cacheKey = 'zamtel_access_token';
        
        return Cache::remember($cacheKey, 3600, function () {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post($this->config['zamtel']['token_url'], [
                'username' => $this->config['zamtel']['username'],
                'password' => $this->config['zamtel']['password']
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            throw new Exception('Failed to get Zamtel access token: ' . $response->body());
        });
    }

    /**
     * Check MTN transaction status
     */
    protected function checkMTNTransactionStatus(string $referenceId, string $accessToken): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'X-Target-Environment' => $this->config['mtn']['environment'] ?? 'sandbox',
                'Ocp-Apim-Subscription-Key' => $this->config['mtn']['subscription_key']
            ])->get($this->config['mtn']['disbursement_url'] . '/' . $referenceId);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => $data['status'] === 'SUCCESSFUL',
                    'status' => $data['status'],
                    'response' => $data
                ];
            }

            return [
                'success' => false,
                'status' => 'UNKNOWN',
                'error' => 'Status check failed: ' . $response->body()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 'ERROR',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate payment configuration
     */
    public function validateConfiguration(): array
    {
        $errors = [];

        // Check MTN configuration
        if (empty($this->config['mtn']['subscription_key'])) {
            $errors[] = 'MTN subscription key not configured';
        }

        // Check Airtel configuration
        if (empty($this->config['airtel']['client_id'])) {
            $errors[] = 'Airtel client ID not configured';
        }

        // Check Zamtel configuration
        if (empty($this->config['zamtel']['username'])) {
            $errors[] = 'Zamtel username not configured';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}