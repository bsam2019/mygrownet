<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { 
    StarIcon, 
    GiftIcon, 
    TrendingUpIcon,
    ArrowRightIcon,
    InfoIcon
} from 'lucide-vue-next';

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'My Earnings', href: route('mygrownet.earnings.hub') }
];

// TODO: These will be passed from the controller with real data
const earningsData = {
    totalEarnings: 0,
    thisMonth: 0,
    pending: 0,
    lgrRewards: 0,
    commissions: 0,
    profitShares: 0
};
</script>

<template>
    <Head title="My Earnings" />

    <MemberLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Earnings</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Track and manage all your earnings, bonuses, and rewards
                    </p>
                </div>
            </div>

            <!-- Total Earnings Summary - Top Section -->
            <div class="grid gap-4 sm:grid-cols-3">
                <!-- Total Earnings -->
                <div class="relative overflow-hidden rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 p-5 text-white shadow-md">
                    <div class="relative z-10">
                        <p class="text-xs font-medium text-blue-100">Total Earnings</p>
                        <p class="mt-1 text-2xl font-bold">K {{ earningsData.totalEarnings.toFixed(2) }}</p>
                        <p class="mt-0.5 text-xs text-blue-100">All time</p>
                    </div>
                    <div class="absolute -right-3 -top-3 h-20 w-20 rounded-full bg-white/10"></div>
                </div>

                <!-- This Month -->
                <div class="relative overflow-hidden rounded-lg bg-gradient-to-br from-green-500 to-green-600 p-5 text-white shadow-md">
                    <div class="relative z-10">
                        <p class="text-xs font-medium text-green-100">This Month</p>
                        <p class="mt-1 text-2xl font-bold">K {{ earningsData.thisMonth.toFixed(2) }}</p>
                        <p class="mt-0.5 text-xs text-green-100">Current period</p>
                    </div>
                    <div class="absolute -right-3 -top-3 h-20 w-20 rounded-full bg-white/10"></div>
                </div>

                <!-- Pending -->
                <div class="relative overflow-hidden rounded-lg bg-gradient-to-br from-amber-500 to-amber-600 p-5 text-white shadow-md">
                    <div class="relative z-10">
                        <p class="text-xs font-medium text-amber-100">Pending</p>
                        <p class="mt-1 text-2xl font-bold">K {{ earningsData.pending.toFixed(2) }}</p>
                        <p class="mt-0.5 text-xs text-amber-100">Processing</p>
                    </div>
                    <div class="absolute -right-3 -top-3 h-20 w-20 rounded-full bg-white/10"></div>
                </div>
            </div>

            <!-- Earnings Breakdown Cards -->
            <div>
                <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Earnings Breakdown</h2>
                <div class="grid gap-4 lg:grid-cols-3">
                    <!-- Loyalty Growth Reward Card -->
                    <div class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-yellow-400 dark:hover:border-yellow-500">
                        <!-- Amount Section -->
                        <div class="border-b border-gray-100 dark:border-gray-700 bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/10 dark:to-amber-900/10 p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-500 shadow-md">
                                    <StarIcon class="h-5 w-5 text-white" />
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">LGR Rewards</p>
                                    <p class="mt-0.5 text-xl font-bold text-gray-900 dark:text-white">
                                        K {{ earningsData.lgrRewards.toFixed(2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Details Section -->
                        <div class="p-4">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                Loyalty Growth Reward
                            </h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                Track your LGR points, tier status, and monthly rewards from platform engagement
                            </p>
                            
                            <Link 
                                :href="route('mygrownet.loyalty-reward.index')"
                                class="inline-flex items-center text-xs font-medium text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 group-hover:translate-x-1 transition-all"
                            >
                                View LGR Dashboard
                                <ArrowRightIcon class="ml-1.5 h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>

                    <!-- Earnings & Bonuses Card -->
                    <div class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-green-400 dark:hover:border-green-500">
                        <!-- Amount Section -->
                        <div class="border-b border-gray-100 dark:border-gray-700 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/10 dark:to-emerald-900/10 p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-500 shadow-md">
                                    <GiftIcon class="h-5 w-5 text-white" />
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Commissions</p>
                                    <p class="mt-0.5 text-xl font-bold text-gray-900 dark:text-white">
                                        K {{ earningsData.commissions.toFixed(2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Details Section -->
                        <div class="p-4">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                Earnings & Bonuses
                            </h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                View your referral commissions, level bonuses, and milestone rewards
                            </p>
                            
                            <Link 
                                :href="route('mygrownet.earnings.index')"
                                class="inline-flex items-center text-xs font-medium text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 group-hover:translate-x-1 transition-all"
                            >
                                View Earnings Details
                                <ArrowRightIcon class="ml-1.5 h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>

                    <!-- Quarterly Profit Shares Card -->
                    <div class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-blue-400 dark:hover:border-blue-500">
                        <!-- Amount Section -->
                        <div class="border-b border-gray-100 dark:border-gray-700 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500 shadow-md">
                                    <TrendingUpIcon class="h-5 w-5 text-white" />
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Profit Shares</p>
                                    <p class="mt-0.5 text-xl font-bold text-gray-900 dark:text-white">
                                        K {{ earningsData.profitShares.toFixed(2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Details Section -->
                        <div class="p-4">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                Quarterly Profit Shares
                            </h3>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                View your profit-sharing distributions from community investment projects
                            </p>
                            
                            <Link 
                                :href="route('mygrownet.profit-shares')"
                                class="inline-flex items-center text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 group-hover:translate-x-1 transition-all"
                            >
                                View Profit Shares
                                <ArrowRightIcon class="ml-1.5 h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4">
                <div class="flex gap-3">
                    <InfoIcon class="h-4 w-4 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="text-xs font-medium text-blue-900 dark:text-blue-100 mb-0.5">
                            Need help understanding your earnings?
                        </p>
                        <p class="text-xs text-blue-800 dark:text-blue-200">
                            Each earnings section provides detailed breakdowns, transaction history, and performance insights. 
                            Click on any card above to explore your specific earnings type.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
