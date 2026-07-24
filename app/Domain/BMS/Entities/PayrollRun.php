<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class PayrollRun
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly string $periodStart,
        public readonly string $periodEnd,
        public readonly string $status,
        public readonly float $totalGrossPay,
        public readonly float $totalDeductions,
        public readonly float $totalNetPay,
        public readonly ?int $processedBy,
        public readonly ?DateTimeImmutable $processedAt,
        public readonly ?string $notes,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            periodStart: $data['period_start'],
            periodEnd: $data['period_end'],
            status: $data['status'] ?? 'draft',
            totalGrossPay: (float) ($data['total_gross_pay'] ?? 0),
            totalDeductions: (float) ($data['total_deductions'] ?? 0),
            totalNetPay: (float) ($data['total_net_pay'] ?? 0),
            processedBy: $data['processed_by'] ?? null,
            processedAt: isset($data['processed_at']) ? new DateTimeImmutable($data['processed_at']) : null,
            notes: $data['notes'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'period_start' => $this->periodStart,
            'period_end' => $this->periodEnd,
            'status' => $this->status,
            'total_gross_pay' => $this->totalGrossPay,
            'total_deductions' => $this->totalDeductions,
            'total_net_pay' => $this->totalNetPay,
            'processed_by' => $this->processedBy,
            'processed_at' => $this->processedAt?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
