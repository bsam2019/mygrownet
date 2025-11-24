<?php

namespace App\Domain\Investor\ValueObjects;

/**
 * Investor Status Value Object
 * 
 * Represents the status of an investor account
 */
class InvestorStatus
{
    private const CIU = 'ciu';
    private const SHAREHOLDER = 'shareholder';
    private const EXITED = 'exited';

    private function __construct(
        private readonly string $value
    ) {
        if (!in_array($value, [self::CIU, self::SHAREHOLDER, self::EXITED])) {
            throw new \InvalidArgumentException("Invalid investor status: {$value}");
        }
    }

    public static function ciu(): self
    {
        return new self(self::CIU);
    }

    public static function shareholder(): self
    {
        return new self(self::SHAREHOLDER);
    }

    public static function exited(): self
    {
        return new self(self::EXITED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(InvestorStatus $other): bool
    {
        return $this->value === $other->value;
    }

    public function isCIU(): bool
    {
        return $this->value === self::CIU;
    }

    public function isShareholder(): bool
    {
        return $this->value === self::SHAREHOLDER;
    }

    public function isExited(): bool
    {
        return $this->value === self::EXITED;
    }

    public function label(): string
    {
        return match($this->value) {
            self::CIU => 'Convertible Investment Unit',
            self::SHAREHOLDER => 'Shareholder',
            self::EXITED => 'Exited',
        };
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
