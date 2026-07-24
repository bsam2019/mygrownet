<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\Integration;
use App\Domain\BizBoost\Repositories\IntegrationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;

class EloquentIntegrationRepository implements IntegrationRepositoryInterface
{
    public function findById(int $id): ?Integration
    {
        $model = BizBoostIntegrationModel::find($id);
        return $model ? Integration::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId): array
    {
        return BizBoostIntegrationModel::where('business_id', $businessId)->get()
            ->map(fn($m) => Integration::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActiveByBusiness(int $businessId): array
    {
        return BizBoostIntegrationModel::where('business_id', $businessId)
            ->where('status', 'active')
            ->get()
            ->map(fn($m) => Integration::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByProvider(int $businessId, string $provider): ?Integration
    {
        $model = BizBoostIntegrationModel::where('business_id', $businessId)
            ->where('provider', $provider)
            ->first();
        return $model ? Integration::reconstitute($model->toArray()) : null;
    }

    public function save(Integration $entity): Integration
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostIntegrationModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostIntegrationModel::create($data);
        return Integration::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostIntegrationModel::destroy($id);
    }
}