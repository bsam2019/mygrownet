<?php

declare(strict_types=1);

namespace App\Domain\CMS\Core\ValueObjects;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case VOID = 'void';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::PARTIAL => 'Partially Paid',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
            self::VOID => 'Void',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::SENT => 'blue',
            self::PARTIAL => 'amber',
            self::PAID => 'green',
            self::CANCELLED => 'red',
            self::VOID => 'gray',
        };
    }

    public function canBeEdited(): bool
    {
        return $this === self::DRAFT;
    }

    public function canBeSent(): bool
    {
        return in_array($this, [self::DRAFT, self::SENT]);
    }

    public function canReceivePayment(): bool
    {
        return in_array($this, [self::SENT, self::PARTIAL]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::DRAFT, self::SENT, self::PARTIAL]);
    }

    public function canBeVoided(): bool
    {
        return $this === self::PAID;
    }

    public static function all(): array
    {
        return array_map(
            fn(self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ],
            self::cases()
        );
    }
}
