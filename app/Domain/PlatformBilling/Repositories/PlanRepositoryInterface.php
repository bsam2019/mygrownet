<?php

namespace App\Domain\PlatformBilling\Repositories;

use App\Domain\PlatformBilling\Entities\SubscriptionPlan;

interface PlanRepositoryInterface
{
    public function findById(int $id): ?SubscriptionPlan;
    public function findBySlug(string $slug): ?SubscriptionPlan;
    public function findActive(): array;
    public function findAll(): array;
    public function save(SubscriptionPlan $plan): SubscriptionPlan;
    public function delete(int $id): void;
}
