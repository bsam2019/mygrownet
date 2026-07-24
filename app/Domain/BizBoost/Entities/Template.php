<?php

namespace App\Domain\BizBoost\Entities;

class Template
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly ?string $category,
        public readonly ?string $industry,
        public readonly ?array $templateData,
        public readonly ?string $thumbnailPath,
        public readonly ?string $previewPath,
        public readonly ?int $width,
        public readonly ?int $height,
        public readonly bool $isPremium,
        public readonly bool $isActive,
        public readonly bool $isFeatured,
        public readonly int $usageCount,
        public readonly ?int $sortOrder,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
            industry: $data['industry'] ?? null,
            templateData: $data['template_data'] ?? null,
            thumbnailPath: $data['thumbnail_path'] ?? null,
            previewPath: $data['preview_path'] ?? null,
            width: isset($data['width']) ? (int) $data['width'] : null,
            height: isset($data['height']) ? (int) $data['height'] : null,
            isPremium: (bool) ($data['is_premium'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
            isFeatured: (bool) ($data['is_featured'] ?? false),
            usageCount: (int) ($data['usage_count'] ?? 0),
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'industry' => $this->industry,
            'template_data' => $this->templateData,
            'thumbnail_path' => $this->thumbnailPath,
            'preview_path' => $this->previewPath,
            'width' => $this->width,
            'height' => $this->height,
            'is_premium' => $this->isPremium,
            'is_active' => $this->isActive,
            'is_featured' => $this->isFeatured,
            'usage_count' => $this->usageCount,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}