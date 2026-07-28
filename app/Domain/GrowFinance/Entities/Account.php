<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\ValueObjects\AccountType;

class Account
{
    /** @var Account[]|null */
    private ?array $children = null;

    private ?Account $parentAccount = null;

    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $code,
        public readonly string $name,
        public readonly AccountType $type,
        public readonly string $normalBalance = 'debit',
        public readonly string $currencyCode = 'ZMW',
        public readonly ?int $parentId = null,
        public readonly int $level = 0,
        public readonly ?string $path = null,
        public readonly ?string $statementCategory = null,
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
            normalBalance: $data['normal_balance'] ?? ($data['type'] === 'asset' || $data['type'] === 'expense' ? 'debit' : 'credit'),
            currencyCode: $data['currency_code'] ?? 'ZMW',
            parentId: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
            level: (int) ($data['level'] ?? 0),
            path: $data['path'] ?? null,
            statementCategory: $data['statement_category'] ?? null,
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
            'normal_balance' => $this->normalBalance,
            'currency_code' => $this->currencyCode,
            'parent_id' => $this->parentId,
            'level' => $this->level,
            'path' => $this->path,
            'statement_category' => $this->statementCategory,
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

    public function isContraAccount(): bool
    {
        if ($this->normalBalance === 'debit' && !$this->type->isDebitNormal()) {
            return true;
        }
        if ($this->normalBalance === 'credit' && $this->type->isDebitNormal()) {
            return true;
        }
        return false;
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function setParentAccount(?Account $parent): void
    {
        $this->parentAccount = $parent;
    }

    /** @return Account[]|null */
    public function children(): ?array
    {
        return $this->children;
    }

    public function parent(): ?Account
    {
        return $this->parentAccount;
    }

    public function getBalance(): float
    {
        if ($this->isContraAccount()) {
            return -$this->currentBalance;
        }
        return $this->currentBalance;
    }
}
