<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\VentureBuilder;

use App\Domain\VentureBuilder\Entities\Investment;
use App\Domain\VentureBuilder\Repositories\InvestmentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;

class EloquentInvestmentRepository implements InvestmentRepositoryInterface
{
    public function findById(int $id): ?Investment
    {
        $model = VentureInvestmentModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByVenture(int $ventureId, array $filters = [], int $perPage = 20): array
    {
        $query = VentureInvestmentModel::where('venture_id', $ventureId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderByDesc('created_at')
            ->paginate($perPage)
            ->items();
    }

    public function findByUser(int $userId, array $filters = [], int $perPage = 20): array
    {
        $query = VentureInvestmentModel::where('user_id', $userId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['venture_id'])) {
            $query->where('venture_id', $filters['venture_id']);
        }

        return $query->orderByDesc('created_at')
            ->paginate($perPage)
            ->items();
    }

    public function findPending(): array
    {
        return VentureInvestmentModel::pending()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(VentureInvestmentModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findConfirmedByUserAndVenture(int $userId, int $ventureId): array
    {
        return VentureInvestmentModel::confirmed()
            ->where('user_id', $userId)
            ->where('venture_id', $ventureId)
            ->get()
            ->map(fn(VentureInvestmentModel $m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function save(Investment $investment): Investment
    {
        $data = $investment->toArray();
        $id = $data['id'] ?? null;
        unset($data['id']);

        if ($id) {
            VentureInvestmentModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = VentureInvestmentModel::create($data);
        return $this->toDomainEntity($model);
    }

    public function updateStatus(int $id, string $status, ?array $extra = null): void
    {
        $data = ['status' => $status];

        if ($extra !== null) {
            $data = array_merge($data, $extra);
        }

        VentureInvestmentModel::where('id', $id)->update($data);
    }

    public function getTotalInvestedByUser(int $userId, int $ventureId): float
    {
        return (float) VentureInvestmentModel::confirmed()
            ->where('user_id', $userId)
            ->where('venture_id', $ventureId)
            ->sum('amount');
    }

    private function toDomainEntity(VentureInvestmentModel $model): Investment
    {
        return Investment::reconstitute($model->toArray());
    }
}
