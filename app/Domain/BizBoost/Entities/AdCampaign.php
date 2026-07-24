<?php

namespace App\Domain\BizBoost\Entities;

class AdCampaign
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly ?int $businessId,
        public readonly string $name,
        public readonly string $objective,
        public readonly float $clientBudget,
        public readonly float $metaBudget,
        public readonly float $platformMarkup,
        public readonly ?string $metaCampaignId,
        public readonly ?string $metaAdSetId,
        public readonly ?string $metaAdId,
        public readonly string $status,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $durationDays,
        public readonly ?array $targeting,
        public readonly ?array $creatives,
        public readonly ?array $insights,
        public readonly ?string $errorMessage,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            businessId: isset($data['business_id']) ? (int) $data['business_id'] : null,
            name: $data['name'],
            objective: $data['objective'],
            clientBudget: (float) ($data['client_budget'] ?? 0),
            metaBudget: (float) ($data['meta_budget'] ?? 0),
            platformMarkup: (float) ($data['platform_markup'] ?? 0),
            metaCampaignId: $data['meta_campaign_id'] ?? null,
            metaAdSetId: $data['meta_ad_set_id'] ?? null,
            metaAdId: $data['meta_ad_id'] ?? null,
            status: $data['status'],
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            durationDays: (int) ($data['duration_days'] ?? 0),
            targeting: $data['targeting'] ?? null,
            creatives: $data['creatives'] ?? null,
            insights: $data['insights'] ?? null,
            errorMessage: $data['error_message'] ?? null,
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
            'name' => $this->name,
            'objective' => $this->objective,
            'client_budget' => $this->clientBudget,
            'meta_budget' => $this->metaBudget,
            'platform_markup' => $this->platformMarkup,
            'meta_campaign_id' => $this->metaCampaignId,
            'meta_ad_set_id' => $this->metaAdSetId,
            'meta_ad_id' => $this->metaAdId,
            'status' => $this->status,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'duration_days' => $this->durationDays,
            'targeting' => $this->targeting,
            'creatives' => $this->creatives,
            'insights' => $this->insights,
            'error_message' => $this->errorMessage,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}