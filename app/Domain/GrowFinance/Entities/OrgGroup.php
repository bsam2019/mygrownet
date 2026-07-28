<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

class OrgGroup
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $parentOrgId,
        public readonly int $childOrgId,
        public readonly string $relationshipType = 'subsidiary',
        public readonly array $consolidationSettings = [],
        public readonly bool $isActive = true,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function create(
        int $parentOrgId,
        int $childOrgId,
        string $relationshipType = 'subsidiary',
        array $consolidationSettings = [],
    ): self {
        return new self(null, $parentOrgId, $childOrgId, $relationshipType, $consolidationSettings);
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            parentOrgId: $data['parent_org_id'] ?? null,
            childOrgId: (int) $data['child_org_id'],
            relationshipType: $data['relationship_type'] ?? 'subsidiary',
            consolidationSettings: json_decode($data['consolidation_settings'] ?? '[]', true),
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'parent_org_id' => $this->parentOrgId,
            'child_org_id' => $this->childOrgId,
            'relationship_type' => $this->relationshipType,
            'consolidation_settings' => json_encode($this->consolidationSettings),
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
