<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\Campaign;
use App\Domain\BizBoost\Repositories\CampaignRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostCampaignModel;

class EloquentCampaignRepository implements CampaignRepositoryInterface
{
    public function findById(int $id): ?Campaign
    {
        $model = BizBoostCampaignModel::find($id);
        return $model ? Campaign::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId, array $filters = []): array
    {
        $query = BizBoostCampaignModel::where('business_id', $businessId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')->get()
            ->map(fn($m) => Campaign::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(Campaign $entity): Campaign
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostCampaignModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostCampaignModel::create($data);
        return Campaign::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostCampaignModel::destroy($id);
    }

    public function countByBusiness(int $businessId, ?string $status = null): int
    {
        $query = BizBoostCampaignModel::where('business_id', $businessId);
        if ($status) {
            $query->where('status', $status);
        }
        return $query->count();
    }
}