<?php

namespace App\Domain\LifePlus\Services;

use App\Domain\Module\Services\ModuleAccessService;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * LifePlus Feature Access Service
 * 
 * Handles feature-level access control and usage limits for LifePlus app.
 * Works with the existing ModuleAccessService for tier-based access.
 */
class LifePlusAccessService
{
    private const MODULE_ID = 'lifeplus';

    public function __construct(
        private readonly ModuleAccessService $moduleAccessService
    ) {}

    /**
     * Get user's current tier for LifePlus
     */
    public function getUserTier(User $user): string
    {
        return $this->moduleAccessService->getAccessLevel($user, ModuleId::fromString(self::MODULE_ID));
    }

    /**
     * Check if user can access a specific feature
     */
    public function canAccessFeature(User $user, string $feature): bool
    {
        $tier = $this->getUserTier($user);
        $features = $this->getTierFeatures($tier);
        
        return $features[$feature] ?? false;
    }

    /**
     * Get the limit for a specific feature
     * Returns -1 for unlimited
     */
    public function getFeatureLimit(User $user, string $feature): int
    {
        $tier = $this->getUserTier($user);
        $limits = $this->getTierLimits($tier);
        
        return $limits[$feature] ?? 0;
    }

    /**
     * Check if user has reached their limit for a feature
     */
    public function hasReachedLimit(User $user, string $feature): bool
    {
        $limit = $this->getFeatureLimit($user, $feature);
        
        if ($limit === -1) {
            return false; // Unlimited
        }
        
        $currentUsage = $this->getCurrentUsage($user, $feature);
        
        return $currentUsage >= $limit;
    }

    /**
     * Get current usage count for a feature
     */
    public function getCurrentUsage(User $user, string $feature): int
    {
        return match ($feature) {
            'tasks' => $this->getActiveTasksCount($user),
            'habits' => $this->getActiveHabitsCount($user),
            'chilimba_groups' => $this->getChilimbaGroupsCount($user),
            default => 0,
        };
    }

    /**
     * Get all access info for frontend
     */
    public function getAccessInfo(User $user): array
    {
        $tier = $this->getUserTier($user);
        $features = $this->getTierFeatures($tier);
        $limits = $this->getTierLimits($tier);
        
        return [
            'tier' => $tier,
            'tier_name' => $this->getTierName($tier),
            'features' => $features,
            'limits' => $limits,
            'usage' => [
                'tasks' => $this->getActiveTasksCount($user),
                'habits' => $this->getActiveHabitsCount($user),
                'chilimba_groups' => $this->getChilimbaGroupsCount($user),
            ],
            'can_upgrade' => $this->canUpgrade($tier),
            'upgrade_benefits' => $this->getUpgradeBenefits($tier),
        ];
    }

    /**
     * Get features available for a tier
     */
    private function getTierFeatures(string $tier): array
    {
        return match ($tier) {
            'free' => [
                'tasks' => true,
                'expenses' => true,
                'habits' => true,
                'community_view' => true,
                'community_post' => false,
                'gigs_view' => true,
                'gigs_post' => false,
                'chilimba' => false,
                'budget_planning' => false,
                'analytics' => false,
                'data_export' => false,
                'notes' => true,
                'knowledge' => true,
            ],
            'premium' => [
                'tasks' => true,
                'expenses' => true,
                'habits' => true,
                'community_view' => true,
                'community_post' => true,
                'gigs_view' => true,
                'gigs_post' => true,
                'chilimba' => true,
                'budget_planning' => true,
                'analytics' => true,
                'data_export' => true,
                'notes' => true,
                'knowledge' => true,
                'offline_mode' => true,
            ],
            'member_free', 'elite', 'business' => [
                'tasks' => true,
                'expenses' => true,
                'habits' => true,
                'community_view' => true,
                'community_post' => true,
                'gigs_view' => true,
                'gigs_post' => true,
                'chilimba' => true,
                'budget_planning' => true,
                'analytics' => true,
                'data_export' => true,
                'notes' => true,
                'knowledge' => true,
                'offline_mode' => true,
                'mygrownet_integration' => true,
                'earnings_tracking' => $tier !== 'premium',
            ],
            default => [
                'tasks' => true,
                'expenses' => true,
                'habits' => true,
                'community_view' => true,
                'community_post' => false,
                'gigs_view' => true,
                'gigs_post' => false,
                'chilimba' => false,
                'budget_planning' => false,
                'analytics' => false,
                'data_export' => false,
                'notes' => true,
                'knowledge' => true,
            ],
        };
    }

    /**
     * Get limits for a tier
     */
    private function getTierLimits(string $tier): array
    {
        return match ($tier) {
            'free' => [
                'tasks' => 10,
                'habits' => 1,
                'chilimba_groups' => 0,
                'expense_history_months' => 1,
                'devices' => 1,
            ],
            'premium' => [
                'tasks' => -1,
                'habits' => -1,
                'chilimba_groups' => 2,
                'expense_history_months' => -1,
                'devices' => 3,
            ],
            'member_free', 'elite', 'business' => [
                'tasks' => -1,
                'habits' => -1,
                'chilimba_groups' => -1,
                'expense_history_months' => -1,
                'devices' => -1,
            ],
            default => [
                'tasks' => 10,
                'habits' => 1,
                'chilimba_groups' => 0,
                'expense_history_months' => 1,
                'devices' => 1,
            ],
        };
    }

    /**
     * Get human-readable tier name
     */
    private function getTierName(string $tier): string
    {
        return match ($tier) {
            'free' => 'Free',
            'premium' => 'Premium',
            'member_free' => 'Member',
            'elite' => 'Elite',
            'business' => 'Business',
            default => 'Free',
        };
    }

    /**
     * Check if user can upgrade from current tier
     */
    private function canUpgrade(string $tier): bool
    {
        return in_array($tier, ['free', 'none']);
    }

    /**
     * Get benefits of upgrading from current tier
     */
    private function getUpgradeBenefits(string $tier): array
    {
        if (!$this->canUpgrade($tier)) {
            return [];
        }

        return [
            'Unlimited tasks and habits',
            'Chilimba group tracker',
            'Budget planning tools',
            'Analytics & reports',
            'Post in community',
            'Create gig listings',
            'Data export',
            'Offline mode',
        ];
    }

    private function getActiveTasksCount(User $user): int
    {
        return DB::table('lifeplus_tasks')
            ->where('user_id', $user->id)
            ->where('is_completed', false)
            ->count();
    }

    private function getActiveHabitsCount(User $user): int
    {
        return DB::table('lifeplus_habits')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->count();
    }

    private function getChilimbaGroupsCount(User $user): int
    {
        return DB::table('lifeplus_chilimba_members')
            ->where('user_id', $user->id)
            ->distinct('group_id')
            ->count('group_id');
    }
}
