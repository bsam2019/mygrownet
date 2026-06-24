<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { CurrencyDollarIcon, CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Dividend {
    id: number;
    dividend_period: string;
    amount: number;
    status: string;
    declaration_date: string;
    payment_date: string | null;
    paid_at: string | null;
    payment_reference: string | null;
    venture: {
        id: number;
        title: string;
        slug: string;
    };
    shareholder: {
        certificate_number: string;
        equity_percentage: number;
    };
}

interface Props {
    dividends: Dividend[];
    totalEarned: number;
    totalPaid: number;
    totalPending: number;
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        declared: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-indigo-100 text-indigo-800',
        paid: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusIcon = (status: string) => {
    if (status === 'paid') return CheckCircleIcon;
    return ClockIcon;
};

const groupedDividends = props.dividends.reduce((groups, dividend) => {
    const period = dividend.dividend_period;
    if (!groups[period]) {
        groups[period] = { period, dividends: [], total: 0 };
    }
    groups[period].dividends.push(dividend);
    groups[period].total += dividend.amount;
    return groups;
}, {} as Record<string, { period: string; dividends: Dividend[]; total: number }>);
</script>

<template>
    <Head title="My Dividends" />

    <AppLayout>
        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">My Dividend History</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Track your dividend earnings across all ventures
                    </p>
                </div>

                <!-- Summary Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-green-100 p-3">
                                <CurrencyDollarIcon class="h-6 w-6 text-green-600" />
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Total Earned</div>
                                <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(props.totalEarned) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-green-100 p-3">
                                <CheckCircleIcon class="h-6 w-6 text-green-600" />
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Paid</div>
                                <div class="text-2xl font-bold text-green-600">{{ formatCurrency(props.totalPaid) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-yellow-100 p-3">
                                <ClockIcon class="h-6 w-6 text-yellow-600" />
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Pending</div>
                                <div class="text-2xl font-bold text-yellow-600">{{ formatCurrency(props.totalPending) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dividends List -->
                <div v-if="dividends.length === 0" class="rounded-lg bg-white p-12 text-center shadow">
                    <CurrencyDollarIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No dividends yet</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Dividends will appear here when ventures start distributing profits.
                    </p>
                </div>

                <div v-else class="space-y-6">
                    <div
                        v-for="(group, period) in groupedDividends"
                        :key="period"
                        class="rounded-lg bg-white shadow"
                    >
                        <div class="border-b border-gray-200 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">{{ period }}</h2>
                                <span class="text-lg font-bold text-green-600">{{ formatCurrency(group.total) }}</span>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-200">
                            <div
                                v-for="dividend in group.dividends"
                                :key="dividend.id"
                                class="px-6 py-4 hover:bg-gray-50 transition-colors"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <Link
                                            :href="route('ventures.show', dividend.venture.slug)"
                                            class="font-semibold text-gray-900 hover:text-blue-600"
                                        >
                                            {{ dividend.venture.title }}
                                        </Link>
                                        <div class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
                                            <div>
                                                <div class="text-xs text-gray-500">Amount</div>
                                                <div class="font-semibold text-gray-900">{{ formatCurrency(dividend.amount) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-500">Declaration Date</div>
                                                <div class="text-sm text-gray-700">{{ formatDate(dividend.declaration_date) }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-500">Payment Date</div>
                                                <div class="text-sm text-gray-700">{{ formatDate(dividend.paid_at || dividend.payment_date) }}</div>
                                            </div>
                                        </div>
                                        <div v-if="dividend.payment_reference" class="mt-2 text-xs text-gray-500">
                                            Ref: {{ dividend.payment_reference }}
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <span :class="['inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium', getStatusColor(dividend.status)]">
                                            <component :is="getStatusIcon(dividend.status)" class="h-3 w-3" />
                                            {{ dividend.status }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ dividend.shareholder.equity_percentage.toFixed(2) }}% equity
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
