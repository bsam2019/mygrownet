<?php

namespace App\Domain\GrowMart\ValueObjects;

final class ProductStatus
{
    private const ACTIVE = 'active';
    private const OUT_OF_STOCK = 'out_of_stock';
    private const DISCONTINUED = 'discontinued';

    private const LABELS = [
        self::ACTIVE => 'Active',
        self::OUT_OF_STOCK => 'Out of Stock',
        self::DISCONTINUED => 'Discontinued',
    ];

    private const COLORS = [
        self::ACTIVE => 'green',
        self::OUT_OF_STOCK => 'yellow',
        self::DISCONTINUED => 'red',
    ];

    private function __construct(private readonly string $value) {}

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function outOfStock(): self
    {
        return new self(self::OUT_OF_STOCK);
    }

    public static function discontinued(): self
    {
        return new self(self::DISCONTINUED);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::ACTIVE => self::active(),
            self::OUT_OF_STOCK => self::outOfStock(),
            self::DISCONTINUED => self::discontinued(),
            default => self::active(),
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }

    public function isOutOfStock(): bool
    {
        return $this->value === self::OUT_OF_STOCK;
    }

    public function isDiscontinued(): bool
    {
        return $this->value === self::DISCONTINUED;
    }

    public function label(): string
    {
        return self::LABELS[$this->value] ?? $this->value;
    }

    public function color(): string
    {
        return self::COLORS[$this->value] ?? 'gray';
    }
}
