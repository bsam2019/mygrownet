<?php

declare(strict_types=1);

namespace App\Domain\Payment\Gateways;

/**
 * Custom MoneyUnify Client
 * 
 * This is a local implementation that handles SSL certificate issues
 * on Windows development environments. In production, SSL verification
 * should always be enabled.
 */
class MoneyUnifyClient
{
    private const BASE_URL = 'https://api.moneyunify.com/v2';
    private string $muid;
    private string $requestPaymentUrl;
    private string $verifyPaymentUrl;
    public \stdClass $response;

    public function __construct(string $muid)
    {
        $this->muid = $muid;
        $this->requestPaymentUrl = self::BASE_URL . '/request_payment';
        $this->verifyPaymentUrl = self::BASE_URL . '/verify_transaction';
        $this->response = new \stdClass();
    }

    /**
     * Request payment from a customer
     */
    public function requestPayment(string $payerPhoneNumber, string $amountToPay): \stdClass
    {
        $bodyFields = http_build_query([
            'muid' => $this->muid,
            'phone_number' => $payerPhoneNumber,
            'amount' => $amountToPay,
        ]);

        $requestResponse = $this->urlRequest($this->requestPaymentUrl, $bodyFields);
        return $this->urlRequestResponse($requestResponse);
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(string $transactionReference): \stdClass
    {
        $bodyFields = http_build_query([
            'muid' => $this->muid,
            'reference' => $transactionReference,
        ]);

        $requestResponse = $this->urlRequest($this->verifyPaymentUrl, $bodyFields);
        return $this->urlRequestResponse($requestResponse);
    }

    /**
     * Process API response
     */
    private function urlRequestResponse(string $request): \stdClass
    {
        // Log raw response for debugging
        \Illuminate\Support\Facades\Log::debug('MoneyUnify raw response', [
            'response' => $request,
            'muid_configured' => !empty($this->muid),
        ]);

        $req = json_decode($request);

        // Handle case where response is not valid JSON
        if ($req === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->response->message = 'Invalid API response: ' . substr($request, 0, 200);
            $this->response->isError = true;
            $this->response->console = ['raw_response' => $request];
            return $this->response;
        }

        $this->response->message = $req->message ?? 'No message returned.';
        $this->response->isError = $req->isError ?? true;

        if (!$this->response->isError) {
            $this->response->data = $req->data ?? null;
        } else {
            $this->response->console = $req->console ?? null;
            
            // If no specific message, check for common issues
            if ($this->response->message === 'No message returned.' && empty($this->muid)) {
                $this->response->message = 'MoneyUnify MUID not configured. Please set MONEYUNIFY_API_KEY in .env';
            }
        }

        return $this->response;
    }

    /**
     * Make HTTP request with SSL handling for development
     */
    private function urlRequest(string $url, ?string $bodyPayload = null): string
    {
        $curl = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $bodyPayload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
                'User-Agent: MoneyUnify-PHP-Client/1.0',
                'X-Requested-With: XMLHttpRequest',
            ],
        ];

        // In local/development environment, disable SSL verification
        // WARNING: Never disable SSL verification in production!
        if (app()->environment('local', 'development', 'testing')) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        // Log for debugging
        \Illuminate\Support\Facades\Log::debug('MoneyUnify cURL request', [
            'url' => $url,
            'http_code' => $httpCode,
            'error' => $error,
            'response_preview' => substr($response ?: '', 0, 500),
        ]);

        if ($response === false) {
            return json_encode([
                'message' => $error ?: 'cURL request failed',
                'console' => ['http_code' => $httpCode],
                'isError' => true,
            ]);
        }

        // Check if response is HTML (bot protection page)
        if (str_starts_with(trim($response), '<!') || str_starts_with(trim($response), '<html')) {
            return json_encode([
                'message' => 'API returned HTML instead of JSON. This may indicate: 1) Invalid MUID, 2) Bot protection triggered, or 3) API endpoint changed. Please verify your MUID at moneyunify.com',
                'console' => ['http_code' => $httpCode, 'response_type' => 'html'],
                'isError' => true,
            ]);
        }

        return $response;
    }
}
