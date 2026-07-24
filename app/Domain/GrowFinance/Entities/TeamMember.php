<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

class TeamMember
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly ?int $userId,
        public readonly ?string $role,
        public readonly ?array $permissions,
        public readonly ?string $status,
        public readonly ?string $invitationToken,
        public readonly ?\DateTimeImmutable $invitedAt,
        public readonly ?\DateTimeImmutable $acceptedAt,
        public readonly ?\DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: (int) $data['business_id'],
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            role: $data['role'] ?? null,
            permissions: isset($data['permissions']) ? (array) $data['permissions'] : null,
            status: $data['status'] ?? null,
            invitationToken: $data['invitation_token'] ?? null,
            invitedAt: isset($data['invited_at']) ? new \DateTimeImmutable($data['invited_at']) : null,
            acceptedAt: isset($data['accepted_at']) ? new \DateTimeImmutable($data['accepted_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'user_id' => $this->userId,
            'role' => $this->role,
            'permissions' => $this->permissions,
            'status' => $this->status,
            'invitation_token' => $this->invitationToken,
            'invited_at' => $this->invitedAt?->format('Y-m-d H:i:s'),
            'accepted_at' => $this->acceptedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return in_array($permission, $this->permissions ?? [], true);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
