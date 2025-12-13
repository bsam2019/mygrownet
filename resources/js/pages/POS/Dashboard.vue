<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import POSLayout from '@/layouts/POSLayout.vue';
import {
    ComputerDesktopIcon,
    ClockIcon,
    BanknotesIcon,
    ChartBarIcon,
    Cog6ToothIcon,
    PlayIcon,
    DocumentTextIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    stats: {
        today_sales: number;
        today_transactions: number;
        active_shift: boolean;
        weekly_sales: number;
    };
    activeShift: {
        id: number;
        shift_number: string;
        started_at: string;
        total_sales: number;
        transaction_count: number;
    } | null;
    recentSales: Array<{
        id: number;
        sale_number: string;
        total_amount: number;
        payment_method: string;
        created_at: string;
    }>;
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        today_sales: 0,
        today_transactions: 0,
        active_shift: false,
        weekly_sales: 0,
    }),
    activeShift: null,
    recentSales: () => [],
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString('en-ZM', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <POSLayout title="Dashboard">
        <Head title="POS Dashboard" />

        <div class="min-h-screen bg-gray-50 py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Point of Sale</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage sales, shifts, and daily operations</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('pos.terminal')"
                            class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700"
                        >
                            <ComputerDesktopIcon class="h-5 w-5" aria-hidden="true" />
                            Open Terminal
                        </Link>
                        <Link
                            :href="route('pos.settings')"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            <Cog6ToothIcon class="h-5 w-5" aria-hidden="true" />
                            Settings
                        </Link>
                    </div>
                </div>

                <!-- Active Shift Banner -->
                <div v-if="activeShift" class="mb-6 rounded-xl bg-gradient-to-r from-purple-600 to-purple-700 p-4 text-white shadow-lg">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-white/20 p-2">
                                <PlayIcon class="h-6 w-6" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-sm text-purple-200">Active Shift</p>
                                <p class="text-lg font-semibold">{{ activeShift.shift_number }}</p>
                            </div>
                        </div>
                        <div class="flex gap-6">
                            <div class="text-center">
                                <p class="text-2xl font-bold">{{ formatCurrency(activeShift.total_sales) }}</p>
                                <p class="text-xs text-purple-200">Total Sales</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold">{{ activeShift.transaction_count }}</p>
                                <p class="text-xs text-purple-200">Transactions</p>
                            </div>
                        </div>
                        <Link
                            :href="route('pos.terminal')"
                            class="rounded-lg bg-white px-4 py-2 text-center text-sm font-medium text-purple-600 hover:bg-purple-50"
                        >
                            Continue Selling
                        </Link>
                    </div>
                </div>

                <!-- No Active Shift -->
                <div v-else class="mb-6 rounded-xl border-2 border-dashed border-gray-300 bg-white p-6 text-center">
                    <ClockIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-lg font-medium text-gray-900">No Active Shift</h3>
                    <p class="mt-1 text-sm text-gray-500">Start a shift to begin recording sales</p>
                    <Link
                        :href="route('pos.shifts.start')"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700"
                    >
                        <PlayIcon class="h-5 w-5" aria-hidden="true" />
                        Start Shift
                    </Link>
                </div>

                <!-- Stats Grid -->
                <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-green-100 p-2">
                                <BanknotesIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Today's Sales</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(stats.today_sales) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-blue-100 p-2">
                                <DocumentTextIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Transactions</p>
                                <p class="text-lg font-semibold text-gray-900">{{ stats.today_transactions }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-purple-100 p-2">
                                <ChartBarIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Weekly Sales</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(stats.weekly_sales) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div :class="stats.active_shift ? 'bg-green-100' : 'bg-gray-100'" class="rounded-lg p-2">
                                <ClockIcon :class="stats.active_shift ? 'text-green-600' : 'text-gray-400'" class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Shift Status</p>
                                <p :class="stats.active_shift ? 'text-green-600' : 'text-gray-500'" class="text-lg font-semibold">
                                    {{ stats.active_shift ? 'Active' : 'Closed' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <Link :href="route('pos.terminal')" class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md">
                        <div class="rounded-lg bg-purple-100 p-3">
                            <ComputerDesktopIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Terminal</p>
                            <p class="text-xs text-gray-500">Make sales</p>
                        </div>
                    </Link>

                    <Link :href="route('pos.shifts')" class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md">
                        <div class="rounded-lg bg-blue-100 p-3">
                            <ClockIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Shifts</p>
                            <p class="text-xs text-gray-500">Manage shifts</p>
                        </div>
                    </Link>

                    <Link :href="route('pos.sales')" class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md">
                        <div class="rounded-lg bg-green-100 p-3">
                            <BanknotesIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Sales</p>
                            <p class="text-xs text-gray-500">View history</p>
                        </div>
                    </Link>

                    <Link :href="route('pos.reports')" class="flex items-center gap-3 rounded-xl bg-white p-4 shadow-sm transition hover:shadow-md">
                        <div class="rounded-lg bg-amber-100 p-3">
                            <ChartBarIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Reports</p>
                            <p class="text-xs text-gray-500">Analytics</p>
                        </div>
                    </Link>
                </div>

                <!-- Recent Sales -->
                <div class="rounded-xl bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Recent Sales</h2>
                        <Link :href="route('pos.sales')" class="text-sm text-purple-600 hover:text-purple-700">
                            View all
                        </Link>
                    </div>

                    <div v-if="recentSales.length === 0" class="py-8 text-center text-gray-500">
                        <BanknotesIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                        <p class="mt-2">No sales recorded today</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="pb-3 text-left text-xs font-medium uppercase text-gray-500">Sale #</th>
                                    <th class="pb-3 text-left text-xs font-medium uppercase text-gray-500">Time</th>
                                    <th class="pb-3 text-left text-xs font-medium uppercase text-gray-500">Payment</th>
                                    <th class="pb-3 text-right text-xs font-medium uppercase text-gray-500">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="sale in recentSales.slice(0, 10)" :key="sale.id" class="hover:bg-gray-50">
                                    <td class="py-3 text-sm font-medium text-gray-900">{{ sale.sale_number }}</td>
                                    <td class="py-3 text-sm text-gray-500">{{ formatTime(sale.created_at) }}</td>
                                    <td class="py-3">
                                        <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium capitalize text-gray-700">
                                            {{ sale.payment_method.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right text-sm font-semibold text-gray-900">
                                        {{ formatCurrency(sale.total_amount) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </POSLayout>
</template>
