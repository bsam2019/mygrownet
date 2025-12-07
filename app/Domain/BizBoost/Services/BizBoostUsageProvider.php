<?php

namespace App\Domain\BizBoost\Services;

use App\Domain\Module\Contracts\ModuleUsageProviderInterface;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * BizBoost Usage Provider
 * 
 * Provides usage metrics for BizBoost marketing module.
 * Implements the centralized ModuleUsageProviderInterface.
 */
class BizBoostUsageProvider implements ModuleUsageProviderInterface
{
    public function getModuleId(): string
    {
        return 'bizboost';
    }

    public function getUsageMetrics(User $user): array
    {
        return [
            'posts_per_month' => $this->getMonthlyPostCount($user),
            'ai_credits_per_month' => $this->getMonthlyAiCreditsUsed($user),
            'templates' => $this->getCustomTemplateCount($user),
            'customers' => $this->getCustomerCount($user),
            'products' => $this->getProductCount($user),
            'campaigns' => $this->getCampaignCount($user),
            'storage_mb' => (int) round($this->getStorageUsed($user) / 1024 / 1024),
            'team_members' => $this->getTeamMemberCount($user),
            'locations' => $this->getLocationCount($user),
        ];
    }

    public function getMetric(User $user, string $metricKey): int
    {
        return match ($metricKey) {
            'posts_per_month' => $this->getMonthlyPostCount($user),
            'ai_credits_per_month' => $this->getMonthlyAiCreditsUsed($user),
            'templates' => $this->getCustomTemplateCount($user),
            'customers' => $this->getCustomerCount($user),
            'products' => $this->getProductCount($user),
            'campaigns' => $this->getCampaignCount($user),
            'storage_mb' => (int) round($this->getStorageUsed($user) / 1024 / 1024),
            'team_members' => $this->getTeamMemberCount($user),
            'locations' => $this->getLocationCount($user),
            default => 0,
        };
    }

    public function clearCache(User $user): void
    {
        $monthKey = now()->format('Y-m');
        $businessId = $this->getBusinessId($user);
        
        Cache::forget("bizboost_posts_count_{$businessId}_{$monthKey}");
        Cache::forget("bizboost_ai_credits_{$businessId}_{$monthKey}");
        Cache::forget("bizboost_templates_{$businessId}");
        Cache::forget("bizboost_customers_{$businessId}");
        Cache::forget("bizboost_products_{$businessId}");
        Cache::forget("bizboost_campaigns_{$businessId}");
        Cache::forget("bizboost_storage_{$businessId}");
        Cache::forget("bizboost_team_{$businessId}");
        Cache::forget("bizboost_locations_{$businessId}");
    }

    public function getStorageUsed(User $user): int
    {
        $businessId = $this->getBusinessId($user);
        if (!$businessId) return 0;
        
        $cacheKey = "bizboost_storage_{$businessId}";

        return Cache::remember($cacheKey, 600, function () use ($businessId) {
            // Sum storage from post media and product images
            $postMedia = DB::table('bizboost_post_media')
                ->whereIn('post_id', function ($query) use ($businessId) {
                    $query->select('id')
                        ->from('bizboost_posts')
                        ->where('business_id', $businessId);
                })
                ->sum('file_size') ?? 0;
                
            $productImages = DB::table('bizboost_product_images')
                ->whereIn('product_id', function ($query) use ($businessId) {
                    $query->select('id')
                        ->from('bizboost_products')
                        ->where('business_id', $businessId);
                })
                ->sum('file_size') ?? 0;
                
            return $postMedia + $productImages;
        });
    }

    private function getBusinessId(User $user): ?int
    {
        return DB::table('bizboost_businesses')
            ->where('user_id', $user->id)
            ->value('id');
    }

    private function getMonthlyPostCount(User $user): int
    {
        $businessId = $this->getBusinessId($user);
        if (!$businessId) return 0;
        
        $cacheKey = "bizboost_posts_count_{$businessId}_" . now()->format('Y-m');

        return Cache::remember($cacheKey, 300, function () use ($businessId) {
            return DB::table('bizboost_posts')
                ->where('business_id', $businessId)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();
        });
    }

    private function getMonthlyAiCreditsUsed(User $user): int
    {
        $businessId = $this->getBusinessId($user);
        if (!$businessId) return 0;
        
        $cacheKey = "bizboost_ai_credits_{$businessId}_" . now()->format('Y-m');

        return Cache::remember($cacheKey, 300, function () use ($businessId) {
            return DB::table('bizboost_ai_usage_logs')
                ->where('business_id', $businessId)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('credits_used') ?? 0;
        });
    }

    private function getCustomTemplateCount(User $user): int
    {
        $businessId = $this->getBusinessId($user);
        if (!$businessId) return 0;
        
        $cacheKey = "bizboost_templates_{$businessId}";

        return Cache::remember($cacheKey, 300, function () use ($businessId) {
            return DB::table('bizboost_custom_templates')
                ->where('business_id', $businessId)
                ->count();
        });
    }

    private function getCustomerCount(User $user): int
    {
        $businessId = $this->getBusinessId($user);
        if (!$businessId) return 0;
        
        $cacheKey = "bizboost_customers_{$businessId}";

        return Cache::remember($cacheKey, 300, function () use ($businessId) {
            return DB::table('bizboost_customers')
                ->where('business_id', $businessId)
                ->count();
        });
    }

    private function getProductCount(User $user): int
    {
        $businessId = $this->getBusinessId($user);
        if (!$businessId) return 0;
        
        $cacheKey = "bizboost_products_{$businessId}";

        return Cache::remember($cacheKey, 300, function () use ($businessId) {
            return DB::table('bizboost_products')
                ->where('business_id', $businessId)
                ->count();
        });
    }

    private function getCampaignCount(User $user): int
    {
        $businessId = $this->getBusinessId($user);
        if (!$businessId) return 0;
        
        $cacheKey = "bizboost_campaigns_{$businessId}";

        return Cache::remember($cacheKey, 300, function () use ($businessId) {
            return DB::table('bizboost_campaigns')
                ->where('business_id', $businessId)
                ->count();
        });
    }

    private function getTeamMemberCount(User $user): int
    {
        $businessId = $this->getBusinessId($user);
        if (!$businessId) return 0;
        
        $cacheKey = "bizboost_team_{$businessId}";

        return Cache::remember($cacheKey, 300, function () use ($businessId) {
            return DB::table('bizboost_team_members')
                ->where('business_id', $businessId)
                ->where('status', 'active')
                ->count();
        });
    }

    private function getLocationCount(User $user): int
    {
        $businessId = $this->getBusinessId($user);
        if (!$businessId) return 0;
        
        $cacheKey = "bizboost_locations_{$businessId}";

        return Cache::remember($cacheKey, 300, function () use ($businessId) {
            return DB::table('bizboost_locations')
                ->where('business_id', $businessId)
                ->count();
        });
    }
}
