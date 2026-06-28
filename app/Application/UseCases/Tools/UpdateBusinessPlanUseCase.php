<?php

namespace App\Application\UseCases\Tools;

use App\Domain\Tools\Repositories\BusinessPlanRepository;
use App\Domain\Tools\ValueObjects\BusinessPlanId;
use App\Domain\Tools\ValueObjects\MonetaryAmount;

class UpdateBusinessPlanUseCase
{
    public function __construct(
        private readonly BusinessPlanRepository $repository
    ) {}

    public function execute(int $planId, array $data): void
    {
        $businessPlan = $this->repository->findById(BusinessPlanId::fromInt($planId));

        if (!$businessPlan) {
            throw new \RuntimeException('Business plan not found');
        }

        $businessPlan->update(
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
    }
}
