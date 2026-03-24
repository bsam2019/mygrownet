<?php

namespace App\Domain\BizDocs\DocumentManagement\ValueObjects;

use InvalidArgumentException;

final class PaymentMethod
{
    private const VALID_METHODS = [
        'cash',
        'mobile_money',
        'bank_transfer',
        'cheque',
        'card',
        'other',
    ];

    private function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, self::VALID_METHODS)) {
            throw new InvalidArgumentException("Invalid payment method: {$value}");
        }
    }

    public static function fromString(string $value): self
    {
        return new self(strtolower($value));
    }

    public static function cash(): self
    {
        return new self('cash');
    }

    public static function mobileMoney(): self
    {
        return new self('mobile_money');
    }

    public static function bankTransfer(): self
    {
        return new self('bank_transfer');
    }

    public static function cheque(): self
    {
        return new self('cheque');
    }

    public static function card(): self
    {
        return new self('card');
    }

    public static function other(): self
    {
        return new self('other');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match ($this->value) {
            'cash' => 'Cash',
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'card' => 'Card',
            'other' => 'Other',
        };
    }

    public function equals(PaymentMethod $other): bool
    {
        return $this->value === $other->value;
    }

    public static function all(): array
    {
        return self::VALID_METHODS;
    }
}
