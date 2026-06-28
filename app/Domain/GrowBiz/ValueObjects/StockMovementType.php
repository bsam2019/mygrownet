<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\ValueObjects;

class StockMovementType
{
    private const PURCHASE = 'purchase';
    private const SALE = 'sale';
    private const ADJUSTMENT = 'adjustment';
    private const TRANSFER = 'transfer';
    private const RETURN = 'return';
    private const DAMAGE = 'damage';
    private const INITIAL = 'initial';

    private function __construct(private string $value) {}

    public static function purchase(): self { return new self(self::PURCHASE); }
    public static function sale(): self { return new self(self::SALE); }
    public static function adjustment(): self { return new self(self::ADJUSTMENT); }
    public static function transfer(): self { return new self(self::TRANSFER); }
    public static function return(): self { return new self(self::RETURN); }
    public static function damage(): self { return new self(self::DAMAGE); }
    public static function initial(): self { return new self(self::INITIAL); }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::PURCHASE => self::purchase(),
            self::SALE => self::sale(),
            self::ADJUSTMENT => self::adjustment(),
            self::TRANSFER => self::transfer(),
            self::RETURN => self::return(),
            self::DAMAGE => self::damage(),
            self::INITIAL => self::initial(),
            default => throw new \InvalidArgumentException("Invalid stock movement type: {$value}"),
        };
    }

    public function value(): string { return $this->value; }

    public function isIncoming(): bool
    {
        return in_array($this->value, [self::PURCHASE, self::RETURN, self::INITIAL, self::ADJUSTMENT]);
    }

    public function isOutgoing(): bool
    {
        return in_array($this->value, [self::SALE, self::DAMAGE, self::TRANSFER]);
    }

    public function label(): string
    {
        return match ($this->value) {
            self::PURCHASE => 'Purchase',
            self::SALE => 'Sale',
            self::ADJUSTMENT => 'Adjustment',
            self::TRANSFER => 'Transfer',
            self::RETURN => 'Return',
            self::DAMAGE => 'Damage/Loss',
            self::INITIAL => 'Initial Stock',
            default => ucfirst($this->value),
        };
    }

    public static function all(): array
    {
        return [
            ['value' => self::PURCHASE, 'label' => 'Purchase', 'direction' => 'in'],
            ['value' => self::SALE, 'label' => 'Sale', 'direction' => 'out'],
            ['value' => self::ADJUSTMENT, 'label' => 'Adjustment', 'direction' => 'both'],
            ['value' => self::TRANSFER, 'label' => 'Transfer', 'direction' => 'out'],
            ['value' => self::RETURN, 'label' => 'Return', 'direction' => 'in'],
            ['value' => self::DAMAGE, 'label' => 'Damage/Loss', 'direction' => 'out'],
            ['value' => self::INITIAL, 'label' => 'Initial Stock', 'direction' => 'in'],
        ];
    }
}
