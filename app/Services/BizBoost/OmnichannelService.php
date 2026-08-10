<?php

namespace App\Services\BizBoost;

use App\Domain\BizBoost\Contracts\SmsGatewayInterface;
use App\Domain\BizBoost\Contracts\SmsDispatchResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OmnichannelService
{
    public function __construct(
        protected ?SmsGatewayInterface $smsGateway = null
    ) {}

    /**
     * Generate a trackable short link for WhatsApp/Call campaigns.
     */
    public function createTrackableLink(int $businessId, array $data): string
    {
        $hash = Str::random(6);

        DB::table('bizboost_trackable_links')->insert([
            'business_id' => $businessId,
            'campaign_id' => $data['campaign_id'] ?? null,
            'name' => $data['name'] ?? 'Trackable Link',
            'hash' => $hash,
            'destination_type' => $data['destination_type'] ?? 'whatsapp',
            'target_url' => $data['target_url'],
            'utm_source' => $data['utm_source'] ?? 'bizboost',
            'utm_medium' => $data['utm_medium'] ?? 'shortlink',
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return url("/bizboost/link/{$hash}");
    }

    /**
     * Dispatch SMS using the bound SmsGatewayInterface.
     */
    public function sendSms(string $to, string $message, ?string $senderId = null): SmsDispatchResult
    {
        if (!$this->smsGateway) {
            return new SmsDispatchResult(
                success: true,
                messageId: 'mock_' . Str::random(10),
                costZmw: 0.14
            );
        }

        return $this->smsGateway->sendSms($to, $message, $senderId);
    }
}
