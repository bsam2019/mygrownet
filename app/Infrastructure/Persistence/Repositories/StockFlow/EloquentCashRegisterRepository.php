<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\CashRegister;
use App\Domain\StockFlow\Repositories\CashRegisterRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CashRegisterId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\CashRegisterStatus;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCashRegisterModel;
use DateTimeImmutable;

class EloquentCashRegisterRepository implements CashRegisterRepositoryInterface
{
    public function findById(CashRegisterId $id): ?CashRegister
    {
        $model = SaCashRegisterModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId, int $perPage = 30): array
    {
        return SaCashRegisterModel::where('sa_company_id', $companyId->toInt())
            ->orderBy('register_date', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByDate(CompanyId $companyId, DateTimeImmutable $date): ?CashRegister
    {
        $model = SaCashRegisterModel::where('sa_company_id', $companyId->toInt())
            ->whereDate('register_date', $date->format('Y-m-d'))
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return SaCashRegisterModel::where('sa_company_id', $companyId->toInt())
            ->whereBetween('register_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->orderBy('register_date')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findOpenRegisterForToday(CompanyId $companyId): ?CashRegister
    {
        $today = new DateTimeImmutable();
        $model = SaCashRegisterModel::where('sa_company_id', $companyId->toInt())
            ->whereDate('register_date', $today->format('Y-m-d'))
            ->where('status', 'open')
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(CashRegister $register): CashRegister
    {
        $data = [
            'sa_company_id' => $register->getCompanyId()->toInt(),
            'register_date' => $register->getRegisterDate()->format('Y-m-d'),
            'status' => $register->getStatus()->value(),
            'opening_balance' => $register->getOpeningBalance()->toFloat(),
            'total_sales' => $register->getTotalSales()->toFloat(),
            'total_expenses' => $register->getTotalExpenses()->toFloat(),
            'total_banking' => $register->getTotalBanking()->toFloat(),
            'expected_closing' => $register->getExpectedClosing()->toFloat(),
            'actual_closing' => $register->getActualClosing()?->toFloat(),
            'variance' => $register->getVariance()?->toFloat(),
            'notes' => $register->getNotes(),
            'opened_by' => $register->getOpenedBy(),
            'closed_by' => $register->getClosedBy(),
        ];

        if ($register->id() === 0) {
            $model = SaCashRegisterModel::create($data);
        } else {
            $model = SaCashRegisterModel::find($register->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    private function toDomainEntity(SaCashRegisterModel $model): CashRegister
    {
        return CashRegister::reconstitute(
            id: CashRegisterId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            registerDate: new DateTimeImmutable($model->register_date->format('Y-m-d')),
            status: CashRegisterStatus::fromString($model->status),
            openingBalance: Money::fromFloat((float) $model->opening_balance),
            totalSales: Money::fromFloat((float) $model->total_sales),
            totalExpenses: Money::fromFloat((float) ($model->total_expenses ?? 0)),
            totalBanking: Money::fromFloat((float) ($model->total_banking ?? 0)),
            expectedClosing: Money::fromFloat((float) $model->expected_closing),
            actualClosing: $model->actual_closing !== null ? Money::fromFloat((float) $model->actual_closing) : null,
            variance: $model->variance !== null ? Money::fromFloat((float) $model->variance) : null,
            notes: $model->notes,
            openedBy: $model->opened_by ?? 0,
            closedBy: $model->closed_by,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
