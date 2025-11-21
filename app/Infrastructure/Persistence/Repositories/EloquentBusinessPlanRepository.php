<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Tools\Entities\BusinessPlan;
use App\Domain\Tools\Repositories\BusinessPlanRepository;
use App\Domain\Tools\ValueObjects\BusinessPlanId;
use App\Domain\Tools\ValueObjects\MonetaryAmount;
use App\Infrastructure\Persistence\Eloquent\Tools\BusinessPlanModel;
use DateTimeImmutable;

class EloquentBusinessPlanRepository implements BusinessPlanRepository
{
    public function save(BusinessPlan $businessPlan): BusinessPlanId
    {
        $data = [
            'user_id' => $businessPlan->userId(),
            'business_name' => $businessPlan->businessName(),
            'vision' => $businessPlan->vision(),
            'target_market' => $businessPlan->targetMarket(),
            'income_goal_6months' => $businessPlan->incomeGoal6Months()->value(),
            'income_goal_1year' => $businessPlan->incomeGoal1Year()->value(),
            'team_size_goal' => $businessPlan->teamSizeGoal(),
            'marketing_strategy' => $businessPlan->marketingStrategy(),
            'action_plan' => $businessPlan->actionPlan(),
        ];

        if ($businessPlan->id() !== null) {
            // Update existing
            $model = BusinessPlanModel::findOrFail($businessPlan->id()->value());
            $model->update($data);
            return $businessPlan->id();
        } else {
            // Create new
            $model = BusinessPlanModel::create($data);
            $id = BusinessPlanId::fromInt($model->id);
            $businessPlan->setId($id);
            return $id;
        }
    }

    public function findById(BusinessPlanId $id): ?BusinessPlan
    {
        $model = BusinessPlanModel::find($id->value());
        
        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function findLatestByUserId(int $userId): ?BusinessPlan
    {
        $model = BusinessPlanModel::where('user_id', $userId)
            ->latest()
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toDomainEntity($model);
    }

    public function findAllByUserId(int $userId): array
    {
        $models = BusinessPlanModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function delete(BusinessPlanId $id): void
    {
        BusinessPlanModel::where('id', $id->value())->delete();
    }

    private function toDomainEntity(BusinessPlanModel $model): BusinessPlan
    {
        return BusinessPlan::reconstitute(
            id: BusinessPlanId::fromInt($model->id),
            userId: $model->user_id,
            businessName: $model->business_name,
            vision: $model->vision,
            targetMarket: $model->target_market,
            incomeGoal6Months: MonetaryAmount::fromFloat($model->income_goal_6months),
            incomeGoal1Year: MonetaryAmount::fromFloat($model->income_goal_1year),
            teamSizeGoal: $model->team_size_goal,
            marketingStrategy: $model->marketing_strategy,
            actionPlan: $model->action_plan,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s'))
        );
    }
}
