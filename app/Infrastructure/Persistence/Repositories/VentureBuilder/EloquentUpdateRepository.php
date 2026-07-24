<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\VentureBuilder;

use App\Domain\VentureBuilder\Entities\Update;
use App\Domain\VentureBuilder\Repositories\UpdateRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureUpdateModel;

class EloquentUpdateRepository implements UpdateRepositoryInterface
{
    public function findById(int $id): ?Update
    {
        $model = VentureUpdateModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByVenture(int $ventureId, ?bool $publishedOnly = true): array
    {
        $query = VentureUpdateModel::where('venture_id', $ventureId);

        if ($publishedOnly) {
            $query->published();
        }

        return $query->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->get()
            ->map(fn(VentureUpdateModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function save(Update $update): Update
    {
        $data = $update->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id) {
            VentureUpdateModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = VentureUpdateModel::create($data);
        return $this->toDomainEntity($model);
    }

    public function delete(int $id): void
    {
        VentureUpdateModel::destroy($id);
    }

    private function toDomainEntity(VentureUpdateModel $model): Update
    {
        return Update::reconstitute($model->toArray());
    }
}
