<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorDividend;
use App\Domain\Investor\Repositories\InvestorDividendRepositoryInterface;
use App\Models\Investor\InvestorDividend as InvestorDividendModel;
use DateTimeImmutable;

class EloquentInvestorDividendRepository implements InvestorDividendRepositoryInterface
{
    public function save(InvestorDividend $dividend): InvestorDividend
    {
        $data = [
            'investor_account_id' => $dividend->getInvestorAccountId(),
            'dividend_period' => $dividend->getDividendPeriod(),
            'gross_amount' => $dividend->getGrossAmount(),
            'tax_withheld' => $dividend->getTaxWithheld(),
            'net_amount' => $dividend->getNetAmount(),
            'declaration_date' => $dividend->getDeclarationDate()?->format('Y-m-d'),
            'payment_date' => $dividend->getPaymentDate()?->format('Y-m-d'),
            'status' => $dividend->getStatus(),
            'payment_method' => $dividend->getPaymentMethod(),
            'payment_reference' => $dividend->getPaymentReference(),
        ];

        if ($dividend->getId() > 0) {
            $model = InvestorDividendModel::findOrFail($dividend->getId());
            $model->update($data);
        } else {
            $model = InvestorDividendModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorDividend
    {
        $model = InvestorDividendModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByInvestor(int $investorAccountId): array
    {
        return InvestorDividendModel::where('investor_account_id', $investorAccountId)
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findByStatus(string $status): array
    {
        return InvestorDividendModel::where('status', $status)
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function create(array $data): InvestorDividend
    {
        $model = InvestorDividendModel::create($data);
        return $this->toDomainEntity($model);
    }

    private function toDomainEntity(InvestorDividendModel $model): InvestorDividend
    {
        return InvestorDividend::fromPersistence(
            id: $model->id,
            investorAccountId: $model->investor_account_id,
            dividendPeriod: $model->dividend_period,
            grossAmount: (float) $model->gross_amount,
            taxWithheld: (float) $model->tax_withheld,
            netAmount: (float) $model->net_amount,
            declarationDate: $model->declaration_date ? new DateTimeImmutable($model->declaration_date) : null,
            paymentDate: $model->payment_date ? new DateTimeImmutable($model->payment_date) : null,
            status: $model->status,
            paymentMethod: $model->payment_method,
            paymentReference: $model->payment_reference,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
