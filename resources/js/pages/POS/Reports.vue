<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import POSLayout from '@/layouts/POSLayout.vue';
import {
    ChartBarIcon,
    BanknotesIcon,
    DocumentTextIcon,
    CalendarIcon,
    ArrowTrendingUpIcon,
} from '@heroicons/vue/24/outline';

interface DailyBreakdown {
    date: string;
    day: string;
    total: number;
    count: number;
}

interface Props {
    todayReport: {
        date: string;
        total_sales: number;
        transaction_count: number;
        average_sale: number;
        cash_sales: number;
        mobile_sales: number;
        card_sales: number;
        items_sold: number;
    };
    weeklyStats: {
        total_sales: number;
        transaction_count: number;
        average_sale: number;
        items_sold: number;
        daily_breakdown: DailyBreakdown[];
        payment_breakdown: {
            cash: number;
            mobile_money: number;
            card: number;
        };
    };
}

const props = withDefaults(defineProps<Props>(), {
    todayReport: () => ({
        date: '',
        total_sales: 0,
        transaction_count: 0,
        average_sale: 0,
        cash_sales: 0,
        mobile_sales: 0,
        card_sales: 0,
        items_sold: 0,
    }),
    weeklyStats: () => ({
        total_sales: 0,
        transaction_count: 0,
        average_sale: 0,
        items_sold: 0,
        daily_breakdown: [],
        payment_breakdown: { cash: 0, mobile_money: 0, card: 0 },
    }),
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getMaxDailySales = () => {
    if (!props.weeklyStats.daily_breakdown?.length) return 1;
    return Math.max(...props.weeklyStats.daily_breakdown.map((d) => d.total), 1);
};
</script>

<template>
    <POSLayout title="Reports">
        <Head title="POS Reports" />

        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>
                        <p class="mt-1 text-sm text-gray-500">View sales performance and trends</p>
                    </div>
                    <Link
                        :href="route('pos.reports.daily')"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        <CalendarIcon class="h-5 w-5" aria-hidden="true" />
                        Daily Report
                    </Link>
                </div>

                <!-- Today's Summary -->
                <div class="mb-6 rounded-xl bg-gradient-to-r from-purple-600 to-purple-700 p-6 text-white shadow-lg">
                    <h2 class="mb-4 text-lg font-semibold">Today's Summary</h2>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <p class="text-sm text-purple-200">Total Sales</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(todayReport.total_sales) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-purple-200">Transactions</p>
                            <p class="text-2xl font-bold">{{ todayReport.transaction_count }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-purple-200">Average Sale</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(todayReport.average_sale) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-purple-200">Items Sold</p>
                            <p class="text-2xl font-bold">{{ todayReport.items_sold }}</p>
                        </div>
                    </div>
                </div>

                <!-- Weekly Stats -->
                <div class="mb-6 grid gap-6 lg:grid-cols-2">
                    <!-- Weekly Overview -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">This Week</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-lg bg-gray-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-green-100 p-2">
                                        <BanknotesIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Total Sales</p>
                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ formatCurrency(weeklyStats.total_sales) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-blue-100 p-2">
                                        <DocumentTextIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Transactions</p>
                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ weeklyStats.transaction_count }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-purple-100 p-2">
                                        <ArrowTrendingUpIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Avg. Sale</p>
                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ formatCurrency(weeklyStats.average_sale) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-amber-100 p-2">
                                        <ChartBarIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Items Sold</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ weeklyStats.items_sold }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="rounded-xl bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Payment Methods (This Week)</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="mb-1 flex justify-between text-sm">
                                    <span class="text-gray-600">Cash</span>
                                    <span class="font-medium text-gray-900">
                                        {{ formatCurrency(weeklyStats.payment_breakdown?.cash || 0) }}
                                    </span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                                    <div
                                        class="h-full rounded-full bg-green-500"
                                        :style="{
                                            width: `${weeklyStats.total_sales > 0 ? ((weeklyStats.payment_breakdown?.cash || 0) / weeklyStats.total_sales) * 100 : 0}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-1 flex justify-between text-sm">
                                    <span class="text-gray-600">Mobile Money</span>
                                    <span class="font-medium text-gray-900">
                                        {{ formatCurrency(weeklyStats.payment_breakdown?.mobile_money || 0) }}
                                    </span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                                    <div
                                        class="h-full rounded-full bg-blue-500"
                                        :style="{
                                            width: `${weeklyStats.total_sales > 0 ? ((weeklyStats.payment_breakdown?.mobile_money || 0) / weeklyStats.total_sales) * 100 : 0}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-1 flex justify-between text-sm">
                                    <span class="text-gray-600">Card</span>
                                    <span class="font-medium text-gray-900">
                                        {{ formatCurrency(weeklyStats.payment_breakdown?.card || 0) }}
                                    </span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-200">
                                    <div
                                        class="h-full rounded-full bg-purple-500"
                                        :style="{
                                            width: `${weeklyStats.total_sales > 0 ? ((weeklyStats.payment_breakdown?.card || 0) / weeklyStats.total_sales) * 100 : 0}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Breakdown Chart -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Daily Sales (This Week)</h3>
                    <div
                        v-if="weeklyStats.daily_breakdown?.length"
                        class="flex h-48 items-end justify-between gap-2"
                    >
                        <div
                            v-for="day in weeklyStats.daily_breakdown"
                            :key="day.date"
                            class="flex flex-1 flex-col items-center"
                        >
                            <div class="mb-2 text-xs font-medium text-gray-900">
                                {{ formatCurrency(day.total) }}
                            </div>
                            <div
                                class="w-full rounded-t-lg bg-purple-500 transition-all hover:bg-purple-600"
                                :style="{
                                    height: `${(day.total / getMaxDailySales()) * 100}%`,
                                    minHeight: day.total > 0 ? '8px' : '2px',
                                }"
                            ></div>
                            <div class="mt-2 text-xs text-gray-500">{{ day.day }}</div>
                            <div class="text-xs text-gray-400">{{ day.count }} sales</div>
                        </div>
                    </div>
                    <div v-else class="flex h-48 items-center justify-center text-gray-500">
                        No sales data for this week
                    </div>
                </div>
            </div>
        </div>
    </POSLayout>
</template>
