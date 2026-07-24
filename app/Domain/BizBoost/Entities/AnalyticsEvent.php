<?php

namespace App\Domain\BizBoost\Entities;

class AnalyticsEvent
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $eventType,
        public readonly ?string $source,
        public readonly ?int $postId,
        public readonly ?array $payload,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
        public readonly ?string $referrer,
        public readonly ?string $recordedAt,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            eventType: $data['event_type'],
            source: $data['source'] ?? null,
            postId: isset($data['post_id']) ? (int) $data['post_id'] : null,
            payload: $data['payload'] ?? null,
            ipAddress: $data['ip_address'] ?? null,
            userAgent: $data['user_agent'] ?? null,
            referrer: $data['referrer'] ?? null,
            recordedAt: $data['recorded_at'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'event_type' => $this->eventType,
            'source' => $this->source,
            'post_id' => $this->postId,
            'payload' => $this->payload,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'referrer' => $this->referrer,
            'recorded_at' => $this->recordedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}