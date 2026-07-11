<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\ValueObjects;

class PurchaseOrderStatus
{
    private const DRAFT = 'draft';
    private const ORDERED = 'ordered';
    private const PARTIAL = 'partial';
    private const RECEIVED = 'received';
    private const CANCELLED = 'cancelled';

    private function __construct(private string $value) {}

    public static function draft(): self { return new self(self::DRAFT); }
    public static function ordered(): self { return new self(self::ORDERED); }
    public static function partial(): self { return new self(self::PARTIAL); }
    public static function received(): self { return new self(self::RECEIVED); }
    public static function cancelled(): self { return new self(self::CANCELLED); }
    public static function fromString(string $value): self
    {
        $valid = [self::DRAFT, self::ORDERED, self::PARTIAL, self::RECEIVED, self::CANCELLED];
        if (!in_array($value, $valid, true)) { throw new \InvalidArgumentException("Invalid PO status: {$value}"); }
        return new self($value);
    }
    public function value(): string { return $this->value; }
    public function isReceived(): bool { return $this->value === self::RECEIVED; }
    public function isOpen(): bool { return in_array($this->value, [self::DRAFT, self::ORDERED, self::PARTIAL], true); }
    public function label(): string { return ucfirst($this->value); }
    public static function all(): array { return [self::DRAFT, self::ORDERED, self::PARTIAL, self::RECEIVED, self::CANCELLED]; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
