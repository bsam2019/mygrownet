<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\ValueObjects;

use InvalidArgumentException;

final class Money
{
    public const SUPPORTED_CURRENCIES = ['ZMW', 'USD', 'EUR', 'GBP', 'ZAR'];

    private function __construct(
        private readonly float $amount,
        private readonly string $currency
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        if (!in_array($currency, self::SUPPORTED_CURRENCIES)) {
            throw new InvalidArgumentException("Unsupported currency: {$currency}");
        }
    }

    public static function create(float $amount, string $currency = 'ZMW'): self
    {
        return new self($amount, strtoupper($currency));
    }

    public static function zero(string $currency = 'ZMW'): self
    {
        return new self(0, $currency);
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        $result = $this->amount - $other->amount;
        return new self(max(0, $result), $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    public function percentage(float $percent): self
    {
        return new self($this->amount * ($percent / 100), $this->currency);
    }

    public function format(): string
    {
        $symbol = $this->currencySymbol();
        return $symbol . ' ' . number_format($this->amount, 2);
    }

    public function currencySymbol(): string
    {
        return match ($this->currency) {
            'ZMW' => 'K',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'ZAR' => 'R',
            default => $this->currency,
        };
    }

    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot operate on different currencies');
        }
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'formatted' => $this->format(),
        ];
    }
}
