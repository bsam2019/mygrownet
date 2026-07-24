<?php

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Role
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly string $name,
        public readonly bool $isSystemRole,
        public readonly array $permissions,
        public readonly ?array $approvalAuthority,
        public readonly ?string $description,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            name: $data['name'],
            isSystemRole: (bool) ($data['is_system_role'] ?? false),
            permissions: $data['permissions'] ?? [],
            approvalAuthority: $data['approval_authority'] ?? null,
            description: $data['description'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'name' => $this->name,
            'is_system_role' => $this->isSystemRole,
            'permissions' => $this->permissions,
            'approval_authority' => $this->approvalAuthority,
            'description' => $this->description,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
