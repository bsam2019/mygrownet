<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\OmnichannelLog;
use App\Domain\BizBoost\Repositories\OmnichannelLogRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostOmnichannelLogModel;

class EloquentOmnichannelLogRepository implements OmnichannelLogRepositoryInterface
{
    public function findById(int $id): ?OmnichannelLog
    {
        $model = BizBoostOmnichannelLogModel::find($id);
        return $model ? OmnichannelLog::reconstitute($model->toArray()) : null;
    }

    public function findByUser(int $userId, array $filters = []): array
    {
        $query = BizBoostOmnichannelLogModel::where('user_id', $userId);

        if (!empty($filters['channel_type'])) {
            $query->where('channel_type', $filters['channel_type']);
        }

        return $query->orderByDesc('created_at')->get()
            ->map(fn($m) => OmnichannelLog::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(OmnichannelLog $entity): OmnichannelLog
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        $model = BizBoostOmnichannelLogModel::create($data);
        return OmnichannelLog::reconstitute($model->toArray());
    }
}