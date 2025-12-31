<?php

namespace App\Services\GrowBuilder;

use App\Domain\Module\Services\TierConfigurationService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AIUsageService
{
    private const MODULE_ID = 'growbuilder';
    private const CACHE_TTL = 300; // 5 minutes

    // Fallback AI limits per tier (used if not in database)
    private const DEFAULT_TIER_LIMITS = [
        'free' => 5,
        'starter' => 100,
        'business' => -1, // unlimited
        'agency' => -1,   // unlimited
    ];

    // Fallback features per tier
    private const DEFAULT_TIER_FEATURES = [
        'free' => ['content'],
        'starter' => ['content', 'section'],
        'business' => ['content', 'seo', 'section'],
        'agency' => ['content', 'seo', 'section', 'priority'],
    ];

    public function __construct(
        private TierRestrictionService $tierRestrictionService,
        private ?TierConfigurationService $tierConfigService = null
    ) {}

    /**
     * Get AI limit for user's current tier
     */
    public function getLimit(User $user): int
    {
        $tier = $this->tierRestrictionService->getUserTier($user);
        
        // Try to get from database config
        if ($this->tierConfigService) {
            $tierConfig = $this->tierConfigService->getTierConfig(self::MODULE_ID, $tier);
            
            // Check for ai_prompts limit
            if ($tierConfig && isset($tierConfig['limits']['ai_prompts'])) {
                return (int) $tierConfig['limits']['ai_prompts'];
            }
            
            // Check for ai_unlimited feature
            if ($tierConfig && isset($tierConfig['features']['ai_unlimited'])) {
                $value = is_array($tierConfig['features']['ai_unlimited']) 
                    ? $tierConfig['features']['ai_unlimited']['value'] 
                    : $tierConfig['features']['ai_unlimited'];
                if ($value) return -1;
            }
        }
        
        // Fallback to hardcoded defaults
        return self::DEFAULT_TIER_LIMITS[$tier] ?? 5;
    }

    /**
     * Get current month's usage count
     */
    public function getUsageCount(User $user): int
    {
        $monthYear = now()->format('Y-m');
        $cacheKey = "ai_usage:{$user->id}:{$monthYear}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $monthYear) {
            return DB::table('growbuilder_ai_usage')
                ->where('user_id', $user->id)
                ->where('month_year', $monthYear)
                ->count();
        });
    }

    /**
     * Get remaining prompts for the month
     */
    public function getRemainingPrompts(User $user): int
    {
        $limit = $this->getLimit($user);
        
        // Unlimited
        if ($limit === -1) {
            return -1;
        }

        $used = $this->getUsageCount($user);
        return max(0, $limit - $used);
    }

    /**
     * Check if user can use AI
     */
    public function canUseAI(User $user): bool
    {
        $remaining = $this->getRemainingPrompts($user);
        return $remaining === -1 || $remaining > 0;
    }

    /**
     * Check if user has access to a specific AI feature
     */
    public function hasFeatureAccess(User $user, string $feature): bool
    {
        $tier = $this->tierRestrictionService->getUserTier($user);
        
        // Try to get from database config
        if ($this->tierConfigService) {
            $tierConfig = $this->tierConfigService->getTierConfig(self::MODULE_ID, $tier);
            
            if ($tierConfig && isset($tierConfig['features'])) {
                // Map feature names to database feature keys
                $featureMap = [
                    'content' => true, // Always available
                    'section' => 'section_generator',
                    'seo' => 'ai_seo',
                    'priority' => 'ai_priority',
                ];
                
                $dbKey = $featureMap[$feature] ?? $feature;
                
                if ($dbKey === true) return true;
                
                if (isset($tierConfig['features'][$dbKey])) {
                    $value = is_array($tierConfig['features'][$dbKey]) 
                        ? $tierConfig['features'][$dbKey]['value'] 
                        : $tierConfig['features'][$dbKey];
                    return (bool) $value;
                }
            }
        }
        
        // Fallback to hardcoded defaults
        $features = self::DEFAULT_TIER_FEATURES[$tier] ?? ['content'];
        return in_array($feature, $features, true);
    }

    /**
     * Check if user has unlimited AI
     */
    public function hasUnlimitedAI(User $user): bool
    {
        return $this->getLimit($user) === -1;
    }

    /**
     * Check if user has priority processing
     */
    public function hasPriorityProcessing(User $user): bool
    {
        return $this->hasFeatureAccess($user, 'priority');
    }

    /**
     * Record AI usage
     */
    public function recordUsage(
        User $user,
        string $promptType,
        ?string $prompt = null,
        int $tokensUsed = 0,
        ?int $siteId = null,
        ?string $model = null
    ): bool {
        $monthYear = now()->format('Y-m');

        DB::table('growbuilder_ai_usage')->insert([
            'user_id' => $user->id,
            'site_id' => $siteId,
            'prompt_type' => $promptType,
            'prompt' => $prompt ? substr($prompt, 0, 1000) : null, // Limit stored prompt length
            'tokens_used' => $tokensUsed,
            'month_year' => $monthYear,
            'model' => $model,
            'is_priority' => $this->hasPriorityProcessing($user),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Clear cache
        $this->clearCache($user);

        return true;
    }

    /**
     * Get usage statistics for user
     */
    public function getUsageStats(User $user): array
    {
        $monthYear = now()->format('Y-m');
        $limit = $this->getLimit($user);
        $used = $this->getUsageCount($user);

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => $limit === -1 ? -1 : max(0, $limit - $used),
            'is_unlimited' => $limit === -1,
            'percentage' => $limit > 0 ? min(100, round(($used / $limit) * 100)) : 0,
            'month' => $monthYear,
            'features' => $this->getAvailableFeatures($user),
            'has_priority' => $this->hasPriorityProcessing($user),
        ];
    }

    /**
     * Get available AI features for user
     */
    public function getAvailableFeatures(User $user): array
    {
        // Build features list based on what's available
        $features = ['content']; // Always available
        
        if ($this->hasFeatureAccess($user, 'section')) {
            $features[] = 'section';
        }
        if ($this->hasFeatureAccess($user, 'seo')) {
            $features[] = 'seo';
        }
        if ($this->hasFeatureAccess($user, 'priority')) {
            $features[] = 'priority';
        }
        
        return $features;
    }

    /**
     * Get upgrade message when limit reached
     */
    public function getUpgradeMessage(User $user): string
    {
        $tier = $this->tierRestrictionService->getUserTier($user);

        if ($tier === 'free' || $tier === 'starter') {
            return 'Upgrade to Business plan for unlimited AI prompts and SEO assistant.';
        }

        return 'You have reached your AI limit for this month.';
    }

    /**
     * Clear usage cache for user
     */
    public function clearCache(User $user): void
    {
        $monthYear = now()->format('Y-m');
        Cache::forget("ai_usage:{$user->id}:{$monthYear}");
    }

    /**
     * Get monthly usage breakdown by type
     */
    public function getUsageBreakdown(User $user): array
    {
        $monthYear = now()->format('Y-m');

        return DB::table('growbuilder_ai_usage')
            ->where('user_id', $user->id)
            ->where('month_year', $monthYear)
            ->select('prompt_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(tokens_used) as tokens'))
            ->groupBy('prompt_type')
            ->get()
            ->keyBy('prompt_type')
            ->toArray();
    }
}
