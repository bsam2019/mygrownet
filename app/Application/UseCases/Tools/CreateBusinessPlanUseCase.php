<?php

namespace App\Application\UseCases\Tools;

use App\Domain\Tools\Entities\BusinessPlan;
use App\Domain\Tools\Repositories\BusinessPlanRepository;
use App\Domain\Tools\ValueObjects\MonetaryAmount;

class CreateBusinessPlanUseCase
{
    public function __construct(
        private readonly BusinessPlanRepository $repository
    ) {}

    public function execute(array $data): BusinessPlan
    {
        $businessPlan = BusinessPlan::create(
            userId: $data['user_id'],
            businessName: $data['business_name'],
            vision: $data['vision'],
            targetMarket: $data['target_market'],
            incomeGoal6Months: MonetaryAmount::fromFloat($data['income_goal_6months']),
            incomeGoal1Year: MonetaryAmount::fromFloat($data['income_goal_1year']),
            teamSizeGoal: $data['team_size_goal'],
            marketingStrategy: $data['marketing_strategy'],
            actionPlan: $data['action_plan']
        );

        $this->repository->save($businessPlan);

        return $businessPlan;
    }
}
