<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\ValueObjects;

enum DocumentType: string
{
    case INVOICE = 'invoice';
    case DELIVERY_NOTE = 'delivery_note';
    case QUOTATION = 'quotation';
    case RECEIPT = 'receipt';

    public function label(): string
    {
        return match ($this) {
            self::INVOICE => 'Invoice',
            self::DELIVERY_NOTE => 'Delivery Note',
            self::QUOTATION => 'Quotation',
            self::RECEIPT => 'Receipt',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::INVOICE => 'INV',
            self::DELIVERY_NOTE => 'DN',
            self::QUOTATION => 'QT',
            self::RECEIPT => 'RCP',
        };
    }

    public function showDueDate(): bool
    {
        return match ($this) {
            self::INVOICE, self::QUOTATION => true,
            self::DELIVERY_NOTE, self::RECEIPT => false,
        };
    }

    public function showPaymentStatus(): bool
    {
        return match ($this) {
            self::INVOICE, self::RECEIPT => true,
            self::DELIVERY_NOTE, self::QUOTATION => false,
        };
    }
}
