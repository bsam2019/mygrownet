<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { 
    StarIcon, 
    GiftIcon, 
    TrendingUpIcon,
    ArrowRightIcon 
} from 'lucide-vue-next';

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'My Earnings', href: route('mygrownet.earnings.hub') }
];
</script>

<template>
    <Head title="My Earnings" />

    <MemberLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Page Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Earnings</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    View and manage all your earnings, bonuses, and rewards
                </p>
            </div>

            <!-- Earnings Cards Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Loyalty Growth Reward Card -->
                <Link 
                    :href="route('mygrownet.loyalty-reward.index')"
                    class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 hover:shadow-lg transition-all duration-200 hover:border-blue-500 dark:hover:border-blue-400"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900/20">
                                    <StarIcon class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Loyalty Growth Reward
                                </h3>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Track your LGR points, tier status, and monthly rewards from platform engagement
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:translate-x-1 transition-transform">
                        View LGR Dashboard
                        <ArrowRightIcon class="ml-2 h-4 w-4" />
                    </div>
                </Link>

                <!-- Earnings & Bonuses Card -->
                <Link 
                    :href="route('mygrownet.earnings.index')"
                    class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 hover:shadow-lg transition-all duration-200 hover:border-blue-500 dark:hover:border-blue-400"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/20">
                                    <GiftIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Earnings & Bonuses
                                </h3>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                View your referral commissions, level bonuses, and milestone rewards
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:translate-x-1 transition-transform">
                        View Earnings
                        <ArrowRightIcon class="ml-2 h-4 w-4" />
                    </div>
                </Link>

                <!-- Quarterly Profit Shares Card -->
                <Link 
                    :href="route('mygrownet.profit-shares')"
                    class="group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 hover:shadow-lg transition-all duration-200 hover:border-blue-500 dark:hover:border-blue-400"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/20">
                                    <TrendingUpIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Quarterly Profit Shares
                                </h3>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                View your profit-sharing distributions from community investment projects
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:translate-x-1 transition-transform">
                        View Profit Shares
                        <ArrowRightIcon class="ml-2 h-4 w-4" />
                    </div>
                </Link>
            </div>

            <!-- Quick Stats Summary (Optional) -->
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Quick Overview
                </h3>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Earnings</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">K 0.00</p>
                        <p class="mt-1 text-xs text-gray-500">All time</p>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <p class="text-sm text-gray-600 dark:text-gray-400">This Month</p>
                        <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">K 0.00</p>
                        <p class="mt-1 text-xs text-gray-500">Current period</p>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Pending</p>
                        <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">K 0.00</p>
                        <p class="mt-1 text-xs text-gray-500">Processing</p>
                    </div>
                </div>
            </div>

            <!-- Help Text -->
            <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4">
                <p class="text-sm text-blue-900 dark:text-blue-100">
                    <strong>Need help?</strong> Each earnings section provides detailed breakdowns and history. 
                    Click on any card above to explore your specific earnings type.
                </p>
            </div>
        </div>
    </MemberLayout>
</template>
</script>
