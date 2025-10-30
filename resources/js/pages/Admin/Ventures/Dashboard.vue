<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { BriefcaseIcon, UsersIcon, CurrencyDollarIcon, ArrowTrendingUpIcon } from '@heroicons/vue/24/outline';

interface Stats {
    total_ventures: number;
    active_ventures: number;
    total_raised: number;
    total_investors: number;
    pending_investments: number;
}

interface Venture {
    id: number;
    title: string;
    status: string;
    total_raised: number;
    funding_target: number;
    investor_count: number;
    created_at: string;
    category: {
        name: string;
    };
}

interface Investment {
    id: number;
    amount: number;
    status: string;
    created_at: string;
    user: {
        name: string;
        email: string;
    };
    venture: {
        title: string;
    };
}

defineProps<{
    stats: Stats;
    recentVentures: Venture[];
    recentInvestments: Investment[];
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800',
        review: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        funding: 'bg-green-100 text-green-800',
        funded: 'bg-indigo-100 text-indigo-800',
        active: 'bg-emerald-100 text-emerald-800',
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-green-100 text-green-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Venture Builder Dashboard" />

    <AdminLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Venture Builder</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage business ventures and member investments
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Ventures -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <BriefcaseIcon class="h-6 w-6 text-blue-600" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="truncate text-sm font-medium text-gray-500">
                                            Total Ventures
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ stats.total_ventures }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Ventures -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ArrowTrendingUpIcon class="h-6 w-6 text-green-600" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="truncate text-sm font-medium text-gray-500">
                                            Active Ventures
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ stats.active_ventures }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Raised -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <CurrencyDollarIcon class="h-6 w-6 text-emerald-600" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="truncate text-sm font-medium text-gray-500">
                                            Total Raised
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ formatCurrency(stats.total_raised) }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Investors -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <UsersIcon class="h-6 w-6 text-indigo-600" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="truncate text-sm font-medium text-gray-500">
                                            Total Investors
                                        </dt>
                                        <dd class="text-2xl font-semibold text-gray-900">
                                            {{ stats.total_investors }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-8 flex gap-4">
                    <Link
                        :href="route('admin.ventures.create')"
                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500"
                    >
                        Create New Venture
                    </Link>
                    <Link
                        :href="route('admin.ventures.index')"
                        class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    >
                        View All Ventures
                    </Link>
                </div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Recent Ventures -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="border-b border-gray-200 bg-white px-6 py-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Ventures</h3>
                        </div>
                        <ul role="list" class="divide-y divide-gray-200">
                            <li
                                v-for="venture in recentVentures"
                                :key="venture.id"
                                class="px-6 py-4 hover:bg-gray-50"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0 flex-1">
                                        <Link
                                            :href="route('admin.ventures.edit', venture.id)"
                                            class="text-sm font-medium text-blue-600 hover:text-blue-500"
                                        >
                                            {{ venture.title }}
                                        </Link>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ venture.category.name }} • {{ formatDate(venture.created_at) }}
                                        </p>
                                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                            <span>{{ formatCurrency(venture.total_raised) }} / {{ formatCurrency(venture.funding_target) }}</span>
                                            <span>{{ venture.investor_count }} investors</span>
                                        </div>
                                    </div>
                                    <span
                                        :class="[
                                            'ml-4 inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                            getStatusColor(venture.status),
                                        ]"
                                    >
                                        {{ venture.status }}
                                    </span>
                                </div>
                            </li>
                            <li v-if="recentVentures.length === 0" class="px-6 py-8 text-center text-sm text-gray-500">
                                No ventures yet
                            </li>
                        </ul>
                    </div>

                    <!-- Recent Investments -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="border-b border-gray-200 bg-white px-6 py-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Investments</h3>
                        </div>
                        <ul role="list" class="divide-y divide-gray-200">
                            <li
                                v-for="investment in recentInvestments"
                                :key="investment.id"
                                class="px-6 py-4 hover:bg-gray-50"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ investment.user.name }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ investment.venture.title }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ formatDate(investment.created_at) }}
                                        </p>
                                    </div>
                                    <div class="ml-4 flex flex-col items-end">
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ formatCurrency(investment.amount) }}
                                        </span>
                                        <span
                                            :class="[
                                                'mt-1 inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                                getStatusColor(investment.status),
                                            ]"
                                        >
                                            {{ investment.status }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                            <li v-if="recentInvestments.length === 0" class="px-6 py-8 text-center text-sm text-gray-500">
                                No investments yet
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
