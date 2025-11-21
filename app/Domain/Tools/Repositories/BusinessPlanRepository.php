<?php

namespace App\Domain\Tools\Repositories;

use App\Domain\Tools\Entities\BusinessPlan;
use App\Domain\Tools\ValueObjects\BusinessPlanId;

interface BusinessPlanRepository
{
    public function save(BusinessPlan $businessPlan): BusinessPlanId;
    
    public function findById(BusinessPlanId $id): ?BusinessPlan;
    
    public function findLatestByUserId(int $userId): ?BusinessPlan;
    
    public function findAllByUserId(int $userId): array;
    
    public function delete(BusinessPlanId $id): void;
}
