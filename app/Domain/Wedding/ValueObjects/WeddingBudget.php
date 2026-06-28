<?php

namespace App\Domain\Wedding\ValueObjects;

class WeddingBudget
{
    private function __construct(
        private float $amount
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Wedding budget cannot be negative');
        }
    }

    public static function fromAmount(float $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(WeddingBudget $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function subtract(WeddingBudget $other): self
    {
        $newAmount = $this->amount - $other->amount;
        if ($newAmount < 0) {
            throw new \InvalidArgumentException('Budget cannot go negative');
        }
        return new self($newAmount);
    }

    public function allocatePercentage(float $percentage): self
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new \InvalidArgumentException('Percentage must be between 0 and 100');
        }
        
        return new self($this->amount * ($percentage / 100));
    }

    public function isGreaterThan(WeddingBudget $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function isLessThan(WeddingBudget $other): bool
    {
        return $this->amount < $other->amount;
    }

    public function equals(WeddingBudget $other): bool
    {
        return abs($this->amount - $other->amount) < 0.01; // Float comparison
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getFormattedAmount(): string
    {
        return 'K' . number_format($this->amount, 2);
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'formatted' => $this->getFormattedAmount()
        ];
    }
}