<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\VentureBuilder;

use App\Domain\VentureBuilder\Entities\Dividend;
use App\Domain\VentureBuilder\Repositories\DividendRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDividendModel;

class EloquentDividendRepository implements DividendRepositoryInterface
{
    public function findById(int $id): ?Dividend
    {
        $model = VentureDividendModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByVenture(int $ventureId): array
    {
        return VentureDividendModel::where('venture_id', $ventureId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(VentureDividendModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findByShareholder(int $shareholderId): array
    {
        return VentureDividendModel::where('shareholder_id', $shareholderId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(VentureDividendModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function create(array $data): Dividend
    {
        $model = VentureDividendModel::create($data);
        return $this->toDomainEntity($model);
    }

    public function updateStatus(int $id, string $status, array $data = []): void
    {
        $data['status'] = $status;
        VentureDividendModel::where('id', $id)->update($data);
    }

    private function toDomainEntity(VentureDividendModel $model): Dividend
    {
        return Dividend::reconstitute($model->toArray());
    }
}
