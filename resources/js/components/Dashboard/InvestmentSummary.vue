<template>
    <div class="grid gap-4 md:grid-cols-3">
        <!-- Total Investments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Total Invested</h3>
                    <p class="mt-1 text-3xl font-bold text-blue-600">{{ formatKwacha(summary.total_invested) }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">Across {{ summary.total_investments }} investments</p>
        </div>

        <!-- Active Investments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Active Investments</h3>
                    <p class="mt-1 text-3xl font-bold text-green-600">{{ formatKwacha(summary.active_investments) }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">Currently earning returns</p>
        </div>

        <!-- Total Returns -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Total Returns</h3>
                    <p class="mt-1 text-3xl font-bold text-purple-600">{{ formatKwacha(summary.total_returns) }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">Total earnings to date</p>
        </div>
    </div>

    <!-- Recent Returns -->
    <div class="mt-6 bg-white rounded-lg shadow">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Recent Returns</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Investment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="return_item in recentReturns" :key="return_item.id">
                            <td class="px-6 py-4 whitespace-nowrap">{{ return_item.investment_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ formatKwacha(return_item.amount) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ return_item.date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="{
                                    'px-2 py-1 text-xs rounded-full': true,
                                    'bg-green-100 text-green-800': return_item.status === 'paid',
                                    'bg-yellow-100 text-yellow-800': return_item.status === 'pending'
                                }">
                                    {{ return_item.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="recentReturns.length === 0">
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No recent returns
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { formatKwacha, formatPercent } from '@/utils/format';

defineProps({
    summary: {
        type: Object,
        required: true
    },
    recentReturns: {
        type: Array,
        required: true
    }
});
</script>