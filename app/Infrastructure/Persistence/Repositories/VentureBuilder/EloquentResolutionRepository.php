<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\VentureBuilder;

use App\Domain\VentureBuilder\Entities\Resolution;
use App\Domain\VentureBuilder\Repositories\ResolutionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureResolutionModel;

class EloquentResolutionRepository implements ResolutionRepositoryInterface
{
    public function findById(int $id): ?Resolution
    {
        $model = VentureResolutionModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByVenture(int $ventureId): array
    {
        return VentureResolutionModel::where('venture_id', $ventureId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(VentureResolutionModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function save(Resolution $resolution): Resolution
    {
        $data = $resolution->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id) {
            VentureResolutionModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = VentureResolutionModel::create($data);
        return $this->toDomainEntity($model);
    }

    public function updateStatus(int $id, string $status, ?array $extra = null): void
    {
        $data = ['status' => $status];

        if ($extra !== null) {
            $data = array_merge($data, $extra);
        }

        VentureResolutionModel::where('id', $id)->update($data);
    }

    public function incrementVote(int $id, string $column, float $equity): void
    {
        VentureResolutionModel::where('id', $id)->increment($column, $equity);
    }

    private function toDomainEntity(VentureResolutionModel $model): Resolution
    {
        return Resolution::reconstitute($model->toArray());
    }
}
