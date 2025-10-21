<?php

namespace App\Infrastructure\Persistence\Repositories\ProfitSharing;

use App\Domain\ProfitSharing\Entities\QuarterlyProfitShare;
use App\Domain\ProfitSharing\Repositories\QuarterlyProfitShareRepository;
use App\Domain\ProfitSharing\ValueObjects\Quarter;
use App\Domain\ProfitSharing\ValueObjects\ProfitAmount;
use App\Infrastructure\Persistence\Eloquent\ProfitSharing\QuarterlyProfitShareModel;
use DateTimeImmutable;

class EloquentQuarterlyProfitShareRepository implements QuarterlyProfitShareRepository
{
    public function save(QuarterlyProfitShare $profitShare): QuarterlyProfitShare
    {
        $data = [
            'year' => $profitShare->quarter()->year(),
            'quarter' => $profitShare->quarter()->quarter(),
            'total_project_profit' => $profitShare->totalProjectProfit()->value(),
            'member_share_amount' => $profitShare->memberShareAmount()->value(),
            'company_retained' => $profitShare->companyRetained()->value(),
            'total_active_members' => $profitShare->totalActiveMembers(),
            'total_bp_pool' => $profitShare->totalBpPool(),
            'distribution_method' => $profitShare->distributionMethod(),
            'status' => $profitShare->status(),
            'notes' => $profitShare->notes(),
            'created_by' => $profitShare->createdBy(),
            'approved_by' => $profitShare->approvedBy(),
            'approved_at' => $profitShare->approvedAt()?->format('Y-m-d H:i:s'),
            'distributed_at' => $profitShare->distributedAt()?->format('Y-m-d H:i:s'),
        ];

        if ($profitShare->id()) {
            $model = QuarterlyProfitShareModel::findOrFail($profitShare->id());
            $model->update($data);
        } else {
            $model = QuarterlyProfitShareModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?QuarterlyProfitShare
    {
        $model = QuarterlyProfitShareModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByQuarter(Quarter $quarter): ?QuarterlyProfitShare
    {
        $model = QuarterlyProfitShareModel::where('year', $quarter->year())
            ->where('quarter', $quarter->quarter())
            ->first();
            
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return QuarterlyProfitShareModel::orderBy('year', 'desc')
            ->orderBy('quarter', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByStatus(string $status): array
    {
        return QuarterlyProfitShareModel::where('status', $status)
            ->orderBy('year', 'desc')
            ->orderBy('quarter', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    private function toDomainEntity(QuarterlyProfitShareModel $model): QuarterlyProfitShare
    {
        return new QuarterlyProfitShare(
            id: $model->id,
            quarter: Quarter::create($model->year, $model->quarter),
            totalProjectProfit: ProfitAmount::fromFloat($model->total_project_profit),
            totalActiveMembers: $model->total_active_members,
            totalBpPool: $model->total_bp_pool,
            distributionMethod: $model->distribution_method,
            status: $model->status,
            notes: $model->notes,
            createdBy: $model->created_by,
            approvedBy: $model->approved_by,
            approvedAt: $model->approved_at ? new DateTimeImmutable($model->approved_at) : null,
            distributedAt: $model->distributed_at ? new DateTimeImmutable($model->distributed_at) : null,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
