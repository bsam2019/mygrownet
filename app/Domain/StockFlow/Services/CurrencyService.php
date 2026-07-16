<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\ExchangeRate;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\ExchangeRateRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\ExchangeRateId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use DateTimeImmutable;
use Throwable;

class CurrencyService
{
    public function __construct(private ExchangeRateRepositoryInterface $exchangeRateRepository) {}

    public function createRate(int $companyId, array $data): ExchangeRate
    {
        try {
            $rate = ExchangeRate::create(
                companyId: CompanyId::fromInt($companyId),
                fromCurrency: $data['from_currency'],
                toCurrency: $data['to_currency'],
                rate: (float) $data['rate'],
                effectiveDate: new DateTimeImmutable($data['effective_date'] ?? 'today'),
            );
            return $this->exchangeRateRepository->save($rate);
        } catch (Throwable $e) {
            throw new OperationFailedException('create exchange rate', $e->getMessage());
        }
    }

    public function updateRate(int $id, int $companyId, array $data): ExchangeRate
    {
        $rate = $this->exchangeRateRepository->findById(ExchangeRateId::fromInt($id));
        if (!$rate) throw new OperationFailedException('update exchange rate', 'Rate not found');
        $rate->update(
            rate: isset($data['rate']) ? (float) $data['rate'] : $rate->getRate(),
            effectiveDate: new DateTimeImmutable($data['effective_date'] ?? 'today'),
        );
        return $this->exchangeRateRepository->save($rate);
    }

    public function deleteRate(int $id, int $companyId): void
    {
        $this->exchangeRateRepository->delete(ExchangeRateId::fromInt($id));
    }

    public function getRates(int $companyId): array
    {
        return $this->exchangeRateRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    public function convert(int $companyId, float $amount, string $from, string $to): ?float
    {
        $rate = $this->exchangeRateRepository->findRate(CompanyId::fromInt($companyId), $from, $to);
        return $rate ? $rate->convert($amount) : null;
    }
}
