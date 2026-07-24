<?php

namespace App\Domain\VentureBuilder\Entities;

use DateTimeImmutable;

class Update
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $ventureId,
        public readonly string $title,
        public readonly ?string $content = null,
        public readonly ?string $type = null,
        public readonly string $visibility,
        public readonly ?bool $sendNotification = null,
        public readonly ?bool $isPinned = null,
        public readonly ?int $viewsCount = null,
        public readonly int $postedBy,
        public readonly ?DateTimeImmutable $publishedAt = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function isPublished(): bool
    {
        return $this->publishedAt !== null && $this->publishedAt < new DateTimeImmutable();
    }

    public function isDraft(): bool
    {
        return $this->publishedAt === null;
    }

    public function canBeViewedBy(string $userRole): bool
    {
        if (!$this->isPublished()) {
            return $userRole === 'admin';
        }

        return match ($this->visibility) {
            'public' => true,
            'investors_only' => in_array($userRole, ['investor', 'shareholder', 'admin'], true),
            'shareholders_only' => in_array($userRole, ['shareholder', 'admin'], true),
            default => false,
        };
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            ventureId: (int) $data['venture_id'],
            title: $data['title'],
            content: $data['content'] ?? null,
            type: $data['type'] ?? null,
            visibility: $data['visibility'],
            sendNotification: isset($data['send_notification']) ? (bool) $data['send_notification'] : null,
            isPinned: isset($data['is_pinned']) ? (bool) $data['is_pinned'] : null,
            viewsCount: array_key_exists('views_count', $data) ? (int) $data['views_count'] : null,
            postedBy: (int) $data['posted_by'],
            publishedAt: isset($data['published_at']) ? new \DateTimeImmutable($data['published_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'venture_id' => $this->ventureId,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'visibility' => $this->visibility,
            'send_notification' => $this->sendNotification,
            'is_pinned' => $this->isPinned,
            'views_count' => $this->viewsCount,
            'posted_by' => $this->postedBy,
            'published_at' => $this->publishedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
