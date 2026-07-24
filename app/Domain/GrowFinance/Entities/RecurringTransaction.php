<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

class RecurringTransaction
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly ?string $type,
        public readonly ?int $accountId,
        public readonly ?int $vendorId,
        public readonly ?int $customerId,
        public readonly ?string $description,
        public readonly ?string $category,
        public readonly float $amount,
        public readonly ?string $paymentMethod,
        public readonly ?string $frequency,
        public readonly ?\DateTimeImmutable $startDate,
        public readonly ?\DateTimeImmutable $endDate,
        public readonly ?\DateTimeImmutable $nextDueDate,
        public readonly ?\DateTimeImmutable $lastProcessedDate,
        public readonly bool $isActive,
        public readonly int $occurrencesCount,
        public readonly ?int $maxOccurrences,
        public readonly ?string $notes,
        public readonly ?\DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: (int) $data['business_id'],
            type: $data['type'] ?? null,
            accountId: isset($data['account_id']) ? (int) $data['account_id'] : null,
            vendorId: isset($data['vendor_id']) ? (int) $data['vendor_id'] : null,
            customerId: isset($data['customer_id']) ? (int) $data['customer_id'] : null,
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
            amount: (float) $data['amount'],
            paymentMethod: $data['payment_method'] ?? null,
            frequency: $data['frequency'] ?? null,
            startDate: isset($data['start_date']) ? new \DateTimeImmutable($data['start_date']) : null,
            endDate: isset($data['end_date']) ? new \DateTimeImmutable($data['end_date']) : null,
            nextDueDate: isset($data['next_due_date']) ? new \DateTimeImmutable($data['next_due_date']) : null,
            lastProcessedDate: isset($data['last_processed_date']) ? new \DateTimeImmutable($data['last_processed_date']) : null,
            isActive: (bool) ($data['is_active'] ?? false),
            occurrencesCount: (int) ($data['occurrences_count'] ?? 0),
            maxOccurrences: isset($data['max_occurrences']) ? (int) $data['max_occurrences'] : null,
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
            'type' => $this->type,
            'account_id' => $this->accountId,
            'vendor_id' => $this->vendorId,
            'customer_id' => $this->customerId,
            'description' => $this->description,
            'category' => $this->category,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
            'frequency' => $this->frequency,
            'start_date' => $this->startDate?->format('Y-m-d H:i:s'),
            'end_date' => $this->endDate?->format('Y-m-d H:i:s'),
            'next_due_date' => $this->nextDueDate?->format('Y-m-d H:i:s'),
            'last_processed_date' => $this->lastProcessedDate?->format('Y-m-d H:i:s'),
            'is_active' => $this->isActive,
            'occurrences_count' => $this->occurrencesCount,
            'max_occurrences' => $this->maxOccurrences,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function shouldProcess(): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if (!$this->nextDueDate) {
            return false;
        }

        if ($this->nextDueDate > new \DateTimeImmutable('now')) {
            return false;
        }

        if ($this->maxOccurrences !== null && $this->occurrencesCount >= $this->maxOccurrences) {
            return false;
        }

        return true;
    }
}
