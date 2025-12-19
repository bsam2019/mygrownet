import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface LifePlusAccess {
    tier: string;
    tier_name: string;
    features: Record<string, boolean>;
    limits: Record<string, number>;
    usage: {
        tasks: number;
        habits: number;
        chilimba_groups: number;
    };
    can_upgrade: boolean;
    upgrade_benefits: string[];
}

/**
 * Composable for accessing LifePlus tier and feature information
 */
export function useLifePlusAccess() {
    const page = usePage();

    const access = computed<LifePlusAccess | null>(() => {
        return (page.props as any).lifeplusAccess || null;
    });

    const tier = computed(() => access.value?.tier || 'free');
    const tierName = computed(() => access.value?.tier_name || 'Free');
    const features = computed(() => access.value?.features || {});
    const limits = computed(() => access.value?.limits || {});
    const usage = computed(() => access.value?.usage || { tasks: 0, habits: 0, chilimba_groups: 0 });
    const canUpgrade = computed(() => access.value?.can_upgrade || false);
    const upgradeBenefits = computed(() => access.value?.upgrade_benefits || []);

    /**
     * Check if user can access a specific feature
     */
    const canAccess = (feature: string): boolean => {
        return features.value[feature] ?? false;
    };

    /**
     * Get the limit for a feature (-1 = unlimited)
     */
    const getLimit = (feature: string): number => {
        return limits.value[feature] ?? 0;
    };

    /**
     * Check if user has reached their limit for a feature
     */
    const hasReachedLimit = (feature: string): boolean => {
        const limit = getLimit(feature);
        if (limit === -1) return false; // Unlimited

        const currentUsage = usage.value[feature as keyof typeof usage.value] ?? 0;
        return currentUsage >= limit;
    };

    /**
     * Get remaining count for a feature
     */
    const getRemaining = (feature: string): number | null => {
        const limit = getLimit(feature);
        if (limit === -1) return null; // Unlimited

        const currentUsage = usage.value[feature as keyof typeof usage.value] ?? 0;
        return Math.max(0, limit - currentUsage);
    };

    /**
     * Check if user is on free tier
     */
    const isFreeTier = computed(() => tier.value === 'free' || tier.value === 'none');

    /**
     * Check if user is a member (MLM)
     */
    const isMember = computed(() => ['member_free', 'elite'].includes(tier.value));

    /**
     * Check if user has premium or higher
     */
    const isPremiumOrHigher = computed(() => !isFreeTier.value);

    return {
        access,
        tier,
        tierName,
        features,
        limits,
        usage,
        canUpgrade,
        upgradeBenefits,
        canAccess,
        getLimit,
        hasReachedLimit,
        getRemaining,
        isFreeTier,
        isMember,
        isPremiumOrHigher,
    };
}
