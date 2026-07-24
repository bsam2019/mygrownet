<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

class ReconciliationPeriod
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly int $bankAccountId,
        public readonly ?\DateTimeImmutable $startDate,
        public readonly ?\DateTimeImmutable $endDate,
        public readonly ?float $openingBalance,
        public readonly ?float $closingBalance,
        public readonly ?float $bookBalance,
        public readonly ?float $difference,
        public readonly ?string $status,
        public readonly ?int $createdBy,
        public readonly ?int $completedBy,
        public readonly ?\DateTimeImmutable $completedAt,
        public readonly ?string $notes,
        public readonly ?\DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: (int) $data['business_id'],
            bankAccountId: (int) $data['bank_account_id'],
            startDate: isset($data['start_date']) ? new \DateTimeImmutable($data['start_date']) : null,
            endDate: isset($data['end_date']) ? new \DateTimeImmutable($data['end_date']) : null,
            openingBalance: isset($data['opening_balance']) ? (float) $data['opening_balance'] : null,
            closingBalance: isset($data['closing_balance']) ? (float) $data['closing_balance'] : null,
            bookBalance: isset($data['book_balance']) ? (float) $data['book_balance'] : null,
            difference: isset($data['difference']) ? (float) $data['difference'] : null,
            status: $data['status'] ?? null,
            createdBy: isset($data['created_by']) ? (int) $data['created_by'] : null,
            completedBy: isset($data['completed_by']) ? (int) $data['completed_by'] : null,
            completedAt: isset($data['completed_at']) ? new \DateTimeImmutable($data['completed_at']) : null,
            notes: $data['notes'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'bank_account_id' => $this->bankAccountId,
            'start_date' => $this->startDate?->format('Y-m-d H:i:s'),
            'end_date' => $this->endDate?->format('Y-m-d H:i:s'),
            'opening_balance' => $this->openingBalance,
            'closing_balance' => $this->closingBalance,
            'book_balance' => $this->bookBalance,
            'difference' => $this->difference,
            'status' => $this->status,
            'created_by' => $this->createdBy,
            'completed_by' => $this->completedBy,
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
