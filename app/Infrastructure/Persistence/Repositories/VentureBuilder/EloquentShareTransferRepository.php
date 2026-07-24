<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\VentureBuilder;

use App\Domain\VentureBuilder\Entities\ShareTransfer;
use App\Domain\VentureBuilder\Repositories\ShareTransferRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareTransferModel;

class EloquentShareTransferRepository implements ShareTransferRepositoryInterface
{
    public function findById(int $id): ?ShareTransfer
    {
        $model = VentureShareTransferModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByVenture(int $ventureId): array
    {
        return VentureShareTransferModel::where('venture_id', $ventureId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(VentureShareTransferModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findPending(): array
    {
        return VentureShareTransferModel::pending()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(VentureShareTransferModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function save(ShareTransfer $transfer): ShareTransfer
    {
        $data = $transfer->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id) {
            VentureShareTransferModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = VentureShareTransferModel::create($data);
        return $this->toDomainEntity($model);
    }

    public function updateStatus(int $id, string $status, array $data = []): void
    {
        $data['status'] = $status;
        VentureShareTransferModel::where('id', $id)->update($data);
    }

    private function toDomainEntity(VentureShareTransferModel $model): ShareTransfer
    {
        return ShareTransfer::reconstitute($model->toArray());
    }
}
