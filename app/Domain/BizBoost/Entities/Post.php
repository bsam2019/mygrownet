<?php

namespace App\Domain\BizBoost\Entities;

class Post
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly ?string $title,
        public readonly string $caption,
        public readonly string $status,
        public readonly ?string $scheduledAt,
        public readonly ?string $publishedAt,
        public readonly ?array $platformTargets,
        public readonly ?array $externalIds,
        public readonly ?array $analytics,
        public readonly ?string $postType,
        public readonly ?int $templateId,
        public readonly ?int $campaignId,
        public readonly ?string $errorMessage,
        public readonly ?int $retryCount,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            title: $data['title'] ?? null,
            caption: $data['caption'],
            status: $data['status'],
            scheduledAt: $data['scheduled_at'] ?? null,
            publishedAt: $data['published_at'] ?? null,
            platformTargets: $data['platform_targets'] ?? null,
            externalIds: $data['external_ids'] ?? null,
            analytics: $data['analytics'] ?? null,
            postType: $data['post_type'] ?? 'standard',
            templateId: isset($data['template_id']) ? (int) $data['template_id'] : null,
            campaignId: isset($data['campaign_id']) ? (int) $data['campaign_id'] : null,
            errorMessage: $data['error_message'] ?? null,
            retryCount: (int) ($data['retry_count'] ?? 0),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public static function create(array $data): self
    {
        return new self(
            id: null,
            businessId: (int) $data['business_id'],
            title: $data['title'] ?? null,
            caption: $data['caption'],
            status: $data['status'],
            scheduledAt: $data['scheduled_at'] ?? null,
            publishedAt: null,
            platformTargets: $data['platform_targets'] ?? [],
            externalIds: null,
            analytics: null,
            postType: $data['post_type'] ?? 'standard',
            templateId: isset($data['template_id']) ? (int) $data['template_id'] : null,
            campaignId: isset($data['campaign_id']) ? (int) $data['campaign_id'] : null,
            errorMessage: null,
            retryCount: 0,
            createdAt: null,
            updatedAt: null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'title' => $this->title,
            'caption' => $this->caption,
            'status' => $this->status,
            'scheduled_at' => $this->scheduledAt,
            'published_at' => $this->publishedAt,
            'platform_targets' => $this->platformTargets,
            'external_ids' => $this->externalIds,
            'analytics' => $this->analytics,
            'post_type' => $this->postType,
            'template_id' => $this->templateId,
            'campaign_id' => $this->campaignId,
            'error_message' => $this->errorMessage,
            'retry_count' => $this->retryCount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}