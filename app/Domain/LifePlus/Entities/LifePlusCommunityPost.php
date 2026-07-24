<?php

namespace App\Domain\LifePlus\Entities;

class LifePlusCommunityPost
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $content,
        public readonly ?string $location,
        public readonly ?\DateTimeImmutable $eventDate,
        public readonly ?string $imageUrl,
        public readonly bool $isPromoted,
        public readonly ?\DateTimeImmutable $expiresAt,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            type: $data['type'] ?? 'notice',
            title: $data['title'],
            content: $data['content'] ?? null,
            location: $data['location'] ?? null,
            eventDate: isset($data['event_date']) ? new \DateTimeImmutable($data['event_date']) : null,
            imageUrl: $data['image_url'] ?? null,
            isPromoted: (bool) ($data['is_promoted'] ?? false),
            expiresAt: isset($data['expires_at']) ? new \DateTimeImmutable($data['expires_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'type' => $this->type,
            'title' => $this->title,
            'content' => $this->content,
            'location' => $this->location,
            'event_date' => $this->eventDate?->format('Y-m-d H:i:s'),
            'image_url' => $this->imageUrl,
            'is_promoted' => $this->isPromoted,
            'expires_at' => $this->expiresAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
