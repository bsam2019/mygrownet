<?php

namespace App\Domain\Learning\Entities;

use App\Domain\Learning\ValueObjects\ModuleId;
use App\Domain\Learning\ValueObjects\ModuleContent;
use App\Domain\Learning\ValueObjects\ContentType;

class LearningModule
{
    private function __construct(
        private ModuleId $id,
        private string $title,
        private string $slug,
        private ModuleContent $content,
        private ContentType $contentType,
        private int $estimatedMinutes,
        private ?string $category,
        private bool $isPublished,
        private bool $isRequired,
        private int $sortOrder
    ) {}

    public static function create(
        string $title,
        string $slug,
        string $content,
        string $contentType = 'text',
        int $estimatedMinutes = 10,
        ?string $category = null
    ): self {
        return new self(
            ModuleId::generate(),
            $title,
            $slug,
            ModuleContent::fromString($content),
            ContentType::fromString($contentType),
            $estimatedMinutes,
            $category,
            true,
            false,
            0
        );
    }

    public function getId(): ModuleId
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getContent(): ModuleContent
    {
        return $this->content;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function getEstimatedMinutes(): int
    {
        return $this->estimatedMinutes;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function publish(): void
    {
        $this->isPublished = true;
    }

    public function unpublish(): void
    {
        $this->isPublished = false;
    }

    public function markAsRequired(): void
    {
        $this->isRequired = true;
    }

    public function updateContent(string $content): void
    {
        $this->content = ModuleContent::fromString($content);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content->value(),
            'content_type' => $this->contentType->value(),
            'estimated_minutes' => $this->estimatedMinutes,
            'category' => $this->category,
            'is_published' => $this->isPublished,
            'is_required' => $this->isRequired,
            'sort_order' => $this->sortOrder,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ModuleId::fromInt($data['id']),
            $data['title'],
            $data['slug'],
            ModuleContent::fromString($data['content']),
            ContentType::fromString($data['content_type']),
            $data['estimated_minutes'],
            $data['category'] ?? null,
            $data['is_published'],
            $data['is_required'],
            $data['sort_order'] ?? 0
        );
    }
}
