<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObjects;

use App\Domain\Employee\Exceptions\InvalidSalaryException;

final class Salary
{
    private const DEFAULT_CURRENCY = 'ZMW'; // Zambian Kwacha
    private const MIN_AMOUNT = 0;

    private int $amount; // Amount in minor units (ngwee for ZMW)
    private string $currency;

    private function __construct(int $amount, string $currency = self::DEFAULT_CURRENCY)
    {
        $this->validateAmount($amount);
        $this->validateCurrency($currency);
        
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function fromKwacha(float $amount, string $currency = self::DEFAULT_CURRENCY): self
    {
        // Convert kwacha to ngwee (multiply by 100)
        $amountInNgwee = (int) round($amount * 100);
        return new self($amountInNgwee, $currency);
    }

    public static function fromNgwee(int $amount, string $currency = self::DEFAULT_CURRENCY): self
    {
        return new self($amount, $currency);
    }

    public static function zero(string $currency = self::DEFAULT_CURRENCY): self
    {
        return new self(0, $currency);
    }

    public function add(Salary $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Salary $other): self
    {
        $this->ensureSameCurrency($other);
        $newAmount = $this->amount - $other->amount;
        
        if ($newAmount < 0) {
            throw InvalidSalaryException::negativeAfterSubtraction();
        }
        
        return new self($newAmount, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        if ($multiplier < 0) {
            throw InvalidSalaryException::negativeMultiplier();
        }
        
        $newAmount = (int) round($this->amount * $multiplier);
        return new self($newAmount, $this->currency);
    }

    public function percentage(float $percentage): self
    {
        if ($percentage < 0 || $percentage > 100) {
            throw InvalidSalaryException::invalidPercentage();
        }
        
        return $this->multiply($percentage / 100);
    }

    public function multiplyByPercentage(float $percentage): self
    {
        if ($percentage < 0) {
            throw InvalidSalaryException::negativeMultiplier();
        }
        
        return $this->multiply($percentage / 100);
    }

    public function isGreaterThan(Salary $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount > $other->amount;
    }

    public function isLessThan(Salary $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount < $other->amount;
    }

    public function isGreaterThanOrEqual(Salary $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount >= $other->amount;
    }

    public function isLessThanOrEqual(Salary $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount <= $other->amount;
    }

    public function equals(Salary $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function getAmountInKwacha(): float
    {
        return $this->amount / 100;
    }

    public function getAmountInNgwee(): int
    {
        return $this->amount;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function format(): string
    {
        return sprintf('%s %.2f', $this->currency, $this->getAmountInKwacha());
    }

    public function toString(): string
    {
        return $this->format();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function validateAmount(int $amount): void
    {
        if ($amount < self::MIN_AMOUNT) {
            throw InvalidSalaryException::negativeAmount();
        }
    }

    private function validateCurrency(string $currency): void
    {
        if (empty(trim($currency))) {
            throw InvalidSalaryException::emptyCurrency();
        }
        
        if (strlen($currency) !== 3) {
            throw InvalidSalaryException::invalidCurrencyLength();
        }
    }

    private function ensureSameCurrency(Salary $other): void
    {
        if ($this->currency !== $other->currency) {
            throw InvalidSalaryException::differentCurrencies($this->currency, $other->currency);
        }
    }
}