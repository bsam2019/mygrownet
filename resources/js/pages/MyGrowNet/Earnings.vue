<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { GiftIcon, TrendingUpIcon, UsersIcon, StarIcon, CoinsIcon } from 'lucide-vue-next';
import { computed } from 'vue';

interface MonthlyEarning {
    month: string;
    amount: number;
}

const props = defineProps<{
    earningsByType: {
        referral_bonuses: number;
        level_commissions: number;
        profit_sharing: number;
        milestone_rewards: number;
    };
    totalEarnings: number;
    monthlyEarnings: MonthlyEarning[];
    currentMonthBP: number;
    currentMonthEarnings: number;
    lifetimePoints: number;
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const earningStreams = computed(() => [
    {
        title: 'Referral Bonuses',
        amount: props.earningsByType.referral_bonuses,
        icon: UsersIcon,
        color: 'bg-blue-100 text-blue-600',
        description: 'Direct referral commissions',
    },
    {
        title: 'Level Commissions',
        amount: props.earningsByType.level_commissions,
        icon: TrendingUpIcon,
        color: 'bg-green-100 text-green-600',
        description: '7-level network earnings',
    },
    {
        title: 'Profit Sharing',
        amount: props.earningsByType.profit_sharing,
        icon: CoinsIcon,
        color: 'bg-purple-100 text-purple-600',
        description: 'Monthly BP-based distribution',
    },
    {
        title: 'Milestone Rewards',
        amount: props.earningsByType.milestone_rewards,
        icon: StarIcon,
        color: 'bg-amber-100 text-amber-600',
        description: 'Achievement bonuses',
    },
]);

const maxMonthlyAmount = computed(() => 
    Math.max(...props.monthlyEarnings.map(m => m.amount), 1)
);
</script>

<template>
    <MemberLayout>
        <Head title="Earnings & Bonuses" />

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Earnings & Bonuses</h1>
                <p class="mt-2 text-sm text-gray-600">Track your income from all streams</p>
            </div>

            <!-- Current Month Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <p class="text-blue-100 text-sm">Total Lifetime Earnings</p>
                    <p class="text-3xl font-bold mt-2">{{ formatCurrency(totalEarnings) }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm">Current Month Earnings</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ formatCurrency(currentMonthEarnings) }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-600 text-sm">Current Month BP</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ currentMonthBP.toLocaleString() }}</p>
                    <p class="text-xs text-gray-500 mt-1">Lifetime: {{ lifetimePoints.toLocaleString() }} LP</p>
                </div>
            </div>

            <!-- Earning Streams -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Income Streams</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="stream in earningStreams"
                        :key="stream.title"
                        class="bg-white rounded-lg shadow p-6"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div :class="[stream.color, 'p-3 rounded-lg']">
                                <component :is="stream.icon" class="h-6 w-6" />
                            </div>
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ stream.title }}</h3>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ formatCurrency(stream.amount) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ stream.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Monthly Earnings Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Monthly Earnings ({{ new Date().getFullYear() }})</h2>
                <div class="space-y-3">
                    <div
                        v-for="month in monthlyEarnings"
                        :key="month.month"
                        class="flex items-center gap-3"
                    >
                        <div class="w-12 text-sm font-medium text-gray-600">{{ month.month }}</div>
                        <div class="flex-1 bg-gray-100 rounded-full h-8 relative overflow-hidden">
                            <div
                                class="bg-gradient-to-r from-blue-500 to-blue-600 h-full rounded-full transition-all duration-500 flex items-center justify-end pr-3"
                                :style="{ width: `${(month.amount / maxMonthlyAmount) * 100}%` }"
                            >
                                <span v-if="month.amount > 0" class="text-xs font-medium text-white">
                                    {{ formatCurrency(month.amount) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex gap-3">
                    <GiftIcon class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
                    <div>
                        <h3 class="font-semibold text-blue-900">How Earnings Work</h3>
                        <p class="text-sm text-blue-800 mt-1">
                            Your monthly earnings are calculated based on your Bonus Points (BP). 
                            The more BP you earn through referrals, purchases, and activities, 
                            the larger your share of the monthly profit distribution.
                        </p>
                        <p class="text-sm text-blue-800 mt-2">
                            <strong>Formula:</strong> Your Bonus = (Your BP / Total BP) × 60% of monthly profit
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </MemberLayout>
</template>
