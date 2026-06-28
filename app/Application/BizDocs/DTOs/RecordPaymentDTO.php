<?php

namespace App\Application\BizDocs\DTOs;

class RecordPaymentDTO
{
    public function __construct(
        public readonly int $documentId,
        public readonly string $paymentDate,
        public readonly float $amount,
        public readonly string $paymentMethod,
        public readonly ?string $referenceNumber = null,
        public readonly ?string $notes = null,
        public readonly bool $generateReceipt = true
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            documentId: $data['document_id'],
            paymentDate: $data['payment_date'],
            amount: $data['amount'],
            paymentMethod: $data['payment_method'],
            referenceNumber: $data['reference_number'] ?? null,
            notes: $data['notes'] ?? null,
            generateReceipt: $data['generate_receipt'] ?? true
        );
    }
}
