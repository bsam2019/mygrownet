<?php

namespace App\Domain\PlatformBilling\Infrastructure;

use App\Domain\PlatformBilling\Entities\Subscription;
use App\Domain\PlatformBilling\Repositories\SubscriptionRepositoryInterface;

class EloquentSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function findById(int $id): ?Subscription
    {
        $model = SubscriptionModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByUser(int $userId): array
    {
        return SubscriptionModel::where('user_id', $userId)->get()
            ->map(fn($m) => $this->toEntity($m))->all();
    }

    public function findByStatus(string $status): array
    {
        return SubscriptionModel::where('status', $status)->get()
            ->map(fn($m) => $this->toEntity($m))->all();
    }

    public function findOverdue(): array
    {
        return SubscriptionModel::whereIn('status', ['active', 'trial'])
            ->where('renewal_date', '<', now())
            ->get()->map(fn($m) => $this->toEntity($m))->all();
    }

    public function findExpiring(int $withinDays): array
    {
        return SubscriptionModel::where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($withinDays)])
            ->get()->map(fn($m) => $this->toEntity($m))->all();
    }

    public function save(Subscription $subscription): Subscription
    {
        $data = $subscription->toArray();
        if ($subscription->id()) {
            SubscriptionModel::where('id', $subscription->id())->update($data);
        } else {
            $model = SubscriptionModel::create($data);
            $subscription = $this->toEntity($model);
        }
        return $subscription;
    }

    public function delete(int $id): void
    {
        SubscriptionModel::destroy($id);
    }

    private function toEntity(SubscriptionModel $model): Subscription
    {
        return Subscription::reconstitute(
            id: $model->id,
            userId: $model->user_id,
            planId: $model->plan_id,
            amount: (float) $model->amount,
            status: $model->status,
            startDate: $model->start_date ? new \DateTimeImmutable($model->start_date) : null,
            endDate: $model->end_date ? new \DateTimeImmutable($model->end_date) : null,
            renewalDate: $model->renewal_date ? new \DateTimeImmutable($model->renewal_date) : null,
            cancelledAt: $model->cancelled_at ? new \DateTimeImmutable($model->cancelled_at) : null,
            cancellationReason: $model->cancellation_reason,
            autoRenew: $model->auto_renew,
            isTrial: $model->is_trial,
            trialDays: $model->trial_days,
            failureCount: $model->failure_count,
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: new \DateTimeImmutable($model->updated_at),
        );
    }
}
