<?php

namespace App\Domain\LifePlus\Entities;

class LifePlusGig
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $category,
        public readonly ?float $paymentAmount,
        public readonly ?string $location,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly string $status,
        public readonly ?int $assignedTo,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
            paymentAmount: isset($data['payment_amount']) ? (float) $data['payment_amount'] : null,
            location: $data['location'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            status: $data['status'] ?? 'open',
            assignedTo: isset($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'payment_amount' => $this->paymentAmount,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'assigned_to' => $this->assignedTo,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
