<?php

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Job
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly int $customerId,
        public readonly string $jobNumber,
        public readonly string $jobType,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?float $estimatedValue,
        public readonly ?float $quotedValue,
        public readonly ?float $actualValue,
        public readonly ?float $materialCost,
        public readonly ?float $laborCost,
        public readonly ?float $overheadCost,
        public readonly ?float $totalCost,
        public readonly ?float $profitAmount,
        public readonly ?float $profitMargin,
        public readonly string $status,
        public readonly string $priority,
        public readonly ?int $assignedTo,
        public readonly ?int $branchId,
        public readonly ?int $quotationId,
        public readonly ?int $invoiceId,
        public readonly ?DateTimeImmutable $deadline,
        public readonly ?DateTimeImmutable $startedAt,
        public readonly ?DateTimeImmutable $completedAt,
        public readonly bool $isLocked,
        public readonly ?DateTimeImmutable $lockedAt,
        public readonly ?int $lockedBy,
        public readonly ?string $notes,
        public readonly ?int $createdBy,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            customerId: (int) $data['customer_id'],
            jobNumber: $data['job_number'] ?? '',
            jobType: $data['job_type'] ?? '',
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            estimatedValue: array_key_exists('estimated_value', $data) ? (float) $data['estimated_value'] : null,
            quotedValue: array_key_exists('quoted_value', $data) ? (float) $data['quoted_value'] : null,
            actualValue: array_key_exists('actual_value', $data) ? (float) $data['actual_value'] : null,
            materialCost: array_key_exists('material_cost', $data) ? (float) $data['material_cost'] : null,
            laborCost: array_key_exists('labor_cost', $data) ? (float) $data['labor_cost'] : null,
            overheadCost: array_key_exists('overhead_cost', $data) ? (float) $data['overhead_cost'] : null,
            totalCost: array_key_exists('total_cost', $data) ? (float) $data['total_cost'] : null,
            profitAmount: array_key_exists('profit_amount', $data) ? (float) $data['profit_amount'] : null,
            profitMargin: array_key_exists('profit_margin', $data) ? (float) $data['profit_margin'] : null,
            status: $data['status'] ?? 'pending',
            priority: $data['priority'] ?? 'normal',
            assignedTo: $data['assigned_to'] ?? null,
            branchId: $data['branch_id'] ?? null,
            quotationId: $data['quotation_id'] ?? null,
            invoiceId: $data['invoice_id'] ?? null,
            deadline: isset($data['deadline']) ? new DateTimeImmutable($data['deadline']) : null,
            startedAt: isset($data['started_at']) ? new DateTimeImmutable($data['started_at']) : null,
            completedAt: isset($data['completed_at']) ? new DateTimeImmutable($data['completed_at']) : null,
            isLocked: (bool) ($data['is_locked'] ?? false),
            lockedAt: isset($data['locked_at']) ? new DateTimeImmutable($data['locked_at']) : null,
            lockedBy: $data['locked_by'] ?? null,
            notes: $data['notes'] ?? null,
            createdBy: $data['created_by'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'customer_id' => $this->customerId,
            'job_number' => $this->jobNumber,
            'job_type' => $this->jobType,
            'title' => $this->title,
            'description' => $this->description,
            'estimated_value' => $this->estimatedValue,
            'quoted_value' => $this->quotedValue,
            'actual_value' => $this->actualValue,
            'material_cost' => $this->materialCost,
            'labor_cost' => $this->laborCost,
            'overhead_cost' => $this->overheadCost,
            'total_cost' => $this->totalCost,
            'profit_amount' => $this->profitAmount,
            'profit_margin' => $this->profitMargin,
            'status' => $this->status,
            'priority' => $this->priority,
            'assigned_to' => $this->assignedTo,
            'branch_id' => $this->branchId,
            'quotation_id' => $this->quotationId,
            'invoice_id' => $this->invoiceId,
            'deadline' => $this->deadline?->format('Y-m-d'),
            'started_at' => $this->startedAt?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'is_locked' => $this->isLocked,
            'locked_at' => $this->lockedAt?->format('Y-m-d H:i:s'),
            'locked_by' => $this->lockedBy,
            'notes' => $this->notes,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
