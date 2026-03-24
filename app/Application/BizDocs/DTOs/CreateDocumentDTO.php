<?php

namespace App\Application\BizDocs\DTOs;

class CreateDocumentDTO
{
    public function __construct(
        public readonly int $businessId,
        public readonly int $customerId,
        public readonly string $documentType,
        public readonly string $issueDate,
        public readonly ?int $templateId = null,
        public readonly ?string $dueDate = null,
        public readonly ?string $validityDate = null,
        public readonly ?string $notes = null,
        public readonly ?string $terms = null,
        public readonly ?string $paymentInstructions = null,
        public readonly string $currency = 'ZMW',
        public readonly string $discountType = 'amount',
        public readonly float $discountValue = 0,
        public readonly bool $collectTax = true,
        public readonly array $items = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            businessId: $data['business_id'],
            customerId: $data['customer_id'],
            documentType: $data['document_type'],
            issueDate: $data['issue_date'],
            templateId: $data['template_id'] ?? null,
            dueDate: $data['due_date'] ?? null,
            validityDate: $data['validity_date'] ?? null,
            notes: $data['notes'] ?? null,
            terms: $data['terms'] ?? null,
            paymentInstructions: $data['payment_instructions'] ?? null,
            currency: $data['currency'] ?? 'ZMW',
            discountType: $data['discount_type'] ?? 'amount',
            discountValue: $data['discount_value'] ?? 0,
            collectTax: $data['collect_tax'] ?? true,
            items: $data['items'] ?? []
        );
    }
}
