<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { 
    ChartBarIcon, 
    ArrowUpIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolidIcon } from '@heroicons/vue/24/solid';

interface UsageItem {
    used: number;
    limit: number;
    remaining?: number;
    allowed?: boolean;
}

interface Props {
    usageSummary: Record<string, UsageItem | any>;
    currentTier: string;
    limits: Record<string, number | boolean>;
}

const props = defineProps<Props>();
const page = usePage();

// Check if user is admin
const isAdmin = computed(() => {
    const user = page.props.auth?.user as any;
    return user?.roles?.some((role: any) => 
        role.name?.toLowerCase().includes('admin')
    ) || props.usageSummary?.is_admin || false;
});

const formatLimit = (limit: number) => {
    if (limit === -1) return 'Unlimited';
    if (limit === 0) return 'Not available';
    return limit.toLocaleString();
};

const getProgressColor = (used: number, limit: number) => {
    if (limit === -1 || limit === 0) return 'bg-emerald-500';
    const percentage = (used / limit) * 100;
    if (percentage >= 90) return 'bg-red-500';
    if (percentage >= 70) return 'bg-amber-500';
    return 'bg-blue-500';
};

const getPercentage = (used: number, limit: number) => {
    if (limit === -1 || limit === 0) return 0;
    return Math.min((used / limit) * 100, 100);
};

const usageLabels: Record<string, string> = {
    posts_per_month: 'Posts Per Month',
    templates: 'Custom Templates',
    ai_credits_per_month: 'AI Credits (Monthly)',
    customers: 'Customers',
    products: 'Products',
    campaigns: 'Marketing Campaigns',
    storage_mb: 'Storage (MB)',
    team_members: 'Team Members',
    locations: 'Business Locations',
};

const tierLabels: Record<string, string> = {
    free: 'Free',
    basic: 'Basic',
    professional: 'Professional',
    business: 'Business',
};

// Filter out non-usage items from summary
const usageMetrics = computed(() => {
    const metrics: Record<string, UsageItem> = {};
    const excludeKeys = ['tier', 'tier_name', 'is_admin', 'features', 'reports', 'storage'];
    
    for (const [key, value] of Object.entries(props.usageSummary)) {
        if (!excludeKeys.includes(key) && typeof value === 'object' && 'used' in value && 'limit' in value) {
            metrics[key] = value as UsageItem;
        }
    }
    return metrics;
});
</script>

<template>
    <Head title="Usage - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <ChartBarIcon class="h-7 w-7 text-violet-600" aria-hidden="true" />
                            Usage Overview
                        </h1>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-sm text-gray-600">Current plan:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-violet-100 text-violet-800">
                                {{ tierLabels[currentTier] || currentTier }}
                            </span>
                            <span v-if="isAdmin" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                                <CheckCircleSolidIcon class="h-4 w-4" aria-hidden="true" />
                                Admin Access
                            </span>
                        </div>
                    </div>
                    <Link
                        v-if="!isAdmin"
                        :href="route('bizboost.upgrade')"
                        class="inline-flex items-center px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors"
                    >
                        <ArrowUpIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                        Upgrade Plan
                    </Link>
                </div>

                <!-- Admin Notice -->
                <div v-if="isAdmin" class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <CheckCircleIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                        <div>
                            <p class="font-medium text-emerald-800">Administrator Access</p>
                            <p class="text-sm text-emerald-700">You have unlimited access to all features and resources.</p>
                        </div>
                    </div>
                </div>

                <!-- Usage Cards -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Resource Usage</h2>
                        <p class="text-sm text-gray-500">Track your usage across different metrics</p>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div
                            v-for="(usage, key) in usageMetrics"
                            :key="key"
                            class="p-5 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-900">{{ usageLabels[key] || key.replace(/_/g, ' ') }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ usage.used.toLocaleString() }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        / {{ formatLimit(usage.limit) }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div v-if="usage.limit > 0" class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div
                                    :class="['h-full rounded-full transition-all duration-300', getProgressColor(usage.used, usage.limit)]"
                                    :style="{ width: `${getPercentage(usage.used, usage.limit)}%` }"
                                ></div>
                            </div>
                            
                            <!-- Unlimited Badge -->
                            <div v-else-if="usage.limit === -1" class="flex items-center gap-1 text-sm text-emerald-600">
                                <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
                                Unlimited
                            </div>
                            
                            <!-- Not Available -->
                            <div v-else class="text-sm text-gray-400">
                                Not available on current plan
                            </div>
                            
                            <!-- Warning -->
                            <div 
                                v-if="usage.limit > 0 && getPercentage(usage.used, usage.limit) >= 80" 
                                class="mt-2 flex items-center gap-1 text-sm"
                                :class="getPercentage(usage.used, usage.limit) >= 100 ? 'text-red-600' : 'text-amber-600'"
                            >
                                <ExclamationTriangleIcon class="h-4 w-4" aria-hidden="true" />
                                {{ getPercentage(usage.used, usage.limit) >= 100 ? 'Limit reached!' : 'Approaching limit' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upgrade CTA -->
                <div v-if="!isAdmin" class="mt-6 bg-gradient-to-r from-violet-50 to-purple-50 border border-violet-200 rounded-xl p-5">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <p class="font-medium text-violet-900">Need more resources?</p>
                            <p class="text-sm text-violet-700">Upgrade your plan to unlock higher limits and additional features.</p>
                        </div>
                        <Link 
                            :href="route('bizboost.upgrade')" 
                            class="inline-flex items-center px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors font-medium"
                        >
                            View Plans
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
