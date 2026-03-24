<?php

namespace App\Domain\BizDocs\DocumentManagement\ValueObjects;

use InvalidArgumentException;

final class DocumentStatus
{
    public const DRAFT = 'draft';
    public const SENT = 'sent';
    public const OVERDUE = 'overdue';
    public const PARTIALLY_PAID = 'partially_paid';
    public const PAID = 'paid';
    public const CANCELLED = 'cancelled';
    public const ISSUED = 'issued';
    public const VOIDED = 'voided';
    public const ACCEPTED = 'accepted';
    public const REJECTED = 'rejected';
    public const EXPIRED = 'expired';
    public const DELIVERED = 'delivered';
    public const ACKNOWLEDGED = 'acknowledged';

    private const INVOICE_STATUSES = [
        self::DRAFT,
        self::SENT,
        self::OVERDUE,
        self::PARTIALLY_PAID,
        self::PAID,
        self::CANCELLED,
    ];

    private const RECEIPT_STATUSES = [
        self::DRAFT,
        self::ISSUED,
        self::VOIDED,
    ];

    private const QUOTATION_STATUSES = [
        self::DRAFT,
        self::SENT,
        self::ACCEPTED,
        self::REJECTED,
        self::EXPIRED,
    ];

    private const DELIVERY_NOTE_STATUSES = [
        self::DRAFT,
        self::SENT,
        self::DELIVERED,
        self::ACKNOWLEDGED,
    ];

    private function __construct(private readonly string $value)
    {
    }

    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    public static function sent(): self
    {
        return new self(self::SENT);
    }

    public static function paid(): self
    {
        return new self(self::PAID);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    public function isFinal(): bool
    {
        return !$this->isDraft();
    }

    public function canTransitionTo(DocumentStatus $newStatus, DocumentType $documentType): bool
    {
        if ($documentType->isInvoice()) {
            return $this->canTransitionInvoice($newStatus);
        }

        if ($documentType->isReceipt()) {
            return $this->canTransitionReceipt($newStatus);
        }

        if ($documentType->isQuotation()) {
            return $this->canTransitionQuotation($newStatus);
        }

        return true;
    }

    public function equals(DocumentStatus $other): bool
    {
        return $this->value === $other->value;
    }

    private function canTransitionInvoice(DocumentStatus $newStatus): bool
    {
        return match ($this->value) {
            self::DRAFT => in_array($newStatus->value, [self::SENT, self::CANCELLED]),
            self::SENT => in_array($newStatus->value, [self::OVERDUE, self::PARTIALLY_PAID, self::PAID, self::CANCELLED]),
            self::OVERDUE => in_array($newStatus->value, [self::PARTIALLY_PAID, self::PAID, self::CANCELLED]),
            self::PARTIALLY_PAID => in_array($newStatus->value, [self::PAID, self::CANCELLED]),
            self::PAID, self::CANCELLED => false,
            default => false,
        };
    }

    private function canTransitionReceipt(DocumentStatus $newStatus): bool
    {
        return match ($this->value) {
            self::DRAFT => $newStatus->value === self::ISSUED,
            self::ISSUED => $newStatus->value === self::VOIDED,
            self::VOIDED => false,
            default => false,
        };
    }

    private function canTransitionQuotation(DocumentStatus $newStatus): bool
    {
        return match ($this->value) {
            self::DRAFT => in_array($newStatus->value, [self::SENT]),
            self::SENT => in_array($newStatus->value, [self::ACCEPTED, self::REJECTED, self::EXPIRED]),
            self::ACCEPTED, self::REJECTED, self::EXPIRED => false,
            default => false,
        };
    }
}
