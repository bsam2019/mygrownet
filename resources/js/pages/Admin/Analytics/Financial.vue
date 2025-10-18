<template>
    <AdminLayout title="Financial Reports">
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                Financial Reports & Analytics
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Key Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                                        <dd class="text-lg font-medium text-gray-900">K{{ formatNumber(stats.total_subscription_revenue) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">This Month</dt>
                                        <dd class="text-lg font-medium text-gray-900">K{{ formatNumber(stats.monthly_subscription_revenue) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Commissions</dt>
                                        <dd class="text-lg font-medium text-gray-900">K{{ formatNumber(stats.total_commissions) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Monthly Commissions</dt>
                                        <dd class="text-lg font-medium text-gray-900">K{{ formatNumber(stats.monthly_commissions) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Net Revenue</dt>
                                        <dd class="text-lg font-medium text-gray-900">K{{ formatNumber(stats.net_revenue) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue by Package -->
                <div class="bg-white shadow rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Revenue by Package</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscriptions</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg per Sub</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% of Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="pkg in revenue_by_package" :key="pkg.name">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ pkg.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatNumber(pkg.subscriptions) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">K{{ formatNumber(pkg.revenue) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">K{{ formatNumber(pkg.revenue / pkg.subscriptions) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ ((pkg.revenue / stats.total_subscription_revenue) * 100).toFixed(1) }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Revenue Trend -->
                <div class="bg-white shadow rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Revenue Trend (Last 12 Months)</h3>
                        <div class="overflow-x-auto">
                            <div class="flex items-end space-x-2 h-64">
                                <div v-for="month in revenue_trend" :key="month.date" class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-green-600 rounded-t hover:bg-green-700 transition-colors cursor-pointer" 
                                         :style="{ height: (month.revenue / Math.max(...revenue_trend.map(m => m.revenue)) * 100) + '%' }"
                                         :title="`${month.date}: K${formatNumber(month.revenue)}`">
                                    </div>
                                    <div class="text-xs text-gray-600 mt-2 transform -rotate-45 origin-top-left whitespace-nowrap">
                                        {{ month.date }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Health Indicators -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white shadow rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Commission Ratio</h4>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ ((stats.total_commissions / stats.total_subscription_revenue) * 100).toFixed(1) }}%
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Of total revenue paid as commissions</p>
                    </div>

                    <div class="bg-white shadow rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Net Margin</h4>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ ((stats.net_revenue / stats.total_subscription_revenue) * 100).toFixed(1) }}%
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Revenue after commissions</p>
                    </div>

                    <div class="bg-white shadow rounded-lg p-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Monthly Growth</h4>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ revenue_trend.length > 1 ? (((revenue_trend[revenue_trend.length - 1].revenue - revenue_trend[revenue_trend.length - 2].revenue) / revenue_trend[revenue_trend.length - 2].revenue) * 100).toFixed(1) : 0 }}%
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Month-over-month change</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{
    stats: {
        total_subscription_revenue: number;
        monthly_subscription_revenue: number;
        total_commissions: number;
        monthly_commissions: number;
        net_revenue: number;
    };
    revenue_by_package: Array<{
        name: string;
        revenue: number;
        subscriptions: number;
    }>;
    revenue_trend: Array<{
        date: string;
        revenue: number;
    }>;
}>();

const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(Math.round(num));
};
</script>
