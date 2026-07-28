<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Anomaly Detection</h1>
                <p class="text-gray-500 text-sm">Suspicious patterns in financial data</p>
            </div>

            <!-- Date Filters -->
            <div class="flex gap-3 mb-6">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                    <input
                        type="date"
                        :value="filters.from"
                        @change="updateFilter('from', ($event.target as HTMLInputElement).value)"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                    <input
                        type="date"
                        :value="filters.to"
                        @change="updateFilter('to', ($event.target as HTMLInputElement).value)"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                </div>
                <div class="flex items-end">
                    <button
                        @click="refresh"
                        class="px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 transition-colors"
                    >
                        Scan
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <p class="text-xs text-gray-500">Total Anomalies</p>
                    <p class="text-2xl font-bold text-gray-900">{{ summary.total }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-red-500">
                    <p class="text-xs text-gray-500">High</p>
                    <p class="text-2xl font-bold text-red-600">{{ summary.high }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-amber-400">
                    <p class="text-xs text-gray-500">Medium</p>
                    <p class="text-2xl font-bold text-amber-600">{{ summary.medium }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-gray-400">
                    <p class="text-xs text-gray-500">Low</p>
                    <p class="text-2xl font-bold text-gray-600">{{ summary.low }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4">
                    <p class="text-xs text-gray-500">Period</p>
                    <p class="text-sm font-medium text-gray-900">{{ filters.from }} &mdash; {{ filters.to }}</p>
                </div>
            </div>

            <!-- Breakdown by Type -->
            <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-3 text-sm">Breakdown by Type</h3>
                <div class="space-y-2">
                    <div v-for="(count, type) in summary.by_type" :key="type" class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 capitalize">{{ type.replace(/_/g, ' ') }}</span>
                        <span class="text-sm font-medium" :class="count > 0 ? 'text-amber-600' : 'text-gray-400'">{{ count }}</span>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="anomalies.length === 0" class="bg-white rounded-2xl shadow-sm p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">No Anomalies Detected</h3>
                <p class="text-sm text-gray-500">No suspicious patterns found in the selected date range.</p>
            </div>

            <!-- Anomaly Table -->
            <div v-else class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase">Severity</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase">Type</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500 text-xs uppercase">Message</th>
                                <th class="text-right px-4 py-3 font-medium text-gray-500 text-xs uppercase">Amount</th>
                                <th class="text-right px-4 py-3 font-medium text-gray-500 text-xs uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(anomaly, i) in anomalies" :key="i" class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span
                                        :class="{
                                            'px-2 py-0.5 rounded-full text-xs font-medium': true,
                                            'bg-red-100 text-red-700': anomaly.severity === 'high',
                                            'bg-amber-100 text-amber-700': anomaly.severity === 'medium',
                                            'bg-gray-100 text-gray-600': anomaly.severity === 'low',
                                        }"
                                    >
                                        {{ anomaly.severity }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 capitalize">{{ anomaly.type.replace(/_/g, ' ') }}</td>
                                <td class="px-4 py-3 text-gray-800 max-w-md truncate" :title="anomaly.message">{{ anomaly.message }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">{{ anomaly.amount ? formatMoney(anomaly.amount) : '-' }}</td>
                                <td class="px-4 py-3 text-right text-gray-500 whitespace-nowrap">{{ anomaly.date || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';

interface Anomaly {
    type: string;
    severity: string;
    message: string;
    reference_id: number | null;
    reference_type: string;
    reference_number: string | null;
    amount: number;
    date: string | null;
}

interface Summary {
    total: number;
    high: number;
    medium: number;
    low: number;
    by_type: Record<string, number>;
}

interface Props {
    anomalies: Anomaly[];
    summary: Summary;
    filters: {
        from: string;
        to: string;
    };
}

const props = defineProps<Props>();

function formatMoney(amount: number): string {
    return 'K' + amount.toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateFilter(key: 'from' | 'to', value: string) {
    router.get(
        route('growfinance.anomalies.index'),
        { ...props.filters, [key]: value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function refresh() {
    router.get(
        route('growfinance.anomalies.index'),
        props.filters,
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
</script>
