<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\ValueObjects\PeriodStatus;
use DateTimeImmutable;

class AccountingPeriod
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly int $fiscalYearId,
        public readonly string $label,
        public readonly DateTimeImmutable $startDate,
        public readonly DateTimeImmutable $endDate,
        public readonly PeriodStatus $status = PeriodStatus::OPEN,
        public readonly ?int $closedBy = null,
        public readonly ?DateTimeImmutable $closedAt = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: (int) $data['business_id'],
            fiscalYearId: (int) $data['fiscal_year_id'],
            label: $data['label'],
            startDate: new DateTimeImmutable($data['start_date']),
            endDate: new DateTimeImmutable($data['end_date']),
            status: PeriodStatus::from($data['status'] ?? 'open'),
            closedBy: $data['closed_by'] ?? null,
            closedAt: isset($data['closed_at']) ? new DateTimeImmutable($data['closed_at']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function close(int $userId, DateTimeImmutable $now): self
    {
        if (!$this->status->canTransitionTo(PeriodStatus::CLOSED)) {
            throw new \DomainException('Period cannot be closed from current state: ' . $this->status->value);
        }

        return new self(
            id: $this->id,
            businessId: $this->businessId,
            fiscalYearId: $this->fiscalYearId,
            label: $this->label,
            startDate: $this->startDate,
            endDate: $this->endDate,
            status: PeriodStatus::CLOSED,
            closedBy: $userId,
            closedAt: $now,
            createdAt: $this->createdAt,
            updatedAt: $now,
        );
    }

    public function reopen(): self
    {
        if (!$this->status->canTransitionTo(PeriodStatus::OPEN)) {
            throw new \DomainException('Closed period cannot be reopened from current state: ' . $this->status->value);
        }

        return new self(
            id: $this->id,
            businessId: $this->businessId,
            fiscalYearId: $this->fiscalYearId,
            label: $this->label,
            startDate: $this->startDate,
            endDate: $this->endDate,
            status: PeriodStatus::OPEN,
            closedBy: null,
            closedAt: null,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'fiscal_year_id' => $this->fiscalYearId,
            'label' => $this->label,
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate->format('Y-m-d'),
            'status' => $this->status->value,
            'closed_by' => $this->closedBy,
            'closed_at' => $this->closedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
