<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Module\Entities\ModuleSubscription;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Domain\Module\ValueObjects\Money;
use App\Domain\Module\ValueObjects\SubscriptionId;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Eloquent-based Module Subscription Repository
 * 
 * Handles subscription data from the database.
 * Falls back to free tier if no subscription exists.
 */
class EloquentModuleSubscriptionRepository implements ModuleSubscriptionRepositoryInterface
{
    private const CACHE_TTL = 300; // 5 minutes

    public function findById(string $id): ?ModuleSubscription
    {
        if (!$this->tableExists()) {
            return null;
        }

        $record = DB::table('module_subscriptions')->find($id);
        return $record ? $this->toEntity($record) : null;
    }

    public function findByUserAndModule(int $userId, string|ModuleId $moduleId): ?ModuleSubscription
    {
        if (!$this->tableExists()) {
            return null;
        }

        $moduleIdValue = $moduleId instanceof ModuleId ? $moduleId->value() : $moduleId;
        $cacheKey = "module_sub_{$userId}_{$moduleIdValue}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $moduleIdValue) {
            $record = DB::table('module_subscriptions')
                ->where('user_id', $userId)
                ->where('module_id', $moduleIdValue)
                ->where('status', 'active')
                ->first();

            return $record ? $this->toEntity($record) : null;
        });
    }

    public function findByUser(int $userId): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        $records = DB::table('module_subscriptions')
            ->where('user_id', $userId)
            ->get();

        return $records->map(fn($r) => $this->toEntity($r))->all();
    }

    public function findActiveByUser(int $userId): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        $records = DB::table('module_subscriptions')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();

        return $records->map(fn($r) => $this->toEntity($r))->all();
    }

    public function findExpiring(int $daysAhead = 7): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        $records = DB::table('module_subscriptions')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($daysAhead)])
            ->get();

        return $records->map(fn($r) => $this->toEntity($r))->all();
    }

    public function findExpired(): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        $records = DB::table('module_subscriptions')
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        return $records->map(fn($r) => $this->toEntity($r))->all();
    }

    public function save(ModuleSubscription $subscription): void
    {
        if (!$this->tableExists()) {
            return;
        }

        $data = [
            'user_id' => $subscription->getUserId(),
            'module_id' => $subscription->getModuleId(),
            'tier' => $subscription->getSubscriptionTier(),
            'status' => $subscription->getStatus(),
            'started_at' => $subscription->getStartedAt()->format('Y-m-d H:i:s'),
            'expires_at' => $subscription->getExpiresAt()?->format('Y-m-d H:i:s'),
            'updated_at' => now(),
        ];

        if ($subscription->getId()) {
            DB::table('module_subscriptions')
                ->where('id', $subscription->getId()->value())
                ->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('module_subscriptions')->insert($data);
        }

        $this->clearCache($subscription->getUserId(), $subscription->getModuleId());
    }

    public function delete(string $id): void
    {
        if (!$this->tableExists()) {
            return;
        }

        $record = DB::table('module_subscriptions')->find($id);
        if ($record) {
            DB::table('module_subscriptions')->delete($id);
            $this->clearCache($record->user_id, $record->module_id);
        }
    }

    public function clearCache(int $userId, string|ModuleId $moduleId): void
    {
        $moduleIdValue = $moduleId instanceof ModuleId ? $moduleId->value() : $moduleId;
        Cache::forget("module_sub_{$userId}_{$moduleIdValue}");
    }

    private function tableExists(): bool
    {
        static $exists = null;
        if ($exists === null) {
            $exists = Schema::hasTable('module_subscriptions');
        }
        return $exists;
    }

    private function toEntity(object $record): ModuleSubscription
    {
        $subscription = new ModuleSubscription(
            id: $record->id ? SubscriptionId::fromString((string) $record->id) : null,
            userId: $record->user_id,
            moduleId: $record->module_id,
            subscriptionTier: $record->tier ?? 'free',
            status: $record->status ?? 'active',
            startedAt: new \DateTimeImmutable($record->started_at ?? 'now'),
            trialEndsAt: isset($record->trial_ends_at) ? new \DateTimeImmutable($record->trial_ends_at) : null,
            expiresAt: isset($record->expires_at) ? new \DateTimeImmutable($record->expires_at) : null,
            cancelledAt: isset($record->cancelled_at) ? new \DateTimeImmutable($record->cancelled_at) : null,
            autoRenew: $record->auto_renew ?? true,
            billingCycle: $record->billing_cycle ?? 'monthly',
            amount: Money::fromCents($record->amount ?? 0, $record->currency ?? 'ZMW'),
            userLimit: $record->user_limit ?? null,
            storageLimitMb: $record->storage_limit_mb ?? null
        );

        return $subscription;
    }
}
