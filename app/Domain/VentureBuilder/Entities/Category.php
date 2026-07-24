<?php

namespace App\Domain\VentureBuilder\Entities;

use DateTimeImmutable;

class Category
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description = null,
        public readonly ?string $icon = null,
        public readonly ?int $sortOrder = null,
        public readonly ?bool $isActive = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
            sortOrder: array_key_exists('sort_order', $data) ? (int) $data['sort_order'] : null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
