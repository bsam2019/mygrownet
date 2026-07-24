<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\ValueObjects\QuotationStatus;
use DateTimeImmutable;

class Quotation
{
    public readonly ?int $id;
    public readonly int $businessId;
    public readonly ?int $customerId;
    public readonly ?int $templateId;
    public readonly ?string $quotationNumber;
    public readonly ?DateTimeImmutable $quotationDate;
    public readonly ?DateTimeImmutable $validUntil;
    public readonly QuotationStatus $status;
    public readonly float $subtotal;
    public readonly float $taxAmount;
    public readonly float $discountAmount;
    public readonly float $totalAmount;
    public readonly ?string $notes;
    public readonly ?string $terms;
    public readonly ?string $subject;
    public readonly ?int $convertedInvoiceId;
    public readonly ?DateTimeImmutable $sentAt;
    public readonly ?DateTimeImmutable $acceptedAt;
    public readonly ?DateTimeImmutable $rejectedAt;
    public readonly ?string $rejectionReason;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        int $businessId,
        ?int $customerId,
        ?int $templateId,
        ?string $quotationNumber,
        ?DateTimeImmutable $quotationDate,
        ?DateTimeImmutable $validUntil,
        QuotationStatus $status,
        float $subtotal,
        float $taxAmount,
        float $discountAmount,
        float $totalAmount,
        ?string $notes,
        ?string $terms,
        ?string $subject,
        ?int $convertedInvoiceId,
        ?DateTimeImmutable $sentAt,
        ?DateTimeImmutable $acceptedAt,
        ?DateTimeImmutable $rejectedAt,
        ?string $rejectionReason,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ) {
        $this->id = $id;
        $this->businessId = $businessId;
        $this->customerId = $customerId;
        $this->templateId = $templateId;
        $this->quotationNumber = $quotationNumber;
        $this->quotationDate = $quotationDate;
        $this->validUntil = $validUntil;
        $this->status = $status;
        $this->subtotal = $subtotal;
        $this->taxAmount = $taxAmount;
        $this->discountAmount = $discountAmount;
        $this->totalAmount = $totalAmount;
        $this->notes = $notes;
        $this->terms = $terms;
        $this->subject = $subject;
        $this->convertedInvoiceId = $convertedInvoiceId;
        $this->sentAt = $sentAt;
        $this->acceptedAt = $acceptedAt;
        $this->rejectedAt = $rejectedAt;
        $this->rejectionReason = $rejectionReason;
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
            quotationNumber: $data['quotation_number'] ?? null,
            quotationDate: isset($data['quotation_date']) ? new DateTimeImmutable($data['quotation_date']) : null,
            validUntil: isset($data['valid_until']) ? new DateTimeImmutable($data['valid_until']) : null,
            status: QuotationStatus::tryFrom($data['status']) ?? QuotationStatus::DRAFT,
            subtotal: (float) ($data['subtotal'] ?? 0),
            taxAmount: (float) ($data['tax_amount'] ?? 0),
            discountAmount: (float) ($data['discount_amount'] ?? 0),
            totalAmount: (float) ($data['total_amount'] ?? 0),
            notes: $data['notes'] ?? null,
            terms: $data['terms'] ?? null,
            subject: $data['subject'] ?? null,
            convertedInvoiceId: $data['converted_invoice_id'] ?? null,
            sentAt: isset($data['sent_at']) ? new DateTimeImmutable($data['sent_at']) : null,
            acceptedAt: isset($data['accepted_at']) ? new DateTimeImmutable($data['accepted_at']) : null,
            rejectedAt: isset($data['rejected_at']) ? new DateTimeImmutable($data['rejected_at']) : null,
            rejectionReason: $data['rejection_reason'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function isExpired(): bool
    {
        if ($this->validUntil === null) {
            return false;
        }
        if (in_array($this->status, [QuotationStatus::ACCEPTED, QuotationStatus::REJECTED, QuotationStatus::CONVERTED], true)) {
            return false;
        }
        return $this->validUntil < new DateTimeImmutable();
    }

    public function getDaysUntilExpiry(): int
    {
        if ($this->validUntil === null) {
            return 0;
        }
        $now = new DateTimeImmutable();
        if ($this->validUntil < $now) {
            return 0;
        }
        return (int) $now->diff($this->validUntil)->days;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'customer_id' => $this->customerId,
            'template_id' => $this->templateId,
            'quotation_number' => $this->quotationNumber,
            'quotation_date' => $this->quotationDate?->format('Y-m-d'),
            'valid_until' => $this->validUntil?->format('Y-m-d'),
            'status' => $this->status->value,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->taxAmount,
            'discount_amount' => $this->discountAmount,
            'total_amount' => $this->totalAmount,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'subject' => $this->subject,
            'converted_invoice_id' => $this->convertedInvoiceId,
            'sent_at' => $this->sentAt?->format('Y-m-d H:i:s'),
            'accepted_at' => $this->acceptedAt?->format('Y-m-d H:i:s'),
            'rejected_at' => $this->rejectedAt?->format('Y-m-d H:i:s'),
            'rejection_reason' => $this->rejectionReason,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
