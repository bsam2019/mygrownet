<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { 
    ArrowTrendingUpIcon, 
    UsersIcon, 
    GiftIcon, 
    StarIcon,
    ArrowRightIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency } from '@/utils/formatting';

interface Props {
    earnings?: {
        referral_commissions?: number;
        matrix_commissions?: number;
        profit_shares?: number;
        pending_earnings?: number;
    };
    referralStats?: {
        total_referrals?: number;
        active_referrals?: number;
        total_commission?: number;
        pending_commission?: number;
    };
    tierInfo?: {
        current_tier?: {
            name: string;
            fixed_profit_rate: number;
            direct_referral_rate: number;
        };
        next_tier?: {
            name: string;
        };
    };
    matrixData?: {
        total_downline?: number;
        level_1?: number;
        level_2?: number;
        level_3?: number;
    };
}

const props = withDefaults(defineProps<Props>(), {
    earnings: () => ({
        referral_commissions: 0,
        matrix_commissions: 0,
        profit_shares: 0,
        pending_earnings: 0,
    }),
    referralStats: () => ({
        total_referrals: 0,
        active_referrals: 0,
        total_commission: 0,
        pending_commission: 0,
    }),
    tierInfo: () => ({}),
    matrixData: () => ({
        total_downline: 0,
        level_1: 0,
        level_2: 0,
        level_3: 0,
    }),
});

const rewardSystemLinks = [
    {
        title: 'Commission Tracking',
        description: 'Track your referral and matrix commissions',
        href: 'referrals.commissions',
        icon: ArrowTrendingUpIcon,
        color: 'bg-green-50 text-green-600',
        value: formatCurrency(props.earnings.referral_commissions + props.earnings.matrix_commissions),
    },
    {
        title: 'Matrix Genealogy',
        description: 'View your complete matrix structure',
        href: 'referrals.matrix-genealogy',
        icon: UsersIcon,
        color: 'bg-blue-50 text-blue-600',
        value: `${props.matrixData.total_downline} Members`,
    },
    {
        title: 'Performance Report',
        description: 'Detailed performance analytics',
        href: 'referrals.performance-report',
        icon: ChartBarIcon,
        color: 'bg-purple-50 text-purple-600',
        value: `${props.referralStats.active_referrals} Active`,
    },
    {
        title: 'Tier Benefits',
        description: 'Compare tier benefits and upgrade',
        href: 'tiers.compare',
        icon: StarIcon,
        color: 'bg-yellow-50 text-yellow-600',
        value: props.tierInfo.current_tier?.name || 'No Tier',
    },
];
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="p-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg mr-3">
                        <GiftIcon class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Reward System</h3>
                        <p class="text-sm text-gray-500">Track your earnings and network growth</p>
                    </div>
                </div>
                <Link 
                    :href="route('referrals.index')" 
                    class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center"
                >
                    View All
                    <ArrowRightIcon class="h-4 w-4 ml-1" />
                </Link>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-lg font-bold text-green-600">
                        {{ formatCurrency(earnings.referral_commissions) }}
                    </div>
                    <div class="text-xs text-green-700">Referral Earnings</div>
                </div>
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-lg font-bold text-blue-600">
                        {{ formatCurrency(earnings.matrix_commissions) }}
                    </div>
                    <div class="text-xs text-blue-700">Matrix Earnings</div>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                    <div class="text-lg font-bold text-purple-600">
                        {{ referralStats.total_referrals }}
                    </div>
                    <div class="text-xs text-purple-700">Total Referrals</div>
                </div>
                <div class="text-center p-3 bg-yellow-50 rounded-lg">
                    <div class="text-lg font-bold text-yellow-600">
                        {{ formatCurrency(earnings.pending_earnings) }}
                    </div>
                    <div class="text-xs text-yellow-700">Pending Earnings</div>
                </div>
            </div>

            <!-- Quick Access Links -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Link
                    v-for="link in rewardSystemLinks"
                    :key="link.title"
                    :href="route(link.href)"
                    class="group p-4 border border-gray-200 rounded-lg hover:border-primary-300 hover:shadow-md transition-all duration-200"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div :class="link.color" class="p-2 rounded-lg mr-3">
                                <component :is="link.icon" class="h-5 w-5" />
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 group-hover:text-primary-600 transition-colors">
                                    {{ link.title }}
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ link.description }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ link.value }}
                            </div>
                            <ArrowRightIcon class="h-4 w-4 text-gray-400 group-hover:text-primary-500 transition-colors ml-auto mt-1" />
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Matrix Level Breakdown -->
            <div v-if="matrixData.total_downline > 0" class="mt-6 pt-6 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Matrix Structure</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-lg font-bold text-blue-600">{{ matrixData.level_1 }}</div>
                        <div class="text-xs text-gray-500">Level 1</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-green-600">{{ matrixData.level_2 }}</div>
                        <div class="text-xs text-gray-500">Level 2</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-purple-600">{{ matrixData.level_3 }}</div>
                        <div class="text-xs text-gray-500">Level 3</div>
                    </div>
                </div>
            </div>

            <!-- Current Tier Info -->
            <div v-if="tierInfo.current_tier" class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Current Tier</h4>
                        <p class="text-xs text-gray-500">{{ tierInfo.current_tier.name }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-green-600">
                            {{ tierInfo.current_tier.fixed_profit_rate }}% Profit
                        </div>
                        <div class="text-xs text-blue-600">
                            {{ tierInfo.current_tier.direct_referral_rate }}% Commission
                        </div>
                    </div>
                </div>
                <div v-if="tierInfo.next_tier" class="mt-2">
                    <Link 
                        :href="route('tiers.compare')" 
                        class="text-xs text-primary-600 hover:text-primary-700 font-medium"
                    >
                        Upgrade to {{ tierInfo.next_tier.name }} →
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>