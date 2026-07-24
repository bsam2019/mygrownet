<?php

namespace App\Domain\BizBoost\Entities;

class Campaign
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $objective,
        public readonly string $status,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $durationDays,
        public readonly ?array $campaignConfig,
        public readonly ?array $targetPlatforms,
        public readonly ?array $analytics,
        public readonly ?int $postsCreated,
        public readonly ?int $postsPublished,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            objective: $data['objective'],
            status: $data['status'],
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            durationDays: (int) ($data['duration_days'] ?? 0),
            campaignConfig: $data['campaign_config'] ?? null,
            targetPlatforms: $data['target_platforms'] ?? null,
            analytics: $data['analytics'] ?? null,
            postsCreated: isset($data['posts_created']) ? (int) $data['posts_created'] : null,
            postsPublished: isset($data['posts_published']) ? (int) $data['posts_published'] : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'description' => $this->description,
            'objective' => $this->objective,
            'status' => $this->status,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'duration_days' => $this->durationDays,
            'campaign_config' => $this->campaignConfig,
            'target_platforms' => $this->targetPlatforms,
            'analytics' => $this->analytics,
            'posts_created' => $this->postsCreated,
            'posts_published' => $this->postsPublished,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}