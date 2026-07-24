<?php

namespace App\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\ValueObjects\AccountType;

class Account
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $code,
        public readonly string $name,
        public readonly AccountType $type,
        public readonly ?string $category = null,
        public readonly ?string $description = null,
        public readonly bool $isSystem = false,
        public readonly bool $isActive = true,
        public readonly float $openingBalance = 0.0,
        public readonly float $currentBalance = 0.0,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            code: $data['code'],
            name: $data['name'],
            type: AccountType::from($data['type']),
            category: $data['category'] ?? null,
            description: $data['description'] ?? null,
            isSystem: (bool) ($data['is_system'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
            openingBalance: (float) ($data['opening_balance'] ?? 0.0),
            currentBalance: (float) ($data['current_balance'] ?? 0.0),
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type->value,
            'category' => $this->category,
            'description' => $this->description,
            'is_system' => $this->isSystem,
            'is_active' => $this->isActive,
            'opening_balance' => $this->openingBalance,
            'current_balance' => $this->currentBalance,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}