<?php

namespace App\Domain\PlatformBilling\Infrastructure;

use App\Domain\PlatformBilling\Entities\SubscriptionPlan;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;

class EloquentPlanRepository implements PlanRepositoryInterface
{
    public function findById(int $id): ?SubscriptionPlan
    {
        $model = PlanModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?SubscriptionPlan
    {
        $model = PlanModel::where('slug', $slug)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findActive(): array
    {
        return PlanModel::active()->get()->map(fn($m) => $this->toEntity($m))->all();
    }

    public function findAll(): array
    {
        return PlanModel::all()->map(fn($m) => $this->toEntity($m))->all();
    }

    public function save(SubscriptionPlan $plan): SubscriptionPlan
    {
        $data = $plan->toArray();
        if ($plan->id()) {
            PlanModel::where('id', $plan->id())->update($data);
        } else {
            $model = PlanModel::create($data);
            $plan = SubscriptionPlan::reconstitute(
                id: $model->id,
                name: $model->name,
                slug: $model->slug,
                monthlyPrice: (float) $model->monthly_price,
                annualPrice: (float) $model->annual_price,
                siteLimit: $model->site_limit,
                storageLimitMb: $model->storage_limit_mb,
                teamMemberLimit: $model->team_member_limit,
                clientLimit: $model->client_limit,
                features: $model->features_json ?? [],
                isActive: $model->is_active,
                sortOrder: $model->sort_order,
                createdAt: new \DateTimeImmutable($model->created_at),
                updatedAt: new \DateTimeImmutable($model->updated_at),
            );
        }
        return $plan;
    }

    public function delete(int $id): void
    {
        PlanModel::destroy($id);
    }

    private function toEntity(PlanModel $model): SubscriptionPlan
    {
        return SubscriptionPlan::reconstitute(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            monthlyPrice: (float) $model->monthly_price,
            annualPrice: (float) $model->annual_price,
            siteLimit: $model->site_limit,
            storageLimitMb: $model->storage_limit_mb,
            teamMemberLimit: $model->team_member_limit,
            clientLimit: $model->client_limit,
            features: $model->features_json ?? [],
            isActive: $model->is_active,
            sortOrder: $model->sort_order,
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: new \DateTimeImmutable($model->updated_at),
        );
    }
}
