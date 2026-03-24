<?php

namespace App\Application\BizDocs\DTOs;

class DocumentItemDTO
{
    public function __construct(
        public readonly string $description,
        public readonly float $quantity,
        public readonly int $unitPrice, // in cents
        public readonly float $taxRate = 0,
        public readonly int $discountAmount = 0, // in cents
        public readonly int $sortOrder = 0,
        public readonly ?string $dimensions = null,
        public readonly float $dimensionsValue = 1.0
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            description: $data['description'],
            quantity: (float) $data['quantity'],
            unitPrice: (int) ($data['unit_price'] * 100), // convert to cents
            taxRate: (float) ($data['tax_rate'] ?? 0),
            discountAmount: (int) (($data['discount_amount'] ?? 0) * 100), // convert to cents
            sortOrder: (int) ($data['sort_order'] ?? 0),
            dimensions: $data['dimensions'] ?? null,
            dimensionsValue: (float) ($data['dimensions_value'] ?? 1.0)
        );
    }
}
