<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Module\Entities\ModuleSubscription;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\ValueObjects\Money;

use App\Infrastructure\Persistence\Eloquent\ModuleSubscriptionModel;
use DateTimeImmutable;

class EloquentModuleSubscriptionRepository implements ModuleSubscriptionRepositoryInterface
{
    public function findById(string $id): ?ModuleSubscription
    {
        $model = ModuleSubscriptionModel::find($id);
        
        return $model ? $this->toDomainEntity($model) : null;
    }
    
    public function findByUserAndModule(int $userId, string $moduleId): ?ModuleSubscription
    {
        $model = ModuleSubscriptionModel::where('user_id', $userId)
            ->where('module_id', $moduleId)
            ->first();
        
        return $model ? $this->toDomainEntity($model) : null;
    }
    
    public function findByUser(int $userId): array
    {
        $models = ModuleSubscriptionModel::where('user_id', $userId)->get();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }
    
    public function findActiveByUser(int $userId): array
    {
        $models = ModuleSubscriptionModel::where('user_id', $userId)
            ->whereIn('status', ['active', 'trial'])
            ->get();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }
    
    public function findExpiring(int $daysAhead = 7): array
    {
        $models = ModuleSubscriptionModel::where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays($daysAhead)])
            ->get();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }
    
    public function findExpired(): array
    {
        $models = ModuleSubscriptionModel::whereIn('status', ['active', 'trial'])
            ->where('expires_at', '<', now())
            ->get();
        
        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }
    
    public function save(ModuleSubscription $subscription): void
    {
        $data = [
            'user_id' => $subscription->getUserId(),
            'module_id' => $subscription->getModuleId(),
            'subscription_tier' => $subscription->getSubscriptionTier(),
            'status' => $subscription->getStatus(),
            'started_at' => $subscription->getStartedAt(),
            'trial_ends_at' => $subscription->getTrialEndsAt(),
            'expires_at' => $subscription->getExpiresAt(),
            'cancelled_at' => $subscription->getCancelledAt(),
            'auto_renew' => $subscription->isAutoRenew(),
            'billing_cycle' => $subscription->getBillingCycle(),
            'amount' => $subscription->getAmount()->getAmount(),
            'currency' => $subscription->getAmount()->getCurrency(),
            'user_limit' => $subscription->getUserLimit(),
            'storage_limit_mb' => $subscription->getStorageLimitMb(),
        ];
        
        if ($subscription->getId()) {
            ModuleSubscriptionModel::where('id', $subscription->getId())
                ->update($data);
        } else {
            $model = ModuleSubscriptionModel::create($data);
            // Update the domain entity with the generated ID
            $subscription->setId($model->id);
        }
    }
    
    public function delete(string $id): void
    {
        ModuleSubscriptionModel::destroy($id);
    }
    
    private function toDomainEntity(ModuleSubscriptionModel $model): ModuleSubscription
    {
        return new ModuleSubscription(
            id: $model->id,
            userId: $model->user_id,
            moduleId: $model->module_id,
            subscriptionTier: $model->subscription_tier,
            status: $model->status,
            startedAt: new DateTimeImmutable($model->started_at),
            trialEndsAt: $model->trial_ends_at ? new DateTimeImmutable($model->trial_ends_at) : null,
            expiresAt: $model->expires_at ? new DateTimeImmutable($model->expires_at) : null,
            cancelledAt: $model->cancelled_at ? new DateTimeImmutable($model->cancelled_at) : null,
            autoRenew: $model->auto_renew,
            billingCycle: $model->billing_cycle,
            amount: Money::fromAmount((int)$model->amount, $model->currency),
            userLimit: $model->user_limit,
            storageLimitMb: $model->storage_limit_mb
        );
    }
}
