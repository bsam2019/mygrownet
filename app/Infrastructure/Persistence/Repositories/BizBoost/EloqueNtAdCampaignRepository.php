<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\AdCampaign;
use App\Domain\BizBoost\Repositories\AdCampaignRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostAdCampaignModel;
use Illuminate\Database\Eloquent\Builder;

class EloquentAdCampaignRepository implements AdCampaignRepositoryInterface
{
    public function findById(int $id): ?AdCampaign
    {
        $model = BizBoostAdCampaignModel::find($id);
        return $model ? AdCampaign::reconstitute($model->toArray()) : null;
    }

    public function findByUser(int $userId, array $filters = []): array
    {
        $query = BizBoostAdCampaignModel::where('user_id', $userId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')->paginate(20)->items();
    }

    public function findByUserAndId(int $userId, int $id): ?AdCampaign
    {
        $model = BizBoostAdCampaignModel::where('user_id', $userId)->find($id);
        return $model ? AdCampaign::reconstitute($model->toArray()) : null;
    }

    public function getQueryByUser(int $userId): Builder
    {
        return BizBoostAdCampaignModel::where('user_id', $userId);
    }

    public function create(array $data): AdCampaign
    {
        $model = BizBoostAdCampaignModel::create($data);
        return AdCampaign::reconstitute($model->toArray());
    }

    public function save(AdCampaign $entity): AdCampaign
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostAdCampaignModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostAdCampaignModel::create($data);
        return AdCampaign::reconstitute($model->toArray());
    }

    public function update(int $id, array $data): ?AdCampaign
    {
        $model = BizBoostAdCampaignModel::find($id);
        if (!$model) {
            return null;
        }
        $model->update($data);
        return AdCampaign::reconstitute($model->fresh()->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostAdCampaignModel::destroy($id);
    }
}