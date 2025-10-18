<template>
    <AdminLayout title="Points Analytics">
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">Points System Analytics</h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Key Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total LP Awarded</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ formatNumber(stats.total_lp) }}</dd>
                        </dl>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total MAP Awarded</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ formatNumber(stats.total_map) }}</dd>
                        </dl>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">This Month LP</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ formatNumber(stats.this_month_lp) }}</dd>
                        </dl>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Qualification Rate</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ stats.qualification_rate }}%</dd>
                        </dl>
                    </div>
                </div>

                <!-- Level Distribution -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Professional Level Distribution</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div v-for="level in level_distribution" :key="level.level" class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ level.count }}</div>
                            <div class="text-sm font-medium text-gray-900 capitalize">{{ level.level }}</div>
                            <div class="text-xs text-gray-500">{{ level.name }}</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Point Transactions</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">LP</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">MAP</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="transaction in recent_transactions" :key="transaction.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ transaction.user_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">{{ transaction.source.replace('_', ' ') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ transaction.lp_amount }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ transaction.map_amount }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{
    stats: any;
    level_distribution: any[];
    top_sources: any[];
    recent_transactions: any[];
    daily_trend: any[];
}>();

const formatNumber = (num: number) => new Intl.NumberFormat().format(num);
</script>
