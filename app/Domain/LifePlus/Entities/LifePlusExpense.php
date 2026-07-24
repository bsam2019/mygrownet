<?php

namespace App\Domain\LifePlus\Entities;

class LifePlusExpense
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly ?int $categoryId,
        public readonly float $amount,
        public readonly ?string $description,
        public readonly \DateTimeImmutable $expenseDate,
        public readonly bool $isSynced,
        public readonly ?string $localId,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['user_id'],
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            amount: (float) $data['amount'],
            description: $data['description'] ?? null,
            expenseDate: new \DateTimeImmutable($data['expense_date']),
            isSynced: (bool) ($data['is_synced'] ?? true),
            localId: $data['local_id'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'category_id' => $this->categoryId,
            'amount' => $this->amount,
            'description' => $this->description,
            'expense_date' => $this->expenseDate->format('Y-m-d'),
            'is_synced' => $this->isSynced,
            'local_id' => $this->localId,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
