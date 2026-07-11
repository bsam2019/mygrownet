<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\ValueObjects;

class PaymentMethod
{
    private const CASH = 'cash';
    private const MOBILE_MONEY = 'mobile_money';
    private const CARD = 'card';
    private const CREDIT = 'credit';
    private const TRANSFER = 'transfer';

    private function __construct(private string $value) {}

    public static function cash(): self { return new self(self::CASH); }
    public static function mobileMoney(): self { return new self(self::MOBILE_MONEY); }
    public static function card(): self { return new self(self::CARD); }
    public static function credit(): self { return new self(self::CREDIT); }
    public static function transfer(): self { return new self(self::TRANSFER); }
    public static function fromString(string $value): self
    {
        $valid = [self::CASH, self::MOBILE_MONEY, self::CARD, self::CREDIT, self::TRANSFER];
        if (!in_array($value, $valid, true)) { throw new \InvalidArgumentException("Invalid payment method: {$value}"); }
        return new self($value);
    }
    public function value(): string { return $this->value; }
    public function isCash(): bool { return $this->value === self::CASH; }
    public function label(): string { return str_replace('_', ' ', ucfirst($this->value)); }
    public static function all(): array { return [self::CASH, self::MOBILE_MONEY, self::CARD, self::CREDIT, self::TRANSFER]; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
