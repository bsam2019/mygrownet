<?php

namespace App\Domain\BizDocs\DocumentManagement\ValueObjects;

use InvalidArgumentException;

final class DocumentType
{
    public const INVOICE = 'invoice';
    public const RECEIPT = 'receipt';
    public const QUOTATION = 'quotation';
    public const DELIVERY_NOTE = 'delivery_note';
    public const PROFORMA_INVOICE = 'proforma_invoice';
    public const CREDIT_NOTE = 'credit_note';
    public const DEBIT_NOTE = 'debit_note';
    public const PURCHASE_ORDER = 'purchase_order';

    private const VALID_TYPES = [
        self::INVOICE,
        self::RECEIPT,
        self::QUOTATION,
        self::DELIVERY_NOTE,
        self::PROFORMA_INVOICE,
        self::CREDIT_NOTE,
        self::DEBIT_NOTE,
        self::PURCHASE_ORDER,
    ];

    private function __construct(private readonly string $value)
    {
        if (!in_array($value, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException("Invalid document type: {$value}");
        }
    }

    public static function invoice(): self
    {
        return new self(self::INVOICE);
    }

    public static function receipt(): self
    {
        return new self(self::RECEIPT);
    }

    public static function quotation(): self
    {
        return new self(self::QUOTATION);
    }

    public static function deliveryNote(): self
    {
        return new self(self::DELIVERY_NOTE);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isInvoice(): bool
    {
        return $this->value === self::INVOICE;
    }

    public function isReceipt(): bool
    {
        return $this->value === self::RECEIPT;
    }

    public function isQuotation(): bool
    {
        return $this->value === self::QUOTATION;
    }

    public function equals(DocumentType $other): bool
    {
        return $this->value === $other->value;
    }

    public function defaultPrefix(): string
    {
        return match ($this->value) {
            self::INVOICE => 'INV',
            self::RECEIPT => 'RCPT',
            self::QUOTATION => 'QTN',
            self::DELIVERY_NOTE => 'DN',
            self::PROFORMA_INVOICE => 'PI',
            self::CREDIT_NOTE => 'CN',
            self::DEBIT_NOTE => 'DN',
            self::PURCHASE_ORDER => 'PO',
        };
    }
}
