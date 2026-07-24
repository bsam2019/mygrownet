<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\ApiToken;
use App\Domain\GrowFinance\Repositories\ApiTokenRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceApiTokenModel;

class EloquentApiTokenRepository implements ApiTokenRepositoryInterface
{
    public function findById(int $id): ?ApiToken
    {
        $model = GrowFinanceApiTokenModel::find($id);
        return $model ? ApiToken::reconstitute($model->toArray()) : null;
    }

    public function save(ApiToken $entity): ApiToken
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceApiTokenModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceApiTokenModel::create($data);
        return ApiToken::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceApiTokenModel::forBusiness($businessId)->get()
            ->map(fn($m) => ApiToken::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $businessId): array
    {
        return GrowFinanceApiTokenModel::forBusiness($businessId)->active()->get()
            ->map(fn($m) => ApiToken::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findValid(int $businessId): array
    {
        return GrowFinanceApiTokenModel::forBusiness($businessId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get()
            ->map(fn($m) => ApiToken::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByToken(string $token): ?ApiToken
    {
        $model = GrowFinanceApiTokenModel::where('token', $token)->first();
        return $model ? ApiToken::reconstitute($model->toArray()) : null;
    }

    public function findByBusinessAndName(int $businessId, string $name): ?ApiToken
    {
        $model = GrowFinanceApiTokenModel::forBusiness($businessId)->where('name', $name)->first();
        return $model ? ApiToken::reconstitute($model->toArray()) : null;
    }
}
