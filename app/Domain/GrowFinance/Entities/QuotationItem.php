<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use DateTimeImmutable;

class QuotationItem
{
    public readonly ?int $id;
    public readonly int $quotationId;
    public readonly ?string $description;
    public readonly float $quantity;
    public readonly float $unitPrice;
    public readonly float $taxRate;
    public readonly float $discountRate;
    public readonly float $lineTotal;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        int $quotationId,
        ?string $description,
        float $quantity,
        float $unitPrice,
        float $taxRate,
        float $discountRate,
        float $lineTotal,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ) {
        $this->id = $id;
        $this->quotationId = $quotationId;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->taxRate = $taxRate;
        $this->discountRate = $discountRate;
        $this->lineTotal = $lineTotal;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            quotationId: $data['quotation_id'],
            description: $data['description'] ?? null,
            quantity: (float) ($data['quantity'] ?? 0),
            unitPrice: (float) ($data['unit_price'] ?? 0),
            taxRate: (float) ($data['tax_rate'] ?? 0),
            discountRate: (float) ($data['discount_rate'] ?? 0),
            lineTotal: (float) ($data['line_total'] ?? 0),
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'quotation_id' => $this->quotationId,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'tax_rate' => $this->taxRate,
            'discount_rate' => $this->discountRate,
            'line_total' => $this->lineTotal,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
