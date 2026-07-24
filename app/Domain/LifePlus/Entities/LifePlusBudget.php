<?php

namespace App\Domain\LifePlus\Entities;

class LifePlusBudget
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly ?int $categoryId,
        public readonly float $amount,
        public readonly string $period,
        public readonly \DateTimeImmutable $startDate,
        public readonly ?\DateTimeImmutable $endDate,
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
            period: $data['period'] ?? 'monthly',
            startDate: new \DateTimeImmutable($data['start_date']),
            endDate: isset($data['end_date']) ? new \DateTimeImmutable($data['end_date']) : null,
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
            'period' => $this->period,
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
