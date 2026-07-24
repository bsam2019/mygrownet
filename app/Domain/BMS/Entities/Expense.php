<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use DateTimeImmutable;

class Expense
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly int $categoryId,
        public readonly ?int $jobId,
        public readonly string $expenseNumber,
        public readonly string $description,
        public readonly float $amount,
        public readonly string $paymentMethod,
        public readonly ?string $receiptPath,
        public readonly ?string $receiptNumber,
        public readonly DateTimeImmutable $expenseDate,
        public readonly string $approvalStatus,
        public readonly ?string $approvalNotes,
        public readonly ?int $approvedBy,
        public readonly ?DateTimeImmutable $approvedAt,
        public readonly ?string $notes,
        public readonly ?int $branchId,
        public readonly ?int $recordedBy,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            companyId: (int) $data['company_id'],
            categoryId: (int) $data['category_id'],
            jobId: $data['job_id'] ?? null,
            expenseNumber: $data['expense_number'] ?? '',
            description: $data['description'],
            amount: (float) $data['amount'],
            paymentMethod: $data['payment_method'],
            receiptPath: $data['receipt_path'] ?? null,
            receiptNumber: $data['receipt_number'] ?? null,
            expenseDate: isset($data['expense_date']) ? new DateTimeImmutable($data['expense_date']) : new DateTimeImmutable(),
            approvalStatus: $data['approval_status'] ?? 'pending',
            approvalNotes: $data['approval_notes'] ?? null,
            approvedBy: $data['approved_by'] ?? null,
            approvedAt: isset($data['approved_at']) ? new DateTimeImmutable($data['approved_at']) : null,
            notes: $data['notes'] ?? null,
            branchId: $data['branch_id'] ?? null,
            recordedBy: $data['recorded_by'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'category_id' => $this->categoryId,
            'job_id' => $this->jobId,
            'expense_number' => $this->expenseNumber,
            'description' => $this->description,
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod,
            'receipt_path' => $this->receiptPath,
            'receipt_number' => $this->receiptNumber,
            'expense_date' => $this->expenseDate->format('Y-m-d'),
            'approval_status' => $this->approvalStatus,
            'approval_notes' => $this->approvalNotes,
            'approved_by' => $this->approvedBy,
            'approved_at' => $this->approvedAt?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'branch_id' => $this->branchId,
            'recorded_by' => $this->recordedBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
