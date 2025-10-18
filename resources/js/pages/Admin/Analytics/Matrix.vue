<template>
    <AdminLayout title="Matrix Analytics">
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">Matrix & Network Analytics</h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Key Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Positions</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ formatNumber(stats.total_positions) }}</dd>
                        </dl>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Filled Positions</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ formatNumber(stats.filled_positions) }}</dd>
                        </dl>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Fill Rate</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ stats.fill_rate }}%</dd>
                        </dl>
                    </div>
                    <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Empty Positions</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ formatNumber(stats.empty_positions) }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Matrix Levels Breakdown -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Matrix Positions by Level</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filled</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fill Rate</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="level in positions_by_level" :key="level.level">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">Level {{ level.level }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatNumber(level.total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatNumber(level.filled) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ level.fill_rate }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Sponsors -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Top Sponsors</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="sponsor in top_sponsors" :key="sponsor.id" class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm font-medium">{{ sponsor.name }}</div>
                            <div class="text-lg font-bold text-blue-600">{{ sponsor.referral_count }} referrals</div>
                        </div>
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
    positions_by_level: any[];
    network_growth: any[];
    top_sponsors: any[];
}>();

const formatNumber = (num: number) => new Intl.NumberFormat().format(num);
</script>
