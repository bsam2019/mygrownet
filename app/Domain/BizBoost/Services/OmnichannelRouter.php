<?php

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Contracts\OmnichannelRouterInterface;
use App\Domain\BizBoost\Contracts\SmsGatewayInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostOmnichannelLogModel;
use App\Services\GrowBuilder\WhatsAppCloudService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OmnichannelRouter implements OmnichannelRouterInterface
{
    private WhatsAppCloudService $whatsapp;
    private ?SmsGatewayInterface $smsGateway;
    private BillingLedgerService $ledger;

    public function __construct(
        WhatsAppCloudService $whatsapp,
        BillingLedgerService $ledger,
        ?SmsGatewayInterface $smsGateway = null,
    ) {
        $this->whatsapp = $whatsapp;
        $this->ledger = $ledger;
        $this->smsGateway = $smsGateway;
    }

    public function checkWhatsAppStatus(string $phone): bool
    {
        try {
            $businessPhone = config('services.whatsapp.business_phone', '260965896512');
            $result = $this->whatsapp->sendTextMessage($phone, ".");
            return isset($result['messages'][0]['id']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function send(int $userId, string $phone, string $message, array $options = []): array
    {
        $channelType = $options['prefer_channel'] ?? 'whatsapp';
        $clientCharge = $options['client_charge'] ?? 0.02;
        $vendorCost = $options['vendor_cost'] ?? 0.005;
        $businessId = $options['business_id'] ?? null;

        $reference = Str::random(20);

        if ($channelType === 'whatsapp' || $this->checkWhatsAppStatus($phone)) {
            return $this->sendViaWhatsApp($userId, $phone, $message, $clientCharge, $vendorCost, $reference, $businessId, $options);
        }

        if ($this->smsGateway) {
            return $this->sendViaSms($userId, $phone, $message, $clientCharge, $vendorCost, $reference, $businessId, $options);
        }

        throw new \RuntimeException("No available channel to send message to {$phone}");
    }

    private function sendViaWhatsApp(int $userId, string $phone, string $message, float $clientCharge, float $vendorCost, string $reference, ?int $businessId, array $options): array
    {
        try {
            $result = $this->whatsapp->sendTextMessage($phone, $message);
            $success = isset($result['messages'][0]['id']);

            $this->logChannelDelivery($userId, $businessId, 'whatsapp', $phone, $clientCharge, $vendorCost, $success ? 'delivered' : 'failed', $reference, $message);

            if ($success) {
                $this->ledger->recordTransaction(
                    userId: $userId,
                    serviceType: 'whatsapp_msg',
                    grossAmountCharged: $clientCharge,
                    netVendorCost: $vendorCost,
                    recipientIdentifier: $phone,
                    vendor: 'meta_whatsapp',
                    deliveryStatus: 'success',
                    meta: ['reference' => $reference, 'message_id' => $result['messages'][0]['id'] ?? null],
                );
            }

            return ['success' => $success, 'channel' => 'whatsapp', 'reference' => $reference];
        } catch (\Exception $e) {
            Log::error("Omnichannel WhatsApp send failed", ['phone' => $phone, 'error' => $e->getMessage()]);
            $this->logChannelDelivery($userId, $businessId, 'whatsapp', $phone, $clientCharge, $vendorCost, 'failed', $reference, $message);

            if ($this->smsGateway) {
                return $this->sendViaSms($userId, $phone, $message, $clientCharge, $vendorCost, $reference . '_sms', $businessId, $options);
            }

            return ['success' => false, 'channel' => 'whatsapp', 'error' => $e->getMessage(), 'reference' => $reference];
        }
    }

    private function sendViaSms(int $userId, string $phone, string $message, float $clientCharge, float $vendorCost, string $reference, ?int $businessId, array $options): array
    {
        try {
            $result = $this->smsGateway->send($phone, $message);
            $success = ($result['status'] ?? '') === 'sent';

            $this->logChannelDelivery($userId, $businessId, 'sms', $phone, $clientCharge, $vendorCost, $success ? 'delivered' : 'failed', $reference, $message);

            if ($success) {
                $this->ledger->recordTransaction(
                    userId: $userId,
                    serviceType: 'sms_msg',
                    grossAmountCharged: $clientCharge,
                    netVendorCost: $vendorCost,
                    recipientIdentifier: $phone,
                    vendor: 'twilio',
                    deliveryStatus: 'success',
                    meta: ['reference' => $reference, 'message_id' => $result['message_id'] ?? null],
                );
            }

            return ['success' => $success, 'channel' => 'sms', 'reference' => $reference];
        } catch (\Exception $e) {
            Log::error("Omnichannel SMS send failed", ['phone' => $phone, 'error' => $e->getMessage()]);
            $this->logChannelDelivery($userId, $businessId, 'sms', $phone, $clientCharge, $vendorCost, 'failed', $reference, $message);
            return ['success' => false, 'channel' => 'sms', 'error' => $e->getMessage(), 'reference' => $reference];
        }
    }

    private function logChannelDelivery(int $userId, ?int $businessId, string $channel, string $phone, float $charged, float $cost, string $status, string $reference, string $message = ''): void
    {
        BizBoostOmnichannelLogModel::create([
            'user_id' => $userId,
            'business_id' => $businessId,
            'channel_type' => $channel,
            'recipient_phone' => $phone,
            'message_content' => $message,
            'client_amount_charged' => $charged,
            'vendor_actual_cost' => $cost,
            'net_platform_profit' => $charged - $cost,
            'delivery_status' => $status,
            'reference' => $reference,
        ]);
    }
}
