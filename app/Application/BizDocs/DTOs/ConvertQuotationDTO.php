<?php

namespace App\Application\BizDocs\DTOs;

class ConvertQuotationDTO
{
    public function __construct(
        public readonly int $quotationId,
        public readonly ?string $issueDate = null,
        public readonly ?string $dueDate = null,
        public readonly ?string $notes = null,
        public readonly ?string $paymentInstructions = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            quotationId: $data['quotation_id'],
            issueDate: $data['issue_date'] ?? null,
            dueDate: $data['due_date'] ?? null,
            notes: $data['notes'] ?? null,
            paymentInstructions: $data['payment_instructions'] ?? null
        );
    }
}
