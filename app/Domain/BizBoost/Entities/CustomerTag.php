<?php

namespace App\Domain\BizBoost\Entities;

class CustomerTag
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly ?string $color,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            color: $data['color'] ?? null,
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
            'color' => $this->color,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}