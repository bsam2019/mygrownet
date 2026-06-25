<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    BuildingStorefrontIcon,
    DocumentTextIcon,
    MegaphoneIcon,
    SparklesIcon,
    BanknotesIcon,
    WalletIcon,
    ArrowTrendingUpIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

interface StatCard {
    label: string;
    value: string | number;
    icon: object;
    color: string;
}

interface TopBusiness {
    id: number;
    name: string;
    industry: string;
    user_name: string;
    is_active: boolean;
    onboarding_completed: boolean;
    posts_count: number;
    campaigns_count: number;
    sales_count: number;
    created_at: string;
}

interface Activity {
    type: string;
    description: string;
    user: string;
    time: string;
}

interface Props {
    stats: {
        total_businesses: number;
        active_businesses: number;
        onboarded_businesses: number;
        posts_this_month: number;
        campaigns_this_month: number;
        ai_credits_used_total: number;
        ai_credits_used_this_month: number;
        revenue_total: number;
        revenue_this_month: number;
        total_wallet_balance: number;
        total_locked_balance: number;
        ad_campaigns_total: number;
        ad_campaigns_active: number;
        ad_spend_total: number;
    };
    topBusinesses: TopBusiness[];
    recentActivity: Activity[];
}

defineProps<Props>();
</script>

<template>
    <Head title="BizBoost Dashboard - Admin" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">BizBoost Dashboard</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Overview of BizBoost module activity</p>
                </div>

                <!-- Stat Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-violet-100 dark:bg-violet-900/30 rounded-lg ring-1 ring-violet-200/50 dark:ring-violet-700/30">
                                <BuildingStorefrontIcon class="h-5 w-5 text-violet-600 dark:text-violet-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Businesses</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.total_businesses }}</p>
                                <p class="text-xs text-gray-400">{{ stats.active_businesses }} active / {{ stats.onboarded_businesses }} onboarded</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-blue-100 dark:bg-blue-900/30 rounded-lg ring-1 ring-blue-200/50 dark:ring-blue-700/30">
                                <DocumentTextIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Posts this month</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.posts_this_month }}</p>
                                <p class="text-xs text-gray-400">{{ stats.campaigns_this_month }} campaigns</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-amber-100 dark:bg-amber-900/30 rounded-lg ring-1 ring-amber-200/50 dark:ring-amber-700/30">
                                <SparklesIcon class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">AI Credits</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.ai_credits_used_total }}</p>
                                <p class="text-xs text-gray-400">{{ stats.ai_credits_used_this_month }} this month</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-green-100 dark:bg-green-900/30 rounded-lg ring-1 ring-green-200/50 dark:ring-green-700/30">
                                <BanknotesIcon class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Revenue</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">${{ stats.revenue_total }}</p>
                                <p class="text-xs text-gray-400">${{ stats.revenue_this_month }} this month</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second row: wallet + ad campaigns -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-teal-100 dark:bg-teal-900/30 rounded-lg ring-1 ring-teal-200/50 dark:ring-teal-700/30">
                                <WalletIcon class="h-5 w-5 text-teal-600 dark:text-teal-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Wallet</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">${{ stats.total_wallet_balance }}</p>
                                <p class="text-xs text-gray-400">${{ stats.total_locked_balance }} locked</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-rose-100 dark:bg-rose-900/30 rounded-lg ring-1 ring-rose-200/50 dark:ring-rose-700/30">
                                <MegaphoneIcon class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Ad Campaigns</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.ad_campaigns_total }}</p>
                                <p class="text-xs text-gray-400">{{ stats.ad_campaigns_active }} active</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-5">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg ring-1 ring-indigo-200/50 dark:ring-indigo-700/30">
                                <ArrowTrendingUpIcon class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Ad Spend</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">${{ stats.ad_spend_total }}</p>
                                <p class="text-xs text-gray-400">Total client budget</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Businesses -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Top Businesses</h2>
                            <Link :href="route('admin.bizboost.businesses.index')" class="text-sm text-violet-600 hover:text-violet-700 dark:text-violet-400">
                                View All →
                            </Link>
                        </div>
                        <div v-if="topBusinesses.length === 0" class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No businesses yet.</div>
                        <div v-else class="space-y-3">
                            <div v-for="b in topBusinesses" :key="b.id" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="flex-shrink-0 h-9 w-9 rounded-lg bg-gradient-to-br from-violet-100 to-fuchsia-100 dark:from-violet-900/40 dark:to-fuchsia-900/20 flex items-center justify-center text-sm font-bold text-violet-600 dark:text-violet-400">
                                        {{ b.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="min-w-0">
                                        <Link :href="route('admin.bizboost.businesses.show', b.id)" class="text-sm font-medium text-gray-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400 truncate block">
                                            {{ b.name }}
                                        </Link>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ b.industry || 'N/A' }} · {{ b.user_name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 flex-shrink-0">
                                    <span title="Posts">{{ b.posts_count }} posts</span>
                                    <span title="Campaigns">{{ b.campaigns_count }} campaigns</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h2>
                        <div v-if="recentActivity.length === 0" class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">No recent activity.</div>
                        <div v-else class="space-y-3">
                            <div v-for="(activity, i) in recentActivity" :key="i" class="flex items-start gap-3 p-2">
                                <div class="p-1.5 rounded-full bg-gray-100 dark:bg-gray-700">
                                    <component :is="activity.type === 'billing' ? BanknotesIcon : BuildingStorefrontIcon" class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ activity.description }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ activity.user }}</p>
                                </div>
                                <span class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0">{{ activity.time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
