<?php

namespace App\Domain\BizBoost\Entities;

class OmnichannelLog
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly ?int $businessId,
        public readonly string $channelType,
        public readonly string $recipientPhone,
        public readonly ?string $messageContent,
        public readonly float $clientAmountCharged,
        public readonly float $vendorActualCost,
        public readonly float $netPlatformProfit,
        public readonly string $deliveryStatus,
        public readonly ?string $errorMessage,
        public readonly ?string $reference,
        public readonly ?array $meta,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            businessId: isset($data['business_id']) ? (int) $data['business_id'] : null,
            channelType: $data['channel_type'],
            recipientPhone: $data['recipient_phone'],
            messageContent: $data['message_content'] ?? null,
            clientAmountCharged: (float) ($data['client_amount_charged'] ?? 0),
            vendorActualCost: (float) ($data['vendor_actual_cost'] ?? 0),
            netPlatformProfit: (float) ($data['net_platform_profit'] ?? 0),
            deliveryStatus: $data['delivery_status'],
            errorMessage: $data['error_message'] ?? null,
            reference: $data['reference'] ?? null,
            meta: $data['meta'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'business_id' => $this->businessId,
            'channel_type' => $this->channelType,
            'recipient_phone' => $this->recipientPhone,
            'message_content' => $this->messageContent,
            'client_amount_charged' => $this->clientAmountCharged,
            'vendor_actual_cost' => $this->vendorActualCost,
            'net_platform_profit' => $this->netPlatformProfit,
            'delivery_status' => $this->deliveryStatus,
            'error_message' => $this->errorMessage,
            'reference' => $this->reference,
            'meta' => $this->meta,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}