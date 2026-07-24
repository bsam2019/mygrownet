<?php

namespace App\Domain\ZamStay\Entities;

class Agent
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $businessName,
        public readonly ?string $licenseNumber,
        public readonly ?string $phone,
        public readonly float $commissionRate,
        public readonly bool $isApproved,
        public readonly ?string $bio,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            businessName: $data['business_name'],
            licenseNumber: $data['license_number'] ?? null,
            phone: $data['phone'] ?? null,
            commissionRate: (float) ($data['commission_rate'] ?? 0),
            isApproved: (bool) ($data['is_approved'] ?? false),
            bio: $data['bio'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'business_name' => $this->businessName,
            'license_number' => $this->licenseNumber,
            'phone' => $this->phone,
            'commission_rate' => $this->commissionRate,
            'is_approved' => $this->isApproved,
            'bio' => $this->bio,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
