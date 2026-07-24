<?php

namespace App\Domain\BizBoost\Entities;

class WeeklyTheme
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $weekStart,
        public readonly string $theme,
        public readonly ?string $description,
        public readonly ?string $color,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            weekStart: $data['week_start'],
            theme: $data['theme'],
            description: $data['description'] ?? null,
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
            'week_start' => $this->weekStart,
            'theme' => $this->theme,
            'description' => $this->description,
            'color' => $this->color,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}