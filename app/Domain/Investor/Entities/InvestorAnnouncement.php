<?php

namespace App\Domain\Investor\Entities;

use App\Domain\Investor\ValueObjects\AnnouncementType;
use App\Domain\Investor\ValueObjects\AnnouncementPriority;
use DateTimeImmutable;

class InvestorAnnouncement
{
    private function __construct(
        private ?int $id,
        private string $title,
        private string $content,
        private ?string $summary,
        private AnnouncementType $type,
        private AnnouncementPriority $priority,
        private bool $isPinned,
        private bool $sendEmail,
        private ?DateTimeImmutable $publishedAt,
        private ?DateTimeImmutable $expiresAt,
        private ?int $createdBy,
        private ?DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        string $title,
        string $content,
        ?string $summary,
        AnnouncementType $type,
        AnnouncementPriority $priority,
        bool $isPinned,
        bool $sendEmail,
        ?DateTimeImmutable $expiresAt,
        ?int $createdBy
    ): self {
        return new self(
            id: null,
            title: $title,
            content: $content,
            summary: $summary,
            type: $type,
            priority: $priority,
            isPinned: $isPinned,
            sendEmail: $sendEmail,
            publishedAt: null,
            expiresAt: $expiresAt,
            createdBy: $createdBy,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            title: $data['title'],
            content: $data['content'],
            summary: $data['summary'] ?? null,
            type: AnnouncementType::from($data['type'] ?? 'general'),
            priority: AnnouncementPriority::from($data['priority'] ?? 'normal'),
            isPinned: $data['is_pinned'] ?? false,
            sendEmail: $data['send_email'] ?? false,
            publishedAt: isset($data['published_at']) ? new DateTimeImmutable($data['published_at']) : null,
            expiresAt: isset($data['expires_at']) ? new DateTimeImmutable($data['expires_at']) : null,
            createdBy: $data['created_by'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null
        );
    }

    public function publish(): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            content: $this->content,
            summary: $this->summary,
            type: $this->type,
            priority: $this->priority,
            isPinned: $this->isPinned,
            sendEmail: $this->sendEmail,
            publishedAt: new DateTimeImmutable(),
            expiresAt: $this->expiresAt,
            createdBy: $this->createdBy,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable()
        );
    }

    public function unpublish(): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            content: $this->content,
            summary: $this->summary,
            type: $this->type,
            priority: $this->priority,
            isPinned: $this->isPinned,
            sendEmail: $this->sendEmail,
            publishedAt: null,
            expiresAt: $this->expiresAt,
            createdBy: $this->createdBy,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable()
        );
    }

    public function isPublished(): bool
    {
        return $this->publishedAt !== null;
    }

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }
        return $this->expiresAt < new DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->isPublished() && !$this->isExpired();
    }

    public function isUrgent(): bool
    {
        return $this->priority === AnnouncementPriority::URGENT || $this->type === AnnouncementType::URGENT;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getContent(): string { return $this->content; }
    public function getSummary(): ?string { return $this->summary; }
    public function getType(): AnnouncementType { return $this->type; }
    public function getPriority(): AnnouncementPriority { return $this->priority; }
    public function isPinned(): bool { return $this->isPinned; }
    public function shouldSendEmail(): bool { return $this->sendEmail; }
    public function getPublishedAt(): ?DateTimeImmutable { return $this->publishedAt; }
    public function getExpiresAt(): ?DateTimeImmutable { return $this->expiresAt; }
    public function getCreatedBy(): ?int { return $this->createdBy; }
    public function getCreatedAt(): ?DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'summary' => $this->summary,
            'type' => $this->type->value,
            'priority' => $this->priority->value,
            'is_pinned' => $this->isPinned,
            'send_email' => $this->sendEmail,
            'published_at' => $this->publishedAt?->format('Y-m-d H:i:s'),
            'expires_at' => $this->expiresAt?->format('Y-m-d H:i:s'),
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
