<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\ValueObjects;

class AuditStatus
{
    private const DRAFT = 'draft';
    private const FINALIZED = 'finalized';

    private function __construct(private string $value) {}

    public static function draft(): self { return new self(self::DRAFT); }
    public static function finalized(): self { return new self(self::FINALIZED); }
    public static function fromString(string $value): self
    {
        $valid = [self::DRAFT, self::FINALIZED];
        if (!in_array($value, $valid, true)) { throw new \InvalidArgumentException("Invalid audit status: {$value}"); }
        return new self($value);
    }
    public function value(): string { return $this->value; }
    public function isFinalized(): bool { return $this->value === self::FINALIZED; }
    public function label(): string { return ucfirst($this->value); }
    public static function all(): array { return [self::DRAFT, self::FINALIZED]; }
    public function equals(self $other): bool { return $this->value === $other->value; }
}
