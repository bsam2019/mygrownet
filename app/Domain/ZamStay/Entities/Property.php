<?php

namespace App\Domain\ZamStay\Entities;

class Property
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $ownerId,
        public readonly string $title,
        public readonly string $description,
        public readonly string $location,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly float $pricePerNight,
        public readonly string $status,
        public readonly array $images,
        public readonly int $maxGuests,
        public readonly int $bedrooms,
        public readonly int $bathrooms,
        public readonly array $amenities,
        public readonly string $propertyType,
        public readonly bool $isActive,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            ownerId: (int) $data['owner_id'],
            title: $data['title'],
            description: $data['description'],
            location: $data['location'],
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            pricePerNight: (float) $data['price_per_night'],
            status: $data['status'] ?? 'available',
            images: isset($data['images']) ? (array) $data['images'] : [],
            maxGuests: (int) ($data['max_guests'] ?? 1),
            bedrooms: (int) ($data['bedrooms'] ?? 0),
            bathrooms: (int) ($data['bathrooms'] ?? 0),
            amenities: isset($data['amenities']) ? (array) $data['amenities'] : [],
            propertyType: $data['property_type'],
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->ownerId,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'price_per_night' => $this->pricePerNight,
            'status' => $this->status,
            'images' => $this->images,
            'max_guests' => $this->maxGuests,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'amenities' => $this->amenities,
            'property_type' => $this->propertyType,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
