<?php

namespace App\Domain\FinancialServicesCore\Entities;

class ExchangeRate
{
    private function __construct(
        private readonly ?int $id,
        private string $fromCurrency,
        private string $toCurrency,
        private float $rate,
        private \DateTimeImmutable $date,
        private ?string $source,
        private \DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        string $fromCurrency,
        string $toCurrency,
        float $rate,
        \DateTimeImmutable $date,
        ?string $source = null,
    ): self {
        return new self(
            id: null,
            fromCurrency: strtoupper($fromCurrency),
            toCurrency: strtoupper($toCurrency),
            rate: $rate,
            date: $date,
            source: $source,
            createdAt: new \DateTimeImmutable(),
            updatedAt: null,
        );
    }

    public static function reconstitute(
        int $id,
        string $fromCurrency,
        string $toCurrency,
        float $rate,
        \DateTimeImmutable $date,
        ?string $source,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            fromCurrency: $fromCurrency,
            toCurrency: $toCurrency,
            rate: $rate,
            date: $date,
            source: $source,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function updateRate(float $newRate): void
    {
        $this->rate = $newRate;
    }

    public function id(): ?int { return $this->id; }
    public function fromCurrency(): string { return $this->fromCurrency; }
    public function toCurrency(): string { return $this->toCurrency; }
    public function rate(): float { return $this->rate; }
    public function date(): \DateTimeImmutable { return $this->date; }
    public function source(): ?string { return $this->source; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'from_currency' => $this->fromCurrency,
            'to_currency' => $this->toCurrency,
            'rate' => $this->rate,
            'date' => $this->date->format('Y-m-d'),
            'source' => $this->source,
        ];
    }
}
