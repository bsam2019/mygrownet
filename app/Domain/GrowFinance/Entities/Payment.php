<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\ValueObjects\PaymentMethod;
use DateTimeImmutable;

class Payment
{
    public readonly ?int $id;
    public readonly int $businessId;
    public readonly ?string $payableType;
    public readonly ?int $payableId;
    public readonly ?DateTimeImmutable $paymentDate;
    public readonly float $amount;
    public readonly ?PaymentMethod $paymentMethod;
    public readonly ?string $reference;
    public readonly ?string $notes;
    public readonly ?DateTimeImmutable $createdAt;
    public readonly ?DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        int $businessId,
        ?string $payableType,
        ?int $payableId,
        ?DateTimeImmutable $paymentDate,
        float $amount,
        ?PaymentMethod $paymentMethod,
        ?string $reference,
        ?string $notes,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ) {
        $this->id = $id;
        $this->businessId = $businessId;
        $this->payableType = $payableType;
        $this->payableId = $payableId;
        $this->paymentDate = $paymentDate;
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
        $this->reference = $reference;
        $this->notes = $notes;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            businessId: $data['business_id'],
            payableType: $data['payable_type'] ?? null,
            payableId: $data['payable_id'] ?? null,
            paymentDate: isset($data['payment_date']) ? new DateTimeImmutable($data['payment_date']) : null,
            amount: (float) ($data['amount'] ?? 0),
            paymentMethod: isset($data['payment_method']) ? PaymentMethod::tryFrom($data['payment_method']) : null,
            reference: $data['reference'] ?? null,
            notes: $data['notes'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'payable_type' => $this->payableType,
            'payable_id' => $this->payableId,
            'payment_date' => $this->paymentDate?->format('Y-m-d'),
            'amount' => $this->amount,
            'payment_method' => $this->paymentMethod?->value,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
