<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Repositories\ScenarioRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceScenarioModel;

class EloquentScenarioRepository implements ScenarioRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $model = GrowFinanceScenarioModel::find($id);
        return $model ? $model->toArray() : null;
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceScenarioModel::forBusiness($businessId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function save(int $businessId, string $name, array $parameters, array $results): array
    {
        $model = GrowFinanceScenarioModel::create([
            'business_id' => $businessId,
            'name' => $name,
            'parameters' => $parameters,
            'results' => $results,
        ]);

        return $model->toArray();
    }

    public function delete(int $id): void
    {
        GrowFinanceScenarioModel::destroy($id);
    }
}
