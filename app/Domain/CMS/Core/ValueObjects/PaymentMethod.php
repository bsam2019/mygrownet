<?php

declare(strict_types=1);

namespace App\Domain\CMS\Core\ValueObjects;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK_TRANSFER = 'bank_transfer';
    case MOBILE_MONEY = 'mobile_money';
    case CHEQUE = 'cheque';
    case CARD = 'card';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Cash',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::MOBILE_MONEY => 'Mobile Money',
            self::CHEQUE => 'Cheque',
            self::CARD => 'Card',
        };
    }

    public function requiresReference(): bool
    {
        return in_array($this, [
            self::BANK_TRANSFER,
            self::MOBILE_MONEY,
            self::CHEQUE,
            self::CARD,
        ]);
    }

    public static function all(): array
    {
        return array_map(
            fn(self $method) => [
                'value' => $method->value,
                'label' => $method->label(),
                'requires_reference' => $method->requiresReference(),
            ],
            self::cases()
        );
    }
}
