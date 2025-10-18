<template>
    <div class="referral-stats-dashboard space-y-6">
        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <UsersIcon class="h-6 w-6 text-blue-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Referrals</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total_referrals_count }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ stats.active_referrals_count }} active
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <BanknoteIcon class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Commission</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_commission_earned) }}</p>
                        <p class="text-xs text-emerald-600 mt-1">
                            +{{ formatCurrency(stats.monthly_commission) }} this month
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <ClockIcon class="h-6 w-6 text-amber-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending Commission</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.pending_commission) }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ stats.pending_transactions_count }} transactions
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <TrendingUpIcon class="h-6 w-6 text-purple-600" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Matrix Earnings</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.matrix_earnings) }}</p>
                        <p class="text-xs text-purple-600 mt-1">
                            {{ stats.matrix_positions_filled }}/39 positions
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings Breakdown -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Earnings Breakdown</h3>
                <div class="flex items-center space-x-2">
                    <button 
                        @click="selectedPeriod = 'week'"
                        :class="periodButtonClasses('week')"
                    >
                        Week
                    </button>
                    <button 
                        @click="selectedPeriod = 'month'"
                        :class="periodButtonClasses('month')"
                    >
                        Month
                    </button>
                    <button 
                        @click="selectedPeriod = 'year'"
                        :class="periodButtonClasses('year')"
                    >
                        Year
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Earnings by Level Chart -->
                <div>
                    <h4 class="text-md font-medium text-gray-700 mb-4">Commission by Level</h4>
                    <div class="space-y-4">
                        <div v-for="level in (earningsBreakdown?.by_level || [])" :key="level.level" class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div :class="levelIndicatorClasses(level.level)"></div>
                                <span class="text-sm font-medium text-gray-700">Level {{ level.level }}</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-gray-900">{{ formatCurrency(level.amount) }}</div>
                                <div class="text-xs text-gray-500">{{ level.count }} referrals</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Earnings by Source -->
                <div>
                    <h4 class="text-md font-medium text-gray-700 mb-4">Earnings by Source</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-emerald-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Direct Referrals</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-gray-900">{{ formatCurrency(earningsBreakdown?.direct_referrals || 0) }}</div>
                                <div class="text-xs text-gray-500">{{ calculatePercentage(earningsBreakdown?.direct_referrals || 0, earningsBreakdown?.total || 0) }}%</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Spillover</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-gray-900">{{ formatCurrency(earningsBreakdown?.spillover || 0) }}</div>
                                <div class="text-xs text-gray-500">{{ calculatePercentage(earningsBreakdown?.spillover || 0, earningsBreakdown?.total || 0) }}%</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Matrix Bonuses</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-gray-900">{{ formatCurrency(earningsBreakdown?.matrix_bonuses || 0) }}</div>
                                <div class="text-xs text-gray-500">{{ calculatePercentage(earningsBreakdown?.matrix_bonuses || 0, earningsBreakdown?.total || 0) }}%</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Reinvestment Bonuses</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold text-gray-900">{{ formatCurrency(earningsBreakdown?.reinvestment_bonuses || 0) }}</div>
                                <div class="text-xs text-gray-500">{{ calculatePercentage(earningsBreakdown?.reinvestment_bonuses || 0, earningsBreakdown?.total || 0) }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Referral Performance -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Referral Performance</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Conversion Rate</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-emerald-500 h-2 rounded-full transition-all duration-300"
                                    :style="{ width: `${performance?.conversion_rate || 0}%` }"
                                ></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ performance?.conversion_rate || 0 }}%</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Average Investment</span>
                        <span class="text-sm font-medium text-gray-900">{{ formatCurrency(performance?.average_investment || 0) }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Retention Rate</span>
                        <div class="flex items-center space-x-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                                    :style="{ width: `${performance?.retention_rate || 0}%` }"
                                ></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ performance?.retention_rate || 0 }}%</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Growth Rate (Monthly)</span>
                        <span class="text-sm font-medium text-emerald-600">+{{ performance?.growth_rate || 0 }}%</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                    <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View All
                    </button>
                </div>
                <div class="space-y-3">
                    <div v-for="activity in (recentActivity || [])" :key="activity.id" class="flex items-center space-x-3">
                        <div :class="activityIconClasses(activity.type)">
                            <component :is="getActivityIcon(activity.type)" class="h-4 w-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 truncate">{{ activity.description }}</p>
                            <p class="text-xs text-gray-500">{{ formatRelativeTime(activity.created_at) }}</p>
                        </div>
                        <div v-if="activity.amount" class="text-sm font-medium text-emerald-600">
                            +{{ formatCurrency(activity.amount) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tier Analysis -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Referral Tier Distribution</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div v-for="tier in (tierDistribution || [])" :key="tier.name" class="text-center">
                    <div :class="tierCardClasses(tier.name)">
                        <div class="text-2xl font-bold">{{ tier.count }}</div>
                        <div class="text-xs font-medium mt-1">{{ tier.name }}</div>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
                        {{ formatCurrency(tier.total_investment) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { 
    UsersIcon, 
    BanknoteIcon, 
    ClockIcon, 
    TrendingUpIcon,
    UserPlusIcon,
    DollarSignIcon,
    ArrowUpIcon,
    GiftIcon
} from 'lucide-vue-next';
import { formatCurrency } from '@/utils/formatting';

interface ReferralStats {
    total_referrals_count: number;
    active_referrals_count: number;
    total_commission_earned: number;
    monthly_commission: number;
    pending_commission: number;
    pending_transactions_count: number;
    matrix_earnings: number;
    matrix_positions_filled: number;
}

interface EarningsBreakdown {
    by_level: Array<{
        level: number;
        amount: number;
        count: number;
    }>;
    direct_referrals: number;
    spillover: number;
    matrix_bonuses: number;
    reinvestment_bonuses: number;
    total: number;
}

interface Performance {
    conversion_rate: number;
    average_investment: number;
    retention_rate: number;
    growth_rate: number;
}

interface Activity {
    id: number;
    type: 'referral' | 'commission' | 'spillover' | 'bonus';
    description: string;
    amount?: number;
    created_at: string;
}

interface TierDistribution {
    name: string;
    count: number;
    total_investment: number;
}

interface Props {
    stats: ReferralStats;
    earningsBreakdown: EarningsBreakdown;
    performance: Performance;
    recentActivity: Activity[];
    tierDistribution: TierDistribution[];
}

const props = defineProps<Props>();

const selectedPeriod = ref<'week' | 'month' | 'year'>('month');

// Computed classes
const periodButtonClasses = (period: string) => [
    'px-3 py-1 text-sm font-medium rounded-md transition-colors',
    selectedPeriod.value === period
        ? 'bg-blue-100 text-blue-700'
        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
];

const levelIndicatorClasses = (level: number) => [
    'w-3 h-3 rounded-full',
    {
        'bg-emerald-500': level === 1,
        'bg-amber-500': level === 2,
        'bg-purple-500': level === 3
    }
];

const activityIconClasses = (type: string) => [
    'w-8 h-8 rounded-full flex items-center justify-center',
    {
        'bg-emerald-100 text-emerald-600': type === 'referral',
        'bg-blue-100 text-blue-600': type === 'commission',
        'bg-amber-100 text-amber-600': type === 'spillover',
        'bg-purple-100 text-purple-600': type === 'bonus'
    }
];

const tierCardClasses = (tierName: string) => [
    'p-4 rounded-lg border-2',
    {
        'bg-gray-50 border-gray-200 text-gray-700': tierName === 'Basic',
        'bg-blue-50 border-blue-200 text-blue-700': tierName === 'Starter',
        'bg-blue-100 border-blue-300 text-blue-800': tierName === 'Builder',
        'bg-indigo-50 border-indigo-200 text-indigo-700': tierName === 'Leader',
        'bg-purple-50 border-purple-200 text-purple-700': tierName === 'Elite'
    }
];



const calculatePercentage = (value: number, total: number): number => {
    return total > 0 ? Math.round((value / total) * 100) : 0;
};

const formatRelativeTime = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    return `${Math.floor(diffInSeconds / 86400)}d ago`;
};

const getActivityIcon = (type: string) => {
    const icons = {
        'referral': UserPlusIcon,
        'commission': DollarSignIcon,
        'spillover': ArrowUpIcon,
        'bonus': GiftIcon
    };
    return icons[type as keyof typeof icons] || UserPlusIcon;
};
</script>

<style scoped>
.referral-stats-dashboard {
    /* Custom styles if needed */
}

/* Animation for progress bars */
.bg-emerald-500, .bg-blue-500 {
    transition: width 0.3s ease-in-out;
}

/* Hover effects for cards */
.bg-white:hover {
    @apply shadow-md;
    transition: box-shadow 0.2s ease-in-out;
}
</style>