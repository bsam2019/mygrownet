<?php

namespace App\Services\CMS;

use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\SmsLogModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Check if SMS is enabled for company
     */
    public function isEnabled(int $companyId): bool
    {
        $company = CompanyModel::find($companyId);
        
        if (!$company) {
            return false;
        }

        $smsSettings = $company->sms_settings ?? [];
        
        return ($smsSettings['enabled'] ?? false) && 
               !empty($smsSettings['provider']) &&
               !empty($smsSettings['api_key']);
    }

    /**
     * Send SMS (only if enabled)
     */
    public function send(int $companyId, string $to, string $message, string $type = 'general'): array
    {
        // Check if SMS is enabled
        if (!$this->isEnabled($companyId)) {
            return [
                'success' => false,
                'error' => 'SMS service is not enabled. Please configure SMS settings.',
                'skipped' => true,
            ];
        }

        $company = CompanyModel::find($companyId);
        $smsSettings = $company->sms_settings ?? [];
        $provider = $smsSettings['provider'] ?? null;

        try {
            $result = match($provider) {
                'africas_talking' => $this->sendViaAfricasTalking($smsSettings, $to, $message),
                'twilio' => $this->sendViaTwilio($smsSettings, $to, $message),
                default => ['success' => false, 'error' => 'Invalid SMS provider'],
            };

            // Log SMS
            $this->logSms($companyId, $to, $message, $type, $result);

            return $result;
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'company_id' => $companyId,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            $this->logSms($companyId, $to, $message, $type, [
                'success' => false,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send via Africa's Talking
     */
    private function sendViaAfricasTalking(array $settings, string $to, string $message): array
    {
        $apiKey = $settings['api_key'] ?? '';
        $username = $settings['username'] ?? '';
        $senderId = $settings['sender_id'] ?? 'MyGrowNet';

        $response = Http::withHeaders([
            'apiKey' => $apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
            'username' => $username,
            'to' => $to,
            'message' => $message,
            'from' => $senderId,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'message_id' => $data['SMSMessageData']['Recipients'][0]['messageId'] ?? null,
                'cost' => $data['SMSMessageData']['Recipients'][0]['cost'] ?? null,
                'status' => $data['SMSMessageData']['Recipients'][0]['status'] ?? 'sent',
            ];
        }

        return [
            'success' => false,
            'error' => $response->json()['SMSMessageData']['Message'] ?? 'Failed to send SMS',
        ];
    }

    /**
     * Send via Twilio
     */
    private function sendViaTwilio(array $settings, string $to, string $message): array
    {
        $accountSid = $settings['account_sid'] ?? '';
        $authToken = $settings['auth_token'] ?? '';
        $fromNumber = $settings['from_number'] ?? '';

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => $fromNumber,
                'To' => $to,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'message_id' => $data['sid'] ?? null,
                'cost' => $data['price'] ?? null,
                'status' => $data['status'] ?? 'sent',
            ];
        }

        return [
            'success' => false,
            'error' => $response->json()['message'] ?? 'Failed to send SMS',
        ];
    }

    /**
     * Log SMS
     */
    private function logSms(int $companyId, string $to, string $message, string $type, array $result): void
    {
        SmsLogModel::create([
            'company_id' => $companyId,
            'to' => $to,
            'message' => $message,
            'type' => $type,
            'status' => $result['success'] ? 'sent' : 'failed',
            'message_id' => $result['message_id'] ?? null,
            'cost' => $result['cost'] ?? null,
            'error' => $result['error'] ?? null,
            'sent_at' => $result['success'] ? now() : null,
        ]);
    }

    /**
     * Get SMS statistics
     */
    public function getStatistics(int $companyId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = SmsLogModel::where('company_id', $companyId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $total = $query->count();
        $sent = $query->where('status', 'sent')->count();
        $failed = $query->where('status', 'failed')->count();
        $totalCost = $query->where('status', 'sent')->sum('cost');

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * Get SMS logs
     */
    public function getLogs(int $companyId, int $perPage = 50)
    {
        return SmsLogModel::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Test SMS configuration
     */
    public function testConnection(int $companyId, string $testNumber): array
    {
        return $this->send(
            $companyId,
            $testNumber,
            'This is a test message from your CMS. SMS is configured correctly!',
            'test'
        );
    }
}
