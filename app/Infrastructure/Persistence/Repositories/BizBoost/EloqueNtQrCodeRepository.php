<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\QrCode;
use App\Domain\BizBoost\Repositories\QrCodeRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostQrCodeModel;

class EloquentQrCodeRepository implements QrCodeRepositoryInterface
{
    public function findById(int $id): ?QrCode
    {
        $model = BizBoostQrCodeModel::find($id);
        return $model ? QrCode::reconstitute($model->toArray()) : null;
    }

    public function findByShortCode(string $code): ?QrCode
    {
        $model = BizBoostQrCodeModel::where('short_code', $code)->where('is_active', true)->first();
        return $model ? QrCode::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId): array
    {
        return BizBoostQrCodeModel::where('business_id', $businessId)->get()
            ->map(fn($m) => QrCode::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(QrCode $entity): QrCode
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostQrCodeModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostQrCodeModel::create($data);
        return QrCode::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostQrCodeModel::destroy($id);
    }
}