<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use DateTimeImmutable;

class ReportSchedule
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly string $name,
        public readonly string $reportType,
        public readonly string $frequency,
        public readonly array $recipients,
        public readonly string $format = 'pdf',
        public readonly bool $isActive = true,
        public readonly ?DateTimeImmutable $lastRunAt = null,
        public readonly ?DateTimeImmutable $nextRunAt = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            reportType: $data['report_type'],
            frequency: $data['frequency'],
            recipients: $data['recipients'] ?? [],
            format: $data['format'] ?? 'pdf',
            isActive: (bool) ($data['is_active'] ?? true),
            lastRunAt: isset($data['last_run_at']) ? new DateTimeImmutable($data['last_run_at']) : null,
            nextRunAt: isset($data['next_run_at']) ? new DateTimeImmutable($data['next_run_at']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'name' => $this->name,
            'report_type' => $this->reportType,
            'frequency' => $this->frequency,
            'recipients' => $this->recipients,
            'format' => $this->format,
            'is_active' => $this->isActive,
            'last_run_at' => $this->lastRunAt?->format('Y-m-d H:i:s'),
            'next_run_at' => $this->nextRunAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function withLastRun(DateTimeImmutable $lastRunAt, DateTimeImmutable $nextRunAt): self
    {
        return new self(
            id: $this->id,
            businessId: $this->businessId,
            name: $this->name,
            reportType: $this->reportType,
            frequency: $this->frequency,
            recipients: $this->recipients,
            format: $this->format,
            isActive: $this->isActive,
            lastRunAt: $lastRunAt,
            nextRunAt: $nextRunAt,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }
}
