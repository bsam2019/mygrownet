<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Investment;

use App\Domain\Investment\Entities\Investment;
use App\Domain\Investment\Repositories\InvestmentRepositoryInterface;
use App\Models\Investment as InvestmentModel;

class EloquentInvestmentRepository implements InvestmentRepositoryInterface
{
    public function findById(int $id): ?Investment
    {
        $model = InvestmentModel::find($id);
        return $model ? Investment::reconstitute($model->toArray()) : null;
    }

    public function save(Investment $entity): Investment
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            InvestmentModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = InvestmentModel::create($data);
        return Investment::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return InvestmentModel::where('id', $id)->delete() > 0;
    }

    public function findByUser(int $userId): array
    {
        return InvestmentModel::where('user_id', $userId)
            ->latest()
            ->get()
            ->map(fn($m) => Investment::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActiveByUser(int $userId): array
    {
        return InvestmentModel::where('user_id', $userId)
            ->where('status', 'active')
            ->latest()
            ->get()
            ->map(fn($m) => Investment::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByStatus(string $status): array
    {
        return InvestmentModel::where('status', $status)
            ->get()
            ->map(fn($m) => Investment::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByOpportunity(int $opportunityId): array
    {
        return InvestmentModel::where('opportunity_id', $opportunityId)
            ->get()
            ->map(fn($m) => Investment::reconstitute($m->toArray()))
            ->toArray();
    }
}
