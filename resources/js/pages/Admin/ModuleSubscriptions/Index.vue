<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { 
    CubeIcon, 
    UserGroupIcon, 
    CurrencyDollarIcon,
    TagIcon,
    GiftIcon,
    ChevronRightIcon,
    CheckCircleIcon,
    XCircleIcon
} from '@heroicons/vue/24/outline';

interface Module {
    id: string;
    name: string;
    category: string;
    icon: string;
    color: string;
    subscribers: number;
    revenue: number;
    tiers_in_db: number;
    requires_subscription: boolean;
}

interface Stats {
    total_subscribers: number;
    total_revenue: number;
    active_discounts: number;
    active_offers: number;
}

const props = defineProps<{
    modules: Module[];
    stats: Stats;
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getCategoryColor = (category: string) => {
    const colors: Record<string, string> = {
        core: 'bg-blue-100 text-blue-800',
        personal: 'bg-green-100 text-green-800',
        sme: 'bg-purple-100 text-purple-800',
        enterprise: 'bg-indigo-100 text-indigo-800',
    };
    return colors[category] || 'bg-gray-100 text-gray-800';
};

const getModuleColor = (color: string) => {
    const colors: Record<string, string> = {
        blue: 'bg-blue-500',
        green: 'bg-green-500',
        purple: 'bg-purple-500',
        pink: 'bg-pink-500',
        emerald: 'bg-emerald-500',
        indigo: 'bg-indigo-500',
    };
    return colors[color] || 'bg-gray-500';
};
</script>

<template>
    <AdminLayout>
        <Head title="Module Subscription Management" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Module Subscription Management</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage subscription tiers, pricing, and features for all apps
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="route('admin.module-subscriptions.discounts')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <TagIcon class="h-5 w-5" aria-hidden="true" />
                        Discounts
                    </Link>
                    <Link
                        :href="route('admin.module-subscriptions.offers')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <GiftIcon class="h-5 w-5" aria-hidden="true" />
                        Special Offers
                    </Link>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <UserGroupIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Subscribers</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_subscribers }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <CurrencyDollarIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_revenue) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-lg">
                            <TagIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Active Discounts</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.active_discounts }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <GiftIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Active Offers</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.active_offers }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modules Grid -->
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Apps & Modules</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    <Link
                        v-for="module in modules"
                        :key="module.id"
                        :href="route('admin.module-subscriptions.show', module.id)"
                        class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center gap-4">
                            <div :class="[getModuleColor(module.color), 'p-3 rounded-xl']">
                                <CubeIcon class="h-6 w-6 text-white" aria-hidden="true" />
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">{{ module.name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span :class="[getCategoryColor(module.category), 'px-2 py-0.5 rounded text-xs font-medium']">
                                        {{ module.category }}
                                    </span>
                                    <span v-if="module.tiers_in_db > 0" class="flex items-center gap-1 text-xs text-green-600">
                                        <CheckCircleIcon class="h-4 w-4" aria-hidden="true" />
                                        {{ module.tiers_in_db }} tiers in DB
                                    </span>
                                    <span v-else class="flex items-center gap-1 text-xs text-gray-400">
                                        <XCircleIcon class="h-4 w-4" aria-hidden="true" />
                                        Config only
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-8">
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Subscribers</p>
                                <p class="font-semibold text-gray-900">{{ module.subscribers }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Revenue</p>
                                <p class="font-semibold text-gray-900">{{ formatCurrency(module.revenue) }}</p>
                            </div>
                            <ChevronRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
