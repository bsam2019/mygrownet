<?php

namespace App\Infrastructure\Services;

use App\Domain\BizBoost\Contracts\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioSmsGateway implements SmsGatewayInterface
{
    private string $accountSid;
    private string $authToken;
    private string $fromNumber;
    private string $baseUrl;

    public function __construct()
    {
        $this->accountSid = config('services.twilio.account_sid', '');
        $this->authToken = config('services.twilio.auth_token', '');
        $this->fromNumber = config('services.twilio.from_number', '');
        $this->baseUrl = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}";
    }

    public function send(string $to, string $message, array $options = []): array
    {
        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post("{$this->baseUrl}/Messages.json", [
                    'From' => $options['from'] ?? $this->fromNumber,
                    'To' => $to,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'sent',
                    'message_id' => $data['sid'] ?? null,
                    'price' => $data['price'] ?? null,
                ];
            }

            $error = $response->json('message') ?? $response->body();
            Log::error("Twilio SMS send failed", ['to' => $to, 'error' => $error]);

            return [
                'success' => false,
                'status' => 'failed',
                'error' => $error,
            ];
        } catch (\Exception $e) {
            Log::error("Twilio SMS exception", ['to' => $to, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendBulk(array $recipients, string $message, array $options = []): array
    {
        $results = [];
        foreach ($recipients as $recipient) {
            $to = is_string($recipient) ? $recipient : ($recipient['phone'] ?? $recipient['to']);
            $results[] = $this->send($to, $message, $options);
        }
        return ['results' => $results];
    }

    public function getDeliveryStatus(string $messageId): string
    {
        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->get("{$this->baseUrl}/Messages/{$messageId}.json");

            if ($response->successful()) {
                return $response->json('status', 'unknown');
            }
        } catch (\Exception $e) {
            Log::error("Twilio status check failed", ['message_id' => $messageId, 'error' => $e->getMessage()]);
        }

        return 'unknown';
    }

    public function getBalance(): float
    {
        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->get("{$this->baseUrl}/Balance.json");

            if ($response->successful()) {
                return (float) ($response->json('balance', 0));
            }
        } catch (\Exception $e) {
            Log::error("Twilio balance check failed", ['error' => $e->getMessage()]);
        }

        return 0;
    }
}
