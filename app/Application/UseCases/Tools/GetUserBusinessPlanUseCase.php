<?php

namespace App\Application\UseCases\Tools;

use App\Domain\Tools\Entities\BusinessPlan;
use App\Domain\Tools\Repositories\BusinessPlanRepository;

class GetUserBusinessPlanUseCase
{
    public function __construct(
        private readonly BusinessPlanRepository $repository
    ) {}

    public function execute(int $userId): ?BusinessPlan
    {
        return $this->repository->findLatestByUserId($userId);
    }
}
