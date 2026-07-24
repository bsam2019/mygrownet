<?php

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class CmsUser
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly int $userId,
        public readonly ?int $roleId,
        public readonly ?int $branchId,
        public readonly string $status,
        public readonly ?DateTimeImmutable $lastActiveAt,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            userId: (int) $data['user_id'],
            roleId: $data['role_id'] ?? null,
            branchId: $data['branch_id'] ?? null,
            status: $data['status'] ?? 'active',
            lastActiveAt: isset($data['last_active_at']) ? new DateTimeImmutable($data['last_active_at']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'user_id' => $this->userId,
            'role_id' => $this->roleId,
            'branch_id' => $this->branchId,
            'status' => $this->status,
            'last_active_at' => $this->lastActiveAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
