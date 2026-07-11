<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\ValueObjects;

class Money
{
    private function __construct(private float $amount, private string $currency = 'MWK') {}

    public static function fromFloat(float $amount, string $currency = 'MWK'): self
    {
        if ($amount < 0) { throw new \InvalidArgumentException('Money amount cannot be negative'); }
        return new self(round($amount, 2), $currency);
    }

    public static function zero(string $currency = 'MWK'): self { return new self(0.0, $currency); }
    public function amount(): float { return $this->amount; }
    public function currency(): string { return $this->currency; }
    public function add(self $other): self { $m = $this->amount + $other->amount; return new self(round($m, 2), $this->currency); }
    public function subtract(self $other): self { $m = $this->amount - $other->amount; return new self(round($m, 2), $this->currency); }
    public function multiply(float $factor): self { return new self(round($this->amount * $factor, 2), $this->currency); }
    public function isZero(): bool { return $this->amount === 0.0; }
    public function isGreaterThan(self $other): bool { return $this->amount > $other->amount; }
    public function isLessThan(self $other): bool { return $this->amount < $other->amount; }
    public function toFloat(): float { return $this->amount; }
    public function equals(self $other): bool { return $this->amount === $other->amount && $this->currency === $other->currency; }
    public function __toString(): string { return number_format($this->amount, 2) . ' ' . $this->currency; }
}
