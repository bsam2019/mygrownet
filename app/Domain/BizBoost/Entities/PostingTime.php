<?php

namespace App\Domain\BizBoost\Entities;

class PostingTime
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $dayType,
        public readonly ?array $times,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            dayType: $data['day_type'],
            times: $data['times'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'day_type' => $this->dayType,
            'times' => $this->times,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}