<?php

namespace App\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\ValueObjects\PaymentMethod;

class Expense
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly ?int $vendorId = null,
        public readonly ?int $accountId = null,
        public readonly ?\DateTimeImmutable $expenseDate = null,
        public readonly ?string $category = null,
        public readonly ?string $description = null,
        public readonly float $amount = 0.0,
        public readonly float $taxAmount = 0.0,
        public readonly ?PaymentMethod $paymentMethod = null,
        public readonly ?string $reference = null,
        public readonly ?string $receiptPath = null,
        public readonly ?int $receiptSize = null,
        public readonly ?string $receiptOriginalName = null,
        public readonly ?string $receiptMimeType = null,
        public readonly bool $isRecurring = false,
        public readonly ?string $notes = null,
        public readonly ?\DateTimeImmutable $createdAt = null,
        public readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function getTotalAmount(): float
    {
        return $this->amount + $this->taxAmount;
    }

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            vendorId: isset($data['vendor_id']) ? (int) $data['vendor_id'] : null,
            accountId: isset($data['account_id']) ? (int) $data['account_id'] : null,
            expenseDate: isset($data['expense_date']) ? new \DateTimeImmutable($data['expense_date']) : null,
            category: $data['category'] ?? null,
            description: $data['description'] ?? null,
            amount: (float) ($data['amount'] ?? 0.0),
            taxAmount: (float) ($data['tax_amount'] ?? 0.0),
            paymentMethod: isset($data['payment_method']) ? PaymentMethod::from($data['payment_method']) : null,
            reference: $data['reference'] ?? null,
            receiptPath: $data['receipt_path'] ?? null,
            receiptSize: isset($data['receipt_size']) ? (int) $data['receipt_size'] : null,
            receiptOriginalName: $data['receipt_original_name'] ?? null,
            receiptMimeType: $data['receipt_mime_type'] ?? null,
            isRecurring: (bool) ($data['is_recurring'] ?? false),
            notes: $data['notes'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'vendor_id' => $this->vendorId,
            'account_id' => $this->accountId,
            'expense_date' => $this->expenseDate?->format('Y-m-d H:i:s'),
            'category' => $this->category,
            'description' => $this->description,
            'amount' => $this->amount,
            'tax_amount' => $this->taxAmount,
            'payment_method' => $this->paymentMethod?->value,
            'reference' => $this->reference,
            'receipt_path' => $this->receiptPath,
            'receipt_size' => $this->receiptSize,
            'receipt_original_name' => $this->receiptOriginalName,
            'receipt_mime_type' => $this->receiptMimeType,
            'is_recurring' => $this->isRecurring,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}