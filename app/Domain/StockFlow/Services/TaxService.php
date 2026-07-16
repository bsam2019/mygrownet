<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\TaxRate;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\TaxRateRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\TaxRateId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use Throwable;

class TaxService
{
    public function __construct(private TaxRateRepositoryInterface $taxRateRepository) {}

    public function createTaxRate(int $companyId, array $data): TaxRate
    {
        try {
            $taxRate = TaxRate::create(
                companyId: CompanyId::fromInt($companyId),
                name: $data['name'],
                rate: (float) $data['rate'],
                type: $data['type'] ?? 'inclusive',
                isDefault: (bool) ($data['is_default'] ?? false),
            );
            return $this->taxRateRepository->save($taxRate);
        } catch (Throwable $e) {
            throw new OperationFailedException('create tax rate', $e->getMessage());
        }
    }

    public function updateTaxRate(int $id, int $companyId, array $data): TaxRate
    {
        $taxRate = $this->taxRateRepository->findById(TaxRateId::fromInt($id));
        if (!$taxRate || $taxRate->getCompanyId()->toInt() !== $companyId) {
            throw new OperationFailedException('update tax rate', 'Tax rate not found');
        }
        $taxRate->update(
            name: $data['name'] ?? $taxRate->getName(),
            rate: isset($data['rate']) ? (float) $data['rate'] : $taxRate->getRate(),
            type: $data['type'] ?? $taxRate->getType(),
            isDefault: isset($data['is_default']) ? (bool) $data['is_default'] : $taxRate->isDefault(),
        );
        return $this->taxRateRepository->save($taxRate);
    }

    public function deleteTaxRate(int $id, int $companyId): void
    {
        $taxRate = $this->taxRateRepository->findById(TaxRateId::fromInt($id));
        if (!$taxRate || $taxRate->getCompanyId()->toInt() !== $companyId) {
            throw new OperationFailedException('delete tax rate', 'Tax rate not found');
        }
        $this->taxRateRepository->delete(TaxRateId::fromInt($id));
    }

    public function getTaxRates(int $companyId): array
    {
        return $this->taxRateRepository->findByCompanyId(CompanyId::fromInt($companyId));
    }

    public function getDefaultTaxRate(int $companyId): ?TaxRate
    {
        return $this->taxRateRepository->findDefault(CompanyId::fromInt($companyId));
    }

    public function calculateTax(float $amount, float $rate, string $type): float
    {
        return $type === 'inclusive' ? $amount * ($rate / (100 + $rate)) : $amount * ($rate / 100);
    }
}
