<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\ValueObjects;

class MovementType
{
    private const PURCHASE_IN = 'purchase_in';
    private const SALE_OUT = 'sale_out';
    private const ADJUSTMENT_IN = 'adjustment_in';
    private const ADJUSTMENT_OUT = 'adjustment_out';
    private const DAMAGE_OUT = 'damage_out';
    private const EXPIRED_OUT = 'expired_out';
    private const RETURN_IN = 'return_in';
    private const PHYSICAL_COUNT = 'physical_count';
    private const OPENING_BALANCE = 'opening_balance';
    private const TRANSFER_OUT = 'transfer_out';
    private const TRANSFER_IN = 'transfer_in';
    private const TRANSFER_CANCELLED = 'transfer_cancelled';

    private function __construct(private string $value) {}

    public static function purchaseIn(): self { return new self(self::PURCHASE_IN); }
    public static function saleOut(): self { return new self(self::SALE_OUT); }
    public static function adjustmentIn(): self { return new self(self::ADJUSTMENT_IN); }
    public static function adjustmentOut(): self { return new self(self::ADJUSTMENT_OUT); }
    public static function damageOut(): self { return new self(self::DAMAGE_OUT); }
    public static function expiredOut(): self { return new self(self::EXPIRED_OUT); }
    public static function returnIn(): self { return new self(self::RETURN_IN); }
    public static function physicalCount(): self { return new self(self::PHYSICAL_COUNT); }
    public static function openingBalance(): self { return new self(self::OPENING_BALANCE); }
    public static function transferOut(): self { return new self(self::TRANSFER_OUT); }
    public static function transferIn(): self { return new self(self::TRANSFER_IN); }
    public static function transferCancelled(): self { return new self(self::TRANSFER_CANCELLED); }

    public static function fromString(string $value): self
    {
        $valid = [self::PURCHASE_IN, self::SALE_OUT, self::ADJUSTMENT_IN, self::ADJUSTMENT_OUT, self::DAMAGE_OUT, self::EXPIRED_OUT, self::RETURN_IN, self::PHYSICAL_COUNT, self::OPENING_BALANCE, self::TRANSFER_OUT, self::TRANSFER_IN, self::TRANSFER_CANCELLED];
        if (!in_array($value, $valid, true)) {
            throw new \InvalidArgumentException("Invalid movement type: {$value}");
        }
        return new self($value);
    }

    public function value(): string { return $this->value; }
    public function isIncoming(): bool { return in_array($this->value, [self::PURCHASE_IN, self::ADJUSTMENT_IN, self::RETURN_IN, self::OPENING_BALANCE], true); }
    public function isOutgoing(): bool { return !$this->isIncoming(); }
    public function label(): string { return str_replace('_', ' ', ucfirst($this->value)); }
    public static function all(): array { return [self::PURCHASE_IN, self::SALE_OUT, self::ADJUSTMENT_IN, self::ADJUSTMENT_OUT, self::DAMAGE_OUT, self::EXPIRED_OUT, self::RETURN_IN, self::PHYSICAL_COUNT, self::OPENING_BALANCE]; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
