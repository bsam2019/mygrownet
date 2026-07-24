<?php

namespace App\Domain\BizBoost\Entities;

class TeamMember
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly ?int $userId,
        public readonly string $name,
        public readonly string $email,
        public readonly string $role,
        public readonly ?array $permissions,
        public readonly ?int $locationId,
        public readonly string $status,
        public readonly ?string $invitedAt,
        public readonly ?string $joinedAt,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            name: $data['name'],
            email: $data['email'],
            role: $data['role'],
            permissions: $data['permissions'] ?? null,
            locationId: isset($data['location_id']) ? (int) $data['location_id'] : null,
            status: $data['status'] ?? 'pending',
            invitedAt: $data['invited_at'] ?? null,
            joinedAt: $data['joined_at'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'user_id' => $this->userId,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'permissions' => $this->permissions,
            'location_id' => $this->locationId,
            'status' => $this->status,
            'invited_at' => $this->invitedAt,
            'joined_at' => $this->joinedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}