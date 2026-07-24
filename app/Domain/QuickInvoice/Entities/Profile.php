<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Entities;

use DateTimeImmutable;

class Profile
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly ?int $organizationId,
        public readonly ?string $name,
        public readonly ?string $address,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $logo,
        public readonly ?string $signature,
        public readonly ?string $preparedBy,
        public readonly ?string $taxNumber,
        public readonly ?float $defaultTaxRate,
        public readonly ?float $defaultDiscountRate,
        public readonly ?string $defaultNotes,
        public readonly ?string $defaultTerms,
        public readonly ?string $invoicePrefix,
        public readonly ?int $invoiceNextNumber,
        public readonly ?int $invoiceNumberPadding,
        public readonly ?string $quotationPrefix,
        public readonly ?int $quotationNextNumber,
        public readonly ?int $quotationNumberPadding,
        public readonly ?string $receiptPrefix,
        public readonly ?int $receiptNextNumber,
        public readonly ?int $receiptNumberPadding,
        public readonly ?string $deliveryNotePrefix,
        public readonly ?int $deliveryNoteNextNumber,
        public readonly ?int $deliveryNoteNumberPadding,
        public readonly ?string $defaultTemplate,
        public readonly ?string $defaultColor,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['user_id'],
            organizationId: $data['organization_id'] ?? null,
            name: $data['name'] ?? null,
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            logo: $data['logo'] ?? null,
            signature: $data['signature'] ?? null,
            preparedBy: $data['prepared_by'] ?? null,
            taxNumber: $data['tax_number'] ?? null,
            defaultTaxRate: isset($data['default_tax_rate']) ? (float) $data['default_tax_rate'] : null,
            defaultDiscountRate: isset($data['default_discount_rate']) ? (float) $data['default_discount_rate'] : null,
            defaultNotes: $data['default_notes'] ?? null,
            defaultTerms: $data['default_terms'] ?? null,
            invoicePrefix: $data['invoice_prefix'] ?? null,
            invoiceNextNumber: $data['invoice_next_number'] ?? null,
            invoiceNumberPadding: $data['invoice_number_padding'] ?? null,
            quotationPrefix: $data['quotation_prefix'] ?? null,
            quotationNextNumber: $data['quotation_next_number'] ?? null,
            quotationNumberPadding: $data['quotation_number_padding'] ?? null,
            receiptPrefix: $data['receipt_prefix'] ?? null,
            receiptNextNumber: $data['receipt_next_number'] ?? null,
            receiptNumberPadding: $data['receipt_number_padding'] ?? null,
            deliveryNotePrefix: $data['delivery_note_prefix'] ?? null,
            deliveryNoteNextNumber: $data['delivery_note_next_number'] ?? null,
            deliveryNoteNumberPadding: $data['delivery_note_number_padding'] ?? null,
            defaultTemplate: $data['default_template'] ?? null,
            defaultColor: $data['default_color'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'organization_id' => $this->organizationId,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' => $this->logo,
            'signature' => $this->signature,
            'prepared_by' => $this->preparedBy,
            'tax_number' => $this->taxNumber,
            'default_tax_rate' => $this->defaultTaxRate,
            'default_discount_rate' => $this->defaultDiscountRate,
            'default_notes' => $this->defaultNotes,
            'default_terms' => $this->defaultTerms,
            'invoice_prefix' => $this->invoicePrefix,
            'invoice_next_number' => $this->invoiceNextNumber,
            'invoice_number_padding' => $this->invoiceNumberPadding,
            'quotation_prefix' => $this->quotationPrefix,
            'quotation_next_number' => $this->quotationNextNumber,
            'quotation_number_padding' => $this->quotationNumberPadding,
            'receipt_prefix' => $this->receiptPrefix,
            'receipt_next_number' => $this->receiptNextNumber,
            'receipt_number_padding' => $this->receiptNumberPadding,
            'delivery_note_prefix' => $this->deliveryNotePrefix,
            'delivery_note_next_number' => $this->deliveryNoteNextNumber,
            'delivery_note_number_padding' => $this->deliveryNoteNumberPadding,
            'default_template' => $this->defaultTemplate,
            'default_color' => $this->defaultColor,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function generateDocumentNumber(string $type): string
    {
        $prefix = match ($type) {
            'invoice' => $this->invoicePrefix ?? 'INV',
            'quotation' => $this->quotationPrefix ?? 'QT',
            'receipt' => $this->receiptPrefix ?? 'RCP',
            'delivery_note' => $this->deliveryNotePrefix ?? 'DN',
            default => strtoupper(substr($type, 0, 3)),
        };
        $nextNumber = match ($type) {
            'invoice' => $this->invoiceNextNumber ?? 1,
            'quotation' => $this->quotationNextNumber ?? 1,
            'receipt' => $this->receiptNextNumber ?? 1,
            'delivery_note' => $this->deliveryNoteNextNumber ?? 1,
            default => 1,
        };
        $padding = match ($type) {
            'invoice' => $this->invoiceNumberPadding ?? 4,
            'quotation' => $this->quotationNumberPadding ?? 4,
            'receipt' => $this->receiptNumberPadding ?? 4,
            'delivery_note' => $this->deliveryNoteNumberPadding ?? 4,
            default => 4,
        };

        return $prefix . '-' . str_pad((string) $nextNumber, $padding, '0', STR_PAD_LEFT);
    }

    public function withIncrementedNumber(string $type): self
    {
        $data = $this->toArray();
        $field = $type . '_next_number';
        $data[$field] = ($data[$field] ?? 1) + 1;
        return self::reconstitute($data);
    }
}