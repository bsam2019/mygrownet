<template>
    <div class="grid gap-4 md:grid-cols-3">
        <!-- Total Investments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Total Investment Value</h3>
                    <p class="mt-1 text-3xl font-bold text-blue-600">{{ formatCurrency(summary.total_value) }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">From {{ summary.total_count }} investors</p>
        </div>

        <!-- Active Investments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Active Investments</h3>
                    <p class="mt-1 text-3xl font-bold text-green-600">{{ formatCurrency(summary.active_value) }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">Value: {{ formatCurrency(summary.active_value) }}</p>
        </div>

        <!-- Platform Revenue -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Platform Revenue</h3>
                    <p class="mt-1 text-3xl font-bold text-purple-600">{{ formatCurrency(summary.revenue) }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">{{ summary.revenue_growth }}% growth this month</p>
        </div>
    </div>
</template>

<script setup>
import { formatCurrency } from '@/utils/format';

defineProps({
    summary: {
        type: Object,
        required: true,
        default: () => ({
            total_value: 0,
            total_count: 0,
            active_count: 0,
            active_value: 0,
            revenue: 0,
            revenue_growth: 0
        })
    }
});
</script>