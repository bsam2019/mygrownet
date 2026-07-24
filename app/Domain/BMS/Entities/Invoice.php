<?php

declare(strict_types=1);

namespace App\Domain\BMS\Entities;

use App\Domain\BMS\Core\ValueObjects\InvoiceStatus;
use DateTimeImmutable;

class Invoice
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $companyId,
        public readonly int $customerId,
        public readonly string $invoiceNumber,
        public readonly DateTimeImmutable $invoiceDate,
        public readonly ?DateTimeImmutable $dueDate,
        public readonly string $status,
        public readonly float $subtotal,
        public readonly float $taxAmount,
        public readonly float $discountAmount,
        public readonly float $totalAmount,
        public readonly float $amountPaid,
        public readonly ?string $notes,
        public readonly ?string $terms,
        public readonly ?int $branchId,
        public readonly ?int $jobId,
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
            invoiceNumber: $data['invoice_number'],
            invoiceDate: isset($data['invoice_date']) ? new DateTimeImmutable($data['invoice_date']) : new DateTimeImmutable(),
            dueDate: isset($data['due_date']) ? new DateTimeImmutable($data['due_date']) : null,
            status: $data['status'] ?? InvoiceStatus::DRAFT->value,
            subtotal: (float) ($data['subtotal'] ?? 0),
            taxAmount: (float) ($data['tax_amount'] ?? 0),
            discountAmount: (float) ($data['discount_amount'] ?? 0),
            totalAmount: (float) ($data['total_amount'] ?? 0),
            amountPaid: (float) ($data['amount_paid'] ?? 0),
            notes: $data['notes'] ?? null,
            terms: $data['terms'] ?? null,
            branchId: $data['branch_id'] ?? null,
            jobId: $data['job_id'] ?? null,
            createdBy: $data['created_by'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function getBalanceDue(): float
    {
        return $this->totalAmount - $this->amountPaid;
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::PAID->value;
    }

    public function isOverdue(): bool
    {
        if ($this->dueDate === null) return false;
        if ($this->isPaid()) return false;
        return $this->dueDate < new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'customer_id' => $this->customerId,
            'invoice_number' => $this->invoiceNumber,
            'invoice_date' => $this->invoiceDate->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->taxAmount,
            'discount_amount' => $this->discountAmount,
            'total_amount' => $this->totalAmount,
            'amount_paid' => $this->amountPaid,
            'balance_due' => $this->getBalanceDue(),
            'notes' => $this->notes,
            'terms' => $this->terms,
            'branch_id' => $this->branchId,
            'job_id' => $this->jobId,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
