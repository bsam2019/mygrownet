<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\AiUsageLog;
use App\Domain\BizBoost\Repositories\AiUsageLogRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostAiUsageLogModel;

class EloquentAiUsageLogRepository implements AiUsageLogRepositoryInterface
{
    public function findById(int $id): ?AiUsageLog
    {
        $model = BizBoostAiUsageLogModel::find($id);
        return $model ? AiUsageLog::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId, array $filters = []): array
    {
        $query = BizBoostAiUsageLogModel::where('business_id', $businessId);

        if (!empty($filters['was_successful'])) {
            $query->where('was_successful', true);
        }

        return $query->latest()->take(10)->get()
            ->map(fn($m) => AiUsageLog::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(AiUsageLog $entity): AiUsageLog
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostAiUsageLogModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostAiUsageLogModel::create($data);
        return AiUsageLog::reconstitute($model->toArray());
    }

    public function sumCreditsByBusinessAndMonth(int $businessId, string $start, string $end): int
    {
        return (int) BizBoostAiUsageLogModel::where('business_id', $businessId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('credits_used');
    }
}