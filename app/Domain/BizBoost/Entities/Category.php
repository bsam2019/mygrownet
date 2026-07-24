<?php

namespace App\Domain\BizBoost\Entities;

class Category
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly ?string $slug,
        public readonly ?string $description,
        public readonly ?string $color,
        public readonly ?string $icon,
        public readonly ?int $sortOrder,
        public readonly bool $isActive,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
            color: $data['color'] ?? null,
            icon: $data['icon'] ?? null,
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            isActive: (bool) ($data['is_active'] ?? true),
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
            'slug' => $this->slug,
            'description' => $this->description,
            'color' => $this->color,
            'icon' => $this->icon,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}