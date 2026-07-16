<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\ExchangeRateId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class ExchangeRate implements Arrayable
{
    private function __construct(
        private ExchangeRateId $id,
        private CompanyId $companyId,
        private string $fromCurrency,
        private string $toCurrency,
        private float $rate,
        private DateTimeImmutable $effectiveDate,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CompanyId $companyId, string $fromCurrency, string $toCurrency, float $rate, DateTimeImmutable $effectiveDate): self
    {
        return new self(ExchangeRateId::generate(), $companyId, $fromCurrency, $toCurrency, $rate, $effectiveDate, new DateTimeImmutable(), new DateTimeImmutable());
    }

    public static function reconstitute(ExchangeRateId $id, CompanyId $companyId, string $fromCurrency, string $toCurrency, float $rate, DateTimeImmutable $effectiveDate, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt): self
    {
        return new self($id, $companyId, $fromCurrency, $toCurrency, $rate, $effectiveDate, $createdAt, $updatedAt);
    }

    public function update(float $rate, DateTimeImmutable $effectiveDate): void
    {
        $this->rate = $rate;
        $this->effectiveDate = $effectiveDate;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function convert(float $amount): float { return $amount * $this->rate; }

    public function id(): int { return $this->id->toInt(); }
    public function getFromCurrency(): string { return $this->fromCurrency; }
    public function getToCurrency(): string { return $this->toCurrency; }
    public function getRate(): float { return $this->rate; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'from_currency' => $this->fromCurrency,
            'to_currency' => $this->toCurrency,
            'rate' => $this->rate,
            'effective_date' => $this->effectiveDate->format('Y-m-d'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
