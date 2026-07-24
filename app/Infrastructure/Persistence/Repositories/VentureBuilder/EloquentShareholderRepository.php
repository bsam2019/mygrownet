<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\VentureBuilder;

use App\Domain\VentureBuilder\Entities\Shareholder;
use App\Domain\VentureBuilder\Repositories\ShareholderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;

class EloquentShareholderRepository implements ShareholderRepositoryInterface
{
    public function findById(int $id): ?Shareholder
    {
        $model = VentureShareholderModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByVenture(int $ventureId): array
    {
        return VentureShareholderModel::where('venture_id', $ventureId)
            ->orderByDesc('shares_owned')
            ->get()
            ->map(fn(VentureShareholderModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findByUserAndVenture(int $userId, int $ventureId): ?Shareholder
    {
        $model = VentureShareholderModel::where('user_id', $userId)
            ->where('venture_id', $ventureId)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findActiveByVenture(int $ventureId): array
    {
        return VentureShareholderModel::active()
            ->where('venture_id', $ventureId)
            ->get()
            ->map(fn(VentureShareholderModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findActiveByUserAndVenture(int $userId, int $ventureId): ?Shareholder
    {
        $model = VentureShareholderModel::active()
            ->where('user_id', $userId)
            ->where('venture_id', $ventureId)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(Shareholder $shareholder): Shareholder
    {
        $data = $shareholder->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id) {
            VentureShareholderModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = VentureShareholderModel::create($data);
        return $this->toDomainEntity($model);
    }

    public function decrementShares(int $id, float $shares): void
    {
        VentureShareholderModel::where('id', $id)->decrement('shares_owned', $shares);
    }

    public function decrementInvestment(int $id, float $amount): void
    {
        VentureShareholderModel::where('id', $id)->decrement('total_investment', $amount);
    }

    public function incrementShares(int $id, float $shares): void
    {
        VentureShareholderModel::where('id', $id)->increment('shares_owned', $shares);
    }

    public function incrementInvestment(int $id, float $amount): void
    {
        VentureShareholderModel::where('id', $id)->increment('total_investment', $amount);
    }

    public function updateEquity(int $id, float $percentage): void
    {
        VentureShareholderModel::where('id', $id)->update(['equity_percentage' => $percentage]);
    }

    public function getTotalSharesByVenture(int $ventureId): float
    {
        return (float) VentureShareholderModel::where('venture_id', $ventureId)->sum('shares_owned');
    }

    private function toDomainEntity(VentureShareholderModel $model): Shareholder
    {
        return Shareholder::reconstitute($model->toArray());
    }
}
