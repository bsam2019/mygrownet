<template>
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <h3 class="text-lg font-medium mb-6">Platform Performance</h3>
            
            <!-- Platform Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-80">Monthly Investment</p>
                            <p class="text-2xl font-bold mt-1">{{ formatCurrency(stats.monthly_investment) }}</p>
                        </div>
                        <div class="p-2 bg-white/10 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm mt-2 opacity-80">
                        <span :class="stats.investment_growth >= 0 ? 'text-green-300' : 'text-red-300'">
                            {{ stats.investment_growth >= 0 ? '↑' : '↓' }} {{ Math.abs(stats.investment_growth) }}%
                        </span>
                        vs last month
                    </p>
                </div>

                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-80">User Growth</p>
                            <p class="text-2xl font-bold mt-1">{{ stats.new_users }}</p>
                        </div>
                        <div class="p-2 bg-white/10 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm mt-2 opacity-80">{{ stats.user_growth }}% growth rate</p>
                </div>

                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-80">Success Rate</p>
                            <p class="text-2xl font-bold mt-1">{{ stats.success_rate }}%</p>
                        </div>
                        <div class="p-2 bg-white/10 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm mt-2 opacity-80">Based on {{ stats.completed_investments }} investments</p>
                </div>
            </div>

            <!-- Category Distribution -->
            <div class="mt-8">
                <h4 class="text-sm font-medium text-gray-700 mb-4">Investment Categories Distribution</h4>
                <div class="space-y-4">
                    <div v-for="category in investmentDistribution" :key="category.id" class="relative">
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-600">{{ category.name }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ formatCurrency(category.total_value) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full"
                                 :style="{ width: category.percentage + '%', backgroundColor: category.color }">
                            </div>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-xs text-gray-500">{{ category.count }} investments</span>
                            <span class="text-xs text-gray-500">{{ category.percentage }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { formatCurrency } from '@/utils/formatting';

defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            monthly_investment: 0,
            investment_growth: 0,
            new_users: 0,
            user_growth: 0,
            success_rate: 0,
            completed_investments: 0
        })
    },
    investmentDistribution: {
        type: Array,
        required: true,
        default: () => []
    }
});
</script>