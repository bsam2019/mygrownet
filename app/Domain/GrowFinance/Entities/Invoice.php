<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\ValueObjects\InvoiceStatus;
use DateTimeImmutable;

class Invoice
{
    public readonly ?int $id;
    public readonly int $businessId;
    public readonly ?int $customerId;
    public readonly ?int $templateId;
    public readonly ?string $invoiceNumber;
    public readonly ?DateTimeImmutable $invoiceDate;
    public readonly ?DateTimeImmutable $dueDate;
    public readonly InvoiceStatus $status;
    public readonly float $subtotal;
    public readonly float $taxAmount;
    public readonly float $discountAmount;
    public readonly float $totalAmount;
    public readonly float $amountPaid;
    public readonly ?string $notes;
    public readonly ?string $terms;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        int $businessId,
        ?int $customerId,
        ?int $templateId,
        ?string $invoiceNumber,
        ?DateTimeImmutable $invoiceDate,
        ?DateTimeImmutable $dueDate,
        InvoiceStatus $status,
        float $subtotal,
        float $taxAmount,
        float $discountAmount,
        float $totalAmount,
        float $amountPaid,
        ?string $notes,
        ?string $terms,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ) {
        $this->id = $id;
        $this->businessId = $businessId;
        $this->customerId = $customerId;
        $this->templateId = $templateId;
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceDate = $invoiceDate;
        $this->dueDate = $dueDate;
        $this->status = $status;
        $this->subtotal = $subtotal;
        $this->taxAmount = $taxAmount;
        $this->discountAmount = $discountAmount;
        $this->totalAmount = $totalAmount;
        $this->amountPaid = $amountPaid;
        $this->notes = $notes;
        $this->terms = $terms;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: $data['business_id'],
            customerId: $data['customer_id'] ?? null,
            templateId: $data['template_id'] ?? null,
            invoiceNumber: $data['invoice_number'] ?? null,
            invoiceDate: isset($data['invoice_date']) ? new DateTimeImmutable($data['invoice_date']) : null,
            dueDate: isset($data['due_date']) ? new DateTimeImmutable($data['due_date']) : null,
            status: InvoiceStatus::tryFrom($data['status']) ?? InvoiceStatus::DRAFT,
            subtotal: (float) ($data['subtotal'] ?? 0),
            taxAmount: (float) ($data['tax_amount'] ?? 0),
            discountAmount: (float) ($data['discount_amount'] ?? 0),
            totalAmount: (float) ($data['total_amount'] ?? 0),
            amountPaid: (float) ($data['amount_paid'] ?? 0),
            notes: $data['notes'] ?? null,
            terms: $data['terms'] ?? null,
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
        return $this->status === InvoiceStatus::PAID;
    }

    public function isOverdue(): bool
    {
        if ($this->dueDate === null) {
            return false;
        }
        if ($this->isPaid()) {
            return false;
        }
        return $this->dueDate < new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'customer_id' => $this->customerId,
            'template_id' => $this->templateId,
            'invoice_number' => $this->invoiceNumber,
            'invoice_date' => $this->invoiceDate?->format('Y-m-d'),
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'status' => $this->status->value,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->taxAmount,
            'discount_amount' => $this->discountAmount,
            'total_amount' => $this->totalAmount,
            'amount_paid' => $this->amountPaid,
            'balance_due' => $this->getBalanceDue(),
            'notes' => $this->notes,
            'terms' => $this->terms,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
