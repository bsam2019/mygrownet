<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\QuickInvoice;

use App\Domain\QuickInvoice\Repositories\SubscriptionRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\AdminSettingRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionTierRepositoryInterface;
use App\Models\QuickInvoice\AdminSetting;
use App\Models\QuickInvoice\SubscriptionTier;
use App\Models\QuickInvoice\UserSubscription;

class EloquentSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function findById(string $id): ?array
    {
        $model = UserSubscription::with('tier', 'user')->find($id);
        return $model ? $model->toArray() : null;
    }

    public function getCurrentSubscription(int $userId): ?array
    {
        $model = UserSubscription::with('tier')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        return $model ? $model->toArray() : null;
    }

    public function getOrCreateFreeSubscription(int $userId): array
    {
        $existing = UserSubscription::with('tier')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($existing) {
            return $existing->toArray();
        }

        $freeTier = SubscriptionTier::where('name', 'Free')->where('is_active', true)->first()
            ?? SubscriptionTier::create([
                'name' => 'Free',
                'price' => 0,
                'currency' => 'ZMW',
                'documents_per_month' => -1,
                'features' => [
                    'templates' => 'all',
                    'sharing' => ['pdf_download', 'email', 'whatsapp'],
                    'watermark' => false,
                    'customization' => true,
                    'api_access' => true,
                    'priority_support' => true,
                    'custom_branding' => true,
                    'advanced_templates' => true,
                    'custom_fields' => true,
                    'design_studio' => true,
                    'white_label' => true,
                    'advanced_analytics' => true,
                    'cms_integration' => true,
                ],
                'is_active' => true,
            ]);

        $trialSettings = AdminSetting::get('trial_settings', []);
        $trialDays = $trialSettings['trial_days'] ?? 0;
        $tierOnTrial = $trialSettings['tier_on_trial'] ?? null;

        if ($trialDays > 0 && $tierOnTrial) {
            $trialTier = SubscriptionTier::where('name', $tierOnTrial)
                ->where('is_active', true)
                ->first();

            if ($trialTier && $trialTier->id !== $freeTier->id) {
                $model = UserSubscription::create([
                    'user_id' => $userId,
                    'tier_id' => $trialTier->id,
                    'starts_at' => now(),
                    'trial_ends_at' => now()->addDays($trialDays),
                    'expires_at' => now()->addDays($trialDays),
                    'documents_used' => 0,
                    'is_active' => true,
                ]);
                return $model->load('tier')->toArray();
            }
        }

        $model = UserSubscription::create([
            'user_id' => $userId,
            'tier_id' => $freeTier->id,
            'starts_at' => now(),
            'expires_at' => null,
            'documents_used' => 0,
            'is_active' => true,
        ]);

        return $model->load('tier')->toArray();
    }

    public function incrementUsage(string $id): void
    {
        UserSubscription::where('id', $id)->increment('documents_used');
    }

    public function findActiveByUser(int $userId): ?array
    {
        $model = UserSubscription::with('tier', 'user')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        return $model ? $model->toArray() : null;
    }

    public function findAllActive(): array
    {
        return UserSubscription::with('tier', 'user')
            ->where('is_active', true)
            ->get()
            ->toArray();
    }

    public function countActive(): int
    {
        return UserSubscription::where('is_active', true)->count();
    }

    public function countDistinctUsers(): int
    {
        return UserSubscription::distinct('user_id')->count();
    }

    public function create(array $data): array
    {
        $model = UserSubscription::create($data);
        return $model->load('tier')->toArray();
    }

    public function deactivateByUser(int $userId): void
    {
        UserSubscription::where('user_id', $userId)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    public function sumLastPaymentAmount(): float
    {
        return (float) UserSubscription::whereNotNull('last_payment_amount')
            ->sum('last_payment_amount');
    }

    public function paginateWithFilters(?string $search, ?string $tierFilter, int $perPage = 20): array
    {
        $query = UserSubscription::with(['user', 'tier'])
            ->where('is_active', true);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($tierFilter) {
            $query->whereHas('tier', function ($q) use ($tierFilter) {
                $q->where('name', $tierFilter);
            });
        }

        return $query->paginate($perPage)->toArray();
    }
}