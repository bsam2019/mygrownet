<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Entities;

use DateTimeImmutable;

class InventoryCategory
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $color,
        public readonly int $sortOrder,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            color: $data['color'] ?? '#6b7280',
            sortOrder: (int) ($data['sort_order'] ?? 0),
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
