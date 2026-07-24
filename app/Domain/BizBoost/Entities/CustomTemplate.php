<?php

namespace App\Domain\BizBoost\Entities;

class CustomTemplate
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly ?int $baseTemplateId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $category,
        public readonly ?array $templateData,
        public readonly ?string $thumbnailPath,
        public readonly ?int $width,
        public readonly ?int $height,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            baseTemplateId: isset($data['base_template_id']) ? (int) $data['base_template_id'] : null,
            name: $data['name'],
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
            templateData: $data['template_data'] ?? null,
            thumbnailPath: $data['thumbnail_path'] ?? null,
            width: isset($data['width']) ? (int) $data['width'] : null,
            height: isset($data['height']) ? (int) $data['height'] : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'base_template_id' => $this->baseTemplateId,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'template_data' => $this->templateData,
            'thumbnail_path' => $this->thumbnailPath,
            'width' => $this->width,
            'height' => $this->height,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}