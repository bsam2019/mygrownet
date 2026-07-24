<?php

namespace App\Domain\ZamStay\Entities;

class Review
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $bookingId,
        public readonly int $userId,
        public readonly int $propertyId,
        public readonly int $rating,
        public readonly ?string $comment,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            bookingId: (int) $data['booking_id'],
            userId: (int) $data['user_id'],
            propertyId: (int) $data['property_id'],
            rating: (int) $data['rating'],
            comment: $data['comment'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->bookingId,
            'user_id' => $this->userId,
            'property_id' => $this->propertyId,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
