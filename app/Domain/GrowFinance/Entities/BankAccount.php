<?php

namespace App\Domain\GrowFinance\Entities;

class BankAccount
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $accountName,
        public readonly string $accountNumber,
        public readonly ?string $bankName = null,
        public readonly ?string $bankBranch = null,
        public readonly ?string $accountType = null,
        public readonly ?string $currency = null,
        public readonly float $openingBalance = 0.0,
        public readonly float $currentBalance = 0.0,
        public readonly bool $isDefault = false,
        public readonly bool $isActive = true,
        public readonly ?string $notes = null,
        public readonly ?\DateTimeImmutable $deletedAt = null,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            accountName: $data['account_name'],
            accountNumber: $data['account_number'],
            bankName: $data['bank_name'] ?? null,
            bankBranch: $data['bank_branch'] ?? null,
            accountType: $data['account_type'] ?? null,
            currency: $data['currency'] ?? null,
            openingBalance: (float) ($data['opening_balance'] ?? 0.0),
            currentBalance: (float) ($data['current_balance'] ?? 0.0),
            isDefault: (bool) ($data['is_default'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
            notes: $data['notes'] ?? null,
            deletedAt: isset($data['deleted_at']) ? new \DateTimeImmutable($data['deleted_at']) : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'account_name' => $this->accountName,
            'account_number' => $this->accountNumber,
            'bank_name' => $this->bankName,
            'bank_branch' => $this->bankBranch,
            'account_type' => $this->accountType,
            'currency' => $this->currency,
            'opening_balance' => $this->openingBalance,
            'current_balance' => $this->currentBalance,
            'is_default' => $this->isDefault,
            'is_active' => $this->isActive,
            'notes' => $this->notes,
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}