<?php

namespace App\Domain\BizBoost\Entities;

class Location
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly ?string $address,
        public readonly ?string $city,
        public readonly ?string $phone,
        public readonly ?string $whatsapp,
        public readonly ?array $businessHours,
        public readonly bool $isPrimary,
        public readonly bool $isActive,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        $hours = $data['business_hours'] ?? null;
        if (is_string($hours)) {
            $hours = json_decode($hours, true);
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            phone: $data['phone'] ?? null,
            whatsapp: $data['whatsapp'] ?? null,
            businessHours: $hours,
            isPrimary: (bool) ($data['is_primary'] ?? false),
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
            'address' => $this->address,
            'city' => $this->city,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'business_hours' => $this->businessHours,
            'is_primary' => $this->isPrimary,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}