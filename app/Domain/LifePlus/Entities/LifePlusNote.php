<?php

namespace App\Domain\LifePlus\Entities;

class LifePlusNote
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $title,
        public readonly ?string $content,
        public readonly bool $isPinned,
        public readonly bool $isSynced,
        public readonly ?string $localId,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            title: $data['title'],
            content: $data['content'] ?? null,
            isPinned: (bool) ($data['is_pinned'] ?? false),
            isSynced: (bool) ($data['is_synced'] ?? true),
            localId: $data['local_id'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'title' => $this->title,
            'content' => $this->content,
            'is_pinned' => $this->isPinned,
            'is_synced' => $this->isSynced,
            'local_id' => $this->localId,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
