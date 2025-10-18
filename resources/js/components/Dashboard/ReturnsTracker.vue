<template>
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-6">Returns Performance</h3>
            
            <!-- Performance Metrics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Monthly Returns</p>
                    <p class="mt-1 text-xl font-semibold">{{ formatCurrency(metrics.monthly_returns) }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Quarterly Returns</p>
                    <p class="mt-1 text-xl font-semibold">{{ formatCurrency(metrics.quarterly_returns) }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">YTD Returns</p>
                    <p class="mt-1 text-xl font-semibold">{{ formatCurrency(metrics.ytd_returns) }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Total Returns</p>
                    <p class="mt-1 text-xl font-semibold">{{ formatCurrency(metrics.total_returns) }}</p>
                </div>
            </div>

            <!-- Returns Schedule -->
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Upcoming Returns</h4>
                <div class="space-y-3">
                    <div v-for="schedule in upcomingReturns" :key="schedule.id"
                         class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium">{{ schedule.investment_name }}</p>
                            <p class="text-sm text-gray-500">Expected: {{ formatCurrency(schedule.expected_amount) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium">{{ schedule.due_date }}</p>
                            <p class="text-xs text-gray-500">{{ schedule.days_remaining }} days remaining</p>
                        </div>
                    </div>
                    <p v-if="upcomingReturns.length === 0" class="text-gray-500 text-center py-4">
                        No upcoming returns scheduled
                    </p>
                </div>
            </div>

            <!-- Monthly Performance Chart -->
            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Monthly Performance</h4>
                <div class="h-64 bg-gray-50 rounded-lg p-4">
                    <!-- Chart placeholder - implement with your preferred charting library -->
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        Chart will be implemented here
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { formatCurrency } from '@/utils/formatting';

defineProps({
    metrics: {
        type: Object,
        required: true,
        default: () => ({
            monthly_returns: 0,
            quarterly_returns: 0,
            ytd_returns: 0,
            total_returns: 0
        })
    },
    upcomingReturns: {
        type: Array,
        required: true,
        default: () => []
    }
});
</script>