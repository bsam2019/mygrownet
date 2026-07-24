<?php

namespace App\Domain\BizBoost\Entities;

class BillingLedger
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $serviceType,
        public readonly ?int $campaignId,
        public readonly ?string $recipientIdentifier,
        public readonly float $grossAmountCharged,
        public readonly float $netVendorCost,
        public readonly float $purePlatformProfit,
        public readonly string $currency,
        public readonly ?string $vendor,
        public readonly string $deliveryStatus,
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
            serviceType: $data['service_type'],
            campaignId: isset($data['campaign_id']) ? (int) $data['campaign_id'] : null,
            recipientIdentifier: $data['recipient_identifier'] ?? null,
            grossAmountCharged: (float) ($data['gross_amount_charged'] ?? 0),
            netVendorCost: (float) ($data['net_vendor_cost'] ?? 0),
            purePlatformProfit: (float) ($data['pure_platform_profit'] ?? 0),
            currency: $data['currency'] ?? 'ZMW',
            vendor: $data['vendor'] ?? null,
            deliveryStatus: $data['delivery_status'] ?? 'pending',
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
            'service_type' => $this->serviceType,
            'campaign_id' => $this->campaignId,
            'recipient_identifier' => $this->recipientIdentifier,
            'gross_amount_charged' => $this->grossAmountCharged,
            'net_vendor_cost' => $this->netVendorCost,
            'pure_platform_profit' => $this->purePlatformProfit,
            'currency' => $this->currency,
            'vendor' => $this->vendor,
            'delivery_status' => $this->deliveryStatus,
            'reference' => $this->reference,
            'meta' => $this->meta,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}