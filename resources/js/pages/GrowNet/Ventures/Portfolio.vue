<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BriefcaseIcon, ChartBarIcon, CurrencyDollarIcon, ArrowTrendingUpIcon } from '@heroicons/vue/24/outline';

interface Venture {
    id: number;
    title: string;
    slug: string;
    status: string;
    funding_target: number;
    total_raised: number;
}

interface Investment {
    id: number;
    amount: number;
    shares_allocated: number;
    status: string;
    created_at: string;
    venture: Venture;
}

interface Shareholder {
    id: number;
    total_investment: number;
    shares_owned: number;
    equity_percentage: number;
    status: string;
    total_dividends_received: number;
    investment: Investment;
    venture: Venture;
}

interface Props {
    investments: Investment[];
    shareholders: Shareholder[];
    totalInvested: number;
    totalShares: number;
    activeVentures: number;
    totalDividendsReceived: number;
    ventures: { total: number; active: number; funded: number };
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        confirmed: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        refunded: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="My Portfolio" />

    <AppLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">My Investment Portfolio</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Overview of your venture investments and shareholdings
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-blue-100 p-3">
                                <BriefcaseIcon class="h-6 w-6 text-blue-600" />
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Active Ventures</div>
                                <div class="text-2xl font-bold text-gray-900">{{ props.activeVentures }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-green-100 p-3">
                                <ChartBarIcon class="h-6 w-6 text-green-600" />
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Total Invested</div>
                                <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(props.totalInvested) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-indigo-100 p-3">
                                <ArrowTrendingUpIcon class="h-6 w-6 text-indigo-600" />
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Total Shares</div>
                                <div class="text-2xl font-bold text-gray-900">{{ props.totalShares.toLocaleString() }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-amber-100 p-3">
                                <CurrencyDollarIcon class="h-6 w-6 text-amber-600" />
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Dividends Received</div>
                                <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(props.totalDividendsReceived) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Portfolio Summary -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Portfolio Summary</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-sm text-gray-600">Total Ventures</div>
                            <div class="mt-1 text-2xl font-bold text-gray-900">{{ props.ventures.total }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-sm text-gray-600">Active Investments</div>
                            <div class="mt-1 text-2xl font-bold text-green-600">{{ props.ventures.active }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-sm text-gray-600">Funded Ventures</div>
                            <div class="mt-1 text-2xl font-bold text-blue-600">{{ props.ventures.funded }}</div>
                        </div>
                    </div>
                </div>

                <!-- Investments List -->
                <div class="mb-6 rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">Your Investments</h2>
                    </div>

                    <div v-if="investments.length === 0" class="px-6 py-12 text-center">
                        <BriefcaseIcon class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No investments yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Start building your portfolio today.</p>
                        <div class="mt-6">
                            <Link
                                :href="route('ventures.index')"
                                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                            >
                                Browse Ventures
                            </Link>
                        </div>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div
                            v-for="investment in investments"
                            :key="investment.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <Link
                                        :href="route('ventures.show', investment.venture.slug)"
                                        class="text-lg font-semibold text-gray-900 hover:text-blue-600"
                                    >
                                        {{ investment.venture.title }}
                                    </Link>
                                    <div class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                        <div>
                                            <div class="text-xs text-gray-500">Amount</div>
                                            <div class="font-semibold text-gray-900">{{ formatCurrency(investment.amount) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Shares</div>
                                            <div class="font-semibold text-gray-900">{{ investment.shares_allocated?.toLocaleString() || 0 }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Venture Status</div>
                                            <div class="font-semibold text-gray-900">{{ investment.venture.status }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Date</div>
                                            <div class="font-semibold text-gray-900">{{ new Date(investment.created_at).toLocaleDateString() }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span :class="['inline-flex items-center rounded-full px-3 py-1 text-xs font-medium', getStatusColor(investment.status)]">
                                        {{ investment.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shareholdings -->
                <div v-if="shareholders.length > 0" class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">Registered Shareholdings</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="sh in shareholders"
                            :key="sh.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <Link
                                        :href="route('ventures.show', sh.venture.slug)"
                                        class="text-lg font-semibold text-gray-900 hover:text-blue-600"
                                    >
                                        {{ sh.venture.title }}
                                    </Link>
                                    <div class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                        <div>
                                            <div class="text-xs text-gray-500">Total Investment</div>
                                            <div class="font-semibold text-gray-900">{{ formatCurrency(sh.total_investment) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Shares Owned</div>
                                            <div class="font-semibold text-gray-900">{{ sh.shares_owned.toLocaleString() }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Equity</div>
                                            <div class="font-semibold text-gray-900">{{ sh.equity_percentage.toFixed(2) }}%</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Dividends Received</div>
                                            <div class="font-semibold text-green-600">{{ formatCurrency(sh.total_dividends_received) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                    {{ sh.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
